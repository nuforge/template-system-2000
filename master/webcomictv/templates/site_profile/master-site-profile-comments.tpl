{output(#master-site-profile-back-link#)}
<h2>{site_profile[site_title]} Comment</h2>
<div class="comment">
    <div class="comment_main">
        <div class="comment_header">
            <div class="comment_stamp">{dateFormat(#M d, Y g:ia#,comment[comment_stamp])}</div>
            <div class="comment_details"><h1><img src="/images/icons/comments.png" alt="Comments" width="16" height="16" align="absmiddle"/> <a class="member_username">{comment[mem_username]}</a>{if:comment[comment_subject]} - <a href="/site/{site[site_unique]}/comment/{comment[comment]}/{encodeString(comment[comment_subject])}.html" title="{comment[comment_subject]}">{comment[comment_subject]}</a>{end:}</h1></div>
        </div>
        <div class="comment_body">
            {bbcode(comment[comment_body]):h}

            {if:member}<div class="comment_actions"><a onClick="popupOpen('comment_popup');" class="round_link">Reply</a></div>{end:}
        </div>
    </div>

</div>