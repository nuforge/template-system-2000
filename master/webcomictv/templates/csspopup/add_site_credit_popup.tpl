
<div id="add_site_credit_popup" class="popUpDiv" style="display:none; width: 440px;">
    <div class="round_dark">
        <div style="float: right; margin-bottom: 5px;">
            <a href="" onClick="popupClose('add_site_credit_popup'); return false;">
                <img src="/images/icons/cancel.png" title="cancel" width="16px" height="16px" alt="X" border="0"/>
            </a>
        </div>
        <h2><img src="/images/icons/user.png" width="16" height="16" alt="User Icon" border="0" align="absmiddle"/> Assign Site Credit</h2>
        <form method="post" name="add_site_credit_form" id="add_site_credit_form">
            <table class="hidden">
                <tr>
                    <th width="25%">Member:</th>
                    <td><SELECT name="sc_member" id="sc_member"></SELECT></td>
                </tr>
                <tr>
                    <th>Title:</th>
                    <td><input type="text" name="sc_title" id="sc_title" value="Creator"/></td>
                </tr>
                <tr>
                    <th>Dates:</th>
                    <td>
                        From <input type="text" name="sc_start_date" id="sc_start_date" size="8" /> to
                        <input type="text" name="sc_end_date" id="sc_end_date"  size="8"  />
                    </td>

                </tr>
                <tr>
                    <th>Privileges:</th>
                    <td>
                        <a class="cssCheckbox checked" id="sc_privileges_checkbox" onClick="toggleCheckBox('sc_privileges');">
                            <img src="/images/icons/tick.png" width="16" height="16" id="sc_privileges_icon" alt="Privilege Icon" border="0" align="absmiddle"/> <span id="sc_privileges_text">Has</span> Editing Privileges
                            <input type="hidden" name="sc_privileges" id="sc_privileges" value="1"/>
                        </a>
                    </td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td><TEXTAREA name="sc_description" id="sc_description"></TEXTAREA></td>
                </tr>
            </table>

            <input type="hidden" name="action" id="action" value="assign_site_credit" flexy:ignore />
            <a class="approve_button" onclick="document.add_site_credit_form.submit();">Assign Site Credit</a>
            <a class="reject_button" onclick="popupClose('add_site_credit_popup'); return false;">Cancel</a>


        </form>
    </div>
</div>