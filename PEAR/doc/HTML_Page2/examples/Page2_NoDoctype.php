<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997 - 2004 The PHP Group                              |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Klaus Guenther <klaus@capitalfocus.org>                      |
// +----------------------------------------------------------------------+
//
// $Id: Page2_NoDoctype.php,v 1.1 2004/06/07 17:40:56 thesaur Exp $

require_once "HTML/Page2.php";

// With no initial settings, the following defaults are set:
//    -> lineends = unix (\12)
//    -> doctype = XHTML 1.0 Transitional
//                 (if set to "none", doctype and language are not set)
//    -> language = en
//    -> cache = false

$page = new HTML_Page2(array('doctype'=>'none','tab'=>'  '));

// "simple" Page title defaults to "New Page"

$page->addBodyContent("<h1>Headline</h1>");
$page->addBodyContent("<p>Some text</p>");
$page->display ();

?>
