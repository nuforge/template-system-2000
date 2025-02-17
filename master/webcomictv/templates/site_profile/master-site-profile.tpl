{output(#master-site-side-menu#)}
<div id="site_profile">
    {output(#master-site-header#)}
    <div id="site_profile_contents">
        {output(site_profile_display_page)}
    </div>
</div>

<div id="comment_popup" class="default_popup comment_popup popUpDiv" style="display:none; width: 600px;">
    <div style="float: right;"><a onClick="popupClose('comment_popup');"><img src="/images/icons/cancel.png" width="16px" height="16px" title="Close" alt="X" border="0" align="absmiddle"/></a></div>
    <h2><img src="/images/icons/comments_add.png" alt="Post a Comment" width="16" height="16" align="absmiddle"/> Post a Comment</h2>

    {if:member}
    {if:status_message}{status_message:h}{end:}
    <form method="post" name="post_comment_form">
        <table class="hidden">
            <tr>
                <th>Subject<div class="small">Optional</div></th>
                <td><input type="text" name="comment_subject" id="comment_subject" size="64" maxlength="64" /></td>
            </tr>
            <tr>
                <th>Body
                    <div class="small">
                        [<a  onClick="popupOpen('bbcode');">BBCode</a>] Accepted.
                    </div>
                </th>
                <td><textarea name="comment_body" id="comment_body" cols="32" ></textarea></td>
            </tr>
            <tr>
                <th colspan="2" class="right">
                    <a  class="round_link" onClick="document.post_comment_form.submit();">Post Comment</a>
                </th>
            </tr>
        </table>
        <input type="hidden" name="comment_id" value="{site_profile[site]}"/>
        <input type="hidden" name="form_action" id="form_action" value="post_site_comment" flexy:ignore/>
    </form>
    {else:}
    <p>You must be a member to post a comment. <a href="/login.html" rel="nofollow" title="Login to {pageValue(#sitename#)}">Login</a> | <a href="http://www.webcomictv.com/register.html" target="_blank" title="Register at WebcomicTV">Register for Free at WebcomicTV.com</a></p>
    {end:}
</div>


<div id="bbcode" class="default_popup bbcode_popup popUpDiv" style="display:none; width: 600px;">
    <div style="float: right;"><a onClick="popupClose('bbcode');"><img src="/images/icons/cancel.png" width="16px" height="16px" title="Close" alt="X" border="0" align="absmiddle"/></a></div>
    <h1>BBCode</h1>
    <p>Below is a list of BBCode and what it will look like when posted.</p>
    <table class="standard">
        <tr><td>Code</td>
            <td>Description</td>
            <td>Sample</td>
            <td>Appearance</td>
        </tr>
        <tr>
            <td>[spoiler]</td>
            <td>&nbsp;</td>
            <td>[spoiler]This is a spoiler.[/spoiler]</td>
            <td>{bbcode(#[spoiler]This is a spoiler.[/spoiler]#):h}</td>
        </tr>
        <tr>
            <td>[color]</td>
            <td>&nbsp;</td>
            <td>This is [color=red]red[/color] and this is [color=blue]blue[/color].</td>
            <td>{bbcode(#This is [color=red]red[/color] and this is [color=blue]blue[/color].#):h}</td>
        </tr>
        <tr>
            <td>[quote]</td>
            <td>&nbsp;</td>
            <td>[quote]No WAY![/quote]</td>
            <td>{bbcode(#[quote]No WAY![/quote]#):h}</td>
        </tr>
        <tr>
            <td>[code]</td>
            <td>&nbsp;</td>
            <td>[code]PHP[/code]</td>
            <td>{bbcode(#[code]PHP[/code]#):h}</td>
        </tr>
        <tr>
            <td>[b],[i],[u],[s],[sub],[sup]</td>
            <td>&nbsp;</td>
            <td>[b]bold[/b], [i]italics[/i], [u]underline[/u], [s]strikethrough[/s], [sub]subscript[/sub], [sup]superscript[/sup]</td>
            <td>{bbcode(#[b]bold[/b], [i]italics[/i], [u]underline[/u], [s]strikethrough[/s], [sub]subscript[/sub], [sup]superscript[/sup]#):h}</td>
        </tr>
        <tr>
            <td>[url]</td>
            <td>&nbsp;</td>
            <td>[url]http://www.webcomictv.com[/url] &amp; [url=http://www.google.com]Google[/url]</td>
            <td>{bbcode(#[url]http://www.webcomictv.com[/url] &amp; [url=http://www.google.com]Google[/url]#):h}</td>
        </tr>
        <tr>
            <td>[list][li]</td>
            <td>&nbsp;</td>
            <td>[list][li]Number 1[/li][li]number 2[/li][/list] </td>
            <td>{bbcode(#[list][li]Number 1[/li][li]number 2[/li][/list]#):h}</td>
        </tr>
        <tr>
            <td>[ulist][li]</td>
            <td>&nbsp;</td>
            <td>[ulist][li]Number 1[/li][li]number 2[/li][/ulist] </td>
            <td>{bbcode(#[ulist][li]Number 1[/li][li]number 2[/li][/ulist]#):h}</td>
        </tr>
    </table>
</div>