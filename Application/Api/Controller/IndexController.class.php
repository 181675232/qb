<?php
namespace Api\Controller;
use Think\Controller;
use Think;
use Think\Exception;
 

class IndexController extends Controller { 
	//基本配置
	private $url = 'http://101.200.81.192:8082';
	
	//Jpush key
	private $title = 'Q帮';
	private $app_key='52b9e181f96679e59ffb4fa3';
	private $master_secret = '146a40bb61c640bc1ff6ad1e';
	
	//融云
	private $appKey = 'mgb7ka1nb904g';
	private $appSecret = 'emEDENErpWhAct';	
	
    //注册
    public  function  register(){
    	if(I('post.')){
    		$data = I('post.');
    		$data['addtime'] = time();
			if (mb_strlen(trim($data['password'])) < 6){
				json('400','密码不能小于6位！');
			}
			if ($data['password'] != $data['pass']){
				json('400','两次密码输入不一致，请重新输入');
			}
			unset($data['pass']);
    		$data['password'] = md5(trim(I('post.password')));
    		if (empty($data['username'])){
    			json('400','昵称不能为空！');
    		}
    		if($_FILES){
    			$data1 = $_FILES['simg'];
    			$rand = '';
    			for ($i=0;$i<6;$i++){
    				$rand.=rand(0,9);
    			}
    			$type = explode('.', $data1['name']);
    			$simg = date('YmdHis').$rand.'.'.end($type);
    			if (move_uploaded_file($data1['tmp_name'], './Public/upfile/'.$simg)){				
    				$data['simg'] = '/Public/upfile/'.$simg;
    			}else {
    				json('400','头像上传失败');
    			} 			
    		}else{
    			json('400','请上传头像');
    		}
    		$table = M('user');
    		if ($table->where("phone='{$data['phone']}'")->find()){
    			json('400','该账号已注册');
    		}
    		$return = $table->add($data);		
    		if ($return){
    			$rongyun = new  \Org\Util\Rongyun($this->appKey,$this->appSecret);
    			$simg = $this->url.$data['simg'];
    			$r = $rongyun->getToken($return,$data['username'],$simg);
    			if($r){
    				$rong = json_decode($r);
    				if ($rong->code == 200){
    					$where['token'] = $rong->token;
    					if ($table->where("id = $return")->save($where)){
    						$user = $table->field('id,jpushid,token,phone')->where("id = $return")->find();
    						json('200','成功',$user);
    					}else {
    						json('400','融云集成失败');
    					}
    				}else {
    					json('400','融云内部错误');
    				}
    			}else {
    				json('400','融云token获取失败');
    			}
    		}else{
    			json('400','注册失败');   			
    		}
    	}
    	json('404','没有接收到传值');
    }
    
    //发送验证码1(手机号不存在时调用)
    public function yzm1(){
    	if(I('post.phone')){
    		$phone=I('post.phone');
    		if(!checkPhone($phone)){
    			json('400','请输入正确的手机号！');
    		}
    		$user = M('user');
    		$return = $user->where("phone=$phone")->find();
    		if($return){
    			json('400','用户名已经被注册，请登陆！');
    		}else{
    			yzm($phone);
    		}
    	}
    	json('404','没有接收到传值');
    }
    
    //发送验证码2(手机号存在时调用)
    public function yzm2(){
    	if(I('post.phone')){
    		$phone = I('post.phone');
    		$user = M('user');
    		if (!checkPhone($phone)){
    			json('400','手机格式不正确');
    		}
    		if (!$user->where("phone = $phone")->find()){
    			json('400','手机号码不存在');
    		}
    		yzm($phone);
    	}
    	json('404','没有接收到传值');
    }
    
    //发送验证码3(不检测调用)
    public function yzm3(){
    	if(I('post.mobile')){
    		$phone = I('post.mobile');
    		if (!checkPhone($phone)){
    			json('400','手机格式不正确');
    		}
    		yzm($phone);
    	}
    	json('404','没有接收到传值');
    }
    
    //登录
    public function login(){
    	if(I('post.')){
	    	$table = M('user');
	    	$phone=I('post.phone');
	    	$return = $table->where("phone=$phone")->find();	
	    	if($return){
	    		$data['phone'] = $phone;
	    		$data['password'] = md5(I('post.password')); 	
	    		$user = $table->field('id,phone,jpushid,token')->where($data)->find();
	    		if($user){
	    			if ($user['jpushid'] != I('post.jpushid')){
	    				$return = $table->where("id = '{$user['id']}'")->setField('jpushid',I('post.jpushid'));
	    				$user['jpushid'] = I('post.jpushid');
	    			}
    				json('200','成功',$user);
	    		}else{
	    			json('400','密码错误');
	    		}
	    	}else{
	    		json('400','未注册！');
	    	}
    	}
    	json('404','没有接收到传值');
    }
    
    //忘记密码
    public function forgetpass(){
    	if(I('post.')){
    		$phone = I('post.phone');
    		$user = M('user');
    		if (mb_strlen(trim(I('post.password'))) < 6){
    			json('400','密码不能小于6位！');
    		}
    		if (I('post.password') != I('post.pass')){
    			json('400','两次密码输入不一致，请重新输入');
    		}
    		$data['password'] = md5(trim(I('post.password')));
    		if ($user->where("phone = $phone")->save($data)){
    			json('200','成功');
    		}else {
    			json('400','修改失败');
    		}
    	}
    	json('404','没有接收到传值');
    }
    
    //修改密码
    public function passedit(){
    	if(I('post.')){
    		$user = M('user');
    		$where['id'] = I('post.id');
    		$where['password'] = md5(I('post.fpass'));
    		if (!$user->where($where)->find()){
    			json('400','原密码输入有误');
    		}
    		if (mb_strlen(trim(I('post.password'))) < 6){
    			json('400','密码不能小于6位！');
    		}
    		if (I('post.password') != I('post.pass')){
    			json('400','两次密码输入不一致，请重新输入');
    		}
    		$data['password'] = md5(trim(I('post.password')));
    		if ($user->where("id = '{$where['id']}'")->save($data)){
    			json('200');
    		}else {
    			json('400','修改失败');
    		}
    	}
    	json('404','没有接收到传值');
    }
     
    //个人资料
    public function userinfo(){
    	if(I('post.id')){
 			$user = M('user');
 			$data = $user->find(I('post.id'));
 			if ($data){
 				json('200','成功',$data);
 			}else {
 				json('400','没有获取到资料');
 			}	
		}
		json('404','没有接收到传值');
    }
    
    //修改个人信息
    public function useredit(){
    	if(I('post.')){
    		$user = M('user');
    		$data = I('post.');    
    		if ($data['birth']){
    			$data['birth'] = strtotime($data['birth']);
    		}		
    		if($_FILES){
    			$data1 = $_FILES['simg'];
    			$rand = '';
    			for ($i=0;$i<6;$i++){
    				$rand.=rand(0,9);
    			}
    			$type = explode('.', $data1['name']);
    			$simg = date('YmdHis').$rand.'.'.end($type);
    			if (move_uploaded_file($data1['tmp_name'], './Public/upfile/'.$simg)){				
    				$data['simg'] = '/Public/upfile/'.$simg;
    			}else {
    				json('400','头像上传失败');
    			} 			
    		}
    		if ($user->save($data)){
    			$res = $user->find(I('post.id'));
    			json('200','成功',$res);
    		}else {
    			json('400','修改失败');
    		}
    	}
    	json('404','没有接收到传值');
    }

   
	
	//意见反馈
	public function addmessage(){
		if (I('post.')){
			$where = I('post.');
			$where['addtime'] = time();
			$table = M('message');
			if ($table->add($where)){
				json('200');
			}else {
				json('400','意见反馈失败');
			}
		}
		json('404','没有接收到传值');
	}
	//获取市列表
	public function getcitys(){
		$table = M('city');
		$data = $table->where('isred = 2')->select();
		if ($data){
			json('200','成功',$data);
		}else {
			json('400','暂无数据');
		}
	}	
	//获取区列表
	public function getareas(){
		if (I('post.id')){
			$where['cityid'] = I('post.id');
			$table = M('area');
			$data = $table->where($where)->select();
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','暂无数据');
			}
		}
		json('404','没有接收到传值');
	}
	
	//获取分类列表
	public function shopgroup(){
		$table = M('group');
		$data = $table->where('pid = 0')->select();
		if ($data){
			foreach ($data as $key=>$val){
				$data[$key]['catid'] = $table->where("pid = '{$val['id']}'")->select();
			}
			json('200','成功',$data);
		}else {
			json('400','暂无数据');
		}
	}
	
	//首页新闻
	public function indexnews(){
		if (I('post.')){
			
		}
		json('404');
	}
	
	//新闻web带标题
	public function newstitleweb(){
		$table = M('news');
		$id = I('get.id');
		$data = $table->find($id);
		$this->assign($data);
		$this->display();
	}
	
	
	
	
	
}