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
	<script type="text/javascript" charset="utf-8" src="/Public/js/scripts/kindeditor/kindeditor.js"></script>
	<script type="text/javascript" charset="utf-8" src="/Public/js/scripts/kindeditor/lang/zh_CN.js"></script>
    <link href="/Public/admin/css/pagination.css" rel="stylesheet" type="text/css" />	
	<link href="/Public/admin/admin.css" rel="stylesheet" type="text/css" />
	<link href="/Public/admin/page.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="/Public/js/check.js"></script>
	<script type="text/javascript">
		 //初始化编辑器
        KindEditor.ready(function(K) {
                window.editor = K.create('#content');
        });
        KindEditor.ready(function(K) {
                K.create('#content', {
						uploadJson : '/Public/js/scripts/kindeditor/php/upload_json.php',
						fileManagerJson : '/Public/js/scripts/kindeditor/php/file_manager_json.php',
                        allowFileManager : true
                });
        });
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
				$.get('/Admin/Shop/selectajax',{id:this.value},function(data){
					 var dataobj = eval("("+data+")");
					 $("#ddlParentId1").prev().find('span').html('请选择在市级单位');					 
					 $("#ddlParentId1").html(dataobj.str);
					 $("#ddlParentId1").prev().find('ul').html(dataobj.str1);
				});
			});
			$("#ddlParentId1").change(function(){
				$.get('/Admin/Shop/selectajax1',{id:this.value},function(data){
					 var dataobj = eval("("+data+")");
					 $("#ddlParentId2").prev().find('span').html('请选择在区县单位');					 
					 $("#ddlParentId2").html(dataobj.str);
					 $("#ddlParentId2").prev().find('ul').html(dataobj.str1);
				});
			});
			$("#ddlParentId3").change(function(){
				$.get('/Admin/News/selectajax3',{id:this.value},function(data){
					if(data == 0){
						$("#checkbox").html('<div class="boxwrap"></div>');
					}else{
						 var dataobj = eval("("+data+")");	
						 $("#checkbox").html('<div class="boxwrap"></div>');
						 $("#checkbox").find('div').html(dataobj.str1);
					 		$("#checkbox").append(dataobj.str);
					}				 
				});
			});

	    });
		function sel(obj){		
			$(obj).siblings().removeClass("selected");
            $(obj).addClass("selected"); //添加选中样式
            var indexNum = $(obj).index();
			var titObj = $(obj).parents('.boxwrap');
            var selectObj = $(obj).parents('.boxwrap').next();
            //selectObj.find("option").attr("selected", false);			
           // selectObj.find("option").eq(indexNum).attr("selected", true); //赋值给对应的option
		   selectObj.get(0).selectedIndex =$(obj).index();
            titObj.find("span").text($(obj).text()); //赋值选中值        
            selectObj.trigger("change"); 		
		}
		
		function checkb(obj){
			if($(obj).attr("class")=='selected'){
				$(obj).removeClass("selected");         	
			}else{
				$(obj).addClass("selected"); //添加选中样式
			}		
            var indexNum = $(obj).index();
			var titObj = $(obj).parents('.boxwrap');
            var selectObj = $(obj).parents('.boxwrap').siblings('label');
            //selectObj.find("option").attr("selected", false);			
           // selectObj.find("option").eq(indexNum).attr("selected", true); //赋值给对应的option
		  selectObj.eq(indexNum).trigger("click"); 
            //titObj.find("span").text($(obj).text()); //赋值选中值        
            	
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
  <a href="/Admin/News"><span>新闻列表</span></a>
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
	<input type="hidden" name="type" value="<?php echo ($type); ?>" />
	<dl>
    <dt>新闻形式</dt>
      <dd>
      	<a class="tupianxuanze" href="<?php echo U('Admin/News/add/type/1');?>" <?php if($type == 1): ?>style="border: 2px solid #1e99c7; "<?php else: ?>style="border: 2px solid #ccc; "<?php endif; ?>>
			<div><img src="/Public/admin/a1.png" style="width: 167px; height: 80px;" /></div>
			<div class="tupiantype"><img style="float: right; margin: 5px 5px 0 0" <?php if($type == 1): ?>src="/Public/admin/a6.png"<?php else: ?>src="/Public/admin/a5.png"<?php endif; ?> />普通新闻</div>
		</a>
		<a class="tupianxuanze" href="<?php echo U('Admin/News/add/type/2');?>" <?php if($type == 2): ?>style="border: 2px solid #1e99c7; "<?php else: ?>style="border: 2px solid #ccc; "<?php endif; ?>>
			<div><img src="/Public/admin/a2.png" style="width: 167px; height: 80px;" /></div>
			<div class="tupiantype"><img style="float: right; margin: 5px 5px 0 0" <?php if($type == 2): ?>src="/Public/admin/a6.png"<?php else: ?>src="/Public/admin/a5.png"<?php endif; ?> />图片新闻</div>
		</a>
		<a class="tupianxuanze" href="<?php echo U('Admin/News/add/type/3');?>" <?php if($type == 3): ?>style="border: 2px solid #1e99c7; "<?php else: ?>style="border: 2px solid #ccc; "<?php endif; ?>>
			<div><img src="/Public/admin/a3.png" style="width: 167px; height: 80px;" /></div>
			<div class="tupiantype"><img style="float: right; margin: 5px 5px 0 0" <?php if($type == 3): ?>src="/Public/admin/a6.png"<?php else: ?>src="/Public/admin/a5.png"<?php endif; ?> />视频新闻</div>
		</a>
		<a class="tupianxuanze" href="<?php echo U('Admin/News/add/type/4');?>" <?php if($type == 4): ?>style="border: 2px solid #1e99c7; "<?php else: ?>style="border: 2px solid #ccc; "<?php endif; ?>>
			<div><img src="/Public/admin/a4.png" style="width: 167px; height: 80px;" /></div>
			<div class="tupiantype"><img style="float: right; margin: 5px 5px 0 0" <?php if($type == 4): ?>src="/Public/admin/a6.png"<?php else: ?>src="/Public/admin/a5.png"<?php endif; ?> />推广新闻</div>
		</a>
		
	</dd>
  </dl>
	<dl <?php if($type == 1 or $type == 2): ?>style="display: none"<?php endif; ?>>
    <dt>图片</dt>
      <dd>
      	<img src="/Public/upfile/wutu.jpg" class="upload-img" style="width: 223px; height: 117px;" />
		<input type="hidden" id="txtImgUrl" name="img" Class="input normal upload-path" />
      	<div style="position:relative; top: -13px; left: 5px;" class="upload-box upload-img"></div><span style="position:relative; top: -13px; left: 5px;" class="Validform_checktip">*建议上传334:175比例的jpg,png图片</span>
	</dd>
  </dl>
  <dl <?php if($type != 1): ?>style="display: none"<?php endif; ?>>
    <dt>图片</dt>
      <dd>
      	<img src="/Public/upfile/wutu.jpg" class="upload-img" style="width: 222px; height: 144px;" />
		<input type="hidden" id="txtImgUrl" name="simg" Class="input normal upload-path" />
      	<div style="position:relative; top: -13px; left: 5px;" class="upload-box upload-img"></div><span style="position:relative; top: -13px; left: 5px;" class="Validform_checktip">*建议上传111:72比例的jpg,png图片</span>
	</dd>
  </dl>
  <dl <?php if($type != 2): ?>style="display: none"<?php endif; ?> ID="div_albums_container" runat="server" visible="false">
    <dt>图库</dt>
    <dd>
      <div class="upload-box upload-album"></div>
      <input type="hidden" name="hidFocusPhoto" id="hidFocusPhoto" class="focus-photo" />
      <div class="photo-list">
        <ul>
        <li></li>
         <?php if(is_array($data_img)): $i = 0; $__LIST__ = $data_img;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data_img): $mod = ($i % 2 );++$i;?><li>
			 <input type="hidden" value="<?php echo ($data_img["id"]); ?>" name="img_id[]"></input>
		         <input type="hidden" value="<?php echo ($data_img["simg"]); ?>" name="user_simg[]"></input>
				<input type="hidden" value="<?php echo ($data_img["title"]); ?>" name="user_desc[]"></input>    
              <div class="img-box" onclick="setFocusImg(this);" >
               <a target="_blank" href="<?php echo ($data_img["simg"]); ?>"> <img src="<?php echo ($data_img["simg"]); ?>"  style="width: 220px;" /></a>
                <span class="remark" style="width: 220px;"><i><?php echo ($data_img["title"]); ?></i></span>
              </div>
              <a href="javascript:;" onclick="setRemark(this);">描述</a>
              <a href="javascript:;" onclick="delImg(this);">删除</a>           
            </li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
      </div>
    </dd>
  </dl>
  <dl>
		<dt>城市</dt>
		<dd>
			<div class="rule-single-select">
				<select id="ddlParentId" name="cityid">
					<option value="0" selected="selected">全国</option>
					<?php if(is_array($city)): $i = 0; $__LIST__ = $city;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["id"]); ?>"><?php echo ($val["title"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
				</select>
			</div>
		</dd>
	</dl>
	
	<dl>
	<dt>标题</dt>
		<dd><input type="text" name="title" Class="input normal" datatype="*" sucmsg=" " nullmsg="标题不能为空！" /></dd>
	</dl>
	<dl>
	<dt>来源</dt>
		<dd><input type="text" name="origin" Class="input normal" /></dd>
	</dl>
	<dl <?php if($type == 1 or $type == 2): ?>style="display: none"<?php endif; ?>>
	<dt >视频地址</dt>
		<dd><input type="text" name="url" Class="input normal" /></dd>
	</dl>
	<dl>
		<dt>类别</dt>
		<dd>
			<div class="rule-single-select">
				<select id="ddlParentId3" name="groupid"  datatype="*" sucmsg=" " nullmsg="请选择分类！">
					<option value=" " selected="selected">请选择所属分类</option>
					<?php if(is_array($group)): $i = 0; $__LIST__ = $group;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["id"]); ?>"><?php echo ($val["title"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
				</select>
			</div>
		</dd>
	</dl>
	<dl>
    <dt>标签</dt>
    <dd>
		<div id="checkbox"  class="rule-multi-checkbox  multi-checkbox ">	
    	</div>
	</dd>
  </dl>
  <dl>
    <dt>推荐</dt>
    <dd>
		<div class="rule-multi-radio multi-radio">	
      		<label><input type="radio" Value="2"  name="isred" />是</label>
			<label><input type="radio" Value="1" checked="checked" name="isred" />否</label>
    	</div>
	</dd>
  </dl>
  <dl>
    <dt>推广</dt>
    <dd>
		<div class="rule-multi-radio multi-radio">	
      		<label><input type="radio" Value="2"  name="istop" />是</label>
			<label><input type="radio" Value="1" checked="checked" name="istop" />否</label>
    	</div>
	</dd>
  </dl>
	<dl>
		<dt>排序</dt>
			<dd><input value="99" type="text" name="ord" Class="input small" /><span class="Validform_checktip">数字越小越在前面</span></dd>
		</dl>
	<dl>
		<dt>赞</dt>
		<dd><input value="0" type="text" name="upper" Class="input small" /></dd>
	</dl>
	<dl>
		<dt>踩</dt>
		<dd><input value="0" type="text" name="lower" Class="input small" /></dd>
	</dl>
	<dl <?php if($type == 2): ?>style="display: none"<?php endif; ?>>
		<dt>简介</dt>
		<dd>
			<textarea id="webcopyright" name="description" Class="input" /></textarea>
		</dd>
	</dl>
	
	<dl <?php if($type == 2): ?>style="display: none"<?php endif; ?>>
    <dt>详情</dt>
    <dd><textarea id="content" name="content" style="width:700px;height:200px;visibility:hidden;"></textarea></dd>
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