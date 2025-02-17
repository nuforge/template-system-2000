    <base href="{pageValue(#mainurl#)}" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title>{pageValue(#title#):h} | {pageValue(#sitename#):h}</title>
    <meta name="Keywords" content="{pageValue(#keywords#)}" />
    <meta name="Description" content="{pageValue(#description#)}" />
    <meta name="robots" content="index,follow" />
    <meta name="verify-v1" content="yclDrrDH6aKr+qzs4Py8wJd734GZVD8kRejDEEll5xE=" />
    <!--[if lt IE 7.]>
    <script defer type="text/javascript" src="/js/pings.js"></script>
    <![endif]-->
    {foreach:scriptfiles,scriptfile}
    <script src="/js/{scriptfile[src]}" type="{scriptfile[type]}"></script>
    {end:}
    {foreach:stylesheets,stylesheet}
    <link href="/styles/{stylesheet}.css" rel="stylesheet" type="text/css" />
    {end:}
    <link rel="alternate" type="application/rss+xml" title="RSS Feed for {pageValue(#sitename#)}" href="feed://www.webcomictv.com/rss.xml" />
    {outputScriptEvents()}
