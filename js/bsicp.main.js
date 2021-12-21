(function(e){
 "use strict";
 e.bsicp = {
  baseURI:'./API/index.php',
  request:function(e) {
   let that = this;
   layer.load(2);
   $.ajax({
    url: that.baseURI,
    type: 'GET',
    dataType: 'json',
    data: e.data,
    success:function(res){
     layer.closeAll('loading');
     if(res.errCode == 200){
      e.success(res.data)
     }else {
      that.err(res.errMsg);
     }
    },
    error:function(){
     layer.closeAll('loading');
     that.err('网络连接错误');
    }
   });
  },
  formHandler:function(e) {
   return $(`#${e}`).val();
  },
  formReset:function(e){
   $(`#${e}`).val('');
  },
  err:function(e){
   layer.msg(e,{icon:2});
  },
  testMail:function(e){
   const mailReg = /([\w\-]+\@[\w\-]+\.[\w\-]+)/;
   return mailReg.test(e);
  },
  testDomain:function(e){
   const domainReg = /^([0-9a-zA-Z-]{1,}\.)+([a-zA-Z]{2,})$/;
   return domainReg.test(e);
  },
  testUA:function(){
   const u = navigator.userAgent;
   return{
    mobile: !!u.match(/AppleWebKit.*Mobile.*/),
    android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1,
    iPhone: u.indexOf('iPhone') > -1,
    weixin: u.indexOf('MicroMessenger') > -1
   }
  }
 }

 if (bsicp.testUA().mobile || bsicp.testUA().android || bsicp.testUA().iPhone || bsicp.testUA().weixin) {
  
  

 }

 $(function(){
  
  //弹出层交互
  $(".menu a, .btn-submit-a").click(function(e) {
   $(`#${e.target.name}`).addClass('active');
  });
  $("[name = 'close']").click(function(e) {
   $(`#${e.target.parentNode.id}`).removeClass('active');
  });

  //判断页面是否携带参数
  try {
   const icplink = location.hash.slice(1);
   if (icplink !== '' && (bsicp.testDomain(icplink) || icplink.length == 8)) {
    getRes(icplink);
   }
  } catch(e) {
   bsicp.err('未知错误,请稍后重试');
  }

  //获取邮箱验证码
  $("#getvercode").click(function() {
   let webMail = bsicp.formHandler('webmail');
   if(webMail == '' || !bsicp.testMail(webMail)){
    bsicp.err('管理邮箱无效请重新填写！');
   }else {
    $("#getvercode").attr('disabled', 'true');
    bsicp.request({
     data:{
      action:'getVercode',
      webMail:webMail
     },
     success:function(e){
      if (e.errCode == 200) {
       sessionStorage.setItem('nonceStr', e.nonceStr);
       layer.msg(e.errMsg,{icon:1,shade:0.5},function(){
        let i = 60,
        vtimeout = setInterval(function(){
         $("#getvercode").val(`${i}秒后重新获取`);
         if (i == 0) {
          clearInterval(vtimeout);
          $("#getvercode").val("获取验证码");
          $("#getvercode").removeAttr('disabled');
         }
         i--;
        },1000);
       });
      }else {
       bsicp.err(e.errMsg);
       $("#getvercode").removeAttr('disabled');
      }
     }
    });
   }   
  });

  //表单处理
  $("#submit").click(function(){
   let webName = bsicp.formHandler('webname'),
   webDomain = bsicp.formHandler('webdomain'),
   webDesp = bsicp.formHandler('webdesp'),
   webMaster = bsicp.formHandler('webmaster'),
   webMail = bsicp.formHandler('webmail'),
   verCode = bsicp.formHandler('vercode'),
   nonceStr = sessionStorage.getItem('nonceStr');

   let formDatas = [webName,webDomain,webDesp,webMaster,webMail,verCode];

   if(formDatas.includes('')){
    bsicp.err('必填项不能为空');
   }else if (!nonceStr) {
    bsicp.err('验证码错误请重新获取');
   }else if (!bsicp.testDomain(webDomain)) {
    bsicp.err('首页域名无效请重新填写！');
   }else if (!bsicp.testMail(webMail)) {
    bsicp.err('管理邮箱无效请重新填写！');
   }else{
    //阻止表单重复提交
    $("#submit").attr('disabled', 'true');
    bsicp.request({
     data:{
      action:'icpReg',
      webName:webName,
      webDomain:encodeURIComponent(webDomain),
      webDesp:webDesp,
      webMaster:webMaster,
      webMail:webMail,
      verCode:verCode,
      nonceStr:nonceStr
     },
     success:function(e) {
      if(e.errCode == 200){
       $("#submit").hide();
       sessionStorage.removeItem('nonceStr');
       layer.msg(e.errMsg,{icon:1,shade:0.5},function(){
        $("#popup").removeClass('active');
       });
      }else {
       bsicp.err(e.errMsg);
       $("#submit").removeAttr('disabled');
      }
     }
    })        
   }

  });

  //表单reset
  $("#reset").click(function() {
   /* Act on the event */
   bsicp.formReset('webname');
   bsicp.formReset('webdomain');
   bsicp.formReset('webdesp'),
   bsicp.formReset('webmaster');
   bsicp.formReset('webmail');
   bsicp.formReset('vercode');
   $("#submit").removeAttr('disabled');
   $("#submit").show();
  });

  //查询
  $("#isearch").click(function(e) {
   const keyword =  bsicp.formHandler('searchVal');
   if (bsicp.testDomain(keyword) || keyword.length == 8) {
    getRes(keyword);
   }else {
    layer.msg('请输入需要查询的备案号或首页域名', {
     icon:2,
     offset: 't'
    });
   }

   return false;
  });


  function getRes(e){
   bsicp.request({
    data:{
     action:'searchIcp',
     keyword:e
    },
    success:function(e){
     if(e.errCode == 200){
      if(e.datas == null){
       layer.msg('未查询到备案信息', {
        icon:2,
        offset: 't'
       });
      }else {
       sessionStorage.setItem("info", JSON.stringify(e.datas));
       window.location.href="result.html"; 
      }
     }else{
      layer.msg(e.errMsg, {
       icon:2,
       offset: 't'
      });
     }
    }
   });
  }

  let info = sessionStorage.getItem("info");
  if(info !== null){
   info = JSON.parse(info);
   $("#reicpnum").text(info.inum);
   $("#remaster").text(info.master);
   $("#rename").text(info.name);
   $("#redomain").text(info.domain);
   $("#redesp").text(info.desp);
   $("#retime").text(info.time);
   if(info.status == 2){
    $("#restatus").text('审核中');
    $("#restatus").css('color', '#FF8C00');
   }else if (info.status == 1) {
    $("#restatus").text('正常');
    $("#restatus").css('color', '#228B22');
   }else if (info.status == 3) {
    $("#restatus").text('备案已退回');
    $("#restatus").css('color', 'blue');
   }else{
    $("#restatus").text('已注销');
    $("#restatus").css('color', 'red');
   }
  }

 });

})(window);