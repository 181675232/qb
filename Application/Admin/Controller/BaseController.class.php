<?php
namespace Admin\Controller;

class BaseController extends CommonController {
	public function index(){
		$base = D('Base');
		if (I('post.')){
			if ($base->create()){
				if ($base->save(I('post.'))){
					alertBack('修改成功！');	
				}else {
					$this->error('没有任何修改！');
				}
			}else {
				$this->error($base->getError());
			}
		}
		$data = $base->find(1);
		$this->assign($data);
		$this->display();
	}
	public function erweima(){
		vendor("phpqrcode.phpqrcode");
		$data = 'http://101.200.81.192:8081/Api/index/erweima?id=111111';
            // 纠错级别：L、M、Q、H
            $level = 'L';
            // 点的大小：1到10,用于手机端4就可以了
            $size = 10;
            // 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
            //$path = "images/";
            // 生成的文件名
            //$fileName = $path.$size.'.png';
            \QRcode::png($data, false, $level, $size);
	}
	
}