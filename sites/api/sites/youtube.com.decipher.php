<?php
//deciphering functions
function dswap($p1,$p2){
	$l3 = $p1[0];
	$l4c = $p2 % count($p1);
	$l4 = $p1[$l4c];
	$p1[0] = $l4;
	$p1[$p2] = $l3;
	return $p1;
}
function dclone($p1,$p2){
	return array_slice($p1,$p2);
}
function dreverse($p1){
	return array_reverse($p1);
}

//automatically update decipher method every 20 seconds
date_default_timezone_set('PRC'); //设置中国时区
if((time() - filemtime("./sites/youtube.com.decipher.txt"))>=20){
	$output["cipher"] = "yes";
	//make request to a youtube page
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://www.youtube.com/watch?v=bMUxpTb_wWc");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	$cky = "./sites/youtube.com.cookies.txt";
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cky);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cky);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_REFERER, 'http://www.youtube.com/');
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36");
	$data = curl_exec($ch);
    $error = curl_error($ch);
	curl_close($ch);

	$html5jsurl = "https://www.youtube.com".ma("/<script src=\"((.*)\/(base|html5player)(-new)?.js)/",$data,1);
	$sts = ma("/\"sts\":(\d*),/",$data,1);
	
	//request html5player js
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $html5jsurl);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$cky = "./sites/youtube.com.cookies.txt";
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cky);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cky);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_REFERER, 'http://www.youtube.com/');
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36");
	$data = curl_exec($ch);
	curl_close($ch);

	//find set("signature", to get decrypt function label
    $dflabel = str_replace("$","\\$",ma("/\|\|\"signature\",([a-zA-Z0-9$]+)\(/",$data,1));

	//find decrypt function
	$df = preg_replace("([\r\n]+)","",ma("/((var )?".$dflabel."=function\(a\){[^}]+})/",$data,1));

	//find swap, clone, reverse
	$scrlabel = str_replace("$","\\$",ma("/a=a.split\(\"\"\);([a-zA-Z0-9\$]+)./",$df,1));

	//find swap, clone, reverse functions
	$scr = preg_replace("/[\r\n]+/", "", ma("/var ".$scrlabel."={([a-zA-Z0-9\(\)\,\}\{\[\]=:;%\.\s]+)}};/",$data,1)."}");

	$scrx = explode("},",$scr);

	//get labels for swap, clone, reverse functions
	foreach($scrx as $scrf){
		$label = ma("/^([^:]+)/", $scrf,1);
		if(ma("/(splice)/",$scrf,1)=="splice") $methf[$label] = "clone";
		if(ma("/(a.length)/",$scrf,1)=="a.length") $methf[$label] = "swap";
		if(ma("/(reverse)/",$scrf,1)=="reverse") $methf[$label] = "reverse";
	}

	//finally get method
	$methodline = ma("/a=a.split\(\"\"\);(.*);return a.join/",$df,1);

	//finally build method
	$methods = explode(";",$methodline);
	$finalmethod = "";
	foreach($methods as $method){
		$mtypelabel = ma("/".$scrlabel.".([a-zA-Z0-9]+)\(/", $method,1);
		$finalmethod .= $methf[$mtypelabel]."|".ma("/".$scrlabel.".".$mtypelabel."\(a,([0-9]+)\)/", $method,1);
		$finalmethod .= "\r\n";
	}
	if(!$sts)
		$sts = 17452;
	$finalmethod .= "sts|".$sts."\r\n";

	//trim ends
	$finalmethod = trim($finalmethod);
    if(stripos($finalmethod,'reverse')===false && stripos($finalmethod,'clone')===false && stripos($finalmethod,'swap')===false)
        $finalmethod = "clone|2\r\nswap|2\r\nswap|51\r\nswap|9\r\nclone|2\r\nreverse|63\r\nswap|15\r\nclone|3\r\nsts|17579";

	//write method out to file and include it in the body/head somewhere somehow
	if($finalmethod!="" && $finalmethod!="|") file_put_contents("./sites/youtube.com.decipher.txt", $finalmethod);
	else $output["error"] = "Could not find decipher method.";
}

$GLOBALS["decipher_method"] = file_get_contents("./sites/youtube.com.decipher.txt");

function decipher($sig){
	$sigs = str_split($sig);
	preg_match_all("/(swap|clone|reverse)\|([0-9]+)/",$GLOBALS["decipher_method"],$dfuncs);
	foreach($dfuncs[1] as $key=>$value){
		if($value=="swap") $sigs = dswap($sigs,intval($dfuncs[2][$key]));
		elseif($value=="clone") $sigs = dclone($sigs,intval($dfuncs[2][$key]));
		elseif($value=="reverse") $sigs = dreverse($sigs);
	}
	$sig = join("",$sigs);
	return $sig;
}
?>
