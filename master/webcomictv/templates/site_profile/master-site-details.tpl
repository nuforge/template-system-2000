<ul class="site_details_list">
    {if:site_profile[site_recommended]}
    <li>
        <img src="/images/icons/star.png" width="16" height="16" alt="{page[site_name]} Recommended" border="0" align="absmiddle"/> Recommended
    </li>
    {end:}
    {if:site_profile[site_nsfw]}

    <li style="font-weight: bold; color: #cc0000;">
        <img src="/images/icons/exclamation.png" width="16" height="16" alt="{site_profile[site_title]} contains NSFW Content" title="{site_profile[site_title]} contains NSFW Content" border="0" align="absmiddle"/> NSFW Content
    </li>
    {end:}
    {if:site_profile[site_content_rating]}
    <li>
        <img src="/images/icons/flag_blue.png" width="16" height="16" alt="{site_profile[site_title]} Content Rating" border="0" align="absmiddle"/> Content Rating: <strong>{site_profile[cr_title]}</strong>
    </li>
    {end:}
    {if:site_profile[site_paysite]}

    <li>
        <img src="/images/icons/money_dollar.png" width="16" height="16" alt="{site_profile[site_title]} Paysite" title="{site_profile[site_title]} Paysite" border="0" align="absmiddle"/> Paysite
    </li>
    {end:}
    {if:site_profile[site_ended]}
    <li><img src="/images/icons/book.png" width="16" height="16" alt="{site_profile[site_title]} Completed Webcomic" border="0" align="absmiddle"/> Completed</li>{end:}

    <li>
        {if:site_profile[site_active]}{else:}<img src="/images/icons/error.png" width="16" height="16" alt="{site_profile[site_title]} Inactive" border="0" align="absmiddle"/> Site Not Available{end:}
    </li>



</ul>
{if:site_profile[update_values]}
<h2>Updates</h2>

<div id="site_profile_update_dotw">
    {output(#master-site-updates#)}
</div>
{end:}