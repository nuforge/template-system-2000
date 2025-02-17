<h1>Messages</h1>
{output(#nav#)}

{if:messageList}

{if:pagination[range]}
<div class="nav">{if:pagination[first]}<a href="/messages/inbox/p{pagination[first]}">&laquo;</a>{end:} {foreach:pagination[range],k,v}{if:compare(v,pagination[current])}<span class="nav_current">{v}</span> {else:}<a href="/messages/inbox/p{v}">{v}</a> {end:}{end:} {if:pagination[lastshow]}<a href="/messages/inbox/p{pagination[last]}">&raquo;</a>{end:} {if:pagination[range]}- {end:}{if:pagination[previous]}<a href="/messages/inbox/p{pagination[previous]}">Previous</a>{else:}<em>Previous</em>{end:} {if:pagination[next]}<a href="/messages/inbox/p{pagination[next]}">Next</a>{else:}<em>Next</em>{end:} <a href="/messages/inbox/p{pagination[current]}">Reload</a></div>
{end:}
<div class="grey_box">
    <table class="standard messages">
        <tr class="th">
            <th colspan="3">Message</th>
            <th colspan="4">Sent</th>
        </tr>
        {foreach:messageList,k,message}
        <tr class="{rowMod(k)}">
            <td class="message_action">
                {if:message[message_sent_by_member]}<img src="/images/icons/arrow_right.png" width="16" height="16" alt="sent" align="absmiddle" />{end:}
                {if:message[unread]}
                {if:message[con_flagged]}<img src="/images/icons/error.png" width="16" height="16" alt="Flagged" align="absmiddle" />{else:}<img src="/images/icons/email.png" width="16" height="16" alt="sent" align="absmiddle" />{end:}{end:}
            </td>
            <td class="message_avatar">
                {if:message[member_photo]}
                <a href="/wrestler/{message[member_unique]}/"><img src="/images/photos/{message[member_unique]}/p-{message[member_photo]}-50x.jpeg" width="50px" height="50px" alt="{message[member_username]}" border="0" /></a>
                {end:}
            </td>
            <td><a href="/messages/{message[conversation]}" class="message_subject">{message[con_subject]}</a><br/>
	{if:message[message_sent_by_member]}to{else:}from{end:} 
                <a href="/{message[member_member_type]}/{message[member_unique]}/" class="{message[member_type_class]}">{message[member_username]}</a>
            </td>

            <td class="message_sent">{if:message[message_sent]}{dateFormat(#M d, Y#,message[message_sent])}{end:}</td>
            <td>
                {if:message[folders][favorite]}
                <img src="/images/icons/favorite.png" onclick="moveConversation('favorite',{message[conversation]});" id="conversation_favorite_icon_{message[conversation]}" class="action_icon" width="16" height="16" alt="Favorite" title="Favorite" align="absmiddle" />
                {else:}
                <img src="/images/icons/favorite_grey.png" onclick="moveConversation('favorite',{message[conversation]});" id="conversation_favorite_icon_{message[conversation]}" class="action_icon" width="16" height="16" alt="Favorite" title="Favorite" align="absmiddle" />
                {end:}
                {if:message[folders][archive]}
                <img src="/images/icons/archive.png" onclick="moveConversation('archive',{message[conversation]});" id="conversation_archive_icon_{message[conversation]}" class="action_icon" width="16" height="16" alt="Archive" title="Archive" align="absmiddle" />
                {else:}
                <img src="/images/icons/archive_grey.png" onclick="moveConversation('archive',{message[conversation]});" id="conversation_archive_icon_{message[conversation]}" class="action_icon" width="16" height="16" alt="Archive" title="Archive"  align="absmiddle" />
                {end:}
            </td>
            <td>
                <img src="/images/icons/delete.png" onclick="confirmDeleteConversation({message[conversation]});" id="conversation_delete_icon_{message[conversation]}" class="action_icon" height="16px" width="16px" alt="Delete Message" align="absmiddle" />
            </td>
        </tr>
        {end:}
    </table>
</div>
{if:pagination[range]}
<div class="nav">{if:pagination[first]}<a href="/messages/inbox/p{pagination[first]}">&laquo;</a>{end:} {foreach:pagination[range],k,v}{if:compare(v,pagination[current])}<span class="nav_current">{v}</span> {else:}<a href="/messages/inbox/p{v}">{v}</a> {end:}{end:} {if:pagination[lastshow]}<a href="/messages/inbox/p{pagination[last]}">&raquo;</a>{end:} {if:pagination[range]}- {end:}{if:pagination[previous]}<a href="/messages/inbox/p{pagination[previous]}">Previous</a>{else:}<em>Previous</em>{end:} {if:pagination[next]}<a href="/messages/inbox/p{pagination[next]}">Next</a>{else:}<em>Next</em>{end:} <a href="/messages/inbox/p{pagination[current]}">Reload</a></div>
{end:}

{else:}
<p>You have no messages in this folder.</p>
{end:}



{output(#popup_confirm_delete_message#)}
