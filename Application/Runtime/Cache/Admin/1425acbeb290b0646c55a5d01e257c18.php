<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>管理后台</title>
	<script type="text/javascript" src="/Public/js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="/Public/js/scripts/lhgdialog/lhgdialog.js?skin=idialog"></script>
    <script type="text/javascript" src="/Public/js/layout.js"></script>	
    <link href="/Public/admin/css/pagination.css" rel="stylesheet" type="text/css" />	
	<link href="/Public/admin/base.css" rel="stylesheet" type="text/css" />
	<link href="/Public/admin/layout.css" rel="stylesheet" type="text/css" />
	<link href="/Public/admin/admin.css" rel="stylesheet" type="text/css" />
	<link href="/Public/admin/page.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="/Public/js/check.js"></script>
</head>
<body class="mainbody">
    <form id="form1" method="post">
    <div>
        <!--导航栏-->
        <div class="location">
            <a href="javascript:history.back(-1);" class="back"><i></i><span>返回上一页</span></a>
            <a href="/Admin/Index/center" class="home"><i></i><span>首页</span></a> <i class="arrow">
            </i><span>分类列表</span>
        </div>
        <!--/导航栏-->
		<!--工具栏-->
        <div class="toolbar-wrap">
            <div id="floatHead" class="toolbar">
                <div class="l-list">
                    <ul class="icon-list">
                        <li><a class="add" href="<?php echo U('/Admin/Class/add');?>"><i></i><span> 新增</span></a></li>
                        <li><a class="all" href="javascript:;" onclick="checkAll(this)"><i></i><span>全选</span></a></li>
                        <li><a class="del" style="cursor:pointer;" id="btnDelete" OnClick="return ExePostBack('Class')"><i></i><span>删除</span></a></li>
                    </ul>
                </div>

            </div>
        </div>
        <!--/工具栏-->	
        <!--列表-->
                <table width="100%" border="0" cellspacing="0" cellpadding="0" class="ltable">             
		<tr>
		    <th width="8%">选择</th>
		    <th align="center" width="15%">编号</th>
		    <th align="left">分类名称</th>
			<th width="15%">排序</th>
		    <th width="15%">操作</th>
		</tr>
			<?php if(is_array($group)): $i = 0; $__LIST__ = $group;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val1): $mod = ($i % 2 );++$i;?><tr>
				    <td align="center">	
					<input type="checkbox" Class="checkall" value="<?php echo ($val1["id"]); ?>" Style="vertical-align: middle;" />	
				    </td>
				    <td align="center" style="white-space:nowrap;word-break:break-all;overflow:hidden;"><?php echo ($val1["id"]); ?></td>
				    <td  style="white-space:nowrap;word-break:break-all;overflow:hidden;">
				      <span class="folder-open"></span>
				      <a href="/Admin/Class/edit/id/<?php echo ($val1["id"]); ?>"><?php echo ($val1["title"]); ?></a>
					  	<?php if(empty($val1["url"])): else: ?>
							(链接：<?php echo ($val1["url"]); ?>)<?php endif; ?>
					  
				    </td>
					<td align="center"><input name="ord" value="<?php echo ($val1["ord"]); ?>" Class="sort" style="text-align:center;" onblur="order(<?php echo ($val1["id"]); ?>,this.value,'Class')" /></td>
		            <td align="center">
							<a href="/Admin/Class/edit/id/<?php echo ($val1["id"]); ?>"> 查看/修改</a>
		            </td>
				  </tr>
					  <?php if(is_array($val1["catid"])): $i = 0; $__LIST__ = $val1["catid"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val2): $mod = ($i % 2 );++$i;?><tr>
						  	 <td align="center">
								<input type="checkbox" Class="checkall" value="<?php echo ($val2["id"]); ?>" Style="vertical-align: middle;" />
						    </td>
						    <td align="center" style="white-space:nowrap;word-break:break-all;overflow:hidden;"><?php echo ($val2["id"]); ?></td>
						    <td style="white-space:nowrap;word-break:break-all;overflow:hidden;">
							  <span style="display:inline-block;width:0px;"></span>
						      <span class="folder-line"></span><span class="folder-open"></span>
						      <a href="/Admin/Class/edit/id/<?php echo ($val2["id"]); ?>"><?php echo ($val2["title"]); ?></a>
							  	<?php if(empty($val2["url"])): else: ?>
									(链接：<?php echo ($val2["url"]); ?>)<?php endif; ?>
						    </td>
							<td align="center"></td>
							<!--
							<td align="center">
						      <div class="btn-tools">
						      	
						        <a title="<?php if($val2[istop] == 2): ?>取消置顶<?php else: ?>设置置顶<?php endif; ?>" Class="<?php if($val2[istop] == 2): ?>top selected<?php else: ?>top<?php endif; ?>" href="/Admin/Group/state/id/<?php echo ($val2["id"]); ?>/istop/<?php if($val2[istop] == 2): ?>1<?php else: ?>2<?php endif; ?>"></a>
								
						        <a title="<?php if($val2[isred] == 2): ?>取消推荐<?php else: ?>设置推荐<?php endif; ?>" Class="<?php if($val2[isred] == 2): ?>red selected<?php else: ?>red<?php endif; ?>" href="/Admin/Group/state/id/<?php echo ($val2["id"]); ?>/isred/<?php if($val2[isred] == 2): ?>1<?php else: ?>2<?php endif; ?>"></a>
						      </div>
						    </td>
							-->
				            <td align="center">
				            	<a href="/Admin/Class/edit/id/<?php echo ($val2["id"]); ?>">查看/修改</a>
							</td>
						  </tr><?php endforeach; endif; else: echo "" ;endif; endforeach; endif; else: echo "" ;endif; ?>

                </table>
        <!--/列表-->
        <!--内容底部-->
        <div class="line20">
        </div>
        <!--/内容底部-->
    </div>
    </form>
</body>
</html>