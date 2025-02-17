
    <ul class="site_statistics_list">
        {if:site_profile[site_added]}
        <li>
            <img src="/images/icons/date.png" width="16" height="16" alt="{site_profile[site_title]} Added" border="0" align="absmiddle"/> Added: {dateFormat(#F jS, Y#,site_added)}
        </li>
        {end:}
        {if:site_profile[statistics][exits]}<li><img src="/images/icons/door_in.png" width="16" height="16" alt="Door Out Icon" border="0" align="absmiddle"/> Visits: {site_profile[statistics][exits]}</li>{end:}
        {if:site_profile[statistics][favorites]}<li><img src="/images/icons/thumb_up.png" width="16" height="16" alt="Thumb Up Icon" border="0" align="absmiddle"/> Favorites: {site_profile[statistics][favorites]}</li>{end:}
    </ul>