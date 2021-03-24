
window.onload = function() {
    var form = sf2p2l_get_form();
    sf2p2l_get_form().onsubmit = function( event ) { 
        console.log('submit');
        document.body.style.cursor = 'progress';
        form.w2lsubmit.disabled = true;
        var label = form.w2lsubmit.value;
        setInterval( function(){ 
            var form = sf2p2l_get_form();
            form.w2lsubmit.value += '.';
         }, 500);
        //event.preventDefault();
    }
}

function sf2p2l_get_form(){
    return document.querySelector("*[id^='sf_form_salesforce_w2l_lead']");
}