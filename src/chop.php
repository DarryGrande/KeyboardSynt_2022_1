<?php
/**
 * Puzzle - a PHP/Javascript jigsaw puzzle
 *
 * Copyright (C) 2006 David Eder <david@eder,us>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @author David Eder <david@eder.us>
 * @copyright 2004 David Eder
 * @package puzzle
 * @version .1
 */

  define('TOP',    0);
  define('BOTTOM', 1);
  define('LEFT',   2);
  define('RIGHT',  3);

  $d = $_GET['d'];

  $r = $_GET['r'];
  $c = $_GET['c'];

  $rr = $_GET['rr'];
  $cc = $_GET['cc'];

  $g = str_pad(decbin(ord($_GET['g']) - 65), 4, '0', STR_PAD_LEFT);

  if($r == 0) $g{TOP} = '0';
  if($r == $rr - 1) $g{BOTTOM} = '0';
  if($c == 0) $g{LEFT} = '0';
  if($c == $cc - 1) $g{RIGHT} = '0';

  $img = imagecreatefromjpeg('images/' . $_GET['img']);

  $w = floor(imagesx($img) / $cc);
  $h = floor(imagesy($img) / $rr);

  $x = $c * $w;
  $y = $r * $h;

  $width = 2 * $d + $w;
  $height = 2 * $d + $h;

  $ie = (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') === false) ? true : false;

  if($ie)
  {
    $chop = imagecreate($width, $height);
    $trans = imagecolorallocate($chop, 255, 255, 255);
    imagecolortransparent($chop, $trans);
    imagetruecolortopalette($img, false, 250);
  }
  else
  {
    $chop = imagecreatetruecolor($width, $height);
    imageSaveAlpha($chop, true);
    imageAlphaBlending($chop, false);
    $trans = imagecolorallocatealpha($chop, 0, 0, 0, 127);
    imagefill($chop, 0, 0, $trans);
  }

  imagecopy($chop, $img, 0, 0, $x - $d, $y - $d, $width, $height);

  if($c == 0 || !$g[LEFT]) imagefilledrectangle($chop, 0, 0, $d - 1, $height, $trans);
  if($c != 0)
  {
    if($g[LEFT]) overlay($chop, 0, 0, $d, $height, 1, $trans);
    else         overlay($chop, $d, 0, $d, $height, 0, $trans);
  }

  if($c == $cc - 1 || !$g[RIGHT]) imagefilledrectangle($chop, $width - $d, 0, $width, $height, $trans);
  if($c != $cc - 1)
  {
    if($g[RIGHT]) overlay($chop, $width - $d, 0, $d, $height, 1, $trans);
    else          overlay($chop, $width - 2 * $d, 0, $d, $height, 0, $trans);
  }

  if($r == 0 || !$g[TOP]) imagefilledrectangle($chop, 0, 0, $width, $d - 1, $trans);
  if($r != 0)
  {
    if($g[TOP]) overlay($chop, 0, 0, $width, $d, 1, $trans);
    else overlay($chop, 0, $d, $width, $d, 0, $trans);
  }

  if($r == $rr - 1 || !$g[BOTTOM]) imagefilledrectangle($chop, 0, $height - $d, $width, $height, $trans);
  if($r != $rr - 1)
  {
    if($g[BOTTOM]) overlay($chop, 0, $height - $d, $width, $d, 1, $trans);
    else overlay($chop, 0, $height - 2 * $d, $width, $d, 0, $trans);
  }

  if($ie)
  {
    header('Content-type: image/gif');
    imagegif($chop);
  }
  else
  {
    header('Content-type: image/png');
    imagepng($chop);
  }

  function overlay($img, $x, $y, $w, $h, $z, $trans)
  {
    $mask = imagecreatetruecolor($w, $h);
    $c[0] = imagecolorallocate($mask, 255, 0, 255);
    $c[1] = imagecolorallocate($mask, 0, 0, 127);
    imagefilledrectangle($mask, 0, 0, imagesx($mask), imagesy($mask), $c[1-$z]);
    imagefilledellipse($mask, $w / 2, $h / 2, min($w, $h), min($w, $h), $c[$z]);
    imagecolortransparent($mask, $c[1]);

    imagecopymerge($img, $mask, $x, $y, 0, 0, $w, $h, 100);

    if($z)
    {
      imagefill($img, $x, $y, $trans);
      imagefill($img, $x + imagesx($mask) - 1, $y + imagesy($mask) - 1, $trans);
    }
    else
      imagefill($img, $x + $w / 2, $y + $h / 2, $trans);

    imagedestroy($mask);
  }

?>
