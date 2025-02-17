    {foreach:site_profile[credits],credit}
    <div class="site_profile_site_credit">
        <a href="http://www.webcomictv.com/user/{credit[mem_unique]}/" title="{credit[mem_username]} - WebcomicTV User Profile" class="member_username" target="_blank">{credit[mem_username]}</a>
        <div>{credit[sc_title]}</div>
    </div>
    {end:}