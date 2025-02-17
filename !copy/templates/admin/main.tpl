<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <base href="{page[metaurl]}" />
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <title>{page[title]} :: {page[sitename]}</title>
        <meta name="Keywords" content="{page[keywords]}" />
        <meta name="Description" content="{page[description]}" />
        <meta name="robots" content="index,follow" />
        {foreach:tpldirs,style}
        <link href="{page[mainurl]}styles/{style}.css" rel="stylesheet" type="text/css" />
        {end:}
    </head>
    <body>
        <div id="content">
            <div id="utility">{if:member[member]}<strong>Welcome: </strong>{member[username]} - <a href="logout.html">logout</a>{else:}<a href="login.html">login</a>{end:}</div>
            <div id="container">
                <div id="header">
                    <div id="search">
                        <form id="searchfrm" name="searchfrm" method="post" action="index.php?plugin=admin&amp;amp;pg=search">
                            Quick Search:
                            <select name="field" id="field">
                                <option value="affid">Affid</option>
                                <option value="username">Username</option>
                                <option value="domain">Domain</option>
                            </select>
                            <input name="query" type="text" id="query" size="35" />
                            <input type="submit" name="search" value="Go" />
                        </form>
                    </div>
                    <h1>{page[sitename]} : Administrative Functions</h1>
                </div>
                <div id="sidemenu">
                    <h2>Navigation</h2>{if:member[member]}
	  {else:}
                    <ul>
                        <li><a href="login.html">Login</a></li>
                    </ul>
	  {end:}
                </div>
                <div id ="main"> {output(pg)} </div>
                <div id="footer">d
                </div>
            </div>
        </div>
    </body>
</html>
