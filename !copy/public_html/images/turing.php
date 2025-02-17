<?php
session_start();
ini_set("display_errors","0");
if(!isset($_SESSION['tur9853']))
	{
	die("Error generating turing number : no session specified. Get outta here ! :p");	// In order to arrive here, you do have to want it... so why be gentle with the intruder ? :D
	}

$code=strval($_SESSION['tur9853']);

// Creation of the picture, you shouldn't modify the dimensions unless you change the code's length. But well, you can do whatever you want... :)

$strlen = strlen($code);
$width=20+($strlen *12);	// width
$height=25;	// height
$img = imagecreate($width, $height) or die("Cannot Initialize new GD image stream");

// The colors...
$bgc = imagecolorallocate($img, 250, 250, 250);		// random background color... (not too dark though)
$black = imagecolorallocate($img, 0, 0, 0);
$vlgrey = imagecolorallocate($img, 200, 200, 200);
$lgrey = imagecolorallocate($img, 128, 128, 128);
$grey = imagecolorallocate($img, 25, 25, 25);
$red = imagecolorallocate($img, 250, 0, 0);
$blue = imagecolorallocate($img, 0, 0, 250);
$green = imagecolorallocate($img, 0, 128, 0);
$color = array( $red, $blue, $green);
// Let's paint the background
imagefilledrectangle($img, 0, 0, $width, $height, $bgc);

// Let's add some random lines (be careful not to put too many)

$num = mt_rand(7, 10);
for($i=0;$i< (($width / $num) +1); $i++)
{
	$x1= $i * ($width / $num);
	$x2= $i * ($width / $num);
	$y1=0;
	$y2=$height;
	imageline($img, $x1, $y1, $x2, $y2, $vlgrey);
}
$num = mt_rand(4, 7);
for($i=0;$i< (($height / $num) +1);$i++)
{
	$x1= 0;
	$x2= $width;
	$y1= $i * ($height / $num);
	$y2= $i * ($height / $num);
	imageline($img, $x1, $y1, $x2, $y2, $vlgrey);
}

$num = mt_rand(3, 5);
for($i=0;$i<$num;$i++)
	{
	if($i<2)
		{
		$x1=rand(0,$width);
		$y1=0;
		$x2=abs($x1-mt_rand(0,5));
		$y2=$height;
		}
	else
		{
		$x1=0;
		$y1=rand(0,$height);
		$x2=$width;
		$y2=abs($y1-mt_rand(0,5));
		}
	imageline($img, $x1, $y1, $x2, $y2, $lgrey);
	}

// Writes the code
$hor_pos=mt_rand(5,15); // horizontal position
for($i=0;$i<strlen($code);$i++)
	{
	$fore = $color[mt_rand(0, count($color)-1)];
	//$font = imagepsloadfont("/usr/share/fonts/Type1/n019004l.pfb");
	//imagepstext ($img, $code[$i], $font, 12, $fore, $back, $hor_pos, mt_rand(2,10));
	imagestring($img, 5, $hor_pos, mt_rand(2,10), $code[$i], $fore);
	//imagepsfreefont($font);
	$hor_pos+=mt_rand(10,15);
	}

// Now we're going to make it hard to read the picture :
// Let's spray some multicolored pixels
/*for($i=0;$i<300;$i++)
	{
	imagesetpixel($img, mt_rand(0,$width), mt_rand(0,$height), imagecolorallocate($img, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255)));
	}*/
// Let's add some random lines (be careful not to put too many)
$num = mt_rand(3, 5);
for($i=0;$i<$num;$i++)
	{
	if($i<2)
		{
		$x1=rand(0,$width);
		$y1=0;
		$x2=abs($x1-mt_rand(0,5));
		$y2=$height;
		}
	else
		{
		$x1=0;
		$y1=rand(0,$height);
		$x2=$width;
		$y2=abs($y1-mt_rand(0,5));
		}
	imageline($img, $x1, $y1, $x2, $y2, $grey);
	}

imageline($img, 0, 0, $width, 0, $vlgrey);
imageline($img, $width-1, 0, $width-1, $height, $vlgrey);
imageline($img, $width, $height-1, 0, $height-1, $vlgrey);
imageline($img, 0, $height, 0, 0, $height, $red);

// Creates the headers
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Content-Type: image/png");
imagePNG($img);
imagedestroy($img);
?>