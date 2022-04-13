# 北石ICP备案系统

### 系统介绍
该系统用php开发，轻量，功能强大。请勿与国家工信部ICP备案相比较，本系统无实际用途，纯属娱乐项目。

### 项目截图

![](https://dd-static.jd.com/ddimg/jfs/t1/205895/31/11684/1190478/616c136dEb111777b/d31cd9587331980a.png)

![](https://dd-static.jd.com/ddimg/jfs/t1/205966/33/11640/1248145/616c13a3Eb0e12366/205e57c0147d0e87.png)

![](https://dd-static.jd.com/ddimg/jfs/t1/216925/7/752/1403517/616c13f9E45cae3a3/cf54d3958efde93a.png)

### 使用说明

#### 使用建议：

php版本建议：7.3

#### 使用方法：

下载master分支zip文件，上传至服务器解压

上传数据库bsicp.sql

完成后修改以下信息：

数据库和电子邮箱：/API/Models/Common.php

管理员邮箱地址：/API/Controller/icp.fun.php（172行, 353行）

邮件通知模板：/common/temp/cancel.html、fail.html、success.html

网站标题、内容、favicon图标：index.html、result.html

背景图：/css/style.css、style-result.css

#### 管理后台：

后台地址：域名/Admin

后台用户名/密码：admin/admin

### 参与贡献

神秘布偶猫、Glenlio

### 版本更新

#### 当前版本：

版本v1.2：

-增加后台登录邮件提醒

-增加备案提交邮件通知

-增加备案申请成功、失败、注销邮件通知

#### 历史版本：

版本v1.1：

-增加后台管理

版本v1.0：

-正式发布

