<div id="give_award_popup" class="popUpDiv" style="display:none; width:500px;">
    <div class="round_dark">
        <div style="float: right; margin-bottom: 5px;">
            <a href="" onClick="popupClose('give_award_popup'); return false;">
                <img src="/images/icons/cancel.png" title="cancel" width="16px" height="16px" alt="X" border="0"/>
            </a>
        </div>
        <h2><img src="/images/icons/award_star_gold_1.png" width="16" height="16" alt="Star Icon" border="0" align="absmiddle"/> Give Award</h2>

        <form method="post" name="award_site_form" id="award_site_form">
            <div style="overflow: auto;">
                <div style="width: 45%; margin-bottom: 10px; float: left; margin-right: 10px;">
                    <select name="award" id="award" onChange="changeAwardImage(this.value,'award_icon');">
                    </select>
                    <div style="margin-top: 10px;">
                        <img src="/images/awards/award-{initialAward[award_unique]}.png"  id="award_icon" />
                    </div>

                    <div class="award_description" id="award_description">
                        {initialAward[award_description]:h}
                    </div>
                </div>
                <div style="float: left; width: 50%;">
                    <h2>Comments</h2>

                    <TEXTAREA name="comments" id="comments" style="width: 98%;" rows="3"></TEXTAREA>
                    <input type="hidden" name="action" id="action" value="give_award" flexy:ignore />
                </div>
            </div>
            <a class="approve_button" onclick="document.award_site_form.submit();">Give Award</a>
            <a class="reject_button" onclick="popupClose('give_award_popup');">Cancel</a>
        </form>
    </div>
</div>
