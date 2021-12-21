<?php
/**
 * Small is new Big
 * @Author   Glenlio<270462326.qq.com>
 * @DateTime 2021-09-25
 */

ini_set('date.timezone','Asia/Shanghai');
chdir(dirname(__FILE__));

require_once '../Models/Common.php';

/**
 * @return   [json]                   [邮件发送结果和加密字符串]
 */
function getVercode(){
	$webMail = $_GET["webMail"];
	
	if (empty($webMail) OR !preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$webMail)) {
		
		$res = array('errCode' => 201,'errMsg' => '管理邮箱无效请重新填写！');

	}else{
		
		$Do = sendVercode($webMail);
		$res = array('errCode' => 200,'errMsg' => '验证码已发送至您的邮箱请查收','nonceStr' =>$Do);

	}
	return json_encode($res);
}

// 发送邮件验证码
function sendVercode($webMail){
	
	$verCode = creatCode();

	//配置要发送的内容，可以是HTML
	
	$title = "北石ICP备案验证码";
	$content = "<h3>您正在申请北石ICP备案,您的备案验证码是:".$verCode."</h3>";

	//调用sendMail方法发送邮件
	$M = Common::sendMail($webMail,$title,$content);
	
	return cryptCode($webMail,$verCode);
}


//生成验证码
function creatCode(){
	for ($i=0; $i < 6; $i++) { 
		$randCode[] = mt_rand(1,9);
	}
	return join("",$randCode);	
}

//对验证码进行加密处理
function cryptCode($webMail,$verCode){

	$str = 'BSICP2021'.$webMail.$verCode;

	return md5(base64_encode($str));
}


function icpReg()
{
	/**
	 * [$...All 初始化数据]
	 * @var [String]	
	 */
	$webName = $_GET["webName"];
	$webDomain = $_GET["webDomain"];
	$webDesp = $_GET["webDesp"];
	$webMaster = $_GET["webMaster"];
	$webMail = $_GET["webMail"];
	$verCode = $_GET["verCode"];
	$nonceStr = $_GET["nonceStr"];

	/**
	 * 对数据进行清洗
	 * 	  当前版本未对XSS进行处理(入库已做编码处理可不考虑XSS),迭代时可考虑
	 * 	  更严格的正则测试;  2021-09-27
	 * 	  -------------------------------------------------------------------------
	 * 	  2021-10-05 更新:已全局处理XSS 
	 */
	if (empty($webName) OR empty($webDomain) OR empty($webDesp) OR empty($webMaster) OR empty($webMail) OR empty($verCode) OR empty($nonceStr)) {
		
		$res = array('errCode' => 201,'errMsg' => '必填项不能为空');

	}elseif (cryptCode($webMail,$verCode) !== $nonceStr) {
		
		$res = array('errCode' => 201,'errMsg' => '验证码有误请重新填写');

	}elseif (!isDomain($webDomain)){
		
		$res = array('errCode' => 201,'errMsg' => '网站域名不合法请重新输入！');

	}elseif (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$webMail)) {
		
		$res = array('errCode' => 201,'errMsg' => '管理邮箱无效请重新填写！');

	}else{
		
		$res = ireg($webName,$webDomain,$webDesp,$webMaster,$webMail);

	}
	return json_encode($res);
}

/**
 * @param    [String]                   $domain [待验证的域名]
 * @return   boolean                          [域名正则结果]
 */
function isDomain($domain)
{
	return preg_match("/^([0-9a-zA-Z-]{1,}\.)+([a-zA-Z]{2,})$/", $domain);
}

/**
 * @param    [String]                   $webName   [站点名称]
 * @param    [String]                   $webDomain [首页域名]
 * @param    [String]                   $webDesp   [站点描述]
 * @param    [String]                   $webMaster [所有者]
 * @param    [String]                   $webMail   [管理邮箱]
 * @return   [Json]                              [备案结果和状态]
 */
function ireg($webName,$webDomain,$webDesp,$webMaster,$webMail)
{
	$D = Common::Connect();

	$isReg = $D->count("icpmap",["domain"=>base64_encode($webDomain)]);

	//判断当前域名是否已经提交备案信息
	
	if ($isReg > 0) {
		
		$res = array('errCode' => 201,'errMsg' => '您的备案信息已存在，请勿重复提交!');

	}else{
		
		// 对待入库的数据进行编码
		
		$addicp = $D->insert("icpmap",[
			"name"=>base64_encode($webName),
			"domain"=>base64_encode($webDomain),
			"desp"=>base64_encode($webDesp),
			"master"=>base64_encode($webMaster),
			"email"=>base64_encode($webMail),
			"status"=>2
		]);

		/**----------------------------------------------------
		 * [$notiFication 通知管理员]
		 * @var [Fn]
		 * ----------------------------------------------------
		 */
		$notiFication = notiFication($webName,$webDomain,$webMail);
		
		$res = array('errCode' => 200,'errMsg' => '您的申请已提交，可在首页查询备案状态！');
	}
	return $res;
}


function notiFication($webName,$domain,$webMail)
{
	/**
	 * [$nMail 管理员邮箱地址]
	 * @var string
	 */
	$nMail = ""; // 管理员电子邮箱
	
	$title = "有新的备案信息请及时处理";
	$content = "<h3>站点名称:".$webName."</h3>";
	$content .= "<h3>站点域名:".$domain."</h3>";
	$content .= "<h3>站长邮箱:".$webMail."</h3>";

	$M = Common::sendMail($nMail,$title,$content);
}

/**
 * @version  [1.0]
 * @return   [Json]                   [查询结果]
 */

function searchIcp(){
	
	/**
	 * [$keyword 备案号或域名]
	 * @var [Number String]
	 */
	$keyword = $_GET['keyword'];

	$D = Common::Connect();
	if (empty($keyword)) {
		$res = array('errCode' => 201,'errMsg' => '请输入需要查询的备案号或域名!');
	}else{
		
		
		/**匹配查询结果
		 * 1: 当前版本为精确匹配，后期可考虑做模糊匹配;  Glenlio 2021/9/25
		 */
		$infos = $D ->get("icpmap",["inum","name","domain","desp","master","status","time"],[

			"AND"=>[
				"status[!]" => 0,
				"OR"=>[
					"inum"=>$keyword,
					"domain"=>base64_encode($keyword)
				]
			]
		]);

		//判断结果是否存在

		if($infos){
			$datas = array(
				'inum' =>$infos['inum'],
				"name"=>base64_decode($infos['name']),
				"domain"=>base64_decode($infos['domain']),
				"desp"=>base64_decode($infos['desp']),
				"master"=>base64_decode($infos['master']),
				'status' =>$infos['status'],
				'time' =>$infos['time']
			);
			
			$res = array('errCode' => 200,'datas'=>$datas);

		}else{
			
			$res = array('errCode' => 200,'datas'=>$infos);

		}
	}

	return json_encode($res);
}


/**
 * 后台获取ICP备案的信息
 */

function getIcplist()
{
	/**
	 * [$status 备案状态]
	 * @var [int]   0:注销  1：正常   2：待审核  3：已退回
	 */
	$status = $_GET["s"] == 1 ? [0,1] : $_GET["s"];
	$page = $_GET["page"] - 1;
	$limit = $_GET["limit"];
	
	$D = Common::Connect();
	$count = $D ->count("icpmap",["status"=>$status]);

	/**
	 * 根据分页信息查询，分页中的1，在数据库中的起始位置为 0
	 */
	
	$datas = $D ->select("icpmap",["inum","name","domain","desp","master","email","time","status"],[
		"status"=>$status,
		'LIMIT' => [$page * $limit, $limit]
	]);

	$infos = array();

	foreach ($datas as $data) {

		/**
		 * 对数据进行base64处理
		 */
		$inum = $data['inum'];
		$name = base64_decode($data['name']);
		$domain = base64_decode($data['domain']);
		$desp = base64_decode($data['desp']);
		$master = base64_decode($data['master']);
		$email = base64_decode($data['email']);
		$time = $data['time'];
		$status = $data['status'];

		$infos[] = array('inum' => $inum, 'name'=>$name,'domain'=>$domain,'desp'=>$desp,'master'=>$master,'email'=>$email,'time'=>$time,'status'=>$status);

	}
	$res = array("count"=>$count,"datas"=>$infos);
	return json_encode($res);
}

/**
 * @Author   Glenlio<270462326.qq.com>
 * @return   [Json]                    [备案的修改状态]
 */
function checkICP()
{
	/**
	 * [$option 执行参数 pass:back:lock]
	 * @var [string]
	 */
	$option = $_GET['c'];

	/**
	 * [$inums 备案号]
	 * @var [type]
	 */
	$inums = $_GET['d'];

	$status = 0;

	// 判断更新意图
	if($option == "pass"){
		$status = 1;
	}elseif ($option == "back") {
		$status = 3;
	}

	//执行更新命令
	$D = Common::Connect();
	$infos = $D ->update("icpmap",["status" => $status],["inum"=>$inums]);

	//发送邮件通知
	$sendMail = sendNoticeMail($status,$inums);

	/**
	 * [$res 构造返回结果]
	 * @var array 正常结果 errCode：200   errMsg:返回执行意图
	 */
	$res = array('errCode' => 200,'errMsg'=>$option);
	return json_encode($res);
}

// 登录
function login(){

	$username = $_GET['u'];
	$password = $_GET['p'];

	$D = Common::Connect();

	/**
	 * 判断密码是否正确
	 */
	if(md5($password) !== $D->get("config","password",["username"=>$username])){
		$res = array('errCode' => 201,'errMsg'=>'用户名或密码错误');
	}else{

		/**
		 * [$token 根据时间戳和密码生成管理员登录的令牌]
		 * @var [String(32)]
		 */
		$token = md5($username.$password.time());
		$D->update("config",["token" =>$token]);
		$adminMail = ""; // 管理员邮箱
  $M = Common::sendMail($adminMail,"后台登录成功",'您正在使用管理员账号登录，如非本人请及时修改密码。登录地址:'.$_SERVER['HTTP_HOST']);
		/**
		 * [$res 返回登录令牌给客户端]
		 * @var Array
		 */
		$res = array('errCode' => 200,'token'=>$token);
	}
	return json_encode($res);
}


// 判断是否为管理员登录
function isadmin()
{
	$token = $_GET['t'];
	$D = Common::Connect();

	/**
	 * 判断管理员令牌是否有效
	 */
	if($D->count("config",["token"=>$token]) > 0){
		
		$res = array('errCode' => 200,'errMsg'=>'验证成功');

	}else{

		$res = array('errCode' => 201,'errMsg'=>'验证失败');

	}

	return json_encode($res);
}

// 获取后台首页信息
function getHomeinfo()
{
	$D = Common::Connect();

	$res = array(
		'version' =>PHP_VERSION,
		'uname' =>php_uname(),
		'sapi' =>php_sapi_name(),
		// 'guid' =>php_logo_guid(),
		'isok' => $D->count("icpmap",["status"=>1]),
		'islock'=>$D->count("icpmap",["status"=>0]),
		'isback'=>$D->count("icpmap",["status"=>3]),
		'issub'=>$D->count("icpmap",["status"=>2])
	);
	return json_encode($res);
}

//修改密码
function checkPass()
{
	$oldPassword = $_GET['o'];
	$newPassword = $_GET['n'];

	if (empty($oldPassword) OR empty($newPassword)) {
		exit(json_encode(array('errCode' =>201,'errMsg'=>'请求参数错误!')));
	}

	$D = Common::Connect();
	$password = $D->get("config","password",["username"=>"admin"]);

	/**
	 * 判断原密码是否正确,迭代更新时可考虑多管理员和Token令牌是否有效
	 * 		1、当前模式：禁止管理员在多台设备上同时登录; 21.10.5  Glenlio
	 */
	if (md5($oldPassword) !== $password) {
		$res = array('errCode' => 201,'errMsg'=>'原密码错误请重新输入!');
	}else{
		//更新密码
		$D->update("config",["password"=>md5($newPassword)],["username"=>"admin"]);
		$res = array('errCode' => 200,'errMsg'=>'密码修改成功!');
	}
	return json_encode($res);
}

/**
 * 构造邮件内容
 * @param    [Html(String)]                    $temp  [模板类型]
 * @param    [Array]                    $infos [用户信息]
 * @return   [Html(String)]                           [模板内容]
 */
function getMailTemplate($temp,$infos)
{

	//获取邮件模板
	$temp = file_get_contents('../../common/temp/'.$temp.'.html');

	$temp = str_replace("{webmaster}",base64_decode($infos['master']),$temp);
	$temp = str_replace("{inum}",$infos['inum'],$temp);
	$temp = str_replace("{name}",base64_decode($infos['name']),$temp);
	$temp = str_replace("{domain}",base64_decode($infos['domain']),$temp);
	$temp = str_replace("{desp}",base64_decode($infos['desp']),$temp);
	return $temp;
}

/**
 * 发送邮件通知
 * @param    [String]                    $status [备案状态]
 * @param    [Number]                    $inum   [备案号]
 */
function sendNoticeMail($status,$inum)
{
	
	$D = Common::Connect();

	/**
	 * 判断$inum是否为数组
	 * 	True:消息列队入栈
	 * 	False:直接发送通知消息
	 */
	$inum = is_array($inum) ? $inum : array($inum);
	/**
	 * 获取邮件通知类型
	 */
	switch ($status) {
		case '0':
			$temp = "cancel";
			break;
		case '1':
			$temp = "success";
			break;
		case '3':
			$temp = "fail";
			break;
		default:
			$temp = "none";
			break;
	}
	
	//遍历通知列队数据
	foreach ($inum as $key => $value) {

		$infos = $D ->get("icpmap",["inum","name","domain","desp","master","email","status","time"],["inum"=>$value]);
		
		//创建邮件信息
		$nMail = base64_decode($infos["email"]);

		$title = "北石ICP备案通知";
		$content = getMailTemplate($temp,$infos);

		$M = Common::sendMail($nMail,$title,$content);
	}
}