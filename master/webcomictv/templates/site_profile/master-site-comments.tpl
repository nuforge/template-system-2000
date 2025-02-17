{if:comments}
<h2><img src="/images/icons/comments.png" alt="Comments" width="16" height="16" align="absmiddle"/> Comments</h2>
{foreach:comments,comment}
<div class="comment">
    <div class="comment_main">
        <div class="comment_header">
            <div class="comment_stamp">{dateFormat(#M d, Y g:ia#,comment[comment_stamp])}</div>
            <div class="comment_details">
                <a href="http://www.webcomictv.com/user/{comment[mem_unique]}/" target="_blank" class="member_username">{comment[mem_username]}</a>{if:comment[comment_subject]} - <a href="/site/{site_profile[site_unique]}/comment/{comment[comment]}/{encodeString(comment[comment_subject])}.html" title="{comment[comment_subject]}">{comment[comment_subject]}</a>{end:}</div>
        </div>
        <div class="comment_body">

            {bbcode(comment[comment_body]):h}
            {if:member}<div class="comment_actions"><a onClick="popupOpen('comment_popup');" class="round_link">Reply</a></div>{end:}
        </div>

    </div>
</div>
{end:}

{end:}