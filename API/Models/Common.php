<?php

require_once 'GlenDB.class.php';
require_once 'Mail.class.php';

use GlenDB\GlenDB;

class Common {

 public static function Connect(){
  $database = new GlenDB([
   'database_type' => 'mysql',
   'database_name' => '', // 数据库名
   'server' => 'localhost',
   'username' => '', // 数据库用户名
   'password' => '', // 数据库密码
   'charset' => 'utf8', 
   'option' => [PDO::ATTR_EMULATE_PREPARES => false,PDO::ATTR_STRINGIFY_FETCHES=>false]
  ]);
  return $database;
 }

 public static function sendMail($smtpemailto,$mailsubject,$mailbody){

  $smtpserver = ""; // 邮件服务器地址
  $smtpserverport = 25; // 服务器端口，默认25
  $smtpuser = ""; // 电子邮箱
  $smtppass = ""; // 邮箱密码（密钥）
  $smtpNickname = ""; // 发件人
  $smtpusermail = ""; // 电子邮箱
  
  $mailtype = "HTML"; 
  $smtp = new smtp($smtpserver, $smtpserverport, true, $smtpuser, $smtppass);
  $smtp->debug = false;
  $smtp->sendmail($smtpemailto,$smtpNickname,$smtpusermail, $mailsubject, $mailbody, $mailtype);
 }
}
