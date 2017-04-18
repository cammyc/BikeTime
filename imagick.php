
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$filename = "profilepics/user.png";


$ext = pathinfo($filename, PATHINFO_EXTENSION);

if ($ext=="jpg" || $ext=="jpeg") {
$image_s = imagecreatefromjpeg($filename);
} else if ($ext=="png") {
$image_s = imagecreatefrompng($filename);
}

$width = imagesx($image_s);
$height = imagesy($image_s);


$newwidth = 285;
$newheight = 232;

$image = imagecreatetruecolor($newwidth, $newheight);
imagealphablending($image,true);
imagecopyresampled($image,$image_s,0,0,0,0,$newwidth,$newheight,$width,$height);

// create masking
$mask = imagecreatetruecolor($width, $height);
$mask = imagecreatetruecolor($newwidth, $newheight);



$transparent = imagecolorallocate($mask, 255, 0, 0);
imagecolortransparent($mask, $transparent);



imagefilledellipse($mask, $newwidth/2, $newheight/2, $newwidth, $newheight, $transparent);



$red = imagecolorallocate($mask, 0, 0, 0);
imagecopymerge($image, $mask, 0, 0, 0, 0, $newwidth, $newheight,100);
imagecolortransparent($image, $red);
imagefill($image,0,0, $red);

// output and free memory
header('Content-type: image/png');
imagepng($image);
imagedestroy($image);
imagedestroy($mask);
?>