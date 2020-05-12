/***
 * Package: pdpa-consent
 * (c) Apinan Woratrakun <iamapinan@gmail.com>
 */

document.addEventListener("DOMContentLoaded", function() {
    var allow_button = document.getElementById("PDPAAllow")
    var not_allow_button = document.getElementById("PDPANotAllow")
    var consent_win = document.getElementById("pdpa_screen")

    document.getElementById("PDPANotAllow").addEventListener("click", () => {
        document.getElementById("pdpa_screen").style.display = 'none'
    });
    document.getElementById("PDPAAllow").addEventListener("click", () => {
        document.getElementById("pdpa_screen").style.display = 'none'
    });
})