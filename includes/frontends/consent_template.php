<div class="pdpa-consent-wrap pdpa-place-<?php esc_attr_e($options['popup_type']); ?> <?php echo $options['is_darkmode'] ? 'pdpa-darkmode' : ''; ?>" id="pdpa_screen">
    <div class="pdpa-consent-text">
        <?php esc_html_e($options['popup_message']); ?>
        <a href="/?p=<?php echo $page_id; ?>"><?php _e('Read term and privacy policy', 'pdpa-consent'); ?></a>
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