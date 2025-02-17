<?php
session_start();
require_once ('/usr/local/lib/php/master/class.my_flexy_handler.php');
$flexyHandler = new my_flexy_handler('_SITE',$_GET['template_plugin'],$_GET['template_page']);
?>