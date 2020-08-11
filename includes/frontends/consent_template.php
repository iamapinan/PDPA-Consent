<div class="pdpa-consent-wrap pdpa-place-<?php esc_attr_e($options['popup_type']); ?> <?php echo $options['is_darkmode'] ? 'pdpa-darkmode' : ''; ?>" id="pdpa_screen">
    <h3>Permission to <?php echo get_bloginfo('name');?></h3>
    <div class="pdpa-consent-text">
        <?php esc_html_e($options['popup_message']); ?>
        <a href="/?p=<?php echo $page_id; ?>"><?php _e('Read term and privacy policy', 'pdpa-consent'); ?></a>
    </div>
        <div class="pdpa-consent-select">
            <div class="pdpa_user_mail">
                <label for="pdpa_mail"><?php _e('Your email *', 'pdpa-consent');?><label>
                <input type="email" value="<?php echo $current_user->user_email;?>" name="user_mail" class="pdpa_user_mail_input" id="pdpa_mail">
            </div>
            <b>ข้อมูลส่วนบุคคลส่วนที่ 1 <a href="#" class="dashicons info" title="If allow below permission will make application working correctly.">?</a></b>
            <ul>
            <?php
            $permissions = explode("\n", $options['list_data']);
            foreach($permissions as $items){
                $item_id = strtolower(str_replace(' ', '_', esc_attr($items)));
                printf("<li><div class='pdpa-option-checkbox'><input type='checkbox' class='pdpa_direct_permission' name='permissions_direct[]' value='%s' id='%s'><label for='%s'>%s</label></div><span class='permission_desc'>นำไปใช้สำหรับ...</span></li>", $item_id, $item_id, $item_id, esc_attr($items));
            }
            ?>
            </ul>
        </div>
        <div class="pdpa-consent-select">
            <b>ข้อมูลส่วนบุคคลส่วนที่ 2 <a href="#" class="dashicons info" title="If allow below permission will make application working correctly.">?</a></b>
            <ul>
            <?php
            $permissions = explode("\n", $options['list_data2']);
            foreach($permissions as $items){
                $item_id = strtolower(str_replace(' ', '_', esc_attr($items)));
                printf("<li><div class='pdpa-option-checkbox'><input type='checkbox' class='pdpa_none_direct_permission' name='permissions_none_direct[]' value='%s' id='%s_2'><label for='%s_2'>%s</label></div><span class='permission_desc'>นำไปใช้สำหรับ...</span></li>", $item_id, $item_id, $item_id, esc_attr($items));
            }
            ?>
            </ul>
        </div>
    <div>
        <button class="pdpa-consent-allow-button" id="PDPAAllow" 
        <?php
        if ($options['allow_button_color'] != '') {
            ?>
            style="background-color: <?php esc_attr_e($options['allow_button_color']); ?>"
            <?php
        } ?>
        ><?php _e('Allow', 'pdpa-consent'); ?></button>
        <button class='pdpa-consent-not-allow-button' id="PDPANotAllow"
        <?php
        if ($options['not_allow_button_color'] != '') {
            ?>
            style="background-color: <?php esc_attr_e($options['not_allow_button_color']); ?>"
            <?php
        } ?>
        ><?php _e('Not Allow', 'pdpa-consent'); ?></button>
    </div>
</div>