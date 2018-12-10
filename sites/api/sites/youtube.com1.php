<?
@include("sites/".$input["host"].".decipher.php");

$video_id = ma("/v=([a-zA-Z0-9-_]+)/i",$input['url'],1);
preg_match("/sts\|([0-9]+)/",$GLOBALS["decipher_method"],$match);
if($match&&count($match)==2&&$match[1])
	$sts = $match[1];
else 
	$sts = 17437;
//don't need decipher
$url = "http://www.youtube.com/get_video_info?video_id=".$video_id."&asv=3&el=detailpage";
//need decipher
$ipList = ['37.58.82.168', '37.58.82.169', '37.58.82.170', '37.58.82.171','159.253.144.86'];
$ipIndex = rand(0,4);
//$url = "https://www.youtube.com/get_video_info?video_id=".$video_id."&el=info&asv=3&sts=".$sts;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$cky = "./sites/youtube.com.cookies.txt";
curl_setopt($ch, CURLOPT_COOKIEJAR, $cky);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cky);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($ch, CURLOPT_REFERER, 'http://www.youtube.com/watch?v='.$video_id);
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
$response = curl_exec($ch);
//split headers from body
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);
curl_close($ch);

//parse the response to an array
parse_str($body,$out);

//if useragent is not supprot,use else useragent and try again.
if($out["status"]=="ok" && $out["url_encoded_fmt_stream_map"]==""){
	$allUserAgent = @include("user_agent.php");
	$index = rand(0,count($allUserAgent));
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$cky = "./sites/youtube.com.cookies.txt";
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cky);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cky);
    curl_setopt($ch, CURLOPT_INTERFACE, $ipList[$ipIndex]);
	
	curl_setopt($ch, CURLOPT_REFERER, 'http://www.youtube.com/watch?v='.$video_id);
	curl_setopt($ch, CURLOPT_USERAGENT, $allUserAgent[$index]);
	$response = curl_exec($ch);
	//split headers from body
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$header = substr($response, 0, $header_size);
	$body = substr($response, $header_size);
	curl_close($ch);
	
	//parse the response to an array
	parse_str($body,$out);
}

if($out["url_encoded_fmt_stream_map"]!=""){
	//first set the title
	$title = urldecode($out["title"]);

	//declare formats array
	$order = array(22,18,37,38,
	160,133,134,135,136,137,264,138,266,
	83,82,85,84,139,140,141,5,6,34,35,13,17,36,
	43,44,45,46,100,101,102);
	$fmt = array(
		22=>array("type"=>"MP4","quality"=>"(Max 720p)"),
		18=>array("type"=>"MP4","quality"=>"480p"),
		37=>array("type"=>"MP4","quality"=>"1080p"),
		38=>array("type"=>"MP4","quality"=>"2160p"),
		
		160=>array("type"=>"MP4","quality"=>"144p (Video Only)"),
		133=>array("type"=>"MP4","quality"=>"240p (Video Only)"),
		134=>array("type"=>"MP4","quality"=>"360p (Video Only)"),
		135=>array("type"=>"MP4","quality"=>"480p (Video Only)"),
		136=>array("type"=>"MP4","quality"=>"720p (Video Only)"),
		137=>array("type"=>"MP4","quality"=>"1080p (Video Only)"),
		264=>array("type"=>"MP4","quality"=>"1440p (Video Only)"),
		138=>array("type"=>"MP4","quality"=>"2160p (Video Only)"),
		266=>array("type"=>"MP4","quality"=>"2160p-2304p (Video Only)"),
		
		83=>array("type"=>"MP4","quality"=>"[3D] - 240p"),
		82=>array("type"=>"MP4","quality"=>"[3D] - 360p"),
		85=>array("type"=>"MP4","quality"=>"[3D] - 520p"),
		84=>array("type"=>"MP4","quality"=>"[3D] - 720p"),
		
		139=>array("type"=>"M4A","quality"=>"64 kbps (Audio Only)"),
		140=>array("type"=>"M4A","quality"=>"128 kbps (Audio Only)"),
		141=>array("type"=>"M4A","quality"=>"256 kbps (Audio Only)"),
		
		5=>array("type"=>"FLV","quality"=>"240p"),
		6=>array("type"=>"FLV","quality"=>"270p"),
		34=>array("type"=>"FLV","quality"=>"360p"),
		35=>array("type"=>"FLV","quality"=>"480p"),
		
		13=>array("type"=>"3GP","quality"=>"144p"),
		17=>array("type"=>"3GP","quality"=>"144p"),
		36=>array("type"=>"3GP","quality"=>"240p"),
		
		43=>array("type"=>"WEBM","quality"=>"360p"),
		44=>array("type"=>"WEBM","quality"=>"480p"),
		45=>array("type"=>"WEBM","quality"=>"720p"),
		46=>array("type"=>"WEBM","quality"=>"1080p"),
		
		//278=>array("type"=>"WEBM","quality"=>"144p (Video Only)"),
		//242=>array("type"=>"WEBM","quality"=>"240p (Video Only)"),
		//243=>array("type"=>"WEBM","quality"=>"360p (Video Only)"),
		//244=>array("type"=>"WEBM","quality"=>"480p (Video Only)"),
		//245=>array("type"=>"WEBM","quality"=>"480p, 110 kbps (Video Only)"),
		//246=>array("type"=>"WEBM","quality"=>"480p, 210 kbps (Video Only)"),
		//247=>array("type"=>"WEBM","quality"=>"720p (Video Only)"),
		//248=>array("type"=>"WEBM","quality"=>"1080p (Video Only)"),
		//271=>array("type"=>"WEBM","quality"=>"1440p (Video Only)"),
		//272=>array("type"=>"WEBM","quality"=>"2160p (Video Only)"),
		
		//249=>array("type"=>"WEBM","quality"=>"48 kbps (Audio Only)"),
		//250=>array("type"=>"WEBM","quality"=>"64 kbps (Audio Only)"),
		//251=>array("type"=>"WEBM","quality"=>"160 kbps (Audio Only)"),
		
		100=>array("type"=>"WEBM","quality"=>"[3D] - 360p"),
		101=>array("type"=>"WEBM","quality"=>"[3D] - 360p"),
		102=>array("type"=>"WEBM","quality"=>"[3D] - 720p")
		
		//171=>array("type"=>"WEBM","quality"=>"128 kbps (Audio Only)"),
		//172=>array("type"=>"WEBM","quality"=>"192 kbps (Audio Only)")
	);

	$urlmap = explode(",",$out["url_encoded_fmt_stream_map"]);

	foreach($urlmap as $format){
		parse_str($format,$props);
		$itag = $props["itag"];
		$url = $props["url"];
		if($url!=""){
			if(!preg_match('/signature/',$url)){
				$sig = decipher($props["s"]);
				$url .= "&signature=".$sig;
			}
			$utitle = str_replace("\"","'",$title);
			$url .= "&title=".urlencode($utitle);
			$fmt[$itag]["url"] = $url;
			$fmt[$itag]["rclick"] = 0;
			parse_str($url,$pro);
			if(isset($pro["clen"]))
				$fmt[$itag]["size"] = $pro["clen"];
		}
	}
	
	$dashmap = explode(",",$out["adaptive_fmts"]);
	
	foreach($dashmap as $format){
		parse_str($format,$props);
		$itag = $props["itag"];
		$url = $props["url"];		
		$size = $props["clen"];		
		if($fmt[$itag]["url"]==null){
			if(!preg_match('/signature/',$url)){
				$sig = decipher($props["s"]);
				$url .= "&signature=".$sig;
			}
			$utitle = str_replace("\"","'",$title);
			$url .= "&title=".urlencode($utitle);
			$fmt[$itag]["url"] = $url;
			$fmt[$itag]["size"] = $size;
			$fmt[$itag]["rclick"] = 1;
		}
	}
	/*
	//test itag 5 if not 403
	if($fmt[5]["url"]!=""){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$fmt[5]["url"]);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$test5 = curl_exec($ch);
		curl_close($ch);
	}
	*/
	if(preg_match("/(403 Forbidden)/i",$test5)==1){
		$output["error"] = "Unable to fetch valid download links (403).";
	}else{
		//finally display info
		$output["info"] = array(
			"title"=>$title,
			"image"=>"http://i.ytimg.com/vi/".$video_id."/default.jpg",
			"url"=>"http://www.youtube.com/watch?v=".$video_id,
			"domain"=>"youtube.com",
			"user"=>urldecode($out["author"]),
			"duration"=>duration($out["length_seconds"])
		);
		$doitagain=1;
		$outi=0;
		//display links in order
		foreach($order as $ord){
			if($fmt[$ord]["url"]!=""){
				
				//rename to redirector if necessary (urls that have the "gcr" parameter set, need to be redirected to work)
				if(stristr($fmt[$ord]["url"],"gcr=us")){
					preg_match_all("/http(s)?:\/\/([a-zA-Z0-9-]+)./",$fmt[$ord]["url"],$matches);
					$fmt[$ord]["url"] = str_replace($matches[2][0],"redirector",$fmt[$ord]["url"]);
					$fmt[$ord]["url"] = str_replace('http:',"https:",$fmt[$ord]["url"]);
				}
				
				$output["download_links"][$outi] = array(
					"url"=>$fmt[$ord]["url"],
					"title"=>$title,
					"type"=>$fmt[$ord]["type"],
					"quality"=>$fmt[$ord]["quality"],
					"size"=>$fmt[$ord]["size"],
					"newtab"=>0,
					"saveas"=>$fmt[$ord]["rclick"]
				);
			$outi++;
			}
		}
		if($out["ttsurl"]!="" || $out["caption_tracks"]!=""){
			$output["download_links"][$outi] = array(
				"url"=>"http://keepvid.com/?url=".urlencode("http://youtube.com/watch?v=".$video_id)."&mode=subs",
				"type"=>"SRT",
				"newp"=>1,
				"quality"=>"Subtitles"
			);
			$outi++;
		}
		elseif(isset($out["player_response"])&&$out["player_response"]!=''){  //youtube改版，增加解析方法
			$playerResponse = json_decode($out["player_response"],true);
			if(isset($playerResponse['captions']['playerCaptionsTracklistRenderer']['captionTracks'][0]['baseUrl'])){
				$output["download_links"][$outi] = array(
						"url"=>"http://keepvid.com/?url=".urlencode("http://youtube.com/watch?v=".$video_id)."&mode=subs",
						"type"=>"SRT",
						"newp"=>1,
						"quality"=>"Subtitles"
				);
				$outi++;
			}
		}
		
		$output["download_links"][$outi] = array(
			"url"=>"http://keepvid.com/?url=".urlencode("http://youtube.com/watch?v=".$video_id)."&mode=mp3",
			"type"=>"MP3",
			//"newtab"=>1,
			"newp"=>1,
			"quality"=>"64/128 kbps"
		);
		$outi++;
		
	}
}elseif($out["status"]=="fail"){
	//invalid id, country restricted
	if(stristr($out["reason"],"country") || stristr($out["reason"],"copyright") || stristr($out["reason"],"not available")){
		//try keepvid helper and set kvh value to activate it
		$output["kvh"]=1;
		
		//START: print out deciphering function for keepvid helper
		?>
		var vid = "<? echo $video_id; ?>";
		function dswap(p1,p2){
			var l3 = p1[0];
				var l4c = p2 % p1.length;
			var l4 = p1[l4c];
			p1[0] = l4;
			p1[p2] = l3;
			return p1;
		}
		function dclone(p1,p2){
			return p1.slice(p2);
		}
		function dreverse(p1){
			return p1.reverse();
		}
		function decipher(sig){
			var sigs = sig.split("");
		<?
		$dm_r = fopen("sites/youtube.com.decipher.txt", "r");
		$deciphermethod = fread($dm_r,filesize("sites/youtube.com.decipher.txt"));
		fclose($dm_r);
		$decipherlines = explode("\r\n",$deciphermethod);
		foreach($decipherlines as $dl){
			$madl = explode("|",$dl);
			echo "\tsigs = d".$madl[0]."(sigs,".$madl[1].");\r\n";
		}
		?>
			sig = sigs.join("");
			return sig;
		}
		<?
		//END: print out deciphering function for keepvid helper
		
		//$output["error"] = "Sorry, we are unable to fetch download links because this video is <b>country restricted</b>.";
	}
	$output["error"] = $out["reason"];
	if(stristr($out["reason"],"parameters")) $output["error"] = "Invalid YouTube URL. Please check the URL and try again.";
}elseif(preg_match("/(402 Payment Required)/i",$headers)==1){
	//captcha required
	$output["error"] = "Unable to fetch links, captcha required (402).";
}else{
	//something else
	$output["error"] = "Something went wrong and we didn't catch what it was";
}
?>