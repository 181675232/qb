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
	        //是否启用权限
	        if ($("#ddlRoleType").find("option:selected").attr("value") == 1) {
	            $(".border-table").find("input[type='checkbox']").prop("disabled", true);
	        }
	        $("#ddlRoleType").change(function () {
	            if ($(this).find("option:selected").attr("value") == 1) {
	                $(".border-table").find("input[type='checkbox']").prop("checked", false);
	                $(".border-table").find("input[type='checkbox']").prop("disabled", true);
	            } else {
	                $(".border-table").find("input[type='checkbox']").prop("disabled", false);
	            }
	        });
	        //权限全选
	        $("input[name='checkAll']").click(function () {
	            if ($(this).prop("checked") == true) {
	                $(this).parent().siblings("td").find("input[type='checkbox']").prop("checked", true);
	            } else {
	                $(this).parent().siblings("td").find("input[type='checkbox']").prop("checked", false);
	            }
	        });
	    });

		
	</script>
</head>
<body class="mainbody">
    <form id="form" method="post">
    <!--导航栏-->
<div class="location">
  <a href="javascript:history.back(-1);" class="back"><i></i><span>返回上一页</span></a>
  <a href="/Admin/Index/center" class="home"><i></i><span>首页</span></a>
  <i class="arrow"></i>
  <a href="/Admin/Role"><span>角色</span></a>
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
		<dt>角色名称</dt>
		<dd><input type="text" name="name" datatype="*" Class="input normal" sucmsg=" " /> <span class="Validform_checktip">*</span></dd>
	</dl> 
	<dl>
		<dt>角色简介</dt>
		<dd>
			<textarea id="webcopyright" name="remark" Class="input" /></textarea>
	      	<!--<span class="Validform_checktip">支持HTML</span>-->
		</dd>
	</dl> 
	<dl>
    <dt>管理权限</dt>
    <dd>
      <table border="0" cellspacing="0" cellpadding="0" class="border-table" width="98%">
        <thead>
          <tr>
            <th width="25%">导航名称</th>
            <th>权限分配</th>
            <th width="10%">全选</th>
          </tr>
        </thead>
        <tbody>
        	<input type="hidden" name="node_id[]" value="9_1"  />
          <?php if(is_array($nav)): $i = 0; $__LIST__ = $nav;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i; if(is_array($val["catid"])): $i = 0; $__LIST__ = $val["catid"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val1): $mod = ($i % 2 );++$i;?><tr>
				    <td style="white-space:nowrap;word-break:break-all;overflow:hidden;padding-left: 50px;">
				      <span class="folder-open"></span>
				      <a><?php echo ($val1["title"]); ?></a>
				    </td>		    
		            <td align="left">
		            	<input type="checkbox" id="cblActionType" Class="cbllist" name="node_id[]" value="<?php echo ($val1["id"]); ?>_1" Style="vertical-align: middle;" /> 显示　
	            		<?php if(is_array($val1["role"])): $i = 0; $__LIST__ = $val1["role"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val11): $mod = ($i % 2 );++$i;?><input type="checkbox" id="cblActionType" Class="cbllist" name="node_id[]" value="<?php echo ($val11["id"]); ?>_3" Style="vertical-align: middle;" /> <?php echo ($val11["title"]); ?> 　<?php endforeach; endif; else: echo "" ;endif; ?>
		            </td>
					<td align="center">
						<input name="checkAll" type="checkbox" />
				    </td>
				  </tr>
					  <?php if(is_array($val1["catid"])): $i = 0; $__LIST__ = $val1["catid"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val2): $mod = ($i % 2 );++$i;?><tr>
						    <td style="white-space:nowrap;word-break:break-all;overflow:hidden;padding-left: 50px;">
							  <span style="display:inline-block;width:0px;"></span>
						      <span class="folder-line"></span><span class="folder-open"></span>
						      <a><?php echo ($val2["title"]); ?></a>						  	
						    </td>				    
				            <td align="left">		        
									<input type="checkbox" id="cblActionType" Class="cbllist" name="node_id[]" value="<?php echo ($val2["id"]); ?>_1" Style="vertical-align: middle;" /> 显示　
				            		<?php if(is_array($val2["role"])): foreach($val2["role"] as $key=>$val21): ?><input type="checkbox" id="cblActionType" name="node_id[]" Class="cbllist" value="<?php echo ($val21["id"]); ?>_3" Style="vertical-align: middle;" /> <?php echo ($val21["title"]); ?> 　<?php endforeach; endif; ?>
				            </td>
							<td align="center">
								<input name="checkAll" type="checkbox" />
						    </td>
						  </tr>		
							  <?php if(is_array($val2["catid"])): foreach($val2["catid"] as $key=>$val3): ?><tr>
								    <td style="white-space:nowrap;word-break:break-all;overflow:hidden;padding-left: 50px;">
									  <span style="display:inline-block;width:25px;"></span>
								      <span class="folder-line"></span><span class="folder-open"></span>
								      <a><?php echo ($val3["title"]); ?></a>
								    </td>
						            <td align="left">
						            	<input type="checkbox" Class="cbllist" name="node_id[]" value="<?php echo ($val3["id"]); ?>_2" Style="vertical-align: middle;" /> 显示　
						            	<?php if(is_array($val3["role"])): foreach($val3["role"] as $key=>$val31): ?><input type="checkbox" name="node_id[]" Class="cbllist" value="<?php echo ($val31["id"]); ?>_3" Style="vertical-align: middle;" /> <?php echo ($val31["title"]); ?> 　<?php endforeach; endif; ?>
						            </td>
									<td align="center">
										<input name="checkAll" type="checkbox" />
								    </td>
								  </tr><?php endforeach; endif; endforeach; endif; else: echo "" ;endif; endforeach; endif; else: echo "" ;endif; endforeach; endif; else: echo "" ;endif; ?>
        </tbody>
      </table>
    </dd>
  </dl>
</div>
<!--/内容-->

<!--工具栏-->
<div class="page-footer">
  <div class="btn-list">
    <input id="btnSubmit" type="submit" value="提交保存" Class="btn" onclick="btnSubmit_Click" />
    <input name="btnReturn" type="button" value="返回上一页" class="btn yellow" onclick="javascript:history.back(-1);" />
  </div>
  <div class="clear"></div>
</div>
<!--/工具栏-->
    </form>
</body>
</html>