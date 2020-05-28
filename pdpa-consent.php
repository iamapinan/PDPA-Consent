<?php
/**
 * @package PDPAConsent
 */
/*
Plugin Name: PDPA Consent
Description: PDPA Consent allows you to notify to the user to accept privacy terms. Comply with Thailand PDPA law.
Version: 1.0.8
Author: Apinan Woratrakun
Author URI: https://www.facebook.com/9apinan
Plugin URI: https://github.com/iamapinan/PDPA-Consent
License: GNU License
License URI: https://opensource.org/licenses/lgpl-3.0.html
Text Domain: pdpa-consent
Domain Path: /languages
*/

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

// Check get_plugin_data exists.
if (!function_exists('get_plugin_data')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

define('PDPA_PATH', plugin_dir_path(__FILE__));
// Includes
include_once(PDPA_PATH . 'includes/admin.php');
include_once(PDPA_PATH . 'includes/template.php');
// include_once(PDPA_PATH . 'includes/user.php');

class PDPA_Consent
{
    private $ver;
    private $show_popup = true;
    private $consent_popup_title = '';
    private $consent_popup_button_text = '';
    private $plugin_info = array();
    private $admin;
    private $locale;
    public $cookie_domain;
    public $cookie_expire;
    public $cookie_name = 'pdpa_accepted';

    public function __construct()
    {
        $this->plugin_info = get_plugin_data(PDPA_PATH . 'pdpa-consent.php');
        $this->locale = is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
        $this->cookie_domain = parse_url(site_url())['host'];
        $this->cookie_expire = strtotime("next Month");
        $this->options = get_option('pdpa_option');

        $this->init();
        new pdpa_consent_admin_option;
    }

    public function init()
    {
        register_activation_hook(__FILE__, array( $this, 'plugin_activate' ));

        add_action('admin_init', array( $this, 'load_plugin' ));
        add_filter('body_class', array( $this, 'change_body_class' ));
        // Add consent html to frontend
        if (function_exists('wp_body_open')) {
            add_action('wp_body_open', array( $this, 'add_consent'), 20);
        }

        // Ajax request for logged in user
        add_action('wp_ajax_pdpa_action', array( $this, 'pdpa_ajax_do' ));
        // Ajax request for guest user
        add_action('wp_ajax_nopriv_pdpa_action', array( $this, 'pdpa_ajax_do' ));

        // Add scripts
        add_action('wp_enqueue_scripts', array( $this, 'pdpa_enqueue_scripts' ));
        // Add functions to users
        add_filter('manage_users_columns', array( $this, 'pdpa_add_user_columns' ));
        add_filter('manage_users_custom_column', array( $this, 'pdpa_add_user_column_data' ), 10, 3);
    }

    public function plugin_activate()
    {
        add_option('Activated_Plugin', $this->plugin_info['TextDomain']);
    }

    public function load_plugin()
    {
        if (!function_exists('wp_body_open')) {
            add_action('admin_notices', array( $this, 'version_not_support_notice' ));
        }

        if (is_admin() && get_option('Activated_Plugin') == $this->plugin_info['TextDomain']) {
            $this->generate_pdpa_user_page();
            add_action('admin_notices', array( $this, 'setup_admin_notice' ));
        }
    }

    public function setup_admin_notice()
    {
        if (!get_option('pdpa-consent-page-id')) {
            echo '<div class="notice notice-warning is-dismissible">
                <p>'.__('Please setup PDPA Consent setting in <a href="/wp-admin/admin.php?page=pdpa-consent">plugin page.</a>', 'pdpa-consent').'</p>
            </div>';
        }
    }

    public function version_not_support_notice()
    {
        if (!get_option('pdpa-consent-page-id')) {
            echo '<div class="notice notice-error is-dismissible">
                <p>'.__('PDPA not support WordPress version older than 5.2. Please update your website.', 'pdpa-consent').'</p>
            </div>';
        }
    }

    public function generate_pdpa_user_page()
    {
        if (!get_option('pdpa-consent-user_privacy-page')) {
            $page_details = array(
                'post_title'    => __('User Privacy', 'pdpa-consent'),
                'post_name'     => 'pdpa-user-privacy',
                'post_content'  => '[pdpa_user_page]',
                'post_status'   => 'publish',
                'post_author'   => 1,
                'post_type'     => 'page'
            );
    
            $page_id = wp_insert_post($page_details);
            add_option('pdpa-consent-user_privacy-page', $page_id);

            printf(
                '<div class="notice notice-info is-dismissible"><p>%s <a href="/?p='.$page_id.'">%s</a></p></div>',
                __('User privacy page is created', 'pdpa-consent'),
                __('View page', 'pdpa-consent')
            );
        }
    }

    public function pdpa_enqueue_scripts()
    {
        wp_enqueue_style('pdpa-consent', plugins_url('assets/pdpa-consent.css', __FILE__), array(), $this->plugin_info['Version']);
        
        // Register the script
        wp_enqueue_script('pdpa_axios', plugins_url('assets/axios.min.js', __FILE__), array(), $this->plugin_info['Version'], true);
        wp_register_script('pdpa_ajax_handle', plugins_url('assets/pdpa-consent.js', __FILE__), array(), $this->plugin_info['Version'], true);
        
        // Localize the script with new data
        $ajax_array = array(
            'ajax_url'      => admin_url('admin-ajax.php'),
            'pdpa_nonce'    => wp_create_nonce('pdpa-security'),
            'consent_enable'=> ($this->options['is_enable'] && !$this->pdpa_cookies_set()) ? 'yes' : 'no',
            'current_user'  => is_user_logged_in() ? get_current_user_id() : 'guest',
            'pdpa_version'  => $this->plugin_info['Version']
        );

        wp_localize_script('pdpa_ajax_handle', 'pdpa_ajax', $ajax_array);
        
        // Enqueued script with localized data.
        wp_enqueue_script('pdpa_ajax_handle');
    }
    /**
     * Check is cookie set
     */
    public function pdpa_cookies_set()
    {
        return isset($_COOKIE['pdpa_accepted']) ? true : false;
    }
    /**
     * Check is accept
     */
    public function pdpa_cookies_accepted()
    {
        return (isset($_COOKIE['pdpa_accepted']) && $_COOKIE['pdpa_accepted'] === 1) ? true : false;
    }

    /**
     * Add WP Super Cache cookie.
     */
    public function wpsc_set_cookie()
    {
        do_action('wpsc_add_cookie', 'pdpa_accepted');
    }

    /**
     * Delete WP Super Cache cookie.
     */
    public function wpsc_delete_cookie()
    {
        do_action('wpsc_delete_cookie', 'pdpa_accepted');
    }

    public function clear_cache()
    {
        //Clean WP Super Cache's cache files?
        if (function_exists('wp_cache_clear_cache')) {
            //Newer WP-Super-Cache
            wp_cache_clear_cache();
        } elseif (file_exists(WP_CONTENT_DIR . '/wp-cache-config.php') && function_exists('prune_super_cache')) {
            //Old WP-Super-Cache
            global $cache_path;
            prune_super_cache($cache_path . 'supercache/', true);
            prune_super_cache($cache_path, true);
        }
    }

    /***
     * Ajax processing
     */
    public function pdpa_ajax_do()
    {
        $response = [];
        $consent_set = sanitize_text_field($_POST['set_status']);

        if (! check_ajax_referer('pdpa-security', 'security', false)) {
            wp_send_json_error('Invalid security token sent.');
            wp_die();
        }

        if (is_user_logged_in()) {
            $current_user = get_current_user_id();
            $pdpa_meta = get_user_meta($current_user, 'pdpa_status', true);

            if ($pdpa_meta == '') {
                add_user_meta($current_user, 'pdpa_status', $consent_set);
                add_user_meta($current_user, 'pdpa_status_time', time());
            } else {
                update_user_meta($current_user, 'pdpa_status', $consent_set);
                update_user_meta($current_user, 'pdpa_status_time', time());
            }
        }
        
        switch ($consent_set) {
            case 'pdpa-allow':
                if (!$this->pdpa_cookies_accepted()) {
                    $this->wpsc_set_cookie();
                }
                $response = [
                    'status' => 'success',
                    'type' => 'user_allow',
                    'cookie_domain' => $this->cookie_domain,
                    'cookie_expire' => gmdate("Y-m-d\TH:i:s\Z", $this->cookie_expire),
                    'cookie_name'   => $this->cookie_name
                ];
            break;
            case 'pdpa-not-allow':
                if ($this->pdpa_cookies_accepted()) {
                    $this->wpsc_delete_cookie();
                }
                $response = [
                    'status' => 'success',
                    'type' => 'user_not_allow',
                    'cookie_domain' => $this->cookie_domain,
                    'cookie_expire' => gmdate("Y-m-d\TH:i:s\Z", $this->cookie_expire),
                    'cookie_name'   => $this->cookie_name
                ];
            break;
            case 'pdpa-reset':
                if ($this->pdpa_cookies_accepted()) {
                    $this->wpsc_delete_cookie();
                }
                $response = [
                    'status' => 'success',
                    'type' => 'reset',
                    'cookie_domain' => $this->cookie_domain,
                    'cookie_expire' => gmdate("Y-m-d\TH:i:s\Z", 0),
                    'cookie_name'   => $this->cookie_name
                ];
            break;
        }
        // clear wordpress cache.
        $this->clear_cache();
        
        wp_send_json($response, 200);
        wp_die();
    }

    public function change_body_class($classes)
    {
        if (is_admin()) {
            return $classes;
        }

        if ($this->pdpa_cookies_set()) {
            $classes[] = 'pdpa-set';

            if ($this->pdpa_cookies_accepted()) {
                $classes[] = 'pdpa-accepted';
            } else {
                $classes[] = 'pdpa-refused';
            }
        } else {
            $classes[] = 'pdpa-not-set';
        }

        return $classes;
    }

    public function add_consent()
    {
        $page_id = get_option('pdpa-consent-page-id');
        if ($this->options['is_enable'] && !$this->pdpa_cookies_set()) {
            render_template('consent_template', [
                'options' => $this->options,
                'page_id' => $page_id
            ]);
        }
    }

    //add columns to User panel list page
    public function pdpa_add_user_columns($column)
    {
        $column['pdpa_status'] = __('Consent Allow', 'pdpa-consent');
        return $column;
    }
    
    //add the data
    public function pdpa_add_user_column_data($val, $column_name, $user_id)
    {
        $status = get_user_meta($user_id, 'pdpa_status', true);
        if ($status == '' || $status == 'pdpa-reset') {
            return '<span class="dashicons dashicons-warning"  style="color: gray" title="'.__('Waiting for consent.', 'pdpa-consent').'"></span> '.__('Waiting.', 'pdpa-consent');
        } else {
            return ($status == 'pdpa-not-allow') ? '<span class="dashicons dashicons-dismiss" style="color: red" title="'.__('Not allow.', 'pdpa-consent').'"></span> '.__('Not allow.', 'pdpa-consent') : '<span class="dashicons dashicons-yes-alt"  style="color: green" title="'.__('Allow', 'pdpa-consent').'"></span> '.__('Allow.', 'pdpa-consent');
        }
    }
}

/**
 * Initialize PDPA Consent.
 */
add_action('init', new PDPA_Consent);
