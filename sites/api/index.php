<?php
header('Access-Control-Allow-Origin: *');
//input and output declare
$input = array("url"=>"");
$output = array("error"=>"");

//handle POST URL or GET for testing purposes
$input["url"] = @urldecode($_POST["url"]);
//$input["url"] = 'https://www.youtube.com/watch?v=bMUxpTb_wWc';
if($input["url"]=="") $input["url"] = urldecode($_GET["url"]);

$url = isset($_GET["url"]) ? $_GET["url"] : (isset($_POST["url"]) ? $_POST["url"] : '');
$url = urlencode($url);
/*$gethash = isset($_GET["gethash"]) ? $_GET["gethash"] : (isset($_POST["gethash"]) ? $_POST["gethash"] : false);
if(!$gethash || md5($url.'_keepvid') != $gethash){
	exit('hash error.');
}*/

//include gloabals & functions
include("functions.php");

//set headers
header('Content-Type: application/json');
//header('Access-Control-Allow-Origin: http://keepvid.com');

//parse host from url
preg_match_all("/http(s)?:\/\/(www.)?([^\/]+)/",$input["url"],$matches);
$input["host"] = $matches[3][0];

//check referer first (main server will be sending this referer value)
//if(!preg_match("/^(http(s)?:\/\/(www.)?keepvid.com)/",$_SERVER['HTTP_REFERER'])) $output["error"] = "Access to API denied.";

//if no url, output error
if($input["url"]=="") $output["error"] = "URL parameter not set.";
elseif($input["host"]=="") $output["error"] = "Could not recognize host. Please check the URL and try again.";

//continue if no errors so far
if($output["error"]==""){
	//include file associated with host
	if($input["host"]!="" && file_exists("sites/".$input["host"].".php")) @include("sites/".$input["host"].".php");
	elseif($input["host"]!="" && file_exists("sites/".$input["host"].".js")) $output["kvh"]=1;
	elseif($input["host"]!="" && !file_exists("sites/".$input["host"].".php") && !file_exists("sites/".$input["host"].".js")) $output["error"] = "The website you are trying to download from (<b>".$input["host"]."</b>) is not supported by KeepVid at the moment. If you would like to download from this site, feel free to e-mail us a request for this site to be added.";
}

echo json_encode($output, JSON_FORCE_OBJECT); //print out json array of info about video and download links
?>
