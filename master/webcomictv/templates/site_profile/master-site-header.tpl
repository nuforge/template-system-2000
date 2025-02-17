<div class="round_dark site_header">
    <div class="back_to_directory">
        
        {if:site_profile[site_active]}<div style="overflow: auto;"><a href="/site/{site_profile[site_unique]}/exit/" target="_blank" rel="nofollow" title="Visit {site_profile[site_title]}" class="round_link strong_link site_exit_link" style="float: left;">Visit {site_profile[site_title_short]} &raquo;</a></div>{end:}
        {if:site_profile[site_rss]}
        <div  style="margin-top: 8px; text-align: right;">
        <a href="{site_profile[site_rss]}" target="_blank" rel="nofollow" title="{site_profile[site_title]} RSS Feed">RSS Feed <img src="/images/icons/feed.png" width="16" height="16" alt="{site_profile[site_title]} RSS Feed" border="0" align="absmiddle" class="site_exit_link strong_link"/></a>
        </div>
        {end:}
    </div>
    <h1><a href="/site/{site_profile[site_unique]}/" title="{site_profile[site_title]} - {site_profile[category_title]}">{site_profile[site_title]}</a></h1>
    <div class="" style="margin-top: 8px;"><img src="/images/icons/{site_profile[category_icon]}.png" width="16" height="16" alt="{site_profile[category_title]}" border="0" align="absmiddle"/> {if:site_profile[site_adultwebcomic]}Adult {end:}{site_profile[category_title]}</div>
    <div class="" style="margin-top: 8px;">{if:site_profile[language_flag]}<img src="/images/icons/{site_profile[language_flag]}" width="16" height="16" alt="{site_profile[language_title]}" border="0" align="absmiddle"/> {end:}{site_profile[language_title]}</div>
</div>