<div id="status_message_popup" class="default_popup popUpDiv" style="display:none; width: 300px;">
    <div style="float: right; overflow:auto; margin-bottom: 5px;">
        <a onClick="popupClose('status_message_popup');">
            <img src="/images/icons/cancel.png" title="cancel" width="16px" height="16px" alt="X" border="0"/>
        </a>
    </div>
    <div id="error_message_text">
        {status_message:h}
    </div>

    <a class="approve_button" onClick="popupClose('status_message_popup');">Ok</a>
</div>