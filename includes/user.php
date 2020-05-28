<?php
/***
 * User functions
 *
 * Package: PDPA_Consent
 * (c) Apinan Woratrakun <iamapinan@gmail.com>
 */

function load_script()
{
    wp_enqueue_style('pdpa-consent-user', plugins_url('pdpa-consent/assets/pdpa-consent-user.css'), array(), date('m', time()));
    wp_enqueue_script('pdpa-consent-user', plugins_url('pdpa-consent/assets/pdpa-consent-user.js'), array(), date('m', time()));
}

function user_page_shortcode($atts)
{
    load_script();

    $user_id = get_current_user_id();
    $isLogin = is_user_logged_in();
    $user_info = get_userdata($user_id);

    render_template('user_template', [
        'user_id'   => $user_id,
        'user_info' => $user_info,
        'is_login'  => $isLogin
    ]);
}

add_shortcode('pdpa_user_page', 'user_page_shortcode');
