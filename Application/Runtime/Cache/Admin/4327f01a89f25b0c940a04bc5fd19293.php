<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>管理后台</title>
	<script type="text/javascript" src="/Public/js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="/Public/js/scripts/jquery/Validform_v5.3.2_min.js"></script>
    <script type="text/javascript" src="/Public/js/scripts/lhgdialog/lhgdialog.js?skin=idialog"></script>
    <script type="text/javascript" src="/Public/js/layout.js"></script>	
    <link href="/Public/admin/css/pagination.css" rel="stylesheet" type="text/css" />	
	<link href="/Public/admin/base.css" rel="stylesheet" type="text/css" />
	<link href="/Public/admin/layout.css" rel="stylesheet" type="text/css" />
	<link href="/Public/admin/admin.css" rel="stylesheet" type="text/css" />
	<link href="/Public/admin/page.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="/Public/js/check.js"></script>
	<script type="text/javascript">
    	function stateConfirm() {
			if( confirm('您确定要这样做吗？') ){
		        return true;
		    }else {
				return false;
			}
		}
	 	//发送AJAX请求
	    function sendAjaxUrl(winObj, postData, sendUrl) {
	        $.ajax({
	            type: "post",
	            url: sendUrl,
	            data: postData,
	            dataType: "json",
	            error: function (XMLHttpRequest, textStatus, errorThrown) {
	                $.dialog.alert('尝试发送失败，错误信息：' + errorThrown, function () { }, winObj);
	            },
	            success: function (data, textStatus) {
	                if (data.status == 1) {
	                    winObj.close();
	                    $.dialog.tips(data.msg, 1, '32X32/succ.png', function () { location.reload(); }); //刷新页面
	                } else {
	                    $.dialog.alert('错误提示：' + data.msg, function () { }, winObj);
	                }
	            }
	        });
	    }
	 </script>
</head>
<body class="mainbody">
    <form id="form1" method="get">
    <div>
        <!--导航栏-->
        <div class="location">
            <a href="javascript:history.back(-1);" class="back"><i></i><span>返回上一页</span></a>
            <a href="/Admin/Index/center" class="home"><i></i><span>首页</span></a> <i class="arrow">
            </i><span>群组列表</span>
        </div>
        <!--/导航栏-->
        <!--工具栏-->	
        <div class="toolbar-wrap">
            <div id="floatHead" class="toolbar">
                <div class="l-list">
                    <ul class="icon-list">
                        <li><a class="add" href="<?php echo U('/Admin/Groups/add');?>"><i></i><span> 新增</span></a></li>
						<!--<li><a id="btnSave" Class="save"><i></i><span>保存</span></a></li>-->
                        <li><a class="all" href="javascript:;" onclick="checkAll(this)"><i></i><span>全选</span></a></li>
                        <li><a class="del" style="cursor:pointer;" id="btnDelete" OnClick="return ExePostBack('Groups')"><i></i><span>删除</span></a></li>
                    </ul>
				<div class="menu-list">

				        <div class="rule-single-select single-select">
				          <select id="ddlProperty" name="verify" onchange="location='/Admin/Groups/index/verify/'+options[selectedIndex].value">
				            <option Value="0"  <?php if($verify == 0): ?>selected="selected"<?php endif; ?>>所有</option>
								<option Value="1"  <?php if($verify == 1): ?>selected="selected"<?php endif; ?>>普通群</option>
								<option Value="2"  <?php if($verify == 2): ?>selected="selected"<?php endif; ?>>私密群</option>
								<option Value="3"  <?php if($verify == 3): ?>selected="selected"<?php endif; ?>>爱好群</option>
				          </select>
				        </div>

      				</div>
                </div>
                <div class="r-list">
                	<p style="float:left;height:30px;line-height:30px;">名称：</p>
                    <input type="text" id="txtKeywords" Class="keyword" name="keyword" />
                    <input type="submit" id="lbtnSearch" name="search" Class="btn-search" value="查询" />
                </div>
            </div>
        </div>
        <!--/工具栏-->
		
		<!--文字列表-->
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="ltable">
		  <tr>
		    <th width="8%">选择</th>
		    <th align="center" width="15%">群组名称</th>		    
			<th align="center" width="15%">群主账号</th>	
			<th align="center" width="15%">群主昵称</th>	
			<th align="center" width="15%">群类别</th>
			<th align="center" width="15%">创建时间</th>			
		    <th align="center">操作</th>
		  </tr>
		<?php if($data): if(is_array($data)): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><tr>
			    <td align="center"><input type="checkbox" Class="checkall" value="<?php echo ($val["id"]); ?>" Style="vertical-align: middle;" /></td>
			    <td align="center"><?php echo ($val["title"]); ?></td>
			    <td align="center"><?php echo ($val["phone"]); ?></td>
			    <td align="center"><?php echo ($val["username"]); ?></td>
			    <td align="center"><?php if($val[type] == 1): ?>普通群<?php elseif($val[type] == 2): ?>私密群<?php else: ?>爱好群<?php endif; ?></td>
				<td align="center"><?php echo (date( "Y-m-d H:i:s",$val["addtime"])); ?></td>
				<!--
			    <td align="center"><input name="ord" value="<?php echo ($val["ord"]); ?>" Class="sort" style="text-align:center;" onblur="order(<?php echo ($val["id"]); ?>,this.value,'News')" /></td>
				
			    <td align="center">
			      <div class="btn-tools">
			        <a title="<?php if($val[istop] == 2): ?>取消推广<?php else: ?>设置推广<?php endif; ?>" Class="<?php if($val[istop] == 2): ?>top selected<?php else: ?>top<?php endif; ?>" href="/Admin/News/state/id/<?php echo ($val["id"]); ?>/istop/<?php if($val[istop] == 2): ?>1<?php else: ?>2<?php endif; ?>"></a>
			        <a title="<?php if($val[isred] == 2): ?>取消推荐<?php else: ?>设置推荐<?php endif; ?>" Class="<?php if($val[isred] == 2): ?>red selected<?php else: ?>red<?php endif; ?>" href="/Admin/News/state/id/<?php echo ($val["id"]); ?>/isred/<?php if($val[isred] == 2): ?>1<?php else: ?>2<?php endif; ?>"></a>
			      </div>
			    </td>
				-->
			    <td align="center">
			    	<a href="/Admin/Groups/edit/id/<?php echo ($val["id"]); ?>">查看/修改</a> 
					<?php if($val[state] == 1): ?><a onclick="return stateConfirm()" href="/Admin/Groups/state/id/<?php echo ($val["id"]); ?>">通过</a>
						<a onclick="return stateConfirm()" href="/Admin/Groups/delete/id/<?php echo ($val["id"]); ?>">否定</a><?php endif; ?>
				</td>
			  </tr><?php endforeach; endif; else: echo "" ;endif; ?>
		<?php else: ?>
			<tr><td align="center" colspan="7">暂无记录</td></tr><?php endif; ?>
		</table>
		<!--/文字列表-->
        <!--内容底部-->
        <div class="line20">
        </div>
        <div class="pagelist">
            <div class="flickr">
                <?php echo ($page); ?>
            </div>
        </div>
        <!--/内容底部-->
    </div>
    </form>
</body>
</html>