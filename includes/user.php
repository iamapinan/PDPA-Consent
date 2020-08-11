<?php
/***
 * User functions
 *
 * Package: PDPA_Consent
 * (c) Apinan Woratrakun <iamapinan@gmail.com>
 */

Class PDPA_User {
    function __construct() {
        $this->load();
    }

    function load() {
        add_shortcode('pdpa_user_page', [$this, 'user_page_shortcode']);
    }

    function load_script()
    {
        $plugin_info = get_plugin_data(PDPA_PATH . 'pdpa-consent.php');
    
        wp_enqueue_style('pdpa-consent-user', plugins_url('pdpa-consent/assets/pdpa-consent-user.css'), array(), $plugin_info['Version']);
        wp_enqueue_script('pdpa-consent-user', plugins_url('pdpa-consent/assets/pdpa-consent-user.js'), array(), $plugin_info['Version'], true);
    }

    function user_page_shortcode($atts)
    {
        $user_id = get_current_user_id();
        $isLogin = is_user_logged_in();
        $user_info = get_userdata($user_id);
        $options = get_option('pdpa_option');
        $this->load_script();

        $users = get_users( array( 'fields' => array( 'ID' ) ) );
        foreach($users as $user){
            $user_info->meta = get_user_meta( $user->ID );
        }

        render_template('user_template', [
            'user_id'   => $user_id,
            'user_info' => $user_info,
            'is_login'  => $isLogin,
            'options' => $options,
        ]);
    }
}
