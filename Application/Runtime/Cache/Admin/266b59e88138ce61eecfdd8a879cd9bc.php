<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>管理后台</title>
	<script type="text/javascript" src="/Public/js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="/Public/js/scripts/jquery/Validform_v5.3.2_min.js"></script>
    <script type="text/javascript" src="/Public/js/scripts/lhgdialog/lhgdialog.js?skin=idialog"></script>
	<script type="text/javascript" src="/Public/js/scripts/swfupload/swfupload.js"></script>
	<script type="text/javascript" src="/Public/js/scripts/swfupload/swfupload.handlers.js"></script>
    <script type="text/javascript" src="/Public/js/layout.js"></script>	
    <link href="/Public/admin/css/pagination.css" rel="stylesheet" type="text/css" />	
	<link href="/Public/admin/admin.css" rel="stylesheet" type="text/css" />
	<link href="/Public/admin/page.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="/Public/js/check.js"></script>
	<script type="text/javascript">
	    $(function () {
	        //初始化表单验证
	        $("#form").initValidform();
			 //初始化上传控件
	        $(".upload-img").each(function () {
	            $(this).InitSWFUpload({ sendurl: "/Admin/Public/upload", flashurl: "/Public/js/scripts/swfupload/swfupload.swf" });
	        });
	        $(".upload-album").each(function () {
	            $(this).InitSWFUpload({ btntext: "批量上传", btnwidth: 66, single: false, water: true, thumbnail: true, filesize: "2048", sendurl: "/Admin/Public/upload", flashurl: "/Public/js/scripts/swfupload/swfupload.swf", filetypes: "*.jpg;*.jpge;*.png;*.gif;" });
	        });
	        $(".attach-btn").click(function () {
	            showAttachDialog();
	        });
	        //设置封面图片的样式
	        $(".photo-list ul li .img-box img").each(function () {
	            if ($(this).attr("src") == $("#hidFocusPhoto").val()) {
	                $(this).parent().addClass("selected");
	            }
	        });
			$("#ddlParentId").change(function(){
				$.get('/Admin/Type/selectajax',{id:this.value},function(data){
					 var dataobj = eval("("+data+")");
					 $("#ddlParentId1").prev().find('span').html('请选择所属车系');					 
					 $("#ddlParentId1").html(dataobj.str);
					 $("#ddlParentId1").prev().find('ul').html(dataobj.str1);
				});
			});
			$("#ddlParentId1").change(function(){
				$.get('/Admin/Type/selectajax1',{id:this.value},function(data){
					 var dataobj = eval("("+data+")");
					 $("#ddlParentId2").prev().find('span').html('请选择所属年份');					 
					 $("#ddlParentId2").html(dataobj.str);
					 $("#ddlParentId2").prev().find('ul').html(dataobj.str1);
				});
			});

	    });
		function sel(obj){		
			$(obj).siblings().removeClass("selected");
            $(obj).addClass("selected"); //添加选中样式
            var indexNum = $(obj).index();
			var titObj = $(obj).parents('.boxwrap');
            var selectObj = $(obj).parents('.boxwrap').next();
            selectObj.find("option").attr("selected", false);
            selectObj.find("option").eq(indexNum).attr("selected", true); //赋值给对应的option
            titObj.find("span").text($(obj).text()); //赋值选中值
            selectObj.trigger("change"); 
		}
		
	
		
	</script>
</head>
<body class="mainbody">
    <form id="form" method="post">
    <!--导航栏-->
<div class="location">
  <a href="javascript:history.back(-1);" class="back"><i></i><span>返回上一页</span></a>
  <a href="/Admin/Index/center" class="home"><i></i><span>首页</span></a>
  <i class="arrow"></i>
  <a href="/Admin/City"><span>城市列表</span></a>
  <i class="arrow"></i>
  <span>新增信息</span>
</div>
<div class="line10"></div>
<!--/导航栏-->

<!--内容-->
<div class="content-tab-wrap">
  <div id="floatHead" class="content-tab">
    <div class="content-tab-ul-wrap">
      <ul>
        <li><a href="javascript:;" onclick="tabs(this);" class="selected">基本信息</a></li>
      </ul>
    </div>
  </div>
</div>

<div class="tab-content">
	<dl>
		<dt>省：</dt>
		<dd>
			<div class="rule-single-select">
				<select id="ddlParentId" name="provinceid">
					<option value="0" selected="selected">请选择省级单位</option>
					<?php if(is_array($province)): $i = 0; $__LIST__ = $province;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["provinceid"]); ?>"><?php echo ($val["province"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
				</select>
			</div>
		</dd>
	</dl>
	<dl>
	<dt>市：</dt>
		<dd><input type="text" name="city" datatype="*" Class="input normal" sucmsg=" " /> <span class="Validform_checktip">*</span></dd>
	</dl> 
</div>
<!--/内容-->

<!--工具栏-->
<div class="page-footer">
  <div class="btn-list">
    <input id="btnSubmit" type="submit" value="提交保存" Class="btn" />
    <input name="btnReturn" type="button" value="返回上一页" class="btn yellow" onclick="javascript:history.back(-1);" />
  </div>
  <div class="clear"></div>
</div>
<!--/工具栏-->
    </form>
</body>
</html>