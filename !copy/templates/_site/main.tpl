<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="{pageValue(#mainurl#)}" />
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <title>{pageValue(#title#)} | {pageValue(#sitename#)}</title>
        <meta name="Keywords" content="{pageValue(#keywords#)}" />
        <meta name="Description" content="{pageValue(#description#)}" />
        <meta name="robots" content="index,follow" />
        <!-- Bootstrap -->
        <link href="/css/bootstrap.min.css" rel="stylesheet">
        {foreach:stylesheets,stylesheet}
        <link href="/styles/{stylesheet}.css" rel="stylesheet" type="text/css" />
        {end:}
        {foreach:scriptfiles,scriptfile}
        <script src="/js/{scriptfile[src]}" type="{scriptfile[type]}"></script>
        {end:}
        <!--[if lt IE 7.]>
        <script defer type="text/javascript" src="/js/pings.js"></script>
        <![endif]-->
        {outputScriptEvents()}
    </head>

    <body>
                <div id="blanket" style="display: none;"></div>

                <header id="layout-header" class="header-navbar">
                    
                    <nav id="layout-nav" class="navbar navbar-inverse navbar-fixed-top" role="navigation">
                        <div class="container">
                            <div class="navbar-header" style="line-height: 50px;">
                                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                                    <span class="sr-only">Toggle navigation</span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                </button>
                                <a class="pull-left"  style="vertical-align: middle;" href="/" title="{pageValue(#sitename#)}">{pageValue(#sitename#)}</a>
                            </div>
                            <div class="collapse navbar-collapse navbar-main-collapse">
                                <ul class="nav navbar-nav navbar-right">
                                    <li><a href="/" title="{pageValue(#sitename#)}">Home</a></li>
                                    <li><a href="/about.html" title="About {pageValue(#sitename#)}">About</a></li>
                                    <li><a href="/contact.html" title="Contact {pageValue(#sitename#)}">Contact</a></li>
                                </ul>
                            </div>
                        </div>
                    </nav>
                </header>

                <div id="layout-content">
                    <div class="container">
                        <div id="content">{output(pg)}</div>

                        <div id="footer">
                            <p>Copyright &copy; {dateFormat(#Y#)} <a href="{pageValue(#mainurl#)}" title="{pageValue(#sitename#)}" target="_blank" class="copyright">{pageValue(#sitename#)}</a>. All rights reserved.</p>
                        </div>
                    </div>
                </div>

                
                    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
                    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
                    <!-- Include all compiled plugins (below), or include individual files as needed -->
                    <script src="js/bootstrap.min.js"></script>
                    
            </body>
</html>
