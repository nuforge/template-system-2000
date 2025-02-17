<h2><img src="/images/icons/sitemap.png" width="16" height="16" alt="Site Parent" title="Site Parent" border="0" align="absmiddle"/> {site_profile[site_title]} Sites</h2>
{foreach:site_profile[children],child_site}
<a href="/site/{child_site[site_unique]}/" title="{child_site[site_title]}"><img src="/images/sites/{child_site[site]}-card-{child_site[site_unique]}.png" width="150" height="80" alt="{child_site[site_title]}" border="0"/></a>
{end:}