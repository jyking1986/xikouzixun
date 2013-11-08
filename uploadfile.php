<?php
$stor = new SaeStorage();
$domain = 'test';
$url = NULL;
if($_FILES["file"]["tmp_name"] != NULL)
{
$fileDataName = $_FILES["file"]["name"];
$ext = pathinfo($fileDataName, PATHINFO_EXTENSION);
$fileDataName= gen_uuid().".".$ext;
$dumpdata = file_get_contents($_FILES["file"]["tmp_name"]);
$dowLoadUrl = $stor->write($domain,$fileDataName,$dumpdata);
$url = $stor->getUrl($domain,$fileDataName);
echo "上传的文件:";
echo($url);
}

function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}
?>