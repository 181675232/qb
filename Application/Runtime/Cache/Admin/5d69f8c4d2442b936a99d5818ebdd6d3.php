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
            </i><span>会员列表</span>
        </div>
        <!--/导航栏-->
        <!--工具栏-->
        <div class="toolbar-wrap">
            <div id="floatHead" class="toolbar">
                <div class="l-list">
                    <ul class="icon-list">
                       <!-- <li><a class="add" href="<?php echo U('/Admin/User/add');?>"><i></i><span> 新增</span></a></li> --> 
                        <li><a class="all" href="javascript:;" onclick="checkAll(this)"><i></i><span>全选</span></a></li>
                        <li><a class="del" style="cursor:pointer;" id="btnDelete" OnClick="return ExePostBack('User')"><i></i><span>删除</span></a></li>
                    </ul>
                    <!-- 
					<div class="menu-list">

				        <div class="rule-single-select single-select">
				          <select id="ddlProperty" name="verify" onchange="location='/Admin/User/index/verify/'+options[selectedIndex].value">
				            <option Value=""  <?php if($verify == ''): ?>selected="selected"<?php endif; ?>>所有状态</option>
				            <option Value="0" <?php if($verify == '0'): ?>selected="selected"<?php endif; ?>>未认证</option>
				            <option Value="1" <?php if($verify == '1'): ?>selected="selected"<?php endif; ?>>认证中</option>
				            <option Value="2" <?php if($verify == '2'): ?>selected="selected"<?php endif; ?>>已认证</option>
				          </select>
				        </div>

      				</div>
      				-->
                </div>
                <div class="r-list">
                	<p style="float:left;height:30px;line-height:30px;">账号/手机号：</p>
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
                            选择
                        </th>
						<th align="left" width="5%">

                        </th>
                        <th align="left" width="18%">
 		用户名
                        </th>
						<th align="center" width="8%">
 		余额
                        </th>
                        <th align="center" width="15%">
                           手机号码
                        </th>
						<th align="center" width="15%">
                           注册类型
                        </th>
                        <th align="center" width="15%">
         性别                   
                        </th>
                        <th>
                            操作
                        </th>
                    </tr>
		 <?php if(is_array($data)): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><tr>
                    <td align="center">
                    	<input type="checkbox" Class="checkall" value="<?php echo ($val["id"]); ?>" Style="vertical-align: middle;" />
                    	
                    </td>
                    <td>
                        <a href="">
                        	<?php if(empty($val['simg'])): ?><img width="80" height="80" style=" border-radius: 50%" src="/Public/admin/touxiang.jpg">
								<?php else: ?>
								<img width="80" height="80" style=" border-radius: 50%" src="<?php echo ($val["simg"]); ?>"><?php endif; ?>
							
                            
						</a>
                    </td>
					<td>
				      <div class="user-box">
				        <h4><!--<b>13097305395</b> (-->昵称：<?php echo ($val["username"]); ?></h4>
				        <i>注册时间：<?php echo (date("Y/m/d H:i",$val["addtime"])); ?></i>
				        <span>
				        <!--    <a class="amount" href="amount_log.aspx?keywords=13097305395" title="消费记录">消费记录</a>-->
				       <!--   <a class="point" href="point_log.aspx?keywords=13097305395" title="积分记录">积分</a>-->
				        <!--    <a class="msg" href="message_list.aspx?keywords=13097305395" title="说说记录">说说记录</a>
				          <a class="addr" href="<?php echo U('/Admin/Address/index',array('id'=>$val['id']));?>" title="地址管理">地址管理</a>
				          <a class="xin" href="<?php echo U('/Admin/Vehicle/index',array('id'=>$val['id']));?>" title="爱车管理">爱车管理</a>-->
				       <!--   <a class="sms" href="javascript:;" onclick="PostSMS('13097305395');" title="发送手机短信通知">短信通知</a>-->
				        </span>
				      </div>
				    </td>
					<td align="center">
                        <?php echo ($val["money"]); ?>
                    </td>
                    <td align="center">
                        <?php echo ($val["phone"]); ?>
                    </td>
					<td align="center">
                    	<?php if($val["logintype"] == 1): ?>手机
						<?php elseif($val["logintype"] == 2): ?>qq
						<?php else: ?>微信<?php endif; ?>
                    </td>
                    <td align="center">
                    	<?php if($val["sex"] == 1): ?>男
						<?php elseif($val["sex"] == 2): ?>女
						<?php else: ?>保密<?php endif; ?>
                    </td>
                    <td align="center">
                        <a href="/Admin/User/edit/id/<?php echo ($val["id"]); ?>">查看/修改</a>
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