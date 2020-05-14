<?php
/***
 * User functions
 *
 * Package: pdpa-consent
 * (c) Apinan Woratrakun <iamapinan@gmail.com>
 */

function load_script() {
    wp_enqueue_style('pdpa-consent-user', plugins_url('pdpa-consent/assets/pdpa-consent-user.css'), array(), date('m', time()));
    wp_enqueue_script('pdpa-consent-user', plugins_url('pdpa-consent/assets/pdpa-consent-user.js'), array(), date('m', time()));
}


function user_page_shortcode( $atts ) {
    $user_id = get_current_user_id();
    $user_info = get_userdata($user_id);

    load_script();
    require_once('user_template.php');
}
add_shortcode( 'pdpa_user_page', 'user_page_shortcode' );