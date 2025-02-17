<div id="user_profile_edit_status_popup" class="popUpDiv" style="display:none; width:500px;">
    <div class="round_dark">
        <div style="float: right; margin-bottom: 5px;">
            <a href="" onClick="popupClose('user_profile_edit_status_popup'); return false;">
                <img src="/images/icons/cancel.png" title="cancel" width="16px" height="16px" alt="X" border="0"/>
            </a>
        </div>
        <h2><img src="/images/icons/pencil.png" width="16" height="16" alt="Edit Icon" border="0" align="absmiddle"/> Edit Status</h2>

        <form method="post" name="edit_status_form" id="edit_status_form">
            <input type="text" name="mem_status" id="mem_status" class="long_form"/>
            <input type="hidden" name="form_action" id="form_action" value="edit_status" flexy:ignore />
            <a class="approve_button" onclick="document.edit_status_form.submit();">Change Status Message</a>
            <a class="reject_button" onclick="popupClose('user_profile_edit_status_popup');">Cancel</a>
        </form>
    </div>
</div>
