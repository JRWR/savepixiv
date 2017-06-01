<?php
$total = 1138764;
$x = 1;
$x = trim(file_get_contents("state"));
while($x <= $total){
echo PHP_EOL . "Starting $x" . PHP_EOL;
//$roomhtml = file_get_contents("http://chat.pixiv.net/api/capturepos.php?roomid=$x");
$roomhtml = downloadfile('http://chat.pixiv.net/roomtop.php?id=' . $x, 'http://chat.pixiv.net/');

if($roomhtml["returncode"] != 200){echo "room html returned {$roomhtml['returncode']}, sleeping 10s"; echo $roomhtml["headers"]; sleep(10); continue;}
@mkdir("stor/$x");
file_put_contents("stor/$x/$x.html", $roomhtml["content"]);
//find flashvars to get checkcode and check for NSFW
$pos = strpos($roomhtml["content"],"FlashVars");
if($pos === false){echo "room html returned bad FlashVars, Skipping"; $x = $x + 1; file_put_contents("state", $x); echo "wrote state $x" . PHP_EOL; continue;}
$flash = substr($roomhtml["content"],$pos, 256);

//extract data
$explode = explode(";",(explode("'", $flash))[2]);
// "roomid=13&amp;closed=1&amp;log=d27d7c57&amp;svr=chat-img01&amp;img=http%3A%2F%2Fchat-img01.pixiv.net%2F000%2F000%2F013%2Fd27d7c57%2Fimage_1137.png"
$srv = str_replace([0 => "svr=", 1 => "&amp"], "", $explode[3]);
$logid = str_replace([0 => "log=", 1 => "&amp"], "", $explode[2]);

$api = downloadfile('http://chat.pixiv.net/api/capturepos.php?roomid=' . $x, 'http://chat.pixiv.net/');
if($api["returncode"] != 200){echo "API returned {$api['returncode']}, sleeping 1s"; echo $api["headers"]; sleep(1); continue;}
$apijson = json_decode($api["content"], TRUE);
if(empty($apijson)){echo "API returned {$api['returncode']}, sleeping 1s"; echo $api["headers"]; sleep(1); continue;}
file_put_contents("stor/$x/$logid.json", $api["content"]);
$apicount = count($apijson);
echo "Downloading $apicount parts" . PHP_EOL;
$apicur = 0;
while (list($apikey, $apirow) = each($apijson)) {
$apicur = $apicur + 1;
//split room number
$splitid = str_pad($x, 9, 0, STR_PAD_LEFT);
echo "New ID: $splitid" . PHP_EOL;
$splitid = str_split($splitid, 3);
$getamfid = "$apicur" . "-" . "$apirow";
echo "Downloading $getamfid" . PHP_EOL;
$getamf = downloadfile('http://'.$srv.'.pixiv.net/'.$splitid[0].'/'.$splitid[1].'/'.$splitid[2].'/'.$logid.'/'.$getamfid.'.amf', 'http://chat.pixiv.net/');
if($getamf["returncode"] != 200){echo "AMF returned {$getamf['returncode']}, sleeping 1s"; echo $getamf["headers"]; sleep(1); $apicur = 0; reset($apijson); continue;}
file_put_contents("stor/$x/$getamfid.amf", $getamf["content"]);
$apicur = $apirow;
}
$x = $x + 1;
file_put_contents("state", $x);
echo "wrote state $x";
}



function downloadfile($url, $referer){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/cookiejar.cook");
        curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/cookiejar.cook");
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:53.0) Gecko/20100101 Firefox/53.0 email@jrwr.io ArchiveTeam Bot');
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $output["content"] = $body;
        $output["headers"] = $header;
        $output["returncode"] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $output;
}
