<div id="confirm_remove_site_credit_popup" class="popUpDiv" style="display:none; width: 300px;">
    <div class="round_dark">
        <div style="float: right; margin-bottom: 5px;">
            <a href="" onClick="popupClose('confirm_remove_site_credit_popup'); return false;">
                <img src="/images/icons/cancel.png" title="cancel" width="16px" height="16px" alt="X" border="0"/>
            </a>
        </div>
        <h2><img src="/images/icons/delete.png" width="16" height="16" alt="Star Icon" border="0" align="absmiddle"/> Remove Site Credit</h2>
        <p>Do you really want to remove <span id="site_credit_name"></span> as the <span id="site_credit_title"></span> for {site_profile[site_title]}?</p>
        <form method="post" name="remove_site_credit_form" id="remove_site_credit_form">

            <input type="hidden" name="site_credit_id" id="site_credit_id" flexy:ignore />
            <input type="hidden" name="action" id="action" value="remove_site_credit" flexy:ignore />
            <a class="approve_button" onclick="document.remove_site_credit_form.submit();">Remove Site Credit</a>
            <a class="reject_button" onclick="popupClose('confirm_remove_site_credit_popup'); return false;">Cancel</a>
        </form>
    </div>
</div>