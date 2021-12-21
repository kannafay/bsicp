<?php


header('Content-Type:application/json; charset=utf-8;');

require_once 'Controller/icp.fun.php';

@ $action = $_GET["a"];

if (!function_exists($action) OR empty($action)) {

	exit(json_encode(array('errCode' =>201,'errMsg'=>'请求参数错误!')));

}

$strictMode =  true;	// 是否开启严格模式，生产环境中设置成true
$safeMode = true; 		//是否开启安全模式

$legalParam = ["getIcplist","checkICP","login","isadmin",'getHomeinfo','checkPass'];	//注册合法参数

//执行严格模式下参数配置
if(!in_array($action, $legalParam) && $strictMode){

	exit(json_encode(array('errCode' =>201,'errMsg'=>'参数异常!')));

}

//安全模式下，过滤非法字符
if ($safeMode) {
	
	$getString = strtolower(urldecode($_SERVER["QUERY_STRING"]));
	
	if (strpos($getString, '<') || strpos($getString, '>') || strpos($getString, 'CONTENT-TRANSFER-ENCODING')) {
		
		exit(json_encode(array('errCode' =>201,'errMsg'=>'请勿提交非法字符')));

	}
}


$datas = json_decode($action(),true);
echo(json_encode(['errCode'=>200,'data'=>$datas]));