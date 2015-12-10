<?php
namespace Api\Controller;
use Think\Controller;
use Think;
 

class ShopController extends Controller { 
	//Jpush key
	private $title = '特惠帮';
	private $app_key='52b9e181f96679e59ffb4fa3';
	private $master_secret = '146a40bb61c640bc1ff6ad1e';
	
	//融云
	private $appKey = 'mgb7ka1nb904g';
	private $appSecret = 'emEDENErpWhAct';
	
    
    //登录
    public function login(){
    	if(I('post.')){
	    	$member = M('member');
	    	$phone=I('post.phone');	    	
	    	$return = $member->where("phone=$phone")->find();	    	
	    	if($return){
	    		$data['phone'] = $phone;
	    		$data['password'] = md5(I('post.password')); 		
	    		$res = $member->where($data)->field('id,phone,simg,jpushid,name,pid,level')->find();
	    		
	    		if($res){
	    			if ($res['jpushid'] != I('post.jpushid')){
	    				$return = $member->where("id = '{$res['id']}'")->setField('jpushid',I('post.jpushid'));
	    			 	$res['jpushid'] = I('post.jpushid');
	    			}
	    			
	    			json('200','成功',$res);
	    		}else{
	    			json('400','密码错误');
	    		}
	    	}else{
	    		json('401','未注册！');
	    	}
    	}
    	json('404','没有接收到传值');
    }
    
    //忘记密码
    public function forgetpass(){
    	if(I('post.')){
    		$phone = I('post.phone');
    		$user = M('user');
    		$data['password'] = md5(I('post.password'));
    		if ($user->where("phone = $phone")->save($data)){
    			json('200','成功');
    		}else {
    			json('400','修改失败');
    		}
    	}
    	json('404','没有接收到传值');
    }
     
    //个人资料
    public function memberinfo(){
    	if(I('post.id')){
 			$member = M('member');
 			$data = $member->find(I('post.id'));
 			if ($data){
 				json('200','成功',$data);
 			}else {
 				json('400','没有获取到资料');
 			}	
		}
		json('404','没有接收到传值');
    }
    
    //修改个人信息
    public function memberedit(){
    	if(I('post.')){
    		$member = M('member');
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
    		if ($member->save($data)){
    			$res = $member->find(I('post.id'));
    			json('200','成功',$res);
    		}else {
    			json('400','修改失败');
    		}
    	}
    	json('404','没有接收到传值');
    }

    //确认页面
    public function confirm(){
    	if (I('post.')){
    		$phone = I('post.phone');
    		$total = I('post.total');
    		$shopid = I('shopid');
    		if ($phone){
    			$table = M('user');
    			$data = $table->where("phone = $phone")->find();
    			if ($data){
    				$shop = M('shop');
    				$league = M('league');
    				$shopdata = $shop->where("id = $shopid")->find();
    				$leaguedata = $league->where("shopid = $shopid")->find();
    				if ($leaguedata['state'] == 2){
    					$leagues = $league->where("id = '{$leaguedata['pid']}'")->find();
    					if ($leagues['state'] == 3){
    						$coupons = M('coupons');
    						if ($coupons->where("uid ='{$data['id']}' and leagueid = '{$leagues['id']}' and state = 1")->find()){
    							$res['discount'] = $leaguedata['discount'];
    							$res['type'] = "1";
    						}else {
    							$res['discount'] = $shopdata['discount'];
    							$res['type'] = "2";
    						}
    					}else {
    						$res['discount'] = $shopdata['discount'];
    						$res['type'] = "3";
    					}
    				}else {
    					$res['discount'] = $shopdata['discount'];
    					$res['type'] = "3";
    				}
    				$res['username'] = $data['username'];
    				$res['phone'] = $phone;
    				$res['total'] = $total;
    				$res['price'] = round(($total*$res['discount'])/100);
    			}else {
    				json('400','没有这个用户');
    			}
    		}else {
    			json('400','请填写特惠帮账号');
    		}
    	}
    	json('404');
    }
	
     //点击确认订单
    public function order(){
    	if (I('post.')){
    		$where = I('post');
    		$table = M('order');
    		$user = M('user');
    		$coupons = M('coupons');
    		$shop = M('shop');
    		$league = M('league');
    		$userdata = $user->where("phone = '{$where['phone']}'")->find();
    		$shopdata = $shop->where("id = '{$where['shopid']}'")->find();   		
			if ($where['type'] == 1){
				$leaguedata = $league->where("shopid = '{$where['shopid']}'")->find();
				$coupons->where("uid = '{$userdata['uid']}' and leagueid = '{$leaguedata['id']}' and state = 1")->save("state",'2');
				$where1['uid'] = $userdata['uid'];
				$where1['shopid'] = $where['shopid'];
				$where1['leagueid'] = $leaguedata['id'];
				$where1['addtime'] = time();
				$coupons->add($where1);
			}elseif ($where['type'] == 2){
				$leaguedata = $league->where("shopid = '{$where['shopid']}'")->find();
				$where1['uid'] = $userdata['uid'];
				$where1['shopid'] = $where['shopid'];
				$where1['leagueid'] = $leaguedata['id'];
				$where1['addtime'] = time();
				$coupons->add($where1);
			}
			$base = M('base');
			$info = $base->find();
			$where2['uid'] = $userdata['uid'];
			$where2['shopid'] = $where['shopid'];
			$where2['price'] = $where['price'];
			$where2['userid'] = $where['userid'];
			$yu = round($where2['price']*$shopdata['rebate']/100,3);
			$where2['rebate'] = round($yu*$info['xiao']/100,3);
			$where2['title'] = '交易完成，分享后可获得'.$where2['rebate'].'帮币';
			$where2['type'] = 3;
			$where2['addtime'] = time();
			$where2['discount'] = $where['total'] - $where['price'];
			$yue = $yu - $where2['rebate'];
			if ($table->add($where2)){
				$rebate = M('rebate');
				$where3['phone'] = $where['phone'];
				$where3['name'] = $shopdata['name'];
				$where3['addtime'] = time();
				if ($userdata['pid'] != 0){
					$user1 = $user->find($userdata['pid']);
					$int = round($yu*$info['zhi']/100,3);
					$user1->where("id = '{$user1['id']}'")->setInc('money',$int);
					$user1->where("id = '{$user1['id']}'")->setInc('total',$int);
					$where3['rebate'] = $int;
					$where3['uid'] = $user1['id'];
					$rebate->add($where3);
					$yue = $yue - $int;
					if ($user1['pid'] != 0){
						$user2 = $user->find($user1['pid']);
						$int = round($yu*$info['jian']/100,3);
						$user2->where("id = '{$user2['id']}'")->setInc('money',$int);
						$user2->where("id = '{$user2['id']}'")->setInc('total',$int);
						$where3['rebate'] = $int;
						$where3['uid'] = $user2['id'];
						$rebate->add($where3);
						$yue = $yue - $int;
					}
				}
				$admin = M('admin');
				$area = $admin->where("areaid = '{$shopdata['areaid']}' and level = 3")->find();
				if ($area){
					$int = round($yu*$info['xian']/100,3);
					$admin->where("id = '{$area['id']}'")->setInc('money',$int);
					$where3['type'] = 2;
					$where3['rebate'] = $int;
					$where3['uid'] = $area['id'];
					$rebate->add($where3);
					$yue = $yue - $int;
				}
				$city = $admin->where("areaid = '{$shopdata['cityid']}' and level = 2")->find();
				if ($city){
					$int = round($yu*$info['shi']/100,3);
					$admin->where("id = '{$city['id']}'")->setInc('money',$int);
					$where3['type'] = 2;
					$where3['rebate'] = $int;
					$where3['uid'] = $city['id'];
					$rebate->add($where3);
					$yue = $yue - $int;
				}
				$province = $admin->where("areaid = '{$shopdata['provinceid']}' and level = 2")->find();
				if ($province){
					$int = round($yu*$info['sheng']/100,3);
					$admin->where("id = '{$province['id']}'")->setInc('money',$int);
					$where3['type'] = 2;
					$where3['rebate'] = $int;
					$where3['uid'] = $province['id'];
					$rebate->add($where3);
					$yue = $yue - $int;
				}
				$admin1 = $admin->where('name = admin')->find();
				$admin->where("id = '{$admin1['id']}'")->setInc('money',$yue);
				$where3['type'] = 2;
				$where3['rebate'] = $yue;
				$where3['uid'] = $admin1['id'];
				$rebate->add($where3);
				json('200','成功');				
			}else {
				json('400','失败');
			}			
    	}
    	json('404');
    }

    
    
    //店粉
    public function powder(){
    	if(I('post.shopid')){
    		$push = M('push');
    		$shop = M('shop');
    		$shopid = $_POST['shopid'];
    		$page = (I('post.page')-1)*10;
    		$res['num'] = $shop->field('push')->find($shopid);
    		$res['res'] = $push->where("shopid=$shopid")->limit("$page,10")->select();
			if($res){
				json('200','成功',$res);
			}else{
				json('400','失败');
			}
    	}
    }
    
    //看官
    public function trace(){
    	if(I('post.shopid')){
    		$trace = M('trace');
    		$shopid = $_POST['shopid'];   		
    		//php获取今日开始时间戳和结束时间戳
    	 	$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
    		$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
    		//php获取昨日起始时间戳和结束时间戳
    		$beginYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
    		$endYesterday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
    		//php获取上周起始时间戳和结束时间戳
    		$beginLastweek=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
    		$endLastweek=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
    		//php获取本月起始时间戳和结束时间戳
    		$beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
    		$endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));		
//     		$time1 = time()-86400;//一天
//     		$time2 = time()-172800;//两天
    		$time3 = time()-604800;//七天
//     		$time4 = time()-2592000;//月
    		$res['today'] = $trace->where("shopid=$shopid and addtime>$beginToday")->count();
    		$res['yesterday'] = $trace->where("shopid=$shopid and addtime>$beginYesterday and addtime<$endYesterday")->count();
    		$res['thisweek'] = $trace->where("shopid=$shopid and addtime>$time3")->count();
    		$res['thismonth'] = $trace->where("shopid=$shopid and addtime>$beginThismonth and addtime<$endThismonth")->count();
    		$res['all'] = $trace->where("shopid=$shopid")->count();
    		
    		if($res){
    			json('200','成功',$res);
    		}else{
    			json('400','失败');
    		}
    	}
    }
    
    
  //账本数量
    public function account_books(){
    	if(I('post.shopid')){
    		$order = M('order');
    		$shopid = $_POST['shopid'];
    		
    		
    		//php获取今日开始时间戳和结束时间戳
    	 	$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
    		$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
    		//php获取昨日起始时间戳和结束时间戳
    		$beginYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
    		$endYesterday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
    		//php获取上周起始时间戳和结束时间戳
    		$beginLastweek=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
    		$endLastweek=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
    		//php获取本月起始时间戳和结束时间戳
    		$beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
    		$endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));

			//今日
    		$res['today']= $order->where("shopid=$shopid and addtime>$beginToday")->count();
    		
    		//本周
    		$res['thisweek'] = $order->where("shopid=$shopid and addtime>$endLastweek")->count();
    		
    		//本月
    		$res['thismonth'] = $order->where("shopid=$shopid and addtime>$beginThismonth and addtime<$endThismonth")->count();
    		
    		//全部
    		$res['all'] = $order->where("shopid=$shopid")->count();
    		
    		if($res){
    			json('200','成功',$res);
    		}else{
    			json('400','失败');
    		}
    	}
    }
    
    

    //账本列表
    public function account_books_list(){
    	if(I('post.shopid')){
    		$order = M('order');
    		$shopid = $_POST['shopid'];
    		$page = (I('post.page')-1)*10;
    		$books = $_POST['books'];
    		//php获取今日开始时间戳和结束时间戳
    		$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
    		$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
    		//php获取昨日起始时间戳和结束时间戳
    		$beginYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
    		$endYesterday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
    		//php获取上周起始时间戳和结束时间戳
    		$beginLastweek=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
    		$endLastweek=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
    		//php获取本月起始时间戳和结束时间戳
    		$beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
    		$endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
    
    
    		if($books==1){
    		//今日
    		$res = $order->where("t_order.shopid=$shopid and t_order.addtime>$beginToday")
    		->join('left join t_member on t_order.uid=t_member.id')
    		->join('left join t_shop on t_order.shopid=t_shop.id')
    		->field('t_order.price,t_order.addtime,t_shop.address,t_shop.name as shopname,t_member.name')
    		->limit("$page,10")->select();
    		}elseif ($books==2){
    		//本周
    		$res = $order->where("t_order.shopid=$shopid and t_order.addtime>$endLastweek")
    		->join('left join t_member on t_order.uid=t_member.id')
    		->join('left join t_shop on t_order.shopid=t_shop.id')
    		->field('t_order.price,t_order.addtime,t_shop.address,t_shop.name as shopname,t_member.name')
    		->limit("$page,10")->select();
    		}elseif ($books==3){
    		//本月
    		$res = $order->where("t_order.shopid=$shopid and t_order.addtime>$beginThismonth and t_order.addtime<$endThismonth")
    		->join('left join t_member on t_order.uid=t_member.id')
    		->join('left join t_shop on t_order.shopid=t_shop.id')
    		->field('t_order.price,t_order.addtime,t_shop.address,t_shop.name as shopname,t_member.name')
    		->limit("$page,10")->select();
    		}elseif ($books==4){
    		//全部
    		$res = $order->where("t_order.shopid=$shopid")
    		->join('left join t_member on t_order.uid=t_member.id')
    		->join('left join t_shop on t_order.shopid=t_shop.id')
    		->field('t_order.price,t_order.addtime,t_shop.address,t_shop.name as shopname,t_member.name')
    		->limit("$page,10")->select();
    		}
    		if(isset($books)){
	    		if($res){
	    			json('200','成功',$res);
	    		}else{
	    			json('400','失败');
	    		}
    		}else {
    			json('401','参数错误');
    		}
    	}
    }
    
    //修改登陆密码
    public function passedit(){
    	if(I('post.')){
    		$member = M('member');
    		$where['id'] = I('post.id');
    		$where['password'] = md5(I('post.pass'));
    		if (!$member->where($where)->find()){
    			json('400','原密码输出有误');
    		}
    		$data['password'] = md5(I('post.password'));
    		if ($member->where("id = '{$where['id']}'")->save($data)){
    			json('200');
    		}else {
    			json('400','修改失败');
    		}
    	}
    	json('404','没有接收到传值');
    }
    
    //获取手机号
    public function getphone(){
    	if(I('post.id')){
    		$table = M('member');
    		$data = $table->field('phone')->find(I('post.id'));
    		if ($data){
    			json('200','成功',$data);
    		}else {
    			json('400','失败');
    		}
    	}
    	json('404','没有接收到传值');
    }
    
    //发送验证码2
    public function yzm2(){
    	if(I('post.phone')){
    		$phone = I('post.phone');
    		$member = M('member');
    		if (!checkPhone($phone)){
    			json('400','手机格式不正确');
    		}
    		if (!$member->where("phone = $phone")->find()){
    			json('400','手机号码不存在');
    		}
    		yzm($phone);
    	}
    	json('404','没有接收到传值');
    }
    
    
    //忘记交易密码
    public function forgetjypass(){
    	if(I('post.')){
    		$phone = I('post.phone');
    		$member = M('member');
    		$data['pass'] = md5(I('post.pass'));
    		if ($member->where("phone = $phone")->save($data)){
    			json('200','成功');
    		}else {
    			json('400','修改失败');
    		}
    	}
    	json('404','没有接收到传值');
    }
    
    //修改密码
    public function passjyedit(){
    	if(I('post.')){
    		$member = M('member');
    		$where['id'] = I('post.id');
    		$where['pass'] = md5(I('post.pass'));
    		if (!$member->where($where)->find()){
    			json('400','原密码输出有误');
    		}
    		$data['pass'] = md5(I('post.password'));
    		if ($member->where("id = '{$where['id']}'")->save($data)){
    			json('200');
    		}else {
    			json('400','修改失败');
    		}
    	}
    	json('404','没有接收到传值');
    }
    
    
    
    
    

	
	
	
	
	
}