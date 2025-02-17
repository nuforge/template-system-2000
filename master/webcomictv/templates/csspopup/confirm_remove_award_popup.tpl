
<div id="confirm_remove_award_popup" class="popUpDiv" style="display:none; width: 300px;">
    <div class="round_dark">
        <div style="float: right; margin-bottom: 5px;">
            <a href="" onClick="popupClose('confirm_remove_award_popup'); return false;">
                <img src="/images/icons/cancel.png" title="cancel" width="16px" height="16px" alt="X" border="0"/>
            </a>
        </div>
        <h2><img src="/images/icons/delete.png" width="16" height="16" alt="Star Icon" border="0" align="absmiddle"/> Remove Award</h2>
        <p>Do you really want to remove the following reward from {site_profile[site_title]}?</p>
        <form method="post" name="remove_award_form" id="remove_award_form">
            <div class="award_container">
                <img src="/images/awards/award-{initialAward[award_unique]}.png"  id="remove_award_icon" />
            </div>
            <input type="hidden" name="site_award_id" id="site_award_id" flexy:ignore />
            <input type="hidden" name="action" id="action" value="remove_award" flexy:ignore />
            <a class="approve_button" onclick="document.remove_award_form.submit();">Remove Award</a>
            <a class="reject_button" onclick="popupClose('confirm_remove_award_popup'); return false;">Cancel</a>
        </form>
    </div>
</div>