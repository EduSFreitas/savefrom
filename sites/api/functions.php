<?php
function ma($r,$i,$o){preg_match($r,$i,$m);return $m[$o];}

function duration($p){
	$h = ($p / 3600) % 24;
	$m = ($p / 60) % 60;
	$s = $p % 60;
	return ($h < 1 ? "" : $h.":").(($m < 10 && $h > 0) ? "0".$m : $m).":".($s < 10 ? "0".$s : $s);
}
?>