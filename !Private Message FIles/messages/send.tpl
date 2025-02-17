<h1>Send Private Message</h1>{if:success} 
<p>Private Message succesfully sent.</p>
<p><a href="messages/send.html">Send another.</a></p>
{else:}
{output(#nav#)}
{message:h}
<form name="form1" method="post"><table class="standard messages">
{if:reply}
  <tr>
  <th nowrap class="th">Send To User:</th>
  <td>
   <a href="{page[mainurl]}users/{sender[username]}" class="username">{sender[username]}</a>
 </td>
</tr>
{else:}
{if:friends}
    <tr>
  <th nowrap class="th">Send To User:</th>
  <td>
    <select name="friends">
    </select>
 </td>
</tr>{end:}
<tr>
  <th nowrap class="th">Manual Username Input:</th>
  <td><input name="username" type="text" id="username" size="25"></td>
</tr>{end:}
<tr>
  <th width="10%" nowrap class="th">Subject:</th>
<td><input name="subject" type="text" id="subject" size="50"></td></tr>
{if:message[replied]}{end:}
<tr>
  <th  width="10%" nowrap class="th">Message:</th>
<td><textarea name="body" cols="50" rows="6" id="body"></textarea></td></tr>
<tr>
  <th nowrap class="th">&nbsp;</th>
  <th><input type="submit" name="Submit" value="Send Message"></th>
</tr>
</table> 
</form>
{end:}