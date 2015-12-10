<?php
namespace Admin\Controller;
use Think\Controller;

class CommonController extends Controller {
	public function _initialize(){
		header("Content-Type:text/html; charset=utf-8");
		if (!$_SESSION['userid']){
			$this->redirect('/Admin/Public/admin');
			exit;
		}
		$rbac=new \Org\Util\Rbac();
		//检测是否登录，没有登录就打回设置的网关
		$rbac::checkLogin();
		//检测是否有权限没有权限就做相应的处理

		if(!$rbac::AccessDecision()){
			if (ACTION_NAME == 'delete'){
				echo 1;
				exit;
			}else {
				alertBack('您没有此操作权限！');
			}			
		}
	}
}