
<div id="add_site_link_popup" class="popUpDiv" style="display:none; width: 300px;">
    <div class="round_dark">
        <div style="float: right; margin-bottom: 5px;">
            <a href="" onClick="popupClose('add_site_link_popup'); return false;">
                <img src="/images/icons/cancel.png" title="cancel" width="16px" height="16px" alt="X" border="0"/>
            </a>
        </div>
        <h2><img src="/images/icons/link.png" width="16" height="16" alt="Star Icon" border="0" align="absmiddle"/> Add Site Link</h2>
        <form method="post" name="add_site_link_form" id="remove_site_credit_form">

            <input type="hidden" name="site_link_id" id="site_link_id" flexy:ignore />
            <input type="hidden" name="action" id="action" value="add_site_link" flexy:ignore />
            <a class="approve_button" onclick="document.add_site_link_form.submit();">Add Site Link</a>
            <a class="reject_button" onclick="popupClose('add_site_link_popup'); return false;">Cancel</a>
        </form>
    </div>
</div>