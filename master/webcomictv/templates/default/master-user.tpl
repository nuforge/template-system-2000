<div style="overflow:auto;">
    <div id="column1">
        <div class="round_dark">
            <h1><a href="/user/{profile[mem_unique]}/" title="{profile[mem_username]} - Member Profile" class="member_username">{profile[mem_username]}</a></h1>
            <div style="overflow: auto; margin-bottom: 10px;">
                {if:profile[mem_avatar]}<div class="profile_avatar"><img src="/avatar/{profile[avatar_filename]}" title="{profile[avatar_title]}" alt="{profile[avatar_title]}" border="0" width="100" height="100" align="left"/></div>{end:}
                <div class="profile_details">
                    <ul>
                        {if:profile[mem_age]}<li>{profile[mem_age]} Years Old {if:profile[mem_male]}<img src="/images/icons/male.png" width="16" height="16" align="absmiddle" alt="Male"/>{else:}<img src="/images/icons/female.png" width="16" height="16" align="absmiddle" alt="Female"/>{end:}</li>{end:}
                        {if:profile[mem_stamp]}<li>Joined: {dateFormat(#M jS, Y#,profile[mem_stamp])}</li>{end:}
                        {if:profile[mem_twitter]}<li>
                            <img src="/images/icons/social/twitter.png" width="16" height="16" alt="{twitter} Twitter" border="0" align="absmiddle"/> @<a href="http://twitter.com/{profile[mem_twitter]}" target="_blank" rel="nofollow" title="{profile[mem_twitter]} Twitter" rel="nofollow">{profile[mem_twitter]}</a>
                        </li>{end:}
                    </ul>
                </div>
            </div>
        </div>
        <div class="round_dark link_to_profile">
            <h2>Link to Profile</h2>
            {pageValue(#mainurl#)}user/{profile[mem_unique]}/
        </div>
        {if:profile[favorites]}
        <div class="round_dark">
            <h2>Favorite Sites</h2>
            <ul class="favorite_sites">
                {foreach:profile[favorites],favorite}
                <li>
                    <div style="float: right"><img src="/images/icons/{favorite[category_icon]}.png" width="16" height="16" alt="{favorite[category_title]}" border="0" align="absmiddle"/> {favorite[category_title]}</div>
                    <a href="/site/{favorite[site_unique]}/" class="webcomic_title" title="{favorite[site_title]} - {favorite[category_title]}">{favorite[site_title]}</a>
                </li>
                {end:}
            </ul>

        </div>
        {end:}
    </div>
    <div id="column2">
        <div class="round_dark">
            <a href="/user/{profile[mem_unique]}/" class="member_username">{profile[mem_username]}</a>
            {if:viewing_self}
            <span id="mem_status_span_container" onclick="toggleTextBox('mem_status');" class="has_pointer">
                <div style="float: right;"><img src="/images/ajax-loader.gif" border="0" align="absmiddle" style="display: none;" id="ajax_loader_user_status" /> <img src="/images/icons/pencil.png" width="16" height="16" alt="Edit" border="0" align="absmiddle" id="mem_status_edit_icon"/></div>

                <span id="mem_status_span">{profile[mem_status]}</span>

            </span>
            <span id="mem_status_edit"  style="display: none;" >
                <div style="float: right;">
                    <a onClick="toggleTextBox('mem_status');"><img src="/images/icons/cancel.png" title="Cancel" width="16px" height="16px" alt="X" border="0" align="absmiddle"/></a>
                    <a onClick="changeStatusMessage();"><img src="/images/icons/tick.png" width="16" height="16" title="Save Changes" alt="Save" border="0" align="absmiddle"/></a>
                </div>

                <input type="text" name="mem_status" id="mem_status" style="width: 480px;" class="inline_input"/>

            </span>

            {else:}<span id="user_profile_status">{profile[mem_status]}</span>{end:}

        </div>
        <h2>About {profile[mem_username]}</h2>
        {if:profile[mem_description]}
        <div class="round_dark">
            {bbcode(profile[mem_description]):h}
        </div>
        {end:}

        {if:profile[credits]}
        <div class="round_dark">
            <h2>Website Creative Credits</h2>
            <div style="overflow: auto;">
                {foreach:profile[credits],credit}
                <div class="user_credit">
                    <a href="/site/{credit[site_unique]}/" title="{credit[site_title]} - {credit[category_title]}"><img src="/images/sites/{credit[site]}-card-{credit[site_unique]}.png" border="0" title="{credit[site_title]}  - {credit[category_title]}" alt="{credit[site_title]}  - {credit[category_title]}" height="80px" width="150px"/></a>
                    <div><a href="/site/{credit[site_unique]}/" title="{credit[site_title]} - {credit[category_title]}" class="webcomic_title">{credit[site_title]}</a> - {credit[sc_title]}</div>
                    <div>{if:credit[sc_start_date]}From <strong>{dateFormat(#M Y#,credit[sc_start_date])}</strong> {end:}{if:credit[sc_end_date]}To <strong>{dateFormat(#M Y#,credit[sc_end_date])}</strong>{end:}</div>
                </div>
                {end:}
            </div>
        </div>

        {end:}
    </div>
</div>