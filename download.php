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


//$x = $x + 1;
//file_put_contents("state", $x);
//echo "wrote state $x";
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
