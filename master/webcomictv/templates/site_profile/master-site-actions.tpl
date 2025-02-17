{if:member}
{if:isFavorite}<a class="round_link" onClick="toggleFavorite({site_profile[site]});" id="favorite_site_link"><img src="/images/ajax-loader.gif" border="0" align="absmiddle" style="display: none;" id="ajax_loader_toggle_favorites" /><img src="/images/icons/delete.png" id="favorite_site_icon" width="16" height="16" alt="Favorite Icon" border="0" align="absmiddle"/> <span id="favorite_site_text">Remove From Favorites</span></a>
{else:}<a class="round_link strong_link" onClick="toggleFavorite({site_profile[site]});" id="favorite_site_link"><img src="/images/ajax-loader.gif" border="0" align="absmiddle" style="display: none;" id="ajax_loader_toggle_favorites" /><img src="/images/icons/add.png" id="favorite_site_icon" width="16" height="16" alt="Favorite Icon" border="0" align="absmiddle"/>  <span id="favorite_site_text">Add Site to Favorites</span></a>
{end:}
<a onClick="popupOpen('comment_popup');" class="round_link"><img src="/images/icons/comment.png" width="16" height="16" alt="Comment Icon" border="0" align="absmiddle"/> Post a Comment</a>
{else:}
<a href="http://www.webcomictv.com/register.html" class="round_link">Register For Free</a>
{end:}
{if:admin}<a href="http://www.webcomictv.com/admin/site/{site_profile[site_unique]}/" target="_blank" class="round_link">Admin</a>{end:}