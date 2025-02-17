{output(#master-site-profile-back-link#)}
<h2><a href="/site/{site_profile[site_unique]}/links/" title="{site_profile[site_title]} Links">{site_profile[site_title]} Links</a></h2>
<div id="site_profile_links">
    {foreach:site_profile[links],link}
    <div class="site_link_container round_dark">
        <div class="site_profile_link_details">{if:link[lt_icon]}<img src="/images/icons/{link[lt_icon]}" title="Link Icon" width="16" height="16" align="absmiddle" border="0" alt="{link[lt_title]}"/> {end:}{link[lt_title]} {if:link[link_member]}by <a href="/user/{link[mem_unique]}/" title="{link[mem_username]}" class="member_username">{link[mem_username]}</a>{end:}{if:link[link_stamp]} on {dateFormat(#M d, Y#,link[link_stamp])}{end:}</div>
        <a href="{link[link_url]}" title="{link[link_title]}" rel="nofollow" target="_blank" class="site_link">{link[link_title]} <img src="/images/icons/link.png" title="Link Icon" width="16" height="16" align="absmiddle" border="0" alt="Go"/></a>
        <div class="site_profile_link_url"><a href="{link[link_url]}" title="{link[link_title]}" rel="nofollow" target="_blank">{link[link_url]}</a></div>
        <div class="site_profile_link_description">{bbcode(link[link_description]):h}</div>
        <div class="site_profile_link_link"><a href="{link[link_url]}" title="{link[link_title]}" rel="nofollow" target="_blank" class="round_link">Follow Link &raquo;</a></div>
    </div>
    {end:}
</div>