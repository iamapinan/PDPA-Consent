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

    public function __construct() {
        $this->plugin_info = get_plugin_data( PDPA_PATH . 'pdpa-consent.php' );

        add_action( 'admin_menu', array($this, 'pdpa_admin_menu') );
        add_action( 'admin_init', array($this, 'admin_option_setup') );
    }

    function pdpa_admin_menu() {
        add_menu_page( $this->plugin_info['Name'], __('PDPA Consent', 'pdpa-consent'), $this->capability, $this->plugin_info['TextDomain'], array($this, 'pdpa_admin_option'), 'dashicons-shield-alt', 81 );
    }

    function admin_option_setup() {
    }

    function pdpa_admin_option() {

    }
}