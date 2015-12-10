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
	<script type="text/javascript">
    $(function () {
        imgLayout();
        $(window).resize(function () {
            imgLayout();
        });
        //图片延迟加载
        $(".pic img").lazyload({ load: AutoResizeImage, effect: "fadeIn" });
        //点击图片链接
        $(".pic img").click(function () {
            //$.dialog({ lock: true, title: "查看大图", content: "<img src=\"" + $(this).attr("src") + "\" />", padding: 0 });
            var linkUrl = $(this).parent().parent().find(".foot a").attr("href");
            if (linkUrl != "") {
                location.href = linkUrl; //跳转到修改页面
            }
        });
    });
    //排列图文列表
    function imgLayout() {
        var imgWidth = $(".imglist").width();
        var lineCount = Math.floor(imgWidth / 222);
        var lineNum = imgWidth % 222 / (lineCount - 1);
        $(".imglist ul").width(imgWidth + Math.ceil(lineNum));
        $(".imglist ul li").css("margin-right", parseFloat(lineNum));
    }
    //等比例缩放图片大小
    function AutoResizeImage(e, s) {
        var img = new Image();
        img.src = $(this).attr("src")
        var w = img.width;
        var h = img.height;
        var wRatio = w / h;
        if ((220 / wRatio) >= 165) {
            $(this).width(220); $(this).height(220 / wRatio);
        } else {
            $(this).width(165 * wRatio); $(this).height(165);
        }
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
            </i><span>商铺列表</span>
        </div>
        <!--/导航栏-->
        <!--工具栏-->	
        <div class="toolbar-wrap">
            <div id="floatHead" class="toolbar">
                <div class="l-list">
                    <ul class="icon-list">
                        <li><a class="add" href="<?php echo U('/Admin/Shop/add');?>"><i></i><span> 新增</span></a></li>
						<!--<li><a id="btnSave" Class="save"><i></i><span>保存</span></a></li>-->
                        <li><a class="all" href="javascript:;" onclick="checkAll(this)"><i></i><span>全选</span></a></li>
                        <li><a class="del" style="cursor:pointer;" id="btnDelete" OnClick="return ExePostBack('Shop')"><i></i><span>删除</span></a></li>
                    </ul>
					<div class="menu-list">

				        <div class="rule-single-select single-select">
				          <select id="ddlProperty" name="verify" onchange="location='/Admin/Shop/index/verify/'+options[selectedIndex].value">
				            <option Value=""  <?php if($verify == ''): ?>selected="selected"<?php endif; ?>>所有分类</option>
				            <option Value="1" <?php if($verify == '1'): ?>selected="selected"<?php endif; ?>>美食</option>
				            <option Value="2" <?php if($verify == '2'): ?>selected="selected"<?php endif; ?>>娱乐</option>
				            <option Value="3" <?php if($verify == '3'): ?>selected="selected"<?php endif; ?>>美容保健</option>
							<option Value="4" <?php if($verify == '4'): ?>selected="selected"<?php endif; ?>>酒店</option>
				            <option Value="5" <?php if($verify == '5'): ?>selected="selected"<?php endif; ?>>电影</option>
				            <option Value="6" <?php if($verify == '6'): ?>selected="selected"<?php endif; ?>>KTV</option>
							<option Value="7" <?php if($verify == '7'): ?>selected="selected"<?php endif; ?>>购物</option>
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
		
        <!--图片列表-->

		<div class="imglist">
		  <ul>
		  	<?php if($data): if(is_array($data)): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><li>
		      <div class="details">
		        <div class="check"><input type="checkbox" Class="checkall" value="<?php echo ($val["id"]); ?>" Style="vertical-align: middle;" /></div>
		        <div class="pic">
		        	<?php if($val['simg']): ?><img src="<?php echo ($val["simg"]); ?>" width="220" height="220" />
					<?php else: ?>
						<img src="/Public/admin/loadimg.gif" /><?php endif; ?>
				</div><i class="absbg"></i>
		        <h1><span><a href="/Admin/Shop/edit/id/<?php echo ($val["id"]); ?>"><?php echo ($val["name"]); ?></a></span></h1>
		        <div class="remark">
		        	商铺编号：<?php echo ($val["id"]); ?><br />
		        	<?php if($val['description']): echo ($val["description"]); ?>
		        	<?php else: ?>
					暂无内容摘要说明...<?php endif; ?>
				          
		        </div>
		        <div class="tools">	        	
		        	<a title="<?php if($val[iscomment] == 2): ?>取消评论<?php else: ?>开启评论<?php endif; ?>" Class="<?php if($val[iscomment] == 2): ?>msg selected<?php else: ?>msg<?php endif; ?>" href="/Admin/Shop/state/id/<?php echo ($val["id"]); ?>/iscomment/<?php if($val[iscomment] == 2): ?>1<?php else: ?>2<?php endif; if($_GET[p]): ?>/p/<?php echo ($_GET['p']); endif; if($_GET[verify]): ?>/verify/<?php echo ($_GET['verify']); endif; if($_GET[keyword]): ?>/keyword/<?php echo ($_GET['keyword']); endif; ?>"></a>
				 	<a title="<?php if($val[isred] == 2): ?>取消推荐<?php else: ?>设置推荐<?php endif; ?>" Class="<?php if($val[isred] == 2): ?>red selected<?php else: ?>red<?php endif; ?>" href="/Admin/Shop/state/id/<?php echo ($val["id"]); ?>/isred/<?php if($val[isred] == 2): ?>1<?php else: ?>2<?php endif; if($_GET[p]): ?>/p/<?php echo ($_GET['p']); endif; if($_GET[verify]): ?>/verify/<?php echo ($_GET['verify']); endif; if($_GET[keyword]): ?>/keyword/<?php echo ($_GET['keyword']); endif; ?>"></a>
					
				  <!--
				  <a title="取消置顶" Class="top selected" href=""></a>
		          <a title="取消热门" Class="hot selected" href=""></a>
				  -->
				  <input name="ord" value="<?php echo ($val["ord"]); ?>" Class="sort" style="text-align:center;" onblur="order(<?php echo ($val["id"]); ?>,this.value,'Shop')" />	        
		        </div>
		        <div class="foot">
		        	<!--
		          <p class="time"><?php echo (date( "Y-m-d H:i:s",$val["addtime"])); ?></p>
				  -->
				  <?php if(!$val[uid]): ?><p class="time" style="font-size: 12px; padding: 2px 5px;border: 1px solid #ccc"><a style="color: #666; " title="添加店主" href="<?php echo U('/Admin/Member/add/',array('pid'=>$val['id']));?>">添加店主</a></p>
				  <?php else: ?>
				  	<p class="time" style="font-size: 12px; padding: 2px 5px;border: 1px solid #ccc"><a style="color: #666; " title="修改店主" href="<?php echo U('/Admin/Member/edit/',array('id'=>$val[uid]));?>">修改店主</a></p><?php endif; ?>
		          <a href="/Admin/Shop/edit/id/<?php echo ($val["id"]); ?>" title="编辑" class="edit">编辑</a>
		        </div>
		      </div>
		    </li><?php endforeach; endif; else: echo "" ;endif; ?>
			<?php else: ?>
		    	<div align="center" style="padding: 8px 2px;border: 1px solid #e8e8e8;line-height: 1.5em;color: #666;">暂无记录</div><?php endif; ?>
		  </ul>
		</div>
		<!--/图片列表-->
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