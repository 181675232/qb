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
            </i><span>导航列表</span>
        </div>
        <!--/导航栏-->
		<!--工具栏-->
        <div class="toolbar-wrap">
            <div id="floatHead" class="toolbar">
                <div class="l-list">
                    <ul class="icon-list">
                        <li><a class="add" href="<?php echo U('/Admin/Nav/add');?>"><i></i><span> 新增</span></a></li>
                        <li><a class="all" href="javascript:;" onclick="checkAll(this)"><i></i><span>全选</span></a></li>
                        <li><a class="del" style="cursor:pointer;" id="btnDelete" OnClick="return ExePostBack('Nav')"><i></i><span>删除</span></a></li>
                    </ul>
                </div>

            </div>
        </div>
        <!--/工具栏-->	
        <!--列表-->
                <table width="100%" border="0" cellspacing="0" cellpadding="0" class="ltable">             

		<tr>
		    <th width="10%">选择</th>
		    <th align="left" width="15%">调用ID</th>
		    <th align="left">导航标题</th>
		    <th width="12%">状态</th>
		    <th width="15%">操作</th>
		</tr>
		<?php if(is_array($nav)): $i = 0; $__LIST__ = $nav;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i; if(is_array($val["catid"])): $i = 0; $__LIST__ = $val["catid"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val1): $mod = ($i % 2 );++$i;?><tr>
				    <td align="center">
						<input type="checkbox" Class="checkall" value="<?php echo ($val1["id"]); ?>" Style="vertical-align: middle;" />
				    </td>
				    <td style="white-space:nowrap;word-break:break-all;overflow:hidden;"><?php echo ($val1["name"]); ?></td>
				    <td style="white-space:nowrap;word-break:break-all;overflow:hidden;">
				      <span class="folder-open"></span>
				      <a href="/Admin/Nav/edit/id/<?php echo ($val1["id"]); ?>"><?php echo ($val1["title"]); ?></a>
					  	<?php if(empty($val1["url"])): else: ?>
							(链接：<?php echo ($val1["url"]); ?>)<?php endif; ?>
					  
				    </td>
				    <td align="center"><?php if($val1["state"] == 1): ?>显示<?php else: ?><span style="color: green;">隐藏</span><?php endif; ?></td>
		            <td align="center">
		            	<?php if($val1["state"] == 1): ?><a href="/Admin/Nav/state/id/<?php echo ($val1["id"]); ?>/state/0">隐藏</a>　                	
						<?php else: ?>
							<a href="/Admin/Nav/state/id/<?php echo ($val1["id"]); ?>/state/1">显示</a>　<?php endif; ?>
						<a href="/Admin/Nav/add/id/<?php echo ($val1["id"]); ?>">添加下级</a>　
						<a href="/Admin/Nav/edit/id/<?php echo ($val1["id"]); ?>">修改</a>
		            </td>
				  </tr>
					  <?php if(is_array($val1["catid"])): $i = 0; $__LIST__ = $val1["catid"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val2): $mod = ($i % 2 );++$i;?><tr>
						  	 <td align="center">
								<input type="checkbox" Class="checkall" value="<?php echo ($val2["id"]); ?>" Style="vertical-align: middle;" />
						    </td>
						    <td style="white-space:nowrap;word-break:break-all;overflow:hidden;"><?php echo ($val2["name"]); ?></td>
						    <td style="white-space:nowrap;word-break:break-all;overflow:hidden;">
							  <span style="display:inline-block;width:0px;"></span>
						      <span class="folder-line"></span><span class="folder-open"></span>
						      <a href="/Admin/Nav/edit/id/<?php echo ($val2["id"]); ?>"><?php echo ($val2["title"]); ?></a>
							  	<?php if(empty($val2["url"])): else: ?>
									(链接：<?php echo ($val2["url"]); ?>)<?php endif; ?>
						    </td>
						    <td align="center"><?php if($val2["state"] == 1): ?>显示<?php else: ?><span style="color: green;">隐藏</span><?php endif; ?></td>
				            <td align="center">
				            	<?php if($val2["state"] == 1): ?><a href="/Admin/Nav/state/id/<?php echo ($val2["id"]); ?>/state/0">隐藏</a>　                	
								<?php else: ?>
									<a href="/Admin/Nav/state/id/<?php echo ($val2["id"]); ?>/state/1">显示</a>　<?php endif; ?>
								<a href="/Admin/Nav/add/id/<?php echo ($val2["id"]); ?>">添加下级</a>　
								<a href="/Admin/Nav/edit/id/<?php echo ($val2["id"]); ?>">修改</a>
				            </td>
						  </tr>
				
							  <?php if(is_array($val2["catid"])): foreach($val2["catid"] as $key=>$val3): ?><tr>
								  	 <td align="center">
										<input type="checkbox" Class="checkall" value="<?php echo ($val3["id"]); ?>" Style="vertical-align: middle;" />
								    </td>
								    <td style="white-space:nowrap;word-break:break-all;overflow:hidden;"><?php echo ($val3["name"]); ?></td>
								    <td style="white-space:nowrap;word-break:break-all;overflow:hidden;">
									  <span style="display:inline-block;width:25px;"></span>
								      <span class="folder-line"></span><span class="folder-open"></span>
								      <a href="/Admin/Nav/edit/id/<?php echo ($val3["id"]); ?>"><?php echo ($val3["title"]); ?></a>
									  	<?php if(empty($val3["url"])): else: ?>
											(链接：<?php echo ($val3["url"]); ?>)<?php endif; ?>
								    </td>
								    <td align="center"><?php if($val3["state"] == 1): ?>显示<?php else: ?><span style="color: green;">隐藏</span><?php endif; ?></td>
						            <td align="center">
						            	<?php if($val3["state"] == 1): ?><a href="/Admin/Nav/state/id/<?php echo ($val3["id"]); ?>/state/0">隐藏</a>　                	
										<?php else: ?>
											<a href="/Admin/Nav/state/id/<?php echo ($val3["id"]); ?>/state/1">显示</a>　<?php endif; ?>
										<a href="/Admin/Nav/add/id/<?php echo ($val3["id"]); ?>">添加下级</a>　
										<a href="/Admin/Nav/edit/id/<?php echo ($val3["id"]); ?>">修改</a>
						            </td>
								  </tr><?php endforeach; endif; endforeach; endif; else: echo "" ;endif; endforeach; endif; else: echo "" ;endif; endforeach; endif; else: echo "" ;endif; ?>
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