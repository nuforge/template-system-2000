<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Stijn de Reede <sjr@gmx.co.uk>                               |
// +----------------------------------------------------------------------+
//
// $Id: Basic.php,v 1.6 2007/07/02 16:54:25 cweiske Exp $
//

/**
* @package  HTML_BBCodeParser
* @author   Stijn de Reede  <sjr@gmx.co.uk>
*/


require_once 'HTML/BBCodeParser/Filter.php';




class HTML_BBCodeParser_Filter_Forums extends HTML_BBCodeParser_Filter
{

    /**
    * An array of tags parsed by the engine
    *
    * @access   private
    * @var      array
    */
    var $_definedTags = array(   'spoiler' => array( 'htmlopen'  => 'span style="background-color:#000000; color:#000000;"',
                                                'htmlclose' => 'span',
                                                'allowed'   => 'all',
                                                'attributes'=> array()),
                                'color' => array( 'htmlopen'  => 'span',
                                                'htmlclose' => 'span',
                                                'allowed'   => 'all',
                                                'attributes'=> array('color' =>'style=%2$scolor:%1$s%2$s')),
                                'quote' => array('htmlopen'  => 'blockquote',
                                                'htmlclose' => 'blockquote',
                                                'allowed'   => 'all',
                                                'attributes'=> array('quote' =>'cite=%2$s%1$s%2$s')),
                                'code' => array('htmlopen'  => 'pre',
                                                'htmlclose' => 'pre',
                                                'allowed'   => 'all',
                                                'attributes'=> array()),
                            );

}


?>