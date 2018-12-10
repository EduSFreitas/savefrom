<?php
$secondary_server_url = 'https://www.savefrom.wang/api/index.php';
$url = urldecode($_POST['url']);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $secondary_server_url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, ["url"=>$url]);
curl_setopt($ch, CURLOPT_TIMEOUT, 8);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_REFERER, 'https://www.savefrom.wang/');
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
$apijson = curl_exec($ch);
$curl_error = curl_error($ch);
$total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
curl_close($ch);
$downloadInfo = json_decode($apijson,true);
if(!isset($downloadInfo['download_links']) || $downloadInfo['error']!=''){
    echo '<div class="result-box simple "><div class="message">This video can\'t be downloaded .</div ></div >';
    exit;
}
$response = file_get_contents('download_result.html');
$downloadInfo['info']['title'] = str_replace('"','-',$downloadInfo['info']['title']);
$downloadInfo['download_links'][0]['quality'] = str_replace('(Max 720p)', '720p', $downloadInfo['download_links'][0]['quality']);
$fileName = $downloadInfo['info']['title'] . '.' . strtolower($downloadInfo['download_links'][0]['type']);
$response = str_replace(['__DOWNLOADIMG__', '__TITLE__', '__FILENAME__', '__DOWNLOADLINK__', '__FROMAT__', '__QUALITY__'],
                        [$downloadInfo['info']['image'], $downloadInfo['info']['title'], $fileName, $downloadInfo['download_links'][0]['url'],
                            $downloadInfo['download_links'][0]['type'], $downloadInfo['download_links'][0]['quality']], $response);
$downloadItems = '';
$aItem = '';
foreach($downloadInfo['download_links'] as $item){
    $item['quality'] = str_replace('(Max 720p)', '720p', $item['quality']);
    $fileName = $downloadInfo['info']['title'] . '.' . strtolower($item['type']);
    $aItem .= '<a ';
    if($item['type']=='M4A')
        $aItem .= ' class="audio link link-download subname ga_track_events"';
    elseif(stripos($item['quality'],'(Video Only)')!==false)
        $aItem .= ' class="no-audio link link-download subname ga_track_events"';
    else
        $aItem .=' class="link link-download subname ga_track_events"';
    $quality = str_replace([' (Video Only)',' (Audio Only)'], ['',''], $item['quality']);
    $aItem .= ' download="'.$fileName.'"'.
            ' data-quality="'.$quality.'"'.
            ' data-type="'.$item['type'].'"'.
            ' href="'.$item['url'].'"'.
            ' >'.$item['type'].
            ' <span class="subname">'.$quality.
            '</span></a>';
}
$response = str_replace('__DOWNLOADITEMS__',$aItem,$response);
echo $response;
?>