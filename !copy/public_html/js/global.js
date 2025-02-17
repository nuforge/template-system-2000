

Event.observe( window, 'scroll', function() {autoScrollObjects();} );

var scrollers = new Array();


function toggleButton (fieldID, ActiveText, InactiveText,classNameOn,classNameOff,defaultClass) {
    var hiddenField = document.getElementById(fieldID);
    if(hiddenField.value == 1 ){
        hiddenField.value = 0;
    } else {
        hiddenField.value = 1;
    }
    applyToggleButtonValue (fieldID, ActiveText, InactiveText,classNameOn,classNameOff,defaultClass);
}

function applyToggleButtonValue(fieldID, ActiveText, InactiveText,classNameOn,classNameOff,defaultClass) {
    var hiddenField = document.getElementById(fieldID);
    var buttonToToggle = document.getElementById('button_'+fieldID);

    if(defaultClass == null) {
        defaultClass = "round_link";
    }
    if (classNameOn == null) {
        classNameOn = "toggle_on_button";
    }

    if (classNameOff == null) {
        classNameOff = "toggle_off_button";
    }

    if(hiddenField.value == 1 ){
        buttonToToggle.className=defaultClass+" " + classNameOn;
        buttonToToggle.innerHTML = ActiveText;
    } else {
        buttonToToggle.className=defaultClass+" " + classNameOff;
        buttonToToggle.innerHTML = InactiveText;
    }
}

function popupGlobalMessage(messageText,messageType) {
    var statusPopupText = document.getElementById('status_message_text');
    if(messageType) {
        statusPopupText.innerHTML = '<h2>'+messageType.capitalize()+'</h2><p>'+messageText+'</p>';

    } else {
        statusPopupText.innerHTML = '<p>'+messageText+'</p>';

    }

    popupOpen('status_message_popup');
}

function autoScrollObjects() {
    var autoScrollers = document.getElementsByClassName('autoScroll');
    var leftOff;
    //var scroll = document.body.parentNode.clientHeight - document.body.parentNode.scrollTop;
    for (var a=0; a<autoScrollers.length; a++) {
        a = a +'';
        if(scrollers[a] == null) {
            if (autoScrollers[a].offsetTop < document.body.parentNode.scrollTop) {
                if(autoScrollers[a].style.position != 'absolute') {
                    leftOff = autoScrollers[a].offsetLeft +'px';
                    autoScrollers[a].style.position = 'absolute';
            
                    autoScrollers[a].style.left = leftOff;
                }
                
                scrollers[a] = autoScrollers[a];
                scrollers[a]['top'] = autoScrollers[a].offsetTop;
                scrollers[a].style.top = document.body.parentNode.scrollTop +'px';
            }
        } else {
            if(scrollers[a]['top'] > document.body.parentNode.scrollTop) {
                

                scrollers[a].style.top = scrollers[a]['top'] +'px';
                scrollers[a] = null;

            } else {
                scrollers[a].style.top = document.body.parentNode.scrollTop +'px';
            }


        }


    }

    
}

function ajaxCall(ajaxPage)
{
    var HttpRequest;
    try{
        HttpRequest = new XMLHttpRequest();
    } catch(e){
        try{
            HttpRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch(e){
            try{
                HttpRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch(e){
                alert("Your browser is to old or does not support Ajax.");
            }
        }
    }
    HttpRequest.onreadystatechange=function()
    {
        if(HttpRequest.readyState==4)
        {
            if(HttpRequest.responseText == "1") {
                popupGlobalMessage('Action Completed','success');
            } else {
                popupGlobalMessage(HttpRequest.responseText,'error');
            }
        }
    }
    HttpRequest.open("GET","/index.php?plugin=plugin_ajax&pg="+ajaxPage,true);
    HttpRequest.send(null);
}
