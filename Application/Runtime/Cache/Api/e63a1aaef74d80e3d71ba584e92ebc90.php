<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title></title>
    <style>
    	body{
    		margin: 0;
			padding: 0;
    	}
        .fbl_te{
            width: 94%;
            margin: 0 auto;
            color: #333;
            font-size: 14px;
            line-height: 24px;
            padding: 5px;
           min-height: 150px;
        }
        .fbl_te img{
        width:100%;
        height:auto;
        margin:0 auto
        }
    </style>
</head>
<body>
  <div class="fbl_te">
  	<div style="color: #000; font-size:16px;"><?php echo ($title); ?></div>
	<div style="color: #666; font-size:12px;"><?php echo ($origin); ?>　<?php echo (date( 'Y-m-d H:i',$addtime )); ?></div>
   <div><?php echo ($content); ?></div>
  </div>
</body>
</html>