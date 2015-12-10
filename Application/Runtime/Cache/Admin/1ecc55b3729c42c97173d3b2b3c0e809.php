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
    <form id="form1" method="get">
    <div>
        <!--导航栏-->
        <div class="location">
            <a href="javascript:history.back(-1);" class="back"><i></i><span>返回上一页</span></a>
            <a href="/Admin/Index/center" class="home"><i></i><span>首页</span></a> <i class="arrow">
            </i><span>城市列表</span>
        </div>
        <!--/导航栏-->
        <!--工具栏-->
        <div class="toolbar-wrap">
            <div id="floatHead" class="toolbar">
            	<!--
                <div class="l-list">
                    <ul class="icon-list">
                        <li><a class="add" href="<?php echo U('/Admin/City/add');?>"><i></i><span> 新增</span></a></li> 
                        <li><a class="all" href="javascript:;" onclick="checkAll(this)"><i></i><span>全选</span></a></li>
                        <li><a class="del" style="cursor:pointer;" id="btnDelete" OnClick="return ExePostBack('City')"><i></i><span>删除</span></a></li>
                    </ul>
                    
                </div>
				-->
                <div class="r-list">
                	<p style="float:left;height:30px;line-height:30px;">名称：</p>
                    <input type="text" id="txtKeywords" Class="keyword" name="keyword" />
                    <input type="submit" id="lbtnSearch" name="search" Class="btn-search" value="查询" />
                </div>
            </div>
        </div>
        <!--/工具栏-->
		
        <!--列表-->
                <table width="100%" border="0" cellspacing="0" cellpadding="0" class="ltable">
                    <tr>
                        <th width="12%">
                            编号
                        </th>
                        <th align="center" width="25%">
 		编码
                        </th>
                        <th align="center" width="25%">
                           城市
                        </th>
						<th align="center" width="15%">
                           热门
                        </th>
                        <th>
                            操作
                        </th>
                    </tr>
		 <?php if(is_array($data)): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><tr>
                    <td align="center">
                    	<!--
                    	<input type="checkbox" Class="checkall" value="<?php echo ($val["id"]); ?>" Style="vertical-align: middle;" />
						-->
                    	 <?php echo ($val["id"]); ?>
                    </td>
                    <td align="center">
                       <?php echo ($val["cityid"]); ?>
                    </td>
                     <td align="center">
                        <?php echo ($val["city"]); ?>
                    </td>
					<td align="center">
						<div class="btn-tools">
							<a title="<?php if($val[isred] == 2): ?>取消推荐<?php else: ?>设置推荐<?php endif; ?>" Class="<?php if($val[isred] == 2): ?>red selected<?php else: ?>red<?php endif; ?>" href="/Admin/City/state/id/<?php echo ($val["id"]); ?>/isred/<?php if($val[isred] == 2): ?>1<?php else: ?>2<?php endif; if($_GET[p]): ?>/p/<?php echo ($_GET['p']); endif; if($_GET[verify]): ?>/verify/<?php echo ($_GET['verify']); endif; if($_GET[keyword]): ?>/keyword/<?php echo ($_GET['keyword']); endif; ?>"></a>
						</div>
					</td>
                    <td align="center">
                        <a href="/Admin/City/edit/id/<?php echo ($val["id"]); ?>">查看/修改</a>
                    </td>
                </tr><?php endforeach; endif; else: echo "" ;endif; ?>
 
                </table>
        <!--/列表-->
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