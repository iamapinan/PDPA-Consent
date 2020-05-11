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
Plugin URI: http://www.ioblog.me/pdpa-consent-plugin
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


class PDPA_Consent {
    private $ver;
    private $show_popup = true;
    private $consent_popup_title = '';
    private $consent_popup_button_text = '';

    public function __construct () {

    }


}