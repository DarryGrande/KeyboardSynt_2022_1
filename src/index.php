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
?>
<script type="text/javascript" src="wz_dragdrop.js"></script>

<table width="500" height="200"><tr><td valign="top">
<?php for($i = 1; $i <= 5; $i++) echo "<a href=\"index.php?level=$i\">Level $i</a><br/>\n"; ?>
</td></tr></table>

<?php

  $rows = $cols = max(2, min(10, 2 * (isset($_GET['level']) ? $_GET['level'] : 2)));

  for($r = 0; $r < $rows; $r++)
  {
    for($c = 0; $c < $cols; $c++)
    {
      $h[$r][$c] = (rand(0, 100) > 50) ? 1 : 0;
      $v[$r][$c] = (rand(0, 100) > 50) ? 1 : 0;
    }
  }

  $files = glob('images/*.jpg');
  $img = $files[rand(0, count($files) - 1)];
  list($width, $height) = getimagesize($img);

  $d = floor(min($width / $cols, $height / $rows) / 5);

  $vw = floor($width / $cols);
  $vh = floor($height / $rows);

  $iw = $vw + 2 * $d;
  $ih = $vh + 2 * $d;

  $xinc = $vw;
  if($xinc % 4 == 0) $xinc /= 4;
  elseif($xinc % 3 == 0) $xinc /= 3;
  elseif($xinc % 2 == 0) $xinc /= 2;

  $yinc = $vh;
  if($yinc % 4 == 0) $yinc /= 4;
  elseif($yinc % 3 == 0) $yinc /= 3;
  elseif($yinc % 2 == 0) $yinc /= 2;

  $img = urlencode(basename($img));
  unset($files);

  for($r = 0; $r < $rows; $r++)
  {
    for($c = 0; $c < $cols; $c++)
    {
      $g = chr(65+bindec($v[$r][$c] . (1-$v[$r+1][$c]) . $h[$r][$c] . (1-$h[$r][$c+1])));
      $src = "chop.php?img=$img&r=$r&c=$c&cc=$cols&rr=$rows&d=$d&g=$g";
      echo "<img style=\"position:absolute\" name=\"t_{$r}_{$c}\" src=\"$src\" alt=\"\" width=\"$iw\" height=\"$ih\"/>\n";
      $list[] = "t_{$r}_{$c}";
    }
  }
  $list = '"' . join('","', $list) . '"';
?>

<script type="text/javascript">
  SET_DHTML(<?=$list?>);

  var cluster = new Array();

  for(r = 0; r < <?=$rows?>; r++)
  {
    for(c = 0; c < <?=$cols?>; c++)
    {
      id = 't_' + r + '_' + c;
      obj = dd.elements[id];
      obj.moveTo(<?=$xinc?> * Math.round(Math.random() * 800 / <?=$xinc?>), <?=$yinc?> * Math.round(Math.random() * 600 / <?=$yinc?>));
      obj.row = r;
      obj.col = c;
      obj.cluster = id;
      cluster[id] = new Array();
      cluster[id][id] = id;
    }
  }

  function merge_cluster(a, b)
  {
    for(i in cluster[b])
    {
      obj = dd.elements[cluster[b][i]];
      cluster[a][obj.name] = obj.name;
      obj.cluster = a;
    }
  }

  function align_cluster(a)
  {
    for(i in cluster[a])
    {
      obj = dd.elements[cluster[a][i]];
      obj.moveTo(<?=$xinc?> * Math.round(obj.x / <?=$xinc?>), <?=$yinc?> * Math.round(obj.y / <?=$yinc?>));
    }
  }

  function my_DragFunc()
  {
    for(i in cluster[dd.obj.cluster])
    {
      obj = dd.elements[cluster[dd.obj.cluster][i]];
      if(obj.name != dd.obj.name)
      {
        ox = dd.obj.x + (obj.col - dd.obj.col) * <?=$vw?>;
        oy = dd.obj.y + (obj.row - dd.obj.row) * <?=$vh?>;
        obj.moveTo(ox, oy);
      }
    }
  }

  function my_DropFunc()
  {
    align_cluster(dd.obj.cluster);

    for(r = -1; r <= 1; r++)
    {
      for(c = -1; c <= 1; c++)
      {
        if(c != 0 || r != 0)
        {
          obj = dd.elements['t_'+(dd.obj.row + r)+'_'+(dd.obj.col + c)];
          if(obj)
          {
            if(dd.obj.x + c * <?=$vw?> == obj.x && dd.obj.y + r * <?=$vh?> == obj.y)
            {
              merge_cluster(dd.obj.cluster, obj.cluster);
            }
          }
        }
      }
    }    

  }

</script>
