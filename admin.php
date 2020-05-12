<?php
/***
 * Admin page
 * 
 * Package: pdpa-consent
 * (c) Apinan Woratrakun <iamapinan@gmail.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit;

Class AdminOption {
    private $plugin_info = array();
    private $capability = 'manage_options';
    private $options;
    private $locale;
    private $page_id;
    private $page_name;

    public function __construct() {
        $this->plugin_info = get_plugin_data( PDPA_PATH . 'pdpa-consent.php' );
        $this->locale = get_locale();
        $this->page_id = get_option('pdpa-page-id') ? get_option('pdpa-page-id') : 0;
        $this->page_name = __('pdpa-term', 'pdpa-consent');
        add_action( 'admin_menu', array($this, 'pdpa_admin_menu') );
        add_action( 'admin_init', array($this, 'admin_option_setup') );
    }

    private function serialize_html($html, $settings = [ 'website_name' => '', 'site_description' => '', 'list_data' => '', 'site_address' => '', 'site_contact' => '', 'site_email' => '' ]) {
        $settings['list_data'] = str_replace("\n", "</li><li>", esc_attr($settings['list_data']) );
        $html = str_replace('[service]', esc_attr($settings['website_name']), $html);
        $html = str_replace('[description]', esc_attr($settings['site_description']), $html);
        $html = str_replace('[list_data]', $settings['list_data'], $html);
        $html = str_replace('[address]', esc_attr($settings['site_address']), $html);
        $html = str_replace('[contact]', esc_attr($settings['site_contact']), $html);
        $html = str_replace('[email]', esc_attr($settings['site_email']), $html);
        return $html;
    }

    public function generate_post_from_template() {
        $this->options = get_option( '_option_name' );
        if(file_exists(PDPA_PATH . 'template/'.$this->locale.'.html')) {
            $content = $this->serialize_html( file_get_contents( PDPA_PATH . 'template/'.$this->locale.'.html'), $this->options );
        } else {
            $content = $this->serialize_html( file_get_contents( PDPA_PATH . 'template/th_TH.html'), $this->options );
        }

        $page_details = array(
            'ID'            => $this->page_id,
            'post_title'    => __('Thailand’s Personal Data Protection Act (PDPA)', 'pdpa-consent'),
            'post_name'     => $this->page_name,
            'post_content'  => $content,
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type' => 'page'
        );
        $page_id = wp_insert_post( $page_details );
        add_option( 'pdpa-page-id', $page_id );
    }

    function pdpa_admin_menu() {
        add_menu_page( $this->plugin_info['Name'], __('PDPA Consent', 'pdpa-consent'), $this->capability, $this->plugin_info['TextDomain'], array($this, 'pdpa_admin_option'), 'dashicons-shield-alt', 81 );
    }

    function pdpa_admin_option() {
        if(isset($_POST)) {
            $this->generate_post_from_template();
        }
        ?>
        <style>.admin-page form{margin-top: 24px;background-color: #fff;padding: 15px;width: 90%;border-radius: 6px;box-shadow: 2px 2px 3px rgba(3,3,3,0.15)}</style>
        <div class="admin-page">
            <form method="post" action="options.php" id="pdpaConsent">
            <?php settings_errors(); ?>
            <?php
                settings_fields( '_pdpa_setting_group' );
                do_settings_sections( 'settings' );
            ?>
            <table class="form-table">
                <tr>
                    <th scope='row'></th>
                    <td><?php submit_button();?></td>
                </tr>
            </table>
            </form>
            <p>Development by <?php echo $this->plugin_info['Author'];?></p>
        </div>
        <?php
    }

    function admin_option_setup() {
        register_setting(
            '_pdpa_setting_group', // option_group
            '_option_name', // option_name
        );
        add_settings_section(
            '_pdpa_setting_section', // id
            __( 'PDPA Consent setup','pdpa-consent' ), // title
            array( $this, '_section_fields' ), // callback
            'settings' // page
        );
    }

    public function _section_fields() {
        $this->options = get_option( '_option_name' );

        if($this->page_id !== 0) {
            add_settings_field(
                '_url_', // id
                __( 'Privacy page','pdpa-consent' ), // title
                array( $this, 'url_callback' ), // callback
                'settings', // page
                '_pdpa_setting_section' // section
            );
        }

        add_settings_field(
            'is_enable', // id
            __( 'Enable consent noti','pdpa-consent' ), // title
            array( $this, 'is_enable_callback' ), // callback
            'settings', // page
            '_pdpa_setting_section' // section
        );
        add_settings_field(
            'popup_type', // id
            __( 'Notification popup type','pdpa-consent' ), // title
            array( $this, 'popup_type_callback' ), // callback
            'settings', // page
            '_pdpa_setting_section' // section
        );
        add_settings_field(
            'popup_message', // id
            __( 'Popup message','pdpa-consent' ), // title
            array( $this, 'popup_message_callback' ), // callback
            'settings', // page
            '_pdpa_setting_section' // section
        );
        add_settings_field(
            'website_name', // id
            __( 'Website name (*)','pdpa-consent' ), // title
            array( $this, 'website_name_callback' ), // callback
            'settings', // page
            '_pdpa_setting_section' // section
        );
        add_settings_field(
            'site_description', // id
            __( 'Web description','pdpa-consent' ), // title
            array( $this, 'description_callback' ), // callback
            'settings', // page
            '_pdpa_setting_section' // section
        );
        add_settings_field(
            'list_data', // id
            __( 'List of user data (*)<br>(one data set per line)','pdpa-consent' ), // title
            array( $this, 'list_data_callback' ), // callback
            'settings', // page
            '_pdpa_setting_section' // section
        );
        add_settings_field(
            'site_address', // id
            __( 'Address (*)','pdpa-consent' ), // title
            array( $this, 'address_callback' ), // callback
            'settings', // page
            '_pdpa_setting_section' // section
        );
        add_settings_field(
            'site_contact', // id
            __( 'Contact','pdpa-consent' ), // title
            array( $this, 'contact_callback' ), // callback
            'settings', // page
            '_pdpa_setting_section' // section
        );
        add_settings_field(
            'site_email', // id
            __( 'Email (*)','pdpa-consent' ), // title
            array( $this, 'email_callback' ), // callback
            'settings', // page
            '_pdpa_setting_section' // section
        );
        add_settings_field(
            'custom_css', // id
            __( 'Custom CSS','pdpa-consent' ), // title
            array( $this, 'custom_css_callback' ), // callback
            'settings', // page
            '_pdpa_setting_section' // section
        );
    }

    function url_callback() {
        printf(
            '<a href="/?p=%s">%s</a>' ,
            $this->page_id,
            get_site_url().'/'.$this->page_name
        );
    }
    function is_enable_callback() {
        printf(
            '<input type="checkbox" name="_option_name[is_enable]" id="is_enable" value="1" %s>' ,
            $this->options['is_enable'] == true ? 'checked' : ''
        );
    }
    function popup_type_callback() {
        ?>
            <select name="_option_name[popup_type]">
                <option value="center" <?php echo $this->options['popup_type'] == 'center' ? 'selected' : '';?>><?php _e('Center popup', 'pdpa-consent');?></option>
                <option value="bottom" <?php echo $this->options['popup_type'] == 'bottom' ? 'selected' : '';?>><?php _e('Bottom bar', 'pdpa-consent');?></option>
                <option value="top" <?php echo $this->options['popup_type'] == 'top' ? 'selected' : '';?>><?php _e('Top bar', 'pdpa-consent');?></option>
            </select>
        <?php
    }
    
    function popup_message_callback() {
        printf(
            '<textarea class="regular-text" rows=4 name="_option_name[popup_message]" id="popup_message" required>%s</textarea>' ,
            isset( $this->options['popup_message'] ) ? esc_attr( $this->options['popup_message']) : __('Your privacy is important to us. We need your data just for the important process of services. Please allow if you accept the term of privacy included PDPA compiled.', 'pdpa-consent')
        );
    }

    function website_name_callback() {
        printf(
            '<input class="regular-text" type="text" name="_option_name[website_name]" id="website_name" value="%s" placeholder="%s" required>' ,
            isset( $this->options['website_name'] ) ? esc_attr( $this->options['website_name']) : '',
            __('Your website name or Company name', 'pdpa-consent')
        );
    }

    function description_callback() {
        printf(
            '<input class="regular-text" type="text" name="_option_name[site_description]" id="site_description" value="%s">' ,
            isset( $this->options['site_description'] ) ? esc_attr( $this->options['site_description']) : '',
        );
    }

    function list_data_callback() {
        printf(
            '<textarea class="regular-text" rows=5 name="_option_name[list_data]" id="list_data" placeholder="%s" required>%s</textarea>' ,
            __("Fullname\nBirthday\nEtc."),
            isset( $this->options['list_data'] ) ? esc_attr( $this->options['list_data']) : ''
        );
    }

    function address_callback() {
        printf(
            '<input class="regular-text" type="text" name="_option_name[site_address]" id="site_address" value="%s" required>' ,
            isset( $this->options['site_address'] ) ? esc_attr( $this->options['site_address']) : '',
        );
    }

    function contact_callback() {
        printf(
            '<input class="regular-text" type="text" name="_option_name[site_contact]" id="site_contact" value="%s" placeholder="%s">' ,
            isset( $this->options['site_contact'] ) ? esc_attr( $this->options['site_contact']) : '',
            __("Such as John Doe (081-111-1111)", 'pdpa-consent')
        );
    }

    function email_callback() {
        printf(
            '<input class="regular-text" type="text" name="_option_name[site_email]" id="site_description" value="%s" required>' ,
            isset( $this->options['site_email'] ) ? esc_attr( $this->options['site_email']) : '',
        );
    }

    function custom_css_callback() {
        printf(
            '<textarea class="regular-text" rows=10 name="_option_name[custom_css]" id="custom_css" placeholder="%s">%s</textarea>',
            ".consent-wrap {}\n.place-top {}\n.place-center {}\n.place-bottom {}\n.pdpa-consent-not-allow-button {}\n.pdpa-consent-allow-button {}",
            isset( $this->options['custom_css'] ) ? esc_attr( $this->options['custom_css']) : ''
        );
    }
}