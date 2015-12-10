<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>管理后台</title>
<link href="/Public/admin/base.css" rel="stylesheet" type="text/css" />
<link href="/Public/admin/layout.css" rel="stylesheet" type="text/css" />
<link href="/Public/admin/admin.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/Public/js/jquery-1.10.2.min.js"></script>
</head>

<body class="mainbody">
<form id="form1" runat="server">
<!--导航栏-->
<div class="location">
  <a href="javascript:history.back(-1);" class="back"><i></i><span>返回上一页</span></a>
  <a class="home"><i></i><span>首页</span></a>
  <i class="arrow"></i>
  <span>管理中心</span>
</div>
<!--/导航栏-->

<!--内容-->
<div class="line10"></div>
<div class="nlist-1">
  <ul>
    <li>本次登录IP：<?php echo ($admin["ip"]); ?></li>
    <li>上次登录IP：<?php echo (session('userip')); ?></li>
    <li>上次登录时间：<?php echo (date('Y-m-d H:i:s',session('usertime'))); ?></li>
  </ul>
</div>
<div class="line10"></div>

<div class="nlist-2">
  <h3><i></i>站点信息</h3>
  <ul>
  	<?php if(is_array($info)): $i = 0; $__LIST__ = $info;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><li><?php echo ($key); ?>：<?php echo ($val); ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
   
  </ul>
</div>
<div class="line20"></div>
<div class="nlist-3">
  <ul>
  	<li><a onclick="parent.linkMenuTree(true, 'con_base');" class="icon-setting" href="/Admin/Base"></a><span>基本信息</span></li>
  	<li><a onclick="parent.linkMenuTree(true, 'user_all');" class="icon-user" href="/Admin/Owner"></a><span>会员管理</span></li>
    <li><a onclick="parent.linkMenuTree(true, 'order_all');" class="icon-channel" href="/Admin/Order"></a><span>订单管理</span></li>  
   <li><a onclick="parent.linkMenuTree(true, 'manager_log');" class="icon-log" href="javascript:;"></a><span>系统管理</span></li>
  </ul>
</div>
<div class="nlist-4">
  <h3><i class="site" style="display:none;"></i></h3>
  <ul>
    <li></li>
  </ul>
  <h3><i class="msg" style="display:none;"></i></h3>
  <ul>
   
  </ul>
</div>





<!--/内容-->
</form>
</body>
</html>