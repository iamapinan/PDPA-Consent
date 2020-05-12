<?php
/**
 * @package PDPAConsent
 */
/*
Plugin Name: PDPA Consent
Description: PDPA Consent allows you to notify to the user to accept privacy terms. Comply with Thailand PDPA law.
Version: 1.0.0
Author: Apinan Woratrakun, Aeknarin Sirisub
Author URI: https://www.ioblog.me
Plugin URI: https://github.com/iamapinan/PDPA-Consent
License: GNU License
License URI: https://opensource.org/licenses/lgpl-3.0.html
Text Domain: pdpa-consent
Domain Path: /languages
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'PDPA_PATH', plugin_dir_path( __FILE__ ) );
// Include Admin options
include_once( PDPA_PATH . 'admin.php' );
// Check get_plugin_data exists.
if( !function_exists('get_plugin_data') ){
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if(!class_exists('PDPA_Consent')){
    class PDPA_Consent {
        private $ver;
        private $show_popup = true;
        private $consent_popup_title = '';
        private $consent_popup_button_text = '';
        private $plugin_info = array();
        private $admin;
        private $locale;
        private $cookie_domain;
        private $cookie_expire;
        private $cookie_name = 'pdpa_accepted';

        public function __construct () {
            $this->plugin_info = get_plugin_data( PDPA_PATH . 'pdpa-consent.php' );
            $this->locale = get_locale();
            $this->cookie_domain = $_SERVER['SERVER_NAME'];
            $this->cookie_expire = strtotime("next Month");
            $this->options = get_option( '_option_name' );

            $this->initial();
            new AdminOption;
        }

        public function initial() {
            add_filter( 'body_class', array( $this, 'change_body_class' ) );
            add_action( 'wp_body_open', array($this, 'add_consent') );
            add_action( 'wp_ajax_pdpa_action', array( $this, 'pdpa_action' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'pdpa_enqueue_scripts' ) );
            add_filter( 'manage_users_columns', array( $this, 'pdpa_add_user_columns' ) );
            add_filter( 'manage_users_custom_column', array( $this, 'pdpa_add_user_column_data' ), 10, 3 );
        }

        function setup_admin_notice(){
            global $pagenow;
            echo '<div class="notice notice-warning is-dismissible">
                <p>Please setup PDPA Consent setting in <a href="/wp-admin/admin.php?page=pdpa-consent">plugin page.</a></p>
            </div>';
        }

        public function pdpa_enqueue_scripts() {
            wp_enqueue_style( 'pdpa-consent', plugins_url( 'assets/pdpa-consent.min.css', __FILE__ ), array(), $this->plugin_info['Version'] );
            
            // Register the script
            wp_register_script( 'pdpa_ajax_handle', plugins_url( 'assets/pdpa-consent.min.js', __FILE__ ), array(), $this->plugin_info['Version'] );
            
            // Localize the script with new data
            $ajax_array = array(
                'ajax_url'      => admin_url( 'admin-ajax.php' ),
                'pdpa_nonce'    => wp_create_nonce('pdpa-security'),
                'consent_enable'=> ( $this->options['is_enable'] && !$this->pdpa_cookies_set() ) ? 'yes' : 'no',
                'current_user'  => get_current_user_id()
            );

            wp_localize_script( 'pdpa_ajax_handle', 'pdpa_ajax', $ajax_array );
            
            // Enqueued script with localized data.
            wp_enqueue_script( 'pdpa_ajax_handle' );
        }
        /**
         * Check is cookie set
         */
        public function pdpa_cookies_set() {
            return isset( $_COOKIE['pdpa_accepted'] ) ? true : false;
        }
        /**
         * Check is accept
         */
        public function pdpa_cookies_accepted() {
            return ( isset( $_COOKIE['pdpa_accepted'] ) && $_COOKIE['pdpa_accepted'] === 1 ) ? true : false;
        }

        /**
         * Add WP Super Cache cookie.
         */
        public function wpsc_set_cookie() {
            do_action( 'wpsc_add_cookie', 'pdpa_accepted' );
        }

        /**
         * Delete WP Super Cache cookie.
         */
        public function wpsc_delete_cookie() {
            do_action( 'wpsc_delete_cookie', 'pdpa_accepted' );
        }
        
        public function pdpa_action () {
            if ( ! check_ajax_referer( 'pdpa-security', 'security', false ) ) {	
                wp_send_json_error( 'Invalid security token sent.' );	    
                wp_die();	  
            }

            $response = [];
            $current_user = get_current_user_id();
            $pdpa_meta = get_user_meta( $current_user, 'pdpa_status', true );

            if($pdpa_meta == '') {
                add_user_meta( $current_user, 'pdpa_status', $_POST['set_status'] );
            } else {
                update_user_meta( $current_user,  'pdpa_status', $_POST['set_status'] );
            }
            
            switch($_POST['set_status']) {
                case 'pdpa-allow':
                    if(!$this->pdpa_cookies_accepted()) {
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
                    if($this->pdpa_cookies_accepted()) {
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
            }
            
            wp_send_json( $response, 200 );
        }

        public function change_body_class( $classes ) {
            if ( is_admin() )
                return $classes;

            if ( $this->pdpa_cookies_set() ) {
                $classes[] = 'pdpa-set';

                if ( $this->pdpa_cookies_accepted() )
                    $classes[] = 'pdpa-accepted';
                else
                    $classes[] = 'pdpa-refused';
            } else
                $classes[] = 'pdpa-not-set';

            return $classes;
        }

        public function add_consent() {
            $page_id = get_option('pdpa-page-id');

            if($this->options['is_enable'] && !$this->pdpa_cookies_set() ):
            ?>
            <style><?php echo $this->options['custom_css'];?></style>
            <div class="consent-wrap place-<?php echo $this->options['popup_type'];?>" id="pdpa_screen">
                <div class="consent-text">
                    <?php esc_attr_e($this->options['popup_message']);?>
                    <a href="/?p=<?php echo $page_id;?>"><?php _e('Read term and privacy policy', 'pdpa-consent');?></a>
                </div>
                <div>
                    <button class="pdpa-consent-allow-button" id="PDPAAllow"><?php _e('Allow', 'pdpa-consent');?></button>
                    <button class='pdpa-consent-not-allow-button' id="PDPANotAllow"><?php _e('Not Allow', 'pdpa-consent');?></button>
                </div>
            </div>
            <?php
            endif;
        }

        //add columns to User panel list page
        function pdpa_add_user_columns($column) {
            $column['pdpa_status'] = __('PDPA Allow', 'pdpa-consent');
            return $column;
        }
        

        //add the data
        function pdpa_add_user_column_data( $val, $column_name, $user_id ) {
            $status = get_user_meta($user_id, 'pdpa_status', true);
            if($status == '') {
                return '<span class="dashicons dashicons-warning"  style="color: gray"></span>';
            } else {
                return ($status == 'pdpa-not-allow') ? '<span class="dashicons dashicons-dismiss" style="color: red"></span>' : '<span class="dashicons dashicons-yes-alt"  style="color: green"></span>';
            }
        }
    }

    /**
     * Initialize PDPA Consent.
     */
    new PDPA_Consent;
}