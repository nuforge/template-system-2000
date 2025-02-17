<script type="text/javascript" language="JavaScript">
	
        function sentToFavorite() {
                var favSelect = document.getElementById('message_favorites');
                document.getElementById('message_username').value = favSelect[favSelect.selectedIndex].text;
                document.getElementById('message_unique').value = favSelect.value;
                favSelect.selectedIndex = 0;
                return true;
        }

	
</script>

<h1>Compose Private Message</h1>
{output(#nav#)}

<form name="message" method="post">
    <div class="grey_box">
        <table class="standard messages">
            <tr>
                <th nowrap class="th">Send To:</th>
                <td><input name="message_username" type="text" id="message_username" size="25">
                    <select name="message_favorites" id="message_favorites" onchange="sentToFavorite();">
                    </select>
                    <input type="hidden" name="message_unique" id="message_unique" /></td>
            </tr>
            <tr>
                <th width="10%" nowrap class="th">Subject:</th>
                <td><input name="message_subject" type="text" id="message_subject" size="50"/></td></tr>
            <tr>
                <th  width="10%" nowrap class="th">Message:</th>
                <td><textarea name="message_body" cols="50" rows="6" id="message_body"></textarea></td></tr>
            <tr>
                <th nowrap class="th">&nbsp;</th>
                <th><input type="submit" name="Submit" value="Send Message"></th>
            </tr>
        </table> 
    </div>
</form>