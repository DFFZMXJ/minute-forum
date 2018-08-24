<?php
/**
 * This file is built to test some features and let me debug easier.
 * For security, please remove it when you use in production.
 */
require("intialize.php");//In fact it should be initalize.php but I am lazy to change its name.
header("HTTP/1.1 503 Service Unavailable");
header("Content-Type:application/json");
$curl = curl_init();
curl_setopt($curl,CURLOPT_URL,"https://ptlogin2.qq.com/getface?appid=1006102&imgtype=3&uin=2477819731");
curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
curl_setopt($curl,CURLOPT_HEADER,0);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
echo json_encode([
    'curlQQAvatar'=>curl_exec($curl),
    'post\'{507FB828-D841-B256-F48B-0EE5ADFF8D28}\''=>$p=Database::select('posts','postid','{507FB828-D841-B256-F48B-0EE5ADFF8D28}')[0],
    'userOfLogged'=>$u=User::logged(),
    'isFoundInLikes'=>array_search($u['userid'],$p['likes'])
],JSON_PRETTY_PRINT);
curl_close($curl);