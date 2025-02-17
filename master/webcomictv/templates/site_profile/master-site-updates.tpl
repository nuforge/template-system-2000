
    <div class="site_profile_week_days_container">
    {foreach:site_profile[update_days],day,day_values}

    <div id="dotw_box_{day}" class="updates_days_of_the_week {day_values[class]}">
        {day_values[letter]}
    </div>
    {end:}
    </div>
{if:site_profile[site_update_details]}<div style="font-size: 10px; text-align: center; margin-top: 5px; font-weight: bold;">{site_profile[site_update_details]}</div>{end:}
