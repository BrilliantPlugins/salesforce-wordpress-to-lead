
window.onload = function() {
    var form = document.querySelector("*[id^='sf_form_salesforce_w2l_lead']");
    form.onsubmit = function() { 
        return checkForm(form);

    }
}

function checkForm(form) {
    form.w2lsubmit.disabled = true;
    form.w2lsubmit.value = "Please wait...";
    return true;
}
