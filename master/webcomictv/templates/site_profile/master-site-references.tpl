
<ul class="sources">
    {foreach:site_profile[references],reference}
    <li><a href="http://www.webcomictv.com/shows/{reference[wc_encoded]}/index.html" title="{reference[wc_title]}" target="_blank" class="webcomic_title">{reference[wc_title]}</a><br/>
        <a href="http://www.webcomictv.com/shows/{reference[wc_encoded]}/comic/{reference[comic]}.html" title="{reference[comic_title]}" target="_blank" class="source">{reference[comic_title]}</a></li>
    {end:}
</ul>