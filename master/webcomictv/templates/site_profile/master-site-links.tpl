<ul id="site_profile_links">
    {foreach:site_profile[links],link}
    <li>
        <a href="{link[link_url]}" title="{link[link_title]}" target="_blank" class="webcomic_title">{if:link[lt_icon]}<img src="/images/icons/{link[lt_icon]}" title="Link Icon" width="16" height="16" align="absmiddle" border="0" alt="{link[lt_title]}"/> {end:}{link[link_title]}</a><br/>
        {link[lt_title]}
    </li>
    {end:}
</ul>