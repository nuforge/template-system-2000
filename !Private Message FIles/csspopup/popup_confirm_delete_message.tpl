<div id="confirm_delete_message" class="default_popup popUpDiv" style="display:none; width: 300px;">
    <div style="float: right; overflow:auto; margin-bottom: 5px;">
        <a onClick="popupClose('confirm_delete_message');">
            <img src="/images/icons/cancel.png" title="cancel" width="16px" height="16px" alt="X" border="0"/>
        </a>
    </div>
    <h2>Delete Conversation</h2>
    <p>Please confirm that you would like to delete this conversation. <strong>This cannot be undone.</strong></p>
    <input type="hidden" name="delete_conversation_id" id="delete_conversation_id" />
    <a class="round_link approve_button" onClick="deleteConversation();">Delete Message</a>
    <a class="round_link cancel_button" onClick="popupClose('confirm_delete_message');">Cancel</a>
</div>