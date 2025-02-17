function moveConversation(folderName,conId) {
    var actionIcon = document.getElementById('conversation_'+folderName+'_icon_'+conId);
    var oldSrc;
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
        if(HttpRequest.readyState==1) {
            actionIcon.src = '/images/ajax-loader.gif';

        }
        if(HttpRequest.readyState==4)
        {
            if(HttpRequest.responseText == "2") {
                actionIcon.src = '/images/icons/'+folderName+'_grey.png';

            } else if (HttpRequest.responseText == "1") {
                actionIcon.src = '/images/icons/'+folderName+'.png';

            } else {
                actionIcon.src = oldSrc;
                popupGlobalMessage(HttpRequest.responseText,'Error');
            }
        }
    }
    oldSrc = actionIcon.src;
    HttpRequest.open("GET","/index.php?plugin=plugin_ajax&pg=moveconversation&folder="+folderName+"&conversation="+conId,true);
    HttpRequest.send(null);
}

function confirmDeleteConversation(conId) {
    
    var deleteCon = document.getElementById('delete_conversation_id');
    deleteCon.value = conId;

    popupOpen('confirm_delete_message');
}


function deleteConversation() {
    var deleteCon = document.getElementById('delete_conversation_id');
    var conId = deleteCon.value;
    var deleteIcon = document.getElementById('conversation_delete_icon_'+conId);
    var oldSrc;
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
        if(HttpRequest.readyState==1) {
            deleteIcon.src = '/images/ajax-loader.gif';

        }
        if(HttpRequest.readyState==4)
        {
            popupClose('confirm_delete_message');
            if(HttpRequest.responseText == "1") {
                deleteIcon.src = '/images/icons/cross.png';
                //popupGlobalMessage('Message Deleted','success');
                window.location.reload();

            } else {
                deleteIcon.src = oldSrc;
                popupGlobalMessage(HttpRequest.responseText,false);
            }
        }
    }
    deleteIcon = deleteIcon.src;
    HttpRequest.open("GET","/index.php?plugin=plugin_ajax&pg=moveconversation&folder=delete&conversation="+conId,true);
    HttpRequest.send(null);
}