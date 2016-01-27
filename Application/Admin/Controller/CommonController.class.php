<?php
namespace Admin\Controller;
use Think\Controller;

class CommonController extends Controller {
	//基本配置
	protected $url = 'http://101.200.81.192:8082';
	
	//Jpush key
	protected $title = 'Q帮';
	protected $app_key='36b3dc718f373f05082ef383';
	protected $master_secret = '359dfb9592f02079d7759f0b';
	
	//融云
	protected $appKey = 'pwe86ga5ede86';
	protected $appSecret = '96EvBT4wxIvCL';
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