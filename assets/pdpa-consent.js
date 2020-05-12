/***
 * Package: pdpa-consent
 * (c) Apinan Woratrakun <iamapinan@gmail.com>
 */
console.log(pdpa_ajax.consent_enable)
if(pdpa_ajax.consent_enable == 'yes') {
document.addEventListener("DOMContentLoaded", function() {
        var allow_button = document.getElementById("PDPAAllow")
        var not_allow_button = document.getElementById("PDPANotAllow")
        var consent_win = document.getElementById("pdpa_screen")
    
        not_allow_button.addEventListener("click", () => {
            pdpa_ajax_call('pdpa-not-allow');
            consent_win.style.display = 'none'
        });
    
        allow_button.addEventListener("click", () => {
            pdpa_ajax_call('pdpa-allow');
            consent_win.style.display = 'none'
        });
    })
}

const pdpa_ajax_call = (action_require) => {
    var xhr = new XMLHttpRequest();
    var fd  = new FormData();

    xhr.open("POST", pdpa_ajax.ajax_url, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var json = JSON.parse(xhr.responseText);
            cookie_process(json);
        }
    };

    fd.append( "action", "pdpa_action" );
    fd.append( "set_status", action_require );
    fd.append( "security", pdpa_ajax.pdpa_nonce );
    xhr.send(fd);
}

const cookie_process = ( $d ) => {
    var cookie_string = '';
    if($d.type == 'user_allow') {
        cookie_string = $d.cookie_name+"=1; expires="+$d.cookie_expire+"; domain="+$d.cookie_domain+"; path=/";
    }else if($d.type == 'user_not_allow') {
        cookie_string = $d.cookie_name+"=0; expires="+$d.cookie_expire+"; domain="+$d.cookie_domain+"; path=/";
    } else {
        console.log("error.")
    }
    document.cookie = cookie_string;
}

const deleteAllCookies = () => {
    var cookies = document.cookie.split(";");

    for (var i = 0; i < cookies.length; i++) {
        var cookie = cookies[i];
        var eqPos = cookie.indexOf("=");
        var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
        document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
    }
}