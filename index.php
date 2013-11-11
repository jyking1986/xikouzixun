<?php

class MessageBase{
    protected $ToUserName;
    protected $FromUserName;
    protected $CreateTime;
    protected $MsgType;
    protected $Message;
}

interface IMessage{
    function serialize($xml);
    function deserialize();
}

class textMessage implements IMessage{
    protected $Content;
    protected $MsgId;
    function serialize($xml){
        $Content=$xml->Content;
        $MsgId=$xml->MsgId;
    }
    function deserialize(){
        $messageFormat="<Content><![CDATA[%s]]></Content>";
        return sprintf($messageFormat,$Content);
    }
}

class imageMessage implements IMessage{
    protected $PicUrl;
    protected $MsgId;
    function serialize($xml){
        $PicUrl=$xml->PicUrl;
        $MsgId=$xml->MsgId;
    }
    function deserialize(){
        $messageFormat="<PicUrl><![CDATA[%s]]></PicUrl>";
        return sprintf($messageFormat,$PicUrl);
    }
}

class MessageHandler{
    function serialize($xml){
        $postObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $messageBase=new MessageBase();
        $messageBase->FromUserName = $postObj->FromUserName;
        $messageBase->ToUserName = $postObj->ToUserName;
        $messageBase->CreateTime = $postObj->CreateTime;
        $messageBase->MsgType = $postObj->MsgType;
        $class = new ReflectionClass($messageBase->MsgType.'Message');
        $messageBase->Message  = $class->newInstanceArgs();
        $messageBase->Message->serialize($postObj);
        return $messageBase;
    }

    function deserialize($messageBase){
        $messageBaseFormat="<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType>%s</xml>"; 
        return sprintf($messageBaseFormat, 
            $messageBase->ToUserName,
            $messageBase->FromUserName,
            $messageBase->CreateTime,
            $messageBase->MsgType,
            $messageBase->Message->deserialize());
    } 
}





define("TOKEN", "wechat");
$wechatObj = new wechatCallbackapiTest();

if ($_SERVER["REQUEST_METHOD"] == "POST"){

    $wechatObj->responseMsg();

}else if($_SERVER["REQUEST_METHOD"] == "GET"){
    $wechatObj->valid(); 
}

class wechatCallbackapiTest
{
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }

    public function responseMsg()
    {

            //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $this->logMessage($postStr);

        $messageHandler=new MessageHandler();
        $messageBase=$messageHandler.serialize($postStr);
        $messageToReturn=new MessageBase();
          $messageToReturn->ToUserName=$messageBase->FromUserName;
          $messageToReturn->FromUserName=$messageBase->ToUserName;
          $messageToReturn->CreateTime=time();

      if($messageBase->MsgType == "text"){
        $messageToReturn->MsgType="image";
        $messageBase->Message=new imageMessage();
        $stor = new SaeStorage();
        $url = $stor->getUrl($domain,$fileDataName);
        $messageBase->Message->PicUrl=$url;

    }else{
        $messageToReturn->MsgType="text";
        $messageBase->Message=new textMessage();
        $messageBase->Message->Content="try send text message.";

    }

    echo $messageHandler->deserialize($messageToReturn);
/*


        //extract post data
      if (!empty($postStr)){

         $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
         $fromUsername = $postObj->FromUserName;
         $toUsername = $postObj->ToUserName;
         $keyword = trim($postObj->Content);
         $time = time();
         $textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content></xml>";             
         if(!empty( $keyword ))
         {
            $msgType = "text";
            $contentStr = "Welcome to wechat world!";
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
        }else{
           echo "Input something...";
       }

   }else {
       echo "";
       exit;
   }*/
}
public function logMessage($postStr)
{
    $mysql = new SaeMysql();

    $sql = "INSERT  INTO `log` ( `content` , `catagory`  ) VALUES ( '".$postStr."','Event' ) " ;
    $mysql->runSql( $sql );
    if( $mysql->errno() != 0 )
    {
        die( "Error:" . $mysql->errmsg() );
    }

    $mysql->closeDb();

}

private function checkSignature()
{
    $signature = $_GET["signature"];
    $timestamp = $_GET["timestamp"];
    $nonce = $_GET["nonce"];	

    $token = TOKEN;
    $tmpArr = array($token, $timestamp, $nonce);
    sort($tmpArr);
    $tmpStr = implode( $tmpArr );
    $tmpStr = sha1( $tmpStr );

    if( $tmpStr == $signature ){
     return true;
 }else{
     return false;
 }
}
}

?>