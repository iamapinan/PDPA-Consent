document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('pdpa-status-reset').addEventListener("click", (e) => { 
        pdpa_consent_call('pdpa-reset');
    })
});