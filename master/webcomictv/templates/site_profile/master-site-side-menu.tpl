<div id="site_profile_details">
    <div id="site_profile_side_menu_top" class="round_dark">
        {if:site_profile[site_card]}<div id="profile_site_card"><img src="/images/sites/{site_profile[site]}-card-{site_profile[site_unique]}.png" width="150" height="80" alt="{site_profile[site_title]}"/></div>{end:}
    </div>
    <div class="round_dark"><a href="/sites/p{site_profile[page_number]}.html" title="Site Directory - Page {site_profile[page_number]}" class="round_link">&laquo; Back to Site Directory  - Page {site_profile[page_number]}</a></div>
    <h2>{site_profile[site_title_short]} Details</h2>
    <div class ="round_dark">

        {output(#master-site-details#)}

    </div>
    <h2>{site_profile[site_title_short]} Actions</h2>
    <div id="site_profile_actions" class="round_dark">
        {output(#master-site-actions#)}
    </div>
    {if:site_profile[credits]}
    <h2>{site_profile[site_title_short]} Credits</h2>
    <div class="round_dark">
        {output(#master-site-credits#)}

    </div>
    {end:}
    {if:site_profile[statistics]}
    <h2>{site_profile[site_title_short]} Statistics</h2>
    <div id="site_profile_site_statistics" class="round_dark">
        {output(#master-site-statistics#)}
    </div>
    {end:}
    {if:site_profile[awards]}
    <h2>{site_profile[site_title_short]}  Awards</h2>
    <div id="site_profile_site_awards"  class="round_dark">
        {output(#master-site-awards#)}
    </div>
    {end:}
    {if:site_profile[parent]}
    <h2>{site_profile[site_title_short]} Parent Site</h2>
    <div id="site_profile_site_parent"  class="round_dark">
        {output(#master-site-parent#)}
    </div>
    {end:}
    {if:site_profile[tags]}
    <h2>{site_profile[site_title_short]}  Tags</h2>
    <div id="site_profile_site_tags" class="round_dark">
        {output(#master-site-tags#)}
    </div>
    {end:}
    {if:site_profile[references]}
    <h2>WebcomicTV References</h2>
    <div id="site_profile_site_references" class="round_dark">
    {output(#master-site-references#)}
    </div>
    {end:}

    {if:site_profile[links]}
    <h2><a href="/site/{site_profile[site_unique]}/links/" title="{site_profile[site_title]} Links">{site_profile[site_title_short]} Links</a></h2>
    <div id="site_profile_site_links" class="round_dark">
    {output(#master-site-links#)}
    </div>
    {end:}



</div>