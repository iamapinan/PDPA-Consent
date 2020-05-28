<?php
/***
 * Package: PDPA_Consent
 * 
 * by Apinan Woratrakun
 */

function render_template($file, $vars = null) {
    if (!is_null($vars))
        extract($vars);
        
    include_once( PDPA_PATH . "includes/frontends/$file.php" );
}
?>