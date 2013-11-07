<?php
/**
  * wechat php test tag 1.0.1
  */

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
        }
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