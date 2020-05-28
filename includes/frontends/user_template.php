<div class="pdpa_user_warp">
    <?php if ($is_login):?>
        <div class="profile-header-top">
            <?php echo get_avatar($user_id);?>
            <div class="user_title">
                <h3><?php echo $user_info->display_name;?></h3>
                <div class="status_bage">
                    <?php echo get_user_meta($user_id, 'pdpa_status', true);?>
                </div>
                <p>
                    <?php echo _e('Timestamp', 'pdpa-consent') .'&nbsp;'. date('d/m/Y H:i', get_user_meta($user_id, 'pdpa_status_time', true));?>
                </p>
            </div>
        </div>
        <div class="user_toolbar">
            <button><?php _e('Download profile data', 'pdpa-consent');?></button>
            <button id="pdpa-status-reset"><?php _e('Reset consent', 'pdpa-consent');?></button>
            <button><?php _e('Delete account', 'pdpa-consent');?></button>
        </div>
        <pre>
            <?php print_r($user_info->data);?>
        </pre>
    <?php else: ?>
        <div class="user_toolbar">
            <button id="pdpa-status-reset"><?php _e('Reset consent', 'pdpa-consent');?></button>
        </div>
    <?php endif;?>
</div>