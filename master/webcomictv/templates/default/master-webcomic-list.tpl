
{if:alphas}
<div class="letter_navigation round_dark">
    <div class="jump_to">Jump To:</div>
    <a href="{list_sort}0/" title="Webcomic Sites Beginning with Numbers" class="letter_link">#</a>
    {foreach:alphas,letter}
    <a href="{list_sort}{letter}/" title="Webcomic Sites Beginning with {letter}" class="letter_link">{letter}</a>
    {end:}
</div>
{end:}

<div class="round_dark nav">
    <div style="margin: 0 auto;">
        {if:showOrder}
        <div class="nav_order">
            Order by: <a href="/sites/recent/p{pagination[current]}.html" title="Webcomic List - Page {pagination[current]}" class="nav_order_link {currentOrder[new]}">New Webcomics</a> <a href="/sites/p{pagination[current]}.html" title="Webcomic List - Page {pagination[current]}" class="nav_order_link {currentOrder[title]}">Webcomic Titles</a>
        </div>
        {end:}
        <div class="nav_pages">
            {if:pagination[first]}<a href="{list_sort}p{pagination[first]}.html" title="Webcomic List - Page {pagination[first]}" class="nav_page_link">&laquo;</a>{end:}
            {foreach:pagination[range],k,v}
            {if:compare(v,pagination[current])}<div class="nav_current_page">{v}</div>
            {else:}<a href="{list_sort}p{v}.html" title="Webcomic List - Page {v}" class="nav_page_link">{v}</a> {end:}
            {end:}
            {if:pagination[lastshow]}<a href="{list_sort}p{pagination[last]}.html" title="Webcomic List - Page {pagination[last]}" class="nav_page_link">&raquo;</a>{end:}
        </div>
        {if:pagination[previous]}<a href="{list_sort}p{pagination[previous]}.html" title="Webcomic List - Page {pagination[previous]}" class="nav_page_link">Previous</a>
        {else:}<em>Previous</em>{end:}
        {if:pagination[next]}<a href="{list_sort}p{pagination[next]}.html" title="Webcomic List - Page {pagination[next]}" class="nav_page_link">Next</a>
        {else:}<em>Next</em>{end:}
    </div>
</div>



{foreach:sites,site}
<div class="round_dark site_list_webcomic">
    <div class="site_list_details">
        <ul>
            {if:site[site_recommended]}
            <li><img src="/images/icons/star.png" width="16" height="16" alt="{page[site_name]} Recommended" border="0" align="absmiddle"/> Recommended</li>{end:}

            {if:site[category]}
            <li><img src="/images/icons/{site[category_icon]}.png" width="16" height="16" alt="{site[category_title]}" border="0" align="absmiddle"/>{if:site[site_adultwebcomic]} Adult{end:} {site[category_title]}</li>

            {if:site[site_paysite]}
            <li><img src="/images/icons/money_dollar.png" width="16" height="16" alt="{site[site_title]} Paysite" title="{site[site_title]} Paysite" border="0" align="absmiddle"/> Paysite</li>{end:}

            {if:site[site_content_rating]}
            <li><img src="/images/icons/flag_blue.png" width="16" height="16" alt="{site[site_title]} Content Rating" border="0" align="absmiddle"/> <strong>{site[cr_title]}</strong> {if:site[site_nsfw]}<sup style="font-size: 10px; font-weight: bold; color: #cc0000;" >NSFW</sup>{end:}</li>
            {else:}
            {if:site[site_nsfw]}
            <li style="font-weight: bold; color: #cc0000;">
                <img src="/images/icons/exclamation.png" width="16" height="16" alt="{site[site_title]} contains NSFW Content" title="{site[site_title]} contains NSFW Content" border="0" align="absmiddle"/> NSFW Content</li>{end:}

            {end:}

            {if:site[site_ended]}
            <li><img src="/images/icons/book.png" width="16" height="16" alt="{site[site_title]} Completed Webcomic" border="0" align="absmiddle"/> Completed</li>{else:}

            {if:site[site_active]}{else:}
            <li><img src="/images/icons/error.png" width="16" height="16" alt="{site[site_title]} Inactive" border="0" align="absmiddle"/> Inactive</li>{end:}

        </ul>
        {end:}

        {end:}
    </div>
    <div class="site_list_image">
        {if:site[site_card]}<a href="/site/{site[site_unique]}/" title="{site[site_title]} - Webcomic"><img src="/images/sites/{site[site]}-card-{site[site_unique]}.png" width="150" height="80" alt="{site[site_title]}" border="0"/></a>{end:}
    </div>
    <div class="site_list_description">
            {if:admin}
        <div class="site_exit">
            {if:site[site_active]} <a href="/site/{site[site_unique]}/exit/" target="_blank" rel="nofollow" title="{site[site_title]}" class="site_exit_link round_link">Visit {site[site_title_short]} &raquo;</a>{end:}
            <a href="http://www.webcomictv.com/admin/site/{site[site_unique]}/" target="_blank" class="site_exit site_exit_link round_link">Admin</a>
            </div>
            {end:}
        <a href="/site/{site[site_unique]}/" title="{site[site_title]} - Webcomic">{site[site_title]}</a>{if:site[site_new]} <img src="/images/icons/new.png" width="16" height="16" alt="{page[site_name]} Recenctly Added" border="0" align="absmiddle"/>{end:}

        {text2html2(site[site_description_brief]):h}

        <div style="font-size: 9px;"></div>
    </div>
</div>
{end:}
<div class="round_dark nav">
    <div style="margin: 0 auto;">
        <div class="nav_pages">
            {if:pagination[first]}<a href="{list_sort}p{pagination[first]}.html" title="Webcomic List - Page {pagination[first]}" class="nav_page_link">&laquo;</a>{end:}
            {foreach:pagination[range],k,v}
            {if:compare(v,pagination[current])}<div class="nav_current_page">{v}</div>
            {else:}<a href="{list_sort}p{v}.html" title="Webcomic List - Page {v}" class="nav_page_link">{v}</a> {end:}
            {end:}
            {if:pagination[lastshow]}<a href="{list_sort}p{pagination[last]}.html" title="Webcomic List - Page {pagination[last]}" class="nav_page_link">&raquo;</a>{end:}
        </div>
        {if:pagination[previous]}<a href="{list_sort}p{pagination[previous]}.html" title="Webcomic List - Page {pagination[previous]}" class="nav_page_link">Previous</a>
        {else:}<em>Previous</em>{end:}
        {if:pagination[next]}<a href="{list_sort}p{pagination[next]}.html" title="Webcomic List - Page {pagination[next]}" class="nav_page_link">Next</a>
        {else:}<em>Next</em>{end:}
    </div>
</div>


{if:alphas}
<div class="letter_navigation round_dark">
    <div class="jump_to">Jump To:</div>
    <a href="{list_sort}0/" title="Webcomic Sites Beginning with Numbers" class="letter_link">#</a>
    {foreach:alphas,letter}
    <a href="{list_sort}{letter}/" title="Webcomic Sites Beginning with {letter}" class="letter_link">{letter}</a>
    {end:}
</div>
{end:}