<div id="popup_send_message" class="default_popup popup_send_message popUpDiv" style="display:none; width: 500px;">
    <div style="float: right; overflow:auto; margin-bottom: 5px;">
        <a href="" onClick="popupClose('popup_send_message'); return false;">
            <img src="/images/icons/cancel.png" title="cancel" width="16px" height="16px" alt="X" border="0"/>
        </a>
    </div>
    <h3>{outputIcon(#email#):h} Send Message</h3>
    {if:member_profile[viewer_data][can_message]}
    <p>Sending a message is a good way to get to know someone.</p>
    <form method="post" action="/messages/compose.html" name="form_popup_send_message">
        <input name="message_subject" style="width: 98%;" type="text" id="message_subject" size="50" value="Type Subject Here" onFocus="this.select();"/><br/>
        <textarea name="message_body" style="width: 98%;" rows="6" id="message_body" onFocus="this.select();">Type Body Here</textarea>
        <div>
            <a class="round_link csspopup_approve_button" onclick="document.form_popup_send_message.submit();">Send Message</a>
            <a  class="round_link csspopup_cancel_button" onClick="popupClose('popup_send_message');">Cancel</a>

        </div>

        <input type="hidden" id="message_unique" name="message_unique" value="{member_profile[member_display_unique]}" flexy:ignore />
        <input type="hidden" id="message_username" name="message_username" value="{member_profile[member_display_name]}" flexy:ignore />

    </form>
    {else:}
    {if:member}
    <p>{outputIcon(#error#):h} You must be a verified member to message this member. <a href="/settings/upgrade.html" title="Upgrade Account">Verify Account</a></p>
    {else:}
    <p>{outputIcon(#error#):h} You must be logged into message users. <a href="/login.html" title="login">Login Now</a></p>
    {end:}
    <p>
            <a  class="round_link csspopup_cancel_button" onClick="popupClose('popup_send_message');">Cancel</a>
    </p>
    {end:}
</div>