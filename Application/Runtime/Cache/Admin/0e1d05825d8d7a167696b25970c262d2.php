<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>管理后台</title>
<link href="/Public/admin/base.css" rel="stylesheet" type="text/css" />
<link href="/Public/admin/layout.css" rel="stylesheet" type="text/css" />
<link href="/Public/admin/admin.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/Public/js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="/Public/js/check.js"></script>
</head>

<body class="loginbody">
<form name="form" method="post">
<div class="login-screen">
<!--	<div class="login-icon">LOGO</div>-->
    <div class="login-form">
        <h1>系统管理登录</h1>
        <div class="control-group">
            <input type="text" name="name" class="login-field" placeholder="用户名" title="用户名" />
            <label class="login-field-icon user" for="txtUserName"></label>
        </div>
        <div class="control-group">
            <input type="password" name="password" class="login-field" placeholder="密码" title="密码" />
            <label class="login-field-icon pwd" for="txtPassword"></label>
        </div>
		<div class="control-group">
            <input type="text" name="code" class="login-field1" placeholder="验证码" title="验证码" />
            <img src='/Admin/Public/scode' id="txtCode" name="txtCode" style="vertical-align: -10px; cursor: pointer;" width="150" height="40" border='0' onclick="this.src='/admin/public/scode/'+Math.random();">
        </div>
        <div><input type="submit" value="登 录" class="btn-login" onclick="return manager()" /></div>
       <span class="login-tips"><i></i><b id="msgtip">请输入用户名和密码</b></span>
    </div>
<!--    <i class="arrow">箭头</i>-->
</div>
</form>
</body>
</html>