<h1>Private Message</h1>
{output(#nav#)}

{foreach:conversation[messages],k,message}
<div class="message_container">
    <div class="grey_box {message[class]}">
        <div class="message_left">
            {if:message[member_wrestler_name]}
            {if:message[member_photo]}<a href="/wrestler/{message[member_unique]}/"><img src="/images/photos/{message[member_unique]}/p-{message[member_photo]}-50x.jpeg" width="50px" height="50px" alt="{message[member_username]}" border="0" /></a>{end:}
            {else:}
            {if:message[member_photo]}<a href="/profile/{message[member_unique]}/"><img src="/images/photos/{message[member_unique]}/p-{message[member_photo]}-50x.jpeg" width="50px" height="50px" alt="{message[member_username]}" border="0" /></a>{end:}
            {end:}
        </div>
        <div class="message_body">
		{if:compare(0,k)}<h2>{conversation[con_subject]}</h2>{end:}
		{text2html(message[message_body]):h}

	{if:compare(0,k)}<div class="match_details">{if:sender}Sent to {else:}Message from {end:}


                <a href="/{conversation[member_member_type]}/{conversation[member_unique]}/" class="{conversation[member_type_class]}">{conversation[member_username]}</a>
            </div>{end:}</div>
        <div class="message_right">{dateFormat(#M. d, Y g:i a#,message[message_sent])}</div>
    </div>
</div>
{end:}
<div class="grey_box" id="message_respond">
    <form method="post" >
        <div class="manager_container"><h2>Conversation Options</h2>
            {if:conversation[folders][favorite]}
            <img src="/images/icons/favorite.png" onclick="moveConversation('favorite',{conversation[conversation]});" id="conversation_favorite_icon_{conversation[conversation]}" class="action_icon" width="16" height="16" alt="Favorite" title="Favorite" align="absmiddle" />
            {else:}
            <img src="/images/icons/favorite_grey.png" onclick="moveConversation('favorite',{conversation[conversation]});" id="conversation_favorite_icon_{conversation[conversation]}" class="action_icon" width="16" height="16" alt="Favorite" title="Favorite" align="absmiddle" />
            {end:}
            {if:conversation[folders][archive]}
            <img src="/images/icons/archive.png" onclick="moveConversation('archive',{conversation[conversation]});" id="conversation_archive_icon_{conversation[conversation]}" class="action_icon" width="16" height="16" alt="Archive" title="Archive" align="absmiddle" />
            {else:}
            <img src="/images/icons/archive_grey.png" onclick="moveConversation('archive',{conversation[conversation]});" id="conversation_archive_icon_{conversation[conversation]}" class="action_icon" width="16" height="16" alt="Archive" title="Archive"  align="absmiddle" />
            {end:}
        </div>
        <div class="manager_partner">
            <textarea name="new_message_body" rows="8"></textarea><br />
            <input type="submit" name="submit" value="Send Message" />
            <input type="hidden" name="conversation_id" id="conversation_id" value="{conversation[conversation]}"/>
        </div>
    </form>
</div>