<?php
namespace Api\Controller;
use Think\Controller;
use Think;
use Think\Exception;
use Admin\Controller\AddressController;
 

class IndexController extends Controller { 
	//基本配置
	private $url = 'http://101.200.81.192:8082';
	
	//Jpush key
	private $title = 'Q帮';
	private $app_key='36b3dc718f373f05082ef383';
	private $master_secret = '359dfb9592f02079d7759f0b';
	
	//融云
	private $appKey = 'pwe86ga5ede86';
	private $appSecret = '96EvBT4wxIvCL';	
	
    //注册
    public  function  register(){
    	if(I('post.')){
    		$data = I('post.');
    		$data['addtime'] = $data['logintime']  = time();
			if (mb_strlen(trim($data['password'])) < 6){
				json('400','密码不能小于6位');
			}
			if ($data['password'] != $data['pass']){
				json('400','两次密码输入不一致，请重新输入');
			}
			unset($data['pass']);
    		$data['password'] = md5(trim(I('post.password')));
    		if (empty($data['username'])){
    			json('400','昵称不能为空');
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
    		$data['bgimg'] = '/Public/upfile/bgimg.jpg';
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
    			json('400','请输入正确的手机号');
    		}
    		$user = M('user');
    		$return = $user->where("phone=$phone")->find();
    		if($return){
    			json('400','用户名已经被注册，请登陆');
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
	    		$user = $table->field('id,phone,jpushid,token,username')->where($data)->find();
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
	    		json('400','未注册');
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
    			json('400','密码不能小于6位');
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
    			json('400','密码不能小于6位');
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
    			$data['age'] = date('Y',time()) - date('Y',$data['birth']);
    		}
    		if($_FILES['simg']){
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
    		if($_FILES['bgimg']){
    			$data1 = $_FILES['bgimg'];
    			$rand = '';
    			for ($i=0;$i<6;$i++){
    				$rand.=rand(0,9);
    			}
    			$type = explode('.', $data1['name']);
    			$simg = date('YmdHis').$rand.'.'.end($type);
    			if (move_uploaded_file($data1['tmp_name'], './Public/upfile/'.$simg)){
    				$data['bgimg'] = '/Public/upfile/'.$simg;
    			}else {
    				json('400','头像上传失败');
    			}
    		}
    		if ($user->save($data)){
    			json('200','成功');
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
		$table = M('selfcity');
		$data = $table->order("prefix asc")->select();
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
	public function newsweb(){
		$table = M('news');
		$id = I('get.id');
		$data = $table->find($id);
		$this->assign($data);
		$this->display();
	}
	
	//新闻详情
	public function newsinfo(){
		if (I('post.')){
			$table = M('news');
			$trace = M('trace');
			$tab = M('upper');
			$cell = M('cell');
			$comment = M('comment');
			$where['uid'] = I('post.uid');
			$where['newsid'] = I('post.id');
			$cityid = I('post.cityid');
			if (!$cityid){
				json('400','参数不合法');
			}
			if ($where['uid']){
				$res = $trace->where($where)->find();
				if ($res){
					$trace->where($where)->setField('addtime',time());
				}else {
					$where['addtime'] = time();
					$trace->add($where);
				}
			}
			$data = $table->field('id,tag,lower,upper,url,groupid,type')->find(I('post.id'));
			$data['count'] = $comment->where("type = 'news' and typeid='{$where['newsid']}'")->count();	
			if ($data['type'] == 2){
				$img = M('img');
				$data['simg'] = $img->field('title,simg')->where("type = 'news' and pid = '{$where['newsid']}'")->select();
			}else {
				$data['simg'] = '';
			}
			$ads = M('ads');
			$data['banner'] = $ads->field('id,title,simg')->order('ord asc,id desc')->find();
			$data['trace'] = $trace->field('t_news.id,t_news.title,t_news.origin,t_news.addtime,t_news.type')
			->join('left join t_news on t_news.id = t_trace.newsid')
			->where("t_trace.uid = '{$where['uid']}' and t_news.id !='{$where['newsid']}' and (t_news.cityid = $cityid or t_news.cityid = 0)")->order("t_trace.addtime desc")->limit(2)->select();
			$data['with'] = $table->field('id,title,origin,addtime,type')->where("groupid = '{$data['groupid']}' and id !='{$where['newsid']}' and (cityid = $cityid or cityid = 0)")->order("addtime desc")->limit(2)->select();			
			$s = $tab->where("uid = '{$where['uid']}' and pid = '{$where['newsid']}' and type='news'")->find();
			$c = $cell->where("uid = '{$where['uid']}' and pid = '{$where['newsid']}' and type='news'")->find();
			$data['state'] = $s['state'] ? $s['state'] : 0;
			$data['cell'] = $c ? 1 : 0;
			json('200','成功',$data);
		}
		json('404');
	}
	
	//踩赞
	public function upper(){
		if (I('post.')){
			$where = $where1 = I('post.');
			if (!$where['uid']){
				json('400','登陆后才能执行此操作！');
			}
			unset($where['state']);
			$table = M('upper');
			$tab = M(I('post.type'));
			if ($table->where($where)->find()){
				json('400','不可重复操作');
			}else {
				$where1['addtime'] = time();
				if ($table->add($where1)){
					if (I('post.state') == 1){
						$tab->where("id = '{$where['pid']}'")->setInc('upper',1);
						json('200','成功');
					}elseif (I('post.state') == 2){
						$tab->where("id = '{$where['pid']}'")->setInc('lower',1);
						json('200','成功');
					}else {
						json('400','非法操作');
					}				
				}else {
					json('400','操作失败');
				}
			}
		}
		json('404');
	}
	
	//收藏
	public function cell(){
		if (I('post.')){		
			$table = M('cell');
			$where = I('post.');
			if (!$where['uid']){
				json('400','登陆后才能执行此操作！');
			}
			if (!checkNum($where['pid']) || !checkNum($where['uid']) || checkNull($where['type'])){
				json('400','参数不合法');
			}
			if ($table->where($where)->find()){
				if ($table->where($where)->delete()){
					json('200','取消成功');
				}else {
					json('400','操作失败');
				}
			}else {
				$where['addtime']= time();
				if ($table->add($where)){
					json('200','收藏成功');
				}else {
					json('400','操作失败');
				}
			}					
		}
		json('404');
	}
	
	//关注
	public function fans(){
		if (I('post.')){
			$table = M('fans');
			$user = M('user');
			$message = M('message');
			$useruid = $user->find(I('post.uid'));
			$userfid = $user->find(I('post.fid'));
			$where = I('post.');
			if (!$where['uid']){
				json('400','登陆后才能执行此操作！');
			}
			$where1['uid'] = I('post.fid');
			$where1['fid'] = I('post.uid');
			if ($table->where($where)->find()){
				if ($table->where($where)->delete()){
					$tab = M('friend');
					if ($tab->where($where)->find()){
						$tab->where($where)->delete();
						$tab->where($where1)->delete();
					}
					$data['state'] = 0;
					$data['uid'] = I('post.fid');
					$data['title'] = $useruid['username'].'取消了关注';
					$data['content'] = $useruid['username'].'取消了关注';
					$data['addtime'] = time();
					$data['type'] = 'user';
					$data['typeid'] = I('post.uid');
					$message->add($data);
					$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
					if ($userfid['jpushid']){
						$jpushid[] = $userfid['jpushid'];
					}
					$array['type'] = 'message';
					$content = $data['title'];
					if ($jpushid){
						$jpush->push($jpushid, $this->title,$content,$array);
					}
					json('200','取消成功');
				}else {
					json('400','操作失败');
				}
			}else {
				$where['addtime']= time();
				if ($table->add($where)){
					if ($table->where($where1)->find()){
						$tab = M('friend');
						$where1['addtime'] = time();
						$tab->add($where);
						$tab->add($where1);
					}
					$data['state'] = 0;
					$data['uid'] = I('post.fid');
					$data['title'] = $useruid['username'].'关注了您';
					$data['content'] = $useruid['username'].'关注了您';
					$data['addtime'] = time();
					$data['type'] = 'user';
					$data['typeid'] = I('post.uid');
					$message->add($data);
					$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
					if ($userfid['jpushid']){
						$jpushid[] = $userfid['jpushid'];
					}
					$array['type'] = 'message';
					$content = $data['title'];
					if ($jpushid){
						$jpush->push($jpushid, $this->title,$content,$array);
					}
					json('200','关注成功');
				}else {
					json('400','操作失败');
				}
			}					
		}
		json('404');
	}
	
	//首页分类获取
	public function group(){
		$table = M('group');
		$data = $table->field('id,title')->where('pid = 0')->order('ord asc,id asc')->select();
		json('200','成功',$data);
	}
	
	//新闻首页
	public function index(){
		if (I('post.')){
			$cityid = I('cityid');
			$id = I('post.id');
			$table = M('news');
			$page = I('post.page') ? I('post.page') : 1;
			$groupid = I('post.groupid') ? I('post.groupid') : 'top';
			$page1 = ($page-1)*10;
			$page2 = ($page-1);
			if ($groupid == 'top'){
				$data = $table->field('id,title,description as descript,simg,img,istop,origin,addtime,type')->where("isred = 2 and istop != 2 and (cityid = 0 or cityid = $cityid)")->order('id desc')->limit($page1,10)->select();	
				$data1 = $table->field('id,title,description as descript,simg,img,istop,origin,addtime,type')->where("istop = 2 and (cityid = 0 or cityid = $cityid)")->order('isred desc,ord asc,id desc')->limit($page2,1)->select();
				if (count($data) >= 5 && $data1){
					$res = array_splice($data,5,5,$data1);
					$data = array_merge($data, $res);
				}
			}elseif ($groupid == 'hot'){
				$data = $table->field('id,title,description as descript,simg,img,istop,origin,addtime,type')->where("istop != 2 and (cityid = 0 or cityid = $cityid)")->order('ord asc,id desc')->limit($page1,10)->select();	
				$data1 = $table->field('id,title,description as descript,simg,img,istop,origin,addtime,type')->where("istop = 2 and (cityid = 0 or cityid = $cityid)")->order('isred desc,ord asc,id desc')->limit($page2,1)->select();
				if (count($data) >= 5 && $data1){
					$res = array_splice($data,5,5,$data1);
					$data = array_merge($data, $res);
				}
			}elseif ($groupid == 'news'){
				$data = $table->field('id,title,description as descript,simg,img,istop,origin,addtime,type')->where("istop != 2 and (cityid = 0 or cityid = $cityid)")->order('id desc')->limit($page1,10)->select();
			}else {
				$data = $table->field('id,title,description as descript,simg,img,istop,origin,addtime,type')->where("groupid = $groupid and istop != 2 and (cityid = 0 or cityid = $cityid)")->order('id desc')->limit($page1,10)->select();
			}
			if ($data){
				$comment = M('comment');
				$friend = M('friend');
				$arr = $friend->where("uid = $id")->getField('fid',true);
				if($arr){
					$where['uid'] = array('in',$arr);
				}				
				$where['type'] = 'news';		
				$img = M('img');
				foreach ($data as $key=>$val){			
					$data[$key]['comment'] = $comment->where("type = 'news' and typeid = '{$val['id']}'")->count();
					$where['typeid'] = $val['id'];
					if($arr){
						$data[$key]['friendcount'] = count($comment->where($where)->group('uid')->select());
					}else {
						$data[$key]['friendcount'] = 0;
					}
					if ($val['type'] == 2){
						$data[$key]['simg3'] = $img->field('simg')->where("type = 'news' and pid = '{$val['id']}'")->order('id asc')->limit(3)->select();
					}else {
						$data[$key]['simg3'] = 0;
					}
				}
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}
		}
		json('404');
	}
	
	//历史阅读
	public function tracenews(){
		if (I('post.')){
			$id = I('post.id');
			$table = M('trace');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page-1)*15;
			$data = $table->field('t_news.id,t_news.title,t_news.description as descript,t_news.simg,t_news.img,t_news.istop,t_news.origin,t_news.addtime,t_news.type')
			->join('left join t_news on t_news.id = t_trace.newsid')
			->where("t_trace.uid = $id and t_news.id != ' '")->order('t_trace.addtime desc')->limit($pages,15)->select();
			if ($data){
				$comment = M('comment');
				$friend = M('friend');
				$arr = $friend->where("uid = $id")->getField('fid',true);
				if($arr){
					$where['uid'] = array('in',$arr);
				}
				$where['type'] = 'news';
				$img = M('img');
				foreach ($data as $key=>$val){
					$data[$key]['comment'] = $comment->where("type = 'news' and typeid = '{$val['id']}'")->count();
					$where['typeid'] = $val['id'];
					if($arr){
						$data[$key]['friendcount'] = count($comment->where($where)->group('uid')->select());
					}else {
						$data[$key]['friendcount'] = 0;
					}
					if ($val['type'] == 2){
						$data[$key]['simg3'] = $img->field('simg')->where("type = 'news' and pid = '{$val['id']}'")->order('id asc')->limit(3)->select();
					}else {
						$data[$key]['simg3'] = 0;
					}
				}
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}
		}
		json('404');
	}
	
	//相关新闻列表
	public function withnews(){
		if (I('post.')){
			$id = I('post.id');
			$cityid = I('post.cityid');
			$table = M('news');
			$res = $table->find(I('post.newsid'));
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page-1)*20;
			$data = $table->field('id,title,description as descript,simg,img,istop,origin,addtime,type')->where("groupid = '{$res['groupid']}' and id !='{$res['id']}' and (cityid = $cityid or cityid = 0)")->order("addtime desc")->limit($pages,20)->select();
			if ($data){
				$comment = M('comment');
				$friend = M('friend');
				$arr = $friend->where("uid = $id")->getField('fid',true);
				if($arr){
					$where['uid'] = array('in',$arr);
				}
				$where['type'] = 'news';
				$img = M('img');
				foreach ($data as $key=>$val){
					$data[$key]['comment'] = $comment->where("type = 'news' and typeid = '{$val['id']}'")->count();
					$where['typeid'] = $val['id'];
					if($arr){
						$data[$key]['friendcount'] = count($comment->where($where)->group('uid')->select());
					}else {
						$data[$key]['friendcount'] = 0;
					}
					if ($val['type'] == 2){
						$data[$key]['simg3'] = $img->field('simg')->where("type = 'news' and pid = '{$val['id']}'")->order('id asc')->limit(3)->select();
					}else {
						$data[$key]['simg3'] = 0;
					}
				}
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}
		}
		json('404');
	}
	
	//收藏新闻列表
	public function cellnews(){
		if (I('post.')){
			$id = I('post.id');
			$table = M('cell');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page-1)*15;
			$data = $table->field('t_news.id,t_news.title,t_news.description as descript,t_news.simg,t_news.img,t_news.istop,t_news.origin,t_news.addtime,t_news.type')
			->join('left join t_news on t_news.id = t_cell.pid')
			->where("t_cell.uid = $id and t_cell.type = 'news' and t_news.id != ' '")->order('t_cell.addtime desc')->limit($pages,15)->select();
			if ($data){
				$comment = M('comment');
				$friend = M('friend');
				$arr = $friend->where("uid = $id")->getField('fid',true);
				if($arr){
					$where['uid'] = array('in',$arr);
				}
				$where['type'] = 'news';
				$img = M('img');
				foreach ($data as $key=>$val){
					$data[$key]['comment'] = $comment->where("type = 'news' and typeid = '{$val['id']}'")->count();
					$where['typeid'] = $val['id'];
					if($arr){
						$data[$key]['friendcount'] = count($comment->where($where)->group('uid')->select());
					}else {
						$data[$key]['friendcount'] = 0;
					}
					if ($val['type'] == 2){
						$data[$key]['simg3'] = $img->field('simg')->where("type = 'news' and pid = '{$val['id']}'")->order('id asc')->limit(3)->select();
					}else {
						$data[$key]['simg3'] = 0;
					}
				}
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}
		}
		json('404');
	}
	
	//发布评论
	public function addcomment(){
		if (I('post.')){
			$table = M('comment');
			$where = I('post.');
			if (!$where['uid']){
				json('400','登陆后才能执行此操作！');
			}
			if ($where['uid'] == $where['senduid']){
				json('400','不能评论自己的评论');
			}
			$state = $where['state'];
			unset($where['state']);
			$where['addtime'] = time();
			if ($table->add($where)){
				if ($where['type'] == 'news' and $where['pid'] != 0){
					$table->where("id = '{$where['pid']}'")->setField('addtime',$where['addtime']);
				}
				if ($where['senduid'] != 0 and $state == 1){
					$friend = M('friend');
					if ($friend->where("uid = '{$where['uid']}' and fid = '{$where['senduid']}'")->find()){
						$user = M('user');
						$res = $user->find($where['senduid']);		
						$jpushid[] =$res['jpushid'];
						$array['type'] = 'message';
						$array['id'] = $where['typeid'];
						$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
						$content = '您的好友回复了您';
						$message = M('message');
						$user = M('user');
						$data['title'] = '您的好友回复了您';
						$data['content'] = '您的好友回复了您';
						$data['type'] = $where['type'];
						$data['typeid'] = $where['typeid'];
						$data['state'] = 0;
						$data['uid'] = $res['id'];
						$message->add($data);
						if ($jpushid){
							$jpush->push($jpushid, $this->title,$content,$array);
						}
					}
				}
				json('200','成功');
			}else {
				json('400','评论失败');
			}	
		}
		json('404');
	}
	
	//启动更新
	public function startsave(){
		if (I('post.')){
			$table = M('user');
			$where = I('post.');
			$where['logintime'] = time();
			$ress = $table->find( I('post.id'));
			if ($ress['birth']){
				$where['age'] = date('Y',time()) - date('Y',$ress['birth']);
			}
			if ($where['id']){
				if ($table->save($where)){
					$base = M('base');
					$res = $base->find();
					$data['ban'] = $res['ban'];
					json('200','成功',$data);
				}else {
					json('400','启动失败');
				}
			}
		}
		json('404');
	}
	
	//帖子首页评论
	public function comment(){
		if (I('post.')){
			$table = M('comment');
			$upper = M('upper');
			$friend = M('friend');
			$uid= I('post.uid');
			$where['t_comment.typeid'] = I('post.typeid');
			$where['t_comment.type'] = I('post.type');
			$where['t_comment.pid'] = 0;
			$data['hot'] = $table->field('t_comment.id,t_comment.description as descript,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude')
			->join('left join t_user on t_user.id = t_comment.uid')
			->where($where)->select();
			if ($data['hot']){
				foreach ($data['hot'] as $key=>$val){
					$data['hot'][$key]['count'] = $table->where("pid = '{$val['id']}'")->count();
					if ($data['hot'][$key]['count'] == 0 && $data['hot'][$key]['upper'] == 0){
						unset($data['hot'][$key]);
						continue;
					}
					$data['hot'][$key]['di'] = powc(I('post.latitude'),I('post.longitude'), $val['latitude'], $val['longitude']);
					if ($uid){
						$data['hot'][$key]['state'] = $upper->where("type = 'comment' and pid = '{$val['id']}' and uid = $uid")->find() ? 1 : 0;
						$friendinfo = $friend->where("fid = '{$val['uid']}' and uid = $uid")->find();
						if ($friendinfo){
							if($friendinfo['username']){
								$data['hot'][$key]['username'] = $friendinfo['username'];
							}
							$data['hot'][$key]['isfriend'] = 1;
						}else {
							$data['hot'][$key]['isfriend'] = 0;
						}
					}else {
						$data['hot'][$key]['state'] = 0;
						$data['hot'][$key]['isfriend'] = 0;
					}
					$data['hot'][$key]['data'] = $table->field('t_comment.id,t_comment.description as descript,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude,u.id as uid2,u.username as username2,u.simg as simg2')
					->join('left join t_user on t_user.id = t_comment.uid')
					->join('left join t_user u on u.id = t_comment.senduid')
					->where("t_comment.pid = '{$val['id']}'")->order('t_comment.id desc')->limit(2)->select();
					//计算二级评论距离
					foreach ($data['hot'][$key]['data'] as $k=>$v){
						$data['hot'][$key]['data'][$k]['di'] = powc(I('post.latitude'),I('post.longitude'), $v['latitude'], $v['longitude']);
						if ($data['hot'][$key]['data'][$k]['di'] >= 1000){
							$data['hot'][$key]['data'][$k]['distance'] = ceil($data['hot'][$key]['data'][$k]['di']/1000).'km';
						}else {
							$data['hot'][$key]['data'][$k]['distance'] = $data['hot'][$key]['data'][$k]['di'].'m';
						}
						if ($uid){
							$data['hot'][$key]['data'][$k]['state'] = $upper->where("type = 'comment' and pid = '{$v['id']}' and uid = $uid")->find() ? 1 : 0;
							$friendinfo1 = $friend->where("fid = '{$v['uid']}' and uid = $uid")->find();
 							if ($friendinfo1){
								if($friendinfo1['username']){
									$data['hot'][$key]['data'][$k]['username'] = $friendinfo1['username'];
								}
								$data['hot'][$key]['data'][$k]['isfriend'] = 1;
							}else {
								$data['hot'][$key]['data'][$k]['isfriend'] = 0;
							}
							$data['hot'][$key]['data'][$k]['isfriend2'] = $friend->where("fid = '{$v['uid2']}' and uid = $uid")->find() ? 1 : 0;
						}else {
							$data['hot'][$key]['data'][$k]['state'] = 0;
							$data['hot'][$key]['data'][$k]['isfriend'] = 0;
							$data['hot'][$key]['data'][$k]['isfriend2'] = 0;
						}
					}
					if ($data['hot'][$key]['di'] >= 1000){
						$data['hot'][$key]['distance'] = ceil($data['hot'][$key]['di']/1000).'km';
					}else {
						$data['hot'][$key]['distance'] = $data['hot'][$key]['di'].'m';
					}
					$count[] = $data['hot'][$key]['count'];
				}		
				array_multisort($count,SORT_DESC,$data['hot']);
				$data['hotcount'] = count($data['hot']);
				$data['hot'] = array_page($data['hot'],1,3);			
			}else {
				$data['hotcount'] = 0;
			}
			
			$data['news'] = $table->field('t_comment.id,t_comment.description as descript,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude')
			->join('left join t_user on t_user.id = t_comment.uid')
			->where($where)->order('addtime desc')->limit(10)->select();
			if ($data['news']){
				foreach ($data['news'] as $key=>$val){
					$data['news'][$key]['count'] = $table->where("pid = '{$val['id']}'")->count();
					$data['news'][$key]['di'] = powc(I('post.latitude'),I('post.longitude'), $val['latitude'], $val['longitude']);
					if ($uid){
						$data['news'][$key]['state'] = $upper->where("type = 'comment' and pid = '{$val['id']}' and uid = $uid")->find() ? 1 : 0;
						$friendinfo = $friend->where("fid = '{$val['uid']}' and uid = $uid")->find();
						if ($friendinfo){
							if($friendinfo['username']){
								$data['news'][$key]['username'] = $friendinfo['username'];
							}
							$data['news'][$key]['isfriend'] = 1;
						}else {
							$data['news'][$key]['isfriend'] = 0;
						}
					}else {
						$data['news'][$key]['state'] = 0;
						$data['news'][$key]['isfriend'] = 0;
					}
					$data['news'][$key]['data'] = $table->field('t_comment.id,t_comment.description as descript,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude,u.id as uid2,u.username as username2,u.simg as simg2')
					->join('left join t_user on t_user.id = t_comment.uid')
					->join('left join t_user u on u.id = t_comment.senduid')
					->where("t_comment.pid = '{$val['id']}'")->order('t_comment.id desc')->limit(2)->select();
					//计算二级评论距离
					foreach ($data['news'][$key]['data'] as $k=>$v){
						$data['news'][$key]['data'][$k]['di'] = powc(I('post.latitude'),I('post.longitude'), $v['latitude'], $v['longitude']);
						if ($data['news'][$key]['data'][$k]['di'] >= 1000){
							$data['news'][$key]['data'][$k]['distance'] = ceil($data['news'][$key]['data'][$k]['di']/1000).'km';
						}else {
							$data['news'][$key]['data'][$k]['distance'] = $data['news'][$key]['data'][$k]['di'].'m';
						}
						if ($uid){
							$data['news'][$key]['data'][$k]['state'] = $upper->where("type = 'comment' and pid = '{$v['id']}' and uid = $uid")->find() ? 1 : 0;
							$friendinfo1 = $friend->where("fid = '{$v['uid']}' and uid = $uid")->find();
 							if ($friendinfo1){
								if($friendinfo1['username']){
									$data['news'][$key]['data'][$k]['username'] = $friendinfo1['username'];
								}
								$data['news'][$key]['data'][$k]['isfriend'] = 1;
							}else {
								$data['news'][$key]['data'][$k]['isfriend'] = 0;
							}
							$data['news'][$key]['data'][$k]['isfriend2'] = $friend->where("fid = '{$v['uid2']}' and uid = $uid")->find() ? 1 : 0;
						}else {
							$data['news'][$key]['data'][$k]['state'] = 0;
							$data['news'][$key]['data'][$k]['isfriend'] = 0;
							$data['news'][$key]['data'][$k]['isfriend2'] = 0;
						}
					}
					if ($data['news'][$key]['di'] >= 1000){
						$data['news'][$key]['distance'] = ceil($data['news'][$key]['di']/1000).'km';
					}else {
						$data['news'][$key]['distance'] = $data['news'][$key]['di'].'m';
					}
				}
			}
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}
		}
		json('404');
	}
	
	//加载热门评论
	public function hotmore(){
		if (I('post.')){
			$table = M('comment');
			$upper = M('upper');
			$friend = M('friend');
			$uid= I('post.uid');
			$where['t_comment.typeid'] = I('post.typeid');
			$where['t_comment.type'] = I('post.type');
			$where['t_comment.pid'] = 0;
			$data = $table->field('t_comment.id,t_comment.description as descript,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude')
			->join('left join t_user on t_user.id = t_comment.uid')
			->where($where)->select();
			if ($data){
				foreach ($data as $key=>$val){
					$data[$key]['count'] = $table->where("pid = '{$val['id']}'")->count();
					if ($data[$key]['count'] == 0 && $data[$key]['upper'] == 0){
						unset($data[$key]);
						continue;
					}
					$data[$key]['di'] = powc(I('post.latitude'),I('post.longitude'), $val['latitude'], $val['longitude']);
					if ($uid){
						$data[$key]['state'] = $upper->where("type = 'comment' and pid = '{$val['id']}' and uid = $uid")->find() ? 1 : 0;
						$friendinfo = $friend->where("fid = '{$val['uid']}' and uid = $uid")->find();
						if ($friendinfo){
							if($friendinfo['username']){
								$data[$key]['username'] = $friendinfo['username'];
							}
							$data[$key]['isfriend'] = 1;
						}else {
							$data[$key]['isfriend'] = 0;
						}
						//$data[$key]['isfriend'] = $friend->where("fid = '{$val['uid']}' and uid = $uid")->find() ? 1 : 0;
					}else {
						$data[$key]['state'] = 0;
						$data[$key]['isfriend'] = 0;
					}	
					$data[$key]['data'] = $table->field('t_comment.id,t_comment.description as descript,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude,u.id as uid2,u.username as username2,u.simg as simg2')
					->join('left join t_user on t_user.id = t_comment.uid')
					->join('left join t_user u on u.id = t_comment.senduid')
					->where("t_comment.pid = '{$val['id']}'")->order('t_comment.id desc')->limit(2)->select();
					//计算二级评论距离
					foreach ($data[$key]['data'] as $k=>$v){
						$data[$key]['data'][$k]['di'] = powc(I('post.latitude'),I('post.longitude'), $v['latitude'], $v['longitude']);
						if ($data[$key]['data'][$k]['di'] >= 1000){
							$data[$key]['data'][$k]['distance'] = ceil($data[$key]['data'][$k]['di']/1000).'km';
						}else {
							$data[$key]['data'][$k]['distance'] = $data[$key]['data'][$k]['di'].'m';
						}
						if ($uid){
							$data[$key]['data'][$k]['state'] = $upper->where("type = 'comment' and pid = '{$v['id']}' and uid = $uid")->find() ? 1 : 0;
							//$data[$key]['data'][$k]['isfriend'] = $friend->where("fid = '{$v['uid']}' and uid = $uid")->find() ? 1 : 0;
							$friendinfo1 = $friend->where("fid = '{$v['uid']}' and uid = $uid")->find();
							if ($friendinfo1){
								if($friendinfo1['username']){
									$data[$key]['data'][$k]['username'] = $friendinfo1['username'];
								}
								$data[$key]['data'][$k]['isfriend'] = 1;
							}else {
								$data[$key]['data'][$k]['isfriend'] = 0;
							}
							$data[$key]['data'][$k]['isfriend2'] = $friend->where("fid = '{$v['uid2']}' and uid = $uid")->find() ? 1 : 0;
						}else {
							$data[$key]['data'][$k]['state'] = 0;
							$data[$key]['data'][$k]['isfriend'] = 0;
							$data[$key]['data'][$k]['isfriend2'] = 0;
						}
					}
					if ($data[$key]['di'] >= 1000){
						$data[$key]['distance'] = ceil($data[$key]['di']/1000).'km';
					}else {
						$data[$key]['distance'] = $data[$key]['di'].'m';
					}
					$count[] = $data[$key]['count'];
				}
				array_multisort($count,SORT_DESC,$data);
				$data = array_page($data,1,10);
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}
		}
	}
	
	//加载最新评论
	public function newsmore(){
		if (I('post.')){
			$table = M('comment');
			$upper = M('upper');
			$friend = M('friend');
			$uid= I('post.uid');
			$where['t_comment.typeid'] = I('post.typeid');
			$where['t_comment.type'] = I('post.type');
			$where['t_comment.pid'] = 0;
			$page = I('post.page') ? I('post.page') : 1;
			$page = ($page-1)*10;
			$data = $table->field('t_comment.id,t_comment.description as descript,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude')
			->join('left join t_user on t_user.id = t_comment.uid')
			->where($where)->order('addtime desc')->limit($page,10)->select();		
			if ($data){
				foreach ($data as $key=>$val){
					$data[$key]['count'] = $table->where("pid = '{$val['id']}'")->count();
					$data[$key]['di'] = powc(I('post.latitude'),I('post.longitude'), $val['latitude'], $val['longitude']);
					if ($uid){
						$data[$key]['state'] = $upper->where("type = 'comment' and pid = '{$val['id']}' and uid = $uid")->find() ? 1 : 0;
						$friendinfo = $friend->where("fid = '{$val['uid']}' and uid = $uid")->find();
						if ($friendinfo){
							if($friendinfo['username']){
								$data[$key]['username'] = $friendinfo['username'];
							}
							$data[$key]['isfriend'] = 1;
						}else {
							$data[$key]['isfriend'] = 0;
						}
					}else {
						$data[$key]['state'] = 0;
						$data[$key]['isfriend'] = 0;
					}
					$data[$key]['data'] = $table->field('t_comment.id,t_comment.description as descript,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude,u.id as uid2,u.username as username2,u.simg as simg2')
					->join('left join t_user on t_user.id = t_comment.uid')
					->join('left join t_user u on u.id = t_comment.senduid')
					->where("t_comment.pid = '{$val['id']}'")->order('t_comment.id desc')->limit(2)->select();
					//计算二级评论距离
					foreach ($data[$key]['data'] as $k=>$v){
						$data[$key]['data'][$k]['di'] = powc(I('post.latitude'),I('post.longitude'), $v['latitude'], $v['longitude']);
						if ($data[$key]['data'][$k]['di'] >= 1000){
							$data[$key]['data'][$k]['distance'] = ceil($data[$key]['data'][$k]['di']/1000).'km';
						}else {
							$data[$key]['data'][$k]['distance'] = $data[$key]['data'][$k]['di'].'m';
						}
						if ($uid){
							$data[$key]['data'][$k]['state'] = $upper->where("type = 'comment' and pid = '{$v['id']}' and uid = $uid")->find() ? 1 : 0;
							$friendinfo1 = $friend->where("fid = '{$v['uid']}' and uid = $uid")->find();
							if ($friendinfo1){
								if($friendinfo1['username']){
									$data[$key]['data'][$k]['username'] = $friendinfo1['username'];
								}
								$data[$key]['data'][$k]['isfriend'] = 1;
							}else {
								$data[$key]['data'][$k]['isfriend'] = 0;
							}
							$data[$key]['data'][$k]['isfriend2'] = $friend->where("fid = '{$v['uid2']}' and uid = $uid")->find() ? 1 : 0;
						}else {
							$data[$key]['data'][$k]['state'] = 0;
							$data[$key]['data'][$k]['isfriend'] = 0;
							$data[$key]['data'][$k]['isfriend2'] = 0;
						}
					}
					if ($data[$key]['di'] >= 1000){
						$data[$key]['distance'] = ceil($data[$key]['di']/1000).'km';
					}else {
						$data[$key]['distance'] = $data[$key]['di'].'m';
					}
				}
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}
		}
		json('404');
	}
	
	//加载二级评论
	public function commentmore(){
		if (I('post.')){
			$table = M('comment');
			$upper = M('upper');
			$friend = M('friend');
			$uid= I('post.uid');
			$where['t_comment.pid'] = I('post.pid');
			$where['t_comment.type'] = I('post.type');
			$page = I('post.page') ? I('post.page') : 1;
			$page = ($page-2)*8+2;
			$data = $table->field('t_comment.id,t_comment.description as descript,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude,u.id as uid2,u.username as username2,u.simg as simg2')
			->join('left join t_user on t_user.id = t_comment.uid')
			->join('left join t_user u on u.id = t_comment.senduid')
			->where($where)->order('t_comment.id desc')->limit($page,8)->select();
			//计算二级评论距离
			foreach ($data as $k=>$v){
				if ($uid){
					$data[$k]['state'] = $upper->where("type = 'comment' and pid = '{$v['id']}' and uid = $uid")->find() ? 1 : 0;
					//$data[$k]['isfriend'] = $friend->where("fid = '{$v['uid']}' and uid = $uid")->find() ? 1 : 0;
					$friendinfo = $friend->where("fid = '{$v['uid']}' and uid = $uid")->find();
					if ($friendinfo){
						if($friendinfo['username']){
							$data[$k]['username'] = $friendinfo['username'];
						}
						$data[$k]['isfriend'] = 1;
					}else {
						$data[$k]['isfriend'] = 0;
					}
					$data[$k]['isfriend2'] = $friend->where("fid = '{$v['uid2']}' and uid = $uid")->find() ? 1 : 0;
				}else {
					$data[$k]['state'] = 0;
					$data[$k]['isfriend'] = 0;
				}
				$data[$k]['di'] = powc(I('post.latitude'),I('post.longitude'), $v['latitude'], $v['longitude']);
				if ($data[$k]['di'] >= 1000){
					$data[$k]['distance'] = ceil($data[$k]['di']/1000).'km';
				}else {
					$data[$k]['distance'] = $data[$k]['di'].'m';
				}
			}
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}
		}
		json('404');
	}
	
	//好友评论
	public function friendcomment(){
		if (I('post.')){
			$table = M('comment');
			$uid= I('post.uid');
			if (!$uid){
				json('400','登陆后才能执行此操作！');
			}
			$upper = M('upper');
			$friend = M('friend');
			$arr = $friend->where("uid = $uid")->getField('fid',true);
			$arr[] = $uid;
			if (!$arr){
				json('400','没有数据');
			}
			$where['t_comment.uid'] = array('in',$arr);
			$where['t_comment.typeid'] = I('post.typeid');
			$where['t_comment.type'] = I('post.type');		
			$page = I('post.page') ? I('post.page') : 1;
			$page = ($page-1)*10;
			$data = $table->field('t_comment.id,t_comment.description as descript,t_comment.pid,t_comment.senduid,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude')
			->join('left join t_user on t_user.id = t_comment.uid')
			->where($where)->order('id desc')->limit($page,10)->select();
			if ($data){
				foreach ($data as $key=>$val){
					$data[$key]['count'] = $table->where("pid = '{$val['id']}'")->count();
					$data[$key]['di'] = powc(I('post.latitude'),I('post.longitude'), $val['latitude'], $val['longitude']);
					if ($uid){
						$data[$key]['state'] = $upper->where("type = 'comment' and pid = '{$val['id']}' and uid = $uid")->find() ? 1 : 0;
						$friendinfo = $friend->where("fid = '{$val['uid']}' and uid = $uid")->find();
						if ($friendinfo){
							if($friendinfo['username']){
								$data[$key]['username'] = $friendinfo['username'];
							}
							$data[$key]['isfriend'] = 1;
						}else {
							$data[$key]['isfriend'] = 0;
						}
					}else {
						$data[$key]['state'] = 0;
						$data[$key]['isfriend'] = 0;
					}
					if ($data[$key]['di'] >= 1000){
						$data[$key]['distance'] = ceil($data[$key]['di']/1000).'km';
					}else {
						$data[$key]['distance'] = $data[$key]['di'].'m';
					}
					if ($val['pid']){
						$data[$key]['data'] = $table->field('t_comment.id,t_comment.description as descript,t_comment.pid,t_comment.senduid,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude')
						->join('left join t_user on t_user.id = t_comment.uid')
						->where("t_comment.id = '{$val['iid']}'")->order('t_comment.addtime desc')->limit(1)->select();
						if ($data[$key]['data']){
							//计算二级评论距离
							foreach ($data[$key]['data'] as $k=>$v){
								$data[$key]['data'][$k]['di'] = powc(I('post.latitude'),I('post.longitude'), $v['latitude'], $v['longitude']);
								if ($data[$key]['data'][$k]['di'] >= 1000){
									$data[$key]['data'][$k]['distance'] = ceil($data[$key]['data'][$k]['di']/1000).'km';
								}else {
									$data[$key]['data'][$k]['distance'] = $data[$key]['data'][$k]['di'].'m';
								}
								if ($uid){
									$data[$key]['data'][$k]['state'] = $upper->where("type = 'comment' and pid = '{$v['id']}' and uid = $uid")->find() ? 1 : 0;
									//$data[$key]['data'][$k]['isfriend'] = $friend->where("fid = '{$v['uid']}' and uid = $uid")->find() ? 1 : 0;
									$friendinfo1 = $friend->where("fid = '{$v['uid']}' and uid = $uid")->find();
									if ($friendinfo1){
										if($friendinfo1['username']){
											$data[$key]['data'][$k]['username'] = $friendinfo1['username'];
										}
										$data[$key]['data'][$k]['isfriend'] = 1;
									}else {
										$data[$key]['data'][$k]['isfriend'] = 0;
									}
									//$data[$key]['data'][$k]['isfriend2'] = $friend->where("fid = '{$v['uid2']}' and uid = $uid")->find() ? 1 : 0;
								}else {
									$data[$key]['data'][$k]['state'] = 0;
									$data[$key]['data'][$k]['isfriend'] = 0;
									$data[$key]['data'][$k]['isfriend2'] = 0;
								}
							}
						}else {
							$data[$key]['data'] = array();
						}
					}else {
						$data[$key]['data'] = array();
					}
				}
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}
		}
		json('404');
	}
	
	//会员空间评论新闻
	public function usernews(){
		if (I('post.')){
			$uid = I('post.uid');
			$id =I('post.id');
			$user = M('user');
			$page = I('post.page') ?I('post.page') :1;
			$page = ($page-1)*10;
			$res = $user->field('id,simg,username,latitude,longitude')->find($uid);
			if ($res){
				$friend= M('friend');
				$comment = M('comment');
				$friendinfo = $friend->where("uid = $id and fid = $uid")->find();
				if ($friendinfo){
					$frienddata = 1;
					if ($friendinfo['username']){
						$res['username'] = $friendinfo['username'];
					}
				}else {
					$frienddata = 0;
				}
				$di = powc(I('post.latitude'),I('post.longitude'), $res['latitude'], $res['longitude']);
				if ($di >= 1000){
					$distance = ceil($di/1000).'km';
				}else {
					$distance = $di.'m';
				}
				$data = $comment->field('t_comment.id,t_comment.pid,t_comment.upper,t_comment.description as descript,t_comment.addtime,t_comment.typeid as newsid,t_news.title,t_news.simg,t_news.img,t_news.type')
				->join('left join t_news on t_news.id = t_comment.typeid')
				->where("t_comment.uid = $uid and t_comment.type = 'news'")->order('t_comment.addtime desc')->limit($page,10)->select();
				$img = M('img');
				$upper = M('upper');
				foreach ($data as $key => $val){
					$data[$key]['simg3'] = $img->where("type = 'news' and pid = '{$val['newsid']}'")->getField('simg') ? $img->where("type = 'news' and pid = '{$val['newsid']}'")->getField('simg') : ' ';
					$data[$key]['isupper'] = $upper->where("type = 'comment' and pid = '{$val['id']}' and uid = $id")->find() ? 1 : 0;					
					$data[$key]['userimg'] = $res['simg'];
					$data[$key]['userid'] = $res['id'];
					$data[$key]['username'] = $res['username'];
					$data[$key]['distance'] = $distance;
					$data[$key]['isfriend'] = $frienddata;
				}
				json('200','成功',$data);
			}else {
				json('400','没有此会员');
			}
		}
		json('404');
	}
	
	//会员空间资料
	public function usertop(){
		if (I('post.')){
			$uid = I('post.uid');
			$id =I('post.id');
			$user = M('user');
			$data = $user->field('id,simg,bgimg,username,sex,logintime,latitude,longitude,sign,occup,hobby,addr,description as descript,address,addtime,hometown,birth,state')->find($uid);
			if ($data){
				$fans = M('fans');			
				$comment = M('comment');
				$data['isfans'] = $fans->where("uid = $id and fid = $uid")->find() ? 1 : 0;
				$data['myfans'] = $fans->where("fid = $uid")->count();
				$data['fansto'] = $fans->where("uid = $uid")->count();
				$data['dynamic'] = 0;
				$data['di'] = powc(I('post.latitude'),I('post.longitude'), $data['latitude'], $data['longitude']);
				if ($data['di'] >= 1000){
					$data['distance'] = ceil($data['di']/1000).'km';
				}else {
					$data['distance'] = $data['di'].'m';
				}
				$friend = M('friend');
				$friendinfo = $friend->where("uid = $id and fid = $uid")->find();
				if ($friendinfo){
					$data['isfriend'] = 1;
					if ($friendinfo['username']){
						$data['username1'] = $data['username'];
						$data['username'] = $friendinfo['username'];
					}
				}else {
					$data['isfriend'] = 0;
				}
				//$data['isfriend'] = $friend->where("uid = $id and fid = $uid")->find() ? 1 : 0;
				json('200','成功',$data);
			}else {
				json('400','没有此会员');
			}
		}
		json('404');
	}
	
	//最新动态
	public function newdynamic(){
		if (I('post.')){
			$uid = I('post.uid');
			$table = M('dynamic');
			$data = $table->field('id,type,content as descript')->where("uid = $uid and type = 'user'")->order('addtime desc')->find();
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有数据');
			}
		}
		json('404');
	}
	
	//创建群组
	public function addgroup(){
		if (I('post.')){
			$table = M('groups');
			$where = I('post.');
			if (I('post.type') == 1){
				if ($table->where("uid = '{$where['uid']}' and type = 1")->find()){
					json('400','普通会员只能创建一个普通群');
				}
			}
			if (I('post.type') == 2){
				$count = $table->where("uid = '{$where['uid']}' and type = 2")->count();
				if ($count >= 4){
					json('400','普通会员只能创建4个私密群');
				}
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
					$where['simg'] = '/Public/upfile/'.$simg;
				}else {
					json('400','图片上传失败');
				}
			}else {
				json('400','请上传群图标');
			}
			$where['addtime'] = time();
			$id = $table->add($where);
			if ($id){
				json('200','创建成功，请等待审核');	
			}else {
				json('400','群组创建失败');
			}
		}
		json('404');
	}
	
	//条件群列表
	public function putonggroup(){
		if (I('post.id')){
			$id = I('post.id');
			$table = M('groups');
			$user = M('user');
			$userinfo = $user->find($id);
			$where['state'] = 2;
			$where['type'] =1;
			if (I('post.keyword')){
				$keyword = I('post.keyword');
				$data = $table->field('id,title,addtime,condition,description as descript,type,simg,uid,address,longitude,latitude')->where($where)->find(I('post.keyword'));
				if (!$data){
					$where['_string'] = ' (t_groups.title like "%'.$keyword.'%")  OR ( t_groups.address like "%'.$keyword.'%") ';
					$data = $table->field('id,title,addtime,condition,description as descript,type,simg,uid,address,longitude,latitude')->where($where)->select();
				}			
			}else {
				$data = $table->field('id,title,addtime,condition,description as descript,type,simg,uid,address,longitude,latitude')->where($where)->select();
			}
			$page = I('post.page') ? I('post.page') : 1;
			$tab = M('groupuser');
			$tab1 = M('message');
			foreach ($data as $key=>$val){
				if ($tab->where("group_id = '{$val['id']}' and user_id = '{$id}'")->find()){
					$data[$key]['state'] = 2;
				}elseif ($tab1->where("type = 'group' and typeid =  '{$val['id']}' and uid = '{$id}' and state =1")->find()){
					$data[$key]['state'] = 1;
				}else {
					$data[$key]['state'] = 0;
				}				
				$data[$key]['count'] = $tab->where("group_id = '{$val['id']}'")->count();
				$data[$key]['di'] = powc($userinfo['latitude'],$userinfo['longitude'], $val['latitude'], $val['longitude']);
				if ($data[$key]['di'] >= 1000){
					$data[$key]['distance'] = ceil($data[$key]['di']/1000).'km';
				}else {
					$data[$key]['distance'] = $data[$key]['di'].'m';
				}
				$distance[] = $data[$key]['di'];
				$data[$key]['user'] = $tab->field('t_user.id,t_groupuser.username,t_user.simg,t_user.sex')
				->join('left join t_user on t_user.id = t_groupuser.user_id')
				->where("t_groupuser.group_id = '{$val['id']}'")->order('t_groupuser.poll desc,t_groupuser.level')->limit(6)->select();
			}
			array_multisort($distance,SORT_ASC,$data);
			$data = array_page($data,$page);
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有群组');
			}
		}
		json('404');
	}
	
	//发送群邀请
	public function groupbyfriend(){
		if (I('post.')){
			$where = I('post.');
			unset($where['uid']);
			$groups = M('groups');
			$user = M('user');
			$userinfo = $user->find(I('post.userid'));
			$groupsinfo = $groups->find(I('post.typeid'));
			$table = M('message');
			$uid = explode(',', I('post.uid'));
			$where['addtime'] = time();
			$where['type'] = 'user';
			$where['title'] = '您的好友 '.$userinfo['username'].' 邀请您加入 '.$groupsinfo['title'].' 群组';
			$where['content'] = '您的好友 '.$userinfo['username'].' 邀请您加入 '.$groupsinfo['title'].' 群组';
			$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
			foreach ($uid as  $val){
				$where['uid'] = $val;
				if (!$table->where("uid = $val and typeid = '{$where['typeid']}' and (type = 'group' or type = 'user') and state = 1")->find()){
					$table->add($where);
					$userdata = $user->find($val);
					if ($userdata['jpushid']){
						$jpushid[] = $userdata['jpushid'];
					}
				}					
			}
			$array['type'] = 'message';
			$content = $where['content'];
			if ($jpushid){
				$jpush->push($jpushid, $this->title,$content,$array);
			}
			json('200');
		}
		json('404');
	}
	
	//申请加入群组
	public function joingroup(){	
		if (I('post.')){
			$where = I('post.');
			$groups = M('groups');
			$user = M('user');
			$userinfo = $user->find(I('post.uid'));
			$groupsinfo = $groups->find(I('post.typeid'));
			$table = M('message');
			$where['addtime'] = time();
			$where['type'] = 'group';
			$where['title'] = $userinfo['username'].' 申请加入 '.$groupsinfo['title'].' 群组';
			$where['status'] = 2;
			$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
			if (!$table->where("uid = '{$where['uid']}' and typeid = '{$where['typeid']}' and (type = 'group' or type = 'user') and state = 1")->find()){
				$table->add($where);
				$groupuser = M('groupuser');
				$userdata = $groupuser->field('t_user.jpushid')
				->join('left join t_user on t_user.id = t_groupuser.user_id')
				->where("t_groupuser.group_id = '{$where['typeid']}' and t_groupuser.level > 3")->select();
				foreach ($userdata as $val){
					if ($val['jpushid']){
						$jpushid[] = $val['jpushid'];
					}
				}
				$array['type'] = 'groupmessage';
				$content = $where['title'];
				if ($jpushid){
					$jpush->push($jpushid, $this->title,$content,$array);
				}
				json('200');
			}else {
				json('400','申请中');
			}
			
		}
		json('404');
	}
	
	//群消息列表
	public function groupmessage(){
		if (I('post.id')){
			$id = I('post.id');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1 )*15;
			$groupuser = M('groupuser');
			$table = M('message');
			$res = $groupuser->where("user_id = $id and level >3")->getField('group_id',true);
			if ($res){
				$where['typeid'] = array('in',$res);
				$where['_string'] = '(type = "group")'; 
				$data = $table->where($where)->order('addtime desc')->limit($pages,15)->select();
				if ($data){
					$user = M('user');
					foreach ($data as $key=>$val){
						if ($val['type'] == 'group'){
							$data[$key]['simg'] = $user->where("id = '{$val['uid']}'")->getField('simg');
						}
					}
					json('200','成功',$data);
				}else {
					json('400','没有消息');
				}
			}else { 
				json('400','没有消息');
			}
		}
		json('404');
	}
	
	//同意入群
	public function handlegroup(){
		if (I('post.id')){
			$where = I('post.');
			$where['state'] = 2;
			$table = M('message');
			$res = $table->find(I('post.id'));			
			if ($res){
				$groupuser = M('groupuser');
				$groups = M('groups');
				$user = M('user');
				$groupsinfo = $groups->find($res['typeid']);
				$userinfo = $user->find($res['uid']);		
				if ($table->save($where)){
					$where1['user_id'] = $res['uid'];
					$where1['group_id'] = $res['typeid'];
					$where1['username'] = $userinfo['username'];
					$where1['addtime'] = time();
					$where1['level'] = 3;
					if ($groupuser->where("user_id = '{$where1['user_id']}' and group_id = '{$where1['group_id']}'")->find()){
						json('400','已经加入该群组');
					}
					if ($groupuser->add($where1)){
						$rongyun = new  \Org\Util\Rongyun($this->appKey,$this->appSecret);
						$r = $rongyun->groupJoin($where1['user_id'],$where1['group_id']);
						if($r){
							$rong = json_decode($r);
							if($rong->code == 200){
								$data['state'] = 0;
								$data['uid'] = $res['uid'];
								$data['title'] = $userinfo['username'].'加入了'.$groupsinfo['title'].'群组';
								$data['content'] = $userinfo['username'].'加入了'.$groupsinfo['title'].'群组';
								$data['addtime'] = time();
								$data['type'] = 'group';
								$data['typeid'] = $groupsinfo['id'];
								$table->add($data);
								$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
								$userdata = $groupuser->field('t_user.jpushid')
								->join('left join t_user on t_user.id = t_groupuser.user_id')
								->where("t_groupuser.group_id = '{$res['typeid']}' and t_groupuser.level > 3")->select();
								foreach ($userdata as $val){
									if ($val['jpushid']){
										$jpushid[] = $val['jpushid'];
									}
								}
								$array['type'] = 'groupmessage';
								$content = $data['title'];
								if ($jpushid){
									$jpush->push($jpushid, $this->title,$content,$array);
								}
								$data['title'] = '您加入了'.$groupsinfo['title'].'群组';
								$data['content'] = '您加入了'.$groupsinfo['title'].'群组';
								$data['type'] = 'user';
								$table->add($data);
								$jpushuserid[0] = $userinfo['jpushid'];
								$array['type'] = 'message';
								$content = $data['title'];
								if ($jpushuserid){
									$jpush->push($jpushuserid, $this->title,$content,$array);
								}
								json('200');
							}else {
								json('400','rongyun error1');
							}
						}else {
							json('400','rongyun error2');
						}
					}else {
						$where['state'] = 1;
						$table->save($where);
						json('400','操作失败');
					}
				}else {
					json('400','重复操作');
				}
			}else {
				json('400','消息不存在');
			}
		}
		json('404');
	}
	
	//拒绝入群
	public function refusegroup(){
		if (I('post.id')){
			$where = I('post.');
			$where['state'] = 3;
			unset($where['type']);
			$table = M('message');
			$res = $table->find(I('post.id'));
			if ($res){
				$groups = M('groups');
				$user = M('user');
				$groupsinfo = $groups->find($res['typeid']);
				$userinfo = $user->find($res['uid']);
				if ($table->save($where)){
					$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
					$data['state'] = 0;
					$data['type'] = 'user';
					$data['addtime'] = time();
					$data['typeid'] = $groupsinfo['id'];
					if (I('post.type') == 2){		
						$data['uid'] = $res['uid'];
						$data['title'] = $userinfo['username'].'拒绝了加入'.$groupsinfo['title'].'群组';
						$data['content'] = $userinfo['username'].'拒绝了加入'.$groupsinfo['title'].'群组';				
						$table->add($data);				
						$userdata = $user->find($res['userid']);		
						$jpushid[0] = $userdata['jpushid'];	
					}elseif (I('post.type') == 1){
						$data['uid'] = $res['uid'];
						$data['title'] = '您的申请被'.$groupsinfo['title'].'群组拒绝了';
						$data['content'] = '您的申请被'.$groupsinfo['title'].'群组拒绝了';
						$table->add($data);
						$jpushid[0] = $userinfo['jpushid'];
					}else {
						json('400','传值错误');
					}
					$array['type'] = 'message';
					$content = $data['title'];
					if ($jpushid){
						$jpush->push($jpushid, $this->title,$content,$array);
					}
					json('200');
				}else {
					json('400','重复操作');
				}
			}else {
				json('400','消息不存在');
			}
		}
		json('404');
	}
	
	//附近的人
	public function nearbyuser(){
		if (I('post.id')){
			$id = I('post.id');
			$page = I('post.page') ? I('post.page') : 1;
			$table = M('user');
			$user = $table->find($id);
			if (I('post.sex')){
				$where['sex'] = I('post.sex');
			}
			if (I('post.logintime')){
				if (I('post.logintime') == 1){
					$logintime = time() - 15*60;
				}elseif (I('post.logintime') == 2){
					$logintime = time() - 60*60;
				}elseif (I('post.logintime') == 3){
					$logintime = time() - 24*60*60;
				}elseif (I('post.logintime') == 4){
					$logintime = time() - 3*24*60*60;
				}
				$where['logintime'] = array('gt',$logintime);
			}
			$where['id'] = array('neq',$id);
			$where['state'] = array('neq',2); 
			$data = $table->field("id,username,latitude,longitude,age,hobby,occup,sign,simg,sex,logintime")->where($where)->select();
			foreach ($data as $key=>$val){
				$data[$key]['di'] = powc($user['latitude'],$user['longitude'], $val['latitude'], $val['longitude']);
				if ($data[$key]['di'] >= 10){
					$data[$key]['distance'] = round($data[$key]['di']/1000,2).'km';
				}else {
					$data[$key]['distance'] = '0.01km';
				}
				$distance[] = $data[$key]['di'];
			}
			array_multisort($distance,SORT_ASC,$data);
			$data = array_page($data,$page);
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有附近的人');
			}
		}
		json('404');
	}
	
	//申请交换电话
	public function addchangephone(){
		if (I('post.')){
			$table = M('changephone');
			$where = I('post.');
			$where1['uid'] = I('post.userid');
			$where1['userid'] = I('post.uid');
			$user = M('user');
			$res = $table->where($where)->find();
			$res1 = $table->where($where1)->find();
			if ($res || $res1){
				if ($res['state'] == 2 || $res1['state'] == 2){
					if ($res['state'] == 2){
						json('200','成功',$res);
					}else {
						json('200','成功',$res1);
					}				
				}else {
					$data['state'] = 1;
					$data['addtime'] = time();
					
					if ($res){
						$table->where($where)->save($data);
						json('200','成功',$res);
					}elseif ($res1){
						$table->where($where1)->save($data);
						json('200','成功',$res1);
					}else {
						json('400','申请失败');
					}
				}
			}
			$userinfo1 = $user->find(I('post.uid'));
			$userinfo2 = $user->find(I('post.userid'));
			if ($userinfo1 && $userinfo2){
				$where['phone'] = $userinfo1['phone'];
				$where['uphone'] = $userinfo2['phone'];
				$where['addtime'] = time();
				$id = $table->add($where);
				$res = $table->find($id);
				if ($res){
					json('200','成功',$res);
				}else {
					json('400','申请失败了');
				}
			}else {
				json('400','会员不存在');
			}
		}
		json('404');
	}
	
	//查看交换电话
	public function showchangephone(){
		if (I('post.')){
			$table = M('changephone');
			$where = I('post.');
			$where1['uid'] = $where['userid'];
			$where1['userid'] = $where['uid'];	
			$data = $table->where($where)->find();
			$res = $table->where($where1)->find();
			if ($data){
				json('200','成功',$data);
			}elseif ($res) {
				json('200','成功',$res);
			}else {
				json('400','当前交换不存在');
			}
		}
		json('404');
	}
	
	//处理交换电话
	public function statechangephone(){
		if (I('post.')){
			$table = M('changephone');
			$where = I('post.');
			unset($where['state']);
			$res = $table->where($where)->setField('state',I('post.state'));
			if ($res){
				json('200','成功');
			}else {
				json('400','当前交换不存在');
			}
		}
		json('404');
	}
	
	//退出群
	public function groupquit(){
		if (I('post.')){
			$where['user_id'] = I('post.user_id');
			$where['group_id'] = $where1['group_id'] = I('post.group_id');
			$where1['user_id'] = I('post.id');
			$table = M('groupuser');
			$message = M('message');
			if (I('post.id')){
				$res1 = $table->where($where)->find();
				$res2 = $table->where($where1)->find();
				if ($res1['level'] >= $res2['level']){
					json('400','没有操作权限');
				}
			}
			if ($table->where($where)->delete()){
				$groups = M('groups');
				$user = M('user');
				$groupsinfo = $groups->find($where['group_id']);
				$userinfo = $user->find($where['user_id']);
				$rongyun = new  \Org\Util\Rongyun($this->appKey,$this->appSecret);
				$r = $rongyun->groupQuit($where['user_id'],$where['group_id']);
				if($r){
					$rong = json_decode($r);
					if($rong->code == 200){
						$data['state'] = 0;
						$data['uid'] = $where['user_id'];
						$data['title'] = $userinfo['username'].'退出了'.$groupsinfo['title'].'群组';
						$data['content'] = $userinfo['username'].'退出了'.$groupsinfo['title'].'群组';
						$data['addtime'] = time();
						$data['type'] = 'group';
						$data['typeid'] = $groupsinfo['id'];
						$message->add($data);
						$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
						$userdata = $table->field('t_user.jpushid')
						->join('left join t_user on t_user.id = t_groupuser.user_id')
						->where("t_groupuser.group_id = '{$where['group_id']}' and t_groupuser.level > 3")->select();
						foreach ($userdata as $val){
							if ($val['jpushid']){
								$jpushid[] = $val['jpushid'];
							}
						}
						$array['type'] = 'groupmessage';
						$content = $data['title'];
						if ($jpushid){
							$jpush->push($jpushid, $this->title,$content,$array);
						}
						if (I('post.id')){
							$data['title'] = '您被移除了'.$groupsinfo['title'].'群组';
							$data['content'] = '您被移除了'.$groupsinfo['title'].'群组';
							$data['type'] = 'user';
							$message->add($data);
							$jpushuserid[0] = $userinfo['jpushid'];
							$array['type'] = 'message';
							$content = $data['title'];
							if ($jpushuserid){
								$jpush->push($jpushuserid, $this->title,$content,$array);
							}
						}
						json('200');
					}else {
						json('400','rongyun error1');
					}
				}else {
					json('400','rongyun error2');
				}
			}else {
				json('400','操作失败');
			}
		}
		json('404');
	}
	
	//解散群组
	public function groupdismiss(){
		if (I('post.')){
			$where['group_id'] = I('post.group_id');
			$table = M('groups');
			$message = M('message');
			$groups = M('groups');
			$groupsinfo = $groups->find($where['group_id']);
			if ($groupsinfo){
				if ($groupsinfo['uid'] == I('post.user_id')){
					if ($table->delete(I('post.group_id'))){
						$user = M('groupuser');							
						$rongyun = new  \Org\Util\Rongyun($this->appKey,$this->appSecret);
						$r = $rongyun->groupDismiss(I('post.user_id'),I('post.group_id'));
						if($r){
							$rong = json_decode($r);
							if($rong->code == 200){						
								$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
								$userdata = $user->field('t_user.jpushid,t_user.id')
								->join('left join t_user on t_user.id = t_groupuser.user_id')
								->where("t_groupuser.group_id = '{$where['group_id']}'")->select();
								foreach ($userdata as $val){
									if ($val['jpushid']){
										$data['state'] = 0;
										$data['uid'] = $val['id'];
										$data['title'] = '您所在的'.$groupsinfo['title'].'群组解散了';
										$data['content'] = '您所在的'.$groupsinfo['title'].'群组解散了';
										$data['type'] = 'user';
										$data['addtime'] = time();
										$data['typeid'] = $groupsinfo['id'];
										$message->add($data);
										$jpushid[] = $val['jpushid'];
									}
								}					
								$array['type'] = 'message';
								$content = $data['title'];
								if ($jpushid){
									$jpush->push($jpushid, $this->title,$content,$array);
								}
								$user->where($where)->delete();
								json('200');
							}else {
								json('400','rongyun error1');
							}
						}else {
							json('400','rongyun error2');
						}
					}else {
						json('400','操作失败');
					}
				}else {
					json('400','没有操作权限');
				}
			}else {
				json('400','重复操作');
			}
		}
		json('404');
	}
	
	//修改群成员权限
	public function editgrouplevel(){
		if (I('post.')){
			$where['user_id'] = I('post.user_id');
			$where['group_id'] = $where1['group_id'] = I('post.group_id');
			$where1['user_id'] = I('post.id');
			$table = M('groupuser');
			$groups = M('groups');
			$groupsinfo = $groups->find($where['group_id']);
			$user = M('user');
			$userinfo = $user->find($where['user_id']);
			if (I('post.id')){
				$res1 = $table->where($where)->find();
				$res2 = $table->where($where1)->find();
				if ($res1['level'] >= $res2['level']){
					json('400','没有操作权限');
				}
			}else {
				json('400','没有操作权限');
			}
			if ($table->where($where)->setField('level', I('post.level'))){
				$message = M('message');
				$data['state'] = 0;
				$data['uid'] = $where['user_id'];			
				$data['addtime'] = time();
				$data['type'] = 'group';
				$data['typeid'] = $groupsinfo['id'];
				if (I('post.level') == 4){
					$data['title'] = $res1['username'].'提升为'.$groupsinfo['title'].'群组管理员';
					$data['content'] = $res1['username'].'提升为'.$groupsinfo['title'].'群组管理员';
				}elseif (I('post.level') == 3){
					$data['title'] = $res1['username'].'被降为'.$groupsinfo['title'].'群组普通成员';
					$data['content'] = $res1['username'].'被降为'.$groupsinfo['title'].'群组管普通成员';
				}else {
					json('400','参数错误');
				}
				$message->add($data);
				$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
				$userdata = $table->field('t_user.jpushid')
				->join('left join t_user on t_user.id = t_groupuser.user_id')
				->where("t_groupuser.group_id = '{$where['group_id']}' and t_groupuser.level > 3")->select();
				foreach ($userdata as $val){
					if ($val['jpushid']){
						$jpushid[] = $val['jpushid'];
					}
				}		
				$array['type'] = 'groupmessage';
				$content = $data['title'];
				if ($jpushid){
					$jpush->push($jpushid, $this->title,$content,$array);
				}
				if (I('post.level') == 3){
					$data['title'] = '您被降为'.$groupsinfo['title'].'群组普通成员';
					$data['content'] = '您被降为'.$groupsinfo['title'].'群组普通成员';
					$data['type'] = 'user';
					$message->add($data);
					$jpushuserid[0] = $userinfo['jpushid'];
					$array['type'] = 'message';
					$content = $data['title'];
					if ($jpushuserid){
						$jpush->push($jpushuserid, $this->title,$content,$array);
					}
				}		
				json('200');
			}else {
				json('400','操作失败');
			}
		}
		json('404');
	}
	
	//我的群组
	public function mygroup(){
		if (I('post.id')){
			$id = I('post.id');
			$table = M('groupuser');
			$user = M('user');
			$userinfo = $user->find($id);
			$data = $table->field('t_groups.uid,t_groups.address,t_groups.longitude,t_groups.latitude,t_groups.id,t_groups.simg,t_groups.addtime,t_groups.title,t_groups.condition,t_groups.type,t_groups.description as descript,t_groupuser.level')
			->join('left join t_groups on t_groups.id = t_groupuser.group_id')
			->where("t_groupuser.user_id = $id and t_groups.state = 2")->select();
			foreach ($data as $key=>$val){		
				$data[$key]['count'] = $table->where("group_id = '{$val['id']}'")->count();
				$data[$key]['di'] = powc($userinfo['latitude'],$userinfo['longitude'], $val['latitude'], $val['longitude']);
				if ($data[$key]['di'] >= 1000){
					$data[$key]['distance'] = ceil($data[$key]['di']/1000).'km';
				}else {
					$data[$key]['distance'] = $data[$key]['di'].'m';
				}
				$distance[] = $data[$key]['di'];
				$data[$key]['user'] = $table->field('t_user.id,t_user.username,t_user.simg,t_user.sex')
				->join('left join t_user on t_user.id = t_groupuser.user_id')
				->where("t_groupuser.group_id = '{$val['id']}'")->order('t_groupuser.poll desc,t_groupuser.level')->limit(6)->select();
			}
			array_multisort($distance,SORT_ASC,$data);
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有群组');
			}
		}
		json('404');
	}
	
	//通过群组id查看群组详情
	public function groupinfo(){
		if (I('post.id')){
			$id = I('post.id');
			$uid = I('post.uid');
			$table = M('groups');
			$groupuser = M('groupuser');
			$user = M('user');
			$userinfo = $user->find($uid);
			$data = $table->field('id,title,simg,condition,type,class as classes,cityid,description as descript,address,longitude,latitude,addtime')->find($id);
			if ($data){
				$data['di'] = powc($userinfo['latitude'],$userinfo['longitude'], $data['latitude'], $data['longitude']);
				if ($data['di'] >= 1000){
					$data['distance'] = ceil($data['di']/1000).'km';
				}else {
					$data['distance'] = $data['di'].'m';
				}
				if ($data['cityid']){
					$selfcity = M('selfcity');
					$data['address'] = $selfcity->where("id = '{$data['cityid']}'")->getField('title');
				}
				if ($data['type'] == 3){
					$class = M('class');
					$data['classes'] = $class->where("id = '{$data['classes']}'")->getField('title');
				}
				$users = $groupuser->where("user_id = $uid and group_id = $id")->find();
				if ($users){
					$data['level'] = $users['level'];
					$data['name'] = $users['username'];
				}else {
					$message = M('message');
					if ($message->where("uid = $uid and typeid = $id and (type = 'group' or type = 'user') and state = 1")->find()){
						$data['level'] = 2;
						$data['name'] = '';
					}else {
						$data['level'] = 1;
						$data['name'] = '';
					}
				}
				$data['count'] = $groupuser->where("group_id = $id")->count();
				$data['user'] = $groupuser->field('t_user.id,t_groupuser.username,t_user.simg,t_user.sex')
				->join('left join t_user on t_user.id = t_groupuser.user_id')
				->where("t_groupuser.group_id = $id")->order('t_groupuser.poll desc,t_groupuser.addtime asc')->limit(6)->select();
				$data['qunhua'] = $groupuser->field('t_user.id,t_groupuser.username,t_user.simg,t_user.sex')
				->join('left join t_user on t_user.id = t_groupuser.user_id')
				->where("t_groupuser.group_id = $id and t_user.sex = 2")->order('t_groupuser.poll desc,t_groupuser.addtime asc')->limit(6)->select();
				$data['quncao'] = $groupuser->field('t_user.id,t_groupuser.username,t_user.simg,t_user.sex')
				->join('left join t_user on t_user.id = t_groupuser.user_id')
				->where("t_groupuser.group_id = $id and t_user.sex = 1")->order('t_groupuser.poll desc,t_groupuser.addtime asc')->limit(6)->select();
				$dynamic = M('dynamic');
				$img = M('img');
				$data['img'] = $img->field('id,simg')->where("type = 'group' and pid = '{$id}'")->order('ord asc,addtime desc')->limit(6)->select();
				$activity =M('activity');
				$data['dynamic'] = $dynamic->field('id,uid,content')->where("type = 'group' and typeid = $id")->order('addtime desc')->find() ? $dynamic->field('id,uid,content')->where("type = 'group' and typeid = $id")->order('addtime desc')->find() : array();
				if ($data['dynamic']){
					$data['dynamic']['simg'] = $img->where("type = 'dynamic' and pid = '{$data['dynamic']['id']}'")->find() ? $img->where("type = 'dynamic' and pid = '{$data['dynamic']['id']}'")->find() : array();
				}
				$data['activity'] = $activity->field('id,simg,title')->where("groupid = $id")->order('addtime desc')->find() ? $activity->field('id,simg,title')->where("groupid = $id")->order('addtime desc')->find() : array();
				json('200','成功',$data);
			}else {
				json('400','操作失败');
			}
		}
		json('404');
	}
	
	//发布群活动
	public function addgroupact(){
		if (I('post.')){
			$where = I('post.');
			unset($where['ids']);
			if($_FILES['simg0']){
				$data1 = $_FILES['simg0'];
				$rand = '';
				for ($i=0;$i<6;$i++){
					$rand.=rand(0,9);
				}
				$type = explode('.', $data1['name']);
				$simg = date('YmdHis').$rand.'.'.end($type);
				if (move_uploaded_file($data1['tmp_name'], './Public/upfile/'.$simg)){
					$where['simg'] = '/Public/upfile/'.$simg;
				}else {
					json('400','图片上传失败');
				}
			}else{
				json('400','请上传图片');
			}
			$tab = M('activityuser');
			$ids = explode(',', I('post.ids'));
			$where['starttime'] = strtotime($where['starttime']);
			$where['addtime'] = $data['addtime'] = $where1['addtime'] = $data2['addtime'] = time();
			$table = M('activity');
			$ress = $table->where("groupid = '{$where['groupid']}'")->order('id desc')->find();
			if ($ress){
				$where['number'] = $ress['number'] + 1;
			}else {
				$where['number'] = 1;
			}
			$id = $table->add($where);
			if ($id){
				$where1['uid'] = I('post.uid');
				$where1['pid'] = $id;
				$tab->add($where1);
				$data1 = $_FILES;
				unset($data1['simg0']);
				if($data1){
					$data2['type'] = 'activity';
					$data2['pid'] = $id;
					$img = M('img');
					foreach ($data1 as $val){
						$rand = '';
						for ($i=0;$i<6;$i++){
							$rand.=rand(0,9);
						}
						$type = explode('.', $val['name']);
						$simg = date('YmdHis').$rand.'.'.end($type);
						if (move_uploaded_file($val['tmp_name'], './Public/upfile/'.$simg)){
							$data2['simg'] = '/Public/upfile/'.$simg;
							$img->add($data2);
						}else {
							json('400','图片上传失败');
						}
					}
				}
				
				if ($ids){
					$groups = M('groups');
					$message = M('message');
					$user = M('user');
					$groupinfo = $groups->find(I('post.groupid'));
					$data['title'] = $groupinfo['title'].' 发布了新活动';
					$data['content'] = $groupinfo['title'].' 发布了新活动';
					$data['type'] = 'activity';
					$data['typeid'] = $id;
					$data['state'] = 0;
					$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
					foreach ($ids as $val){
						$data['uid'] = $val;
						if ($message->add($data)){	
							$userdata = $user->field('jpushid')->find($val);
							if ($userdata['jpushid']){
								$jpushid[] = $userdata['jpushid'];
							}
						}
					}
					$array['type'] = 'message';
					$content = $data['title'];
					if ($jpushid){
						$jpush->push($jpushid, $this->title,$content,$array);
					}
				}
				json('200','成功');
			}else {
				json('400','发布失败');
			}
		}
		json('404');
	}
	
	//发布群动态
	public function addgroupdt(){
		if (I('post.')){
			$where = I('post.');
			$where['addtime'] = time();
			$where['type'] = 'group';			
			$table = M('dynamic');
			$data['pid'] = $table->add($where);
			if ($data['pid']){
				$data['addtime'] = time();
				$data['type'] = 'dynamic';
				if($_FILES){
					$img = M('img');
	    			$data1 = $_FILES;
	    			foreach ($data1 as $val){
		    			$rand = '';
		    			for ($i=0;$i<6;$i++){
		    				$rand.=rand(0,9);
		    			}
		    			$type = explode('.', $val['name']);
		    			$simg = date('YmdHis').$rand.'.'.end($type);
		    			if (move_uploaded_file($val['tmp_name'], './Public/upfile/'.$simg)){				
		    				$data['simg'] = '/Public/upfile/'.$simg;
		    				$img->add($data);
		    			}else {
		    				json('400','图片上传失败');
		    			} 			
	    			}
	    		}
	    		json('200','成功');
			}else {
				json('400','发布失败');
			}		
		}
		json('404');
	}
	
	//发布个人动态
	public function adduserdt(){
		if (I('post.')){
			$where = I('post.');
			unset($where['ids']);
			$where['addtime'] = $data['addtime'] = time();
			$where['type'] = 'user';
			$table = M('dynamic');
			$data['pid'] = $table->add($where);
			if ($data['pid']){				
				$data['addtime'] = time();
				$data['type'] = 'dynamic';
				if($_FILES){
					$img = M('img');
					$data1 = $_FILES;
					foreach ($data1 as $val){
						$rand = '';
						for ($i=0;$i<6;$i++){
							$rand.=rand(0,9);
						}
						$type = explode('.', $val['name']);
						$simg = date('YmdHis').$rand.'.'.end($type);
						if (move_uploaded_file($val['tmp_name'], './Public/upfile/'.$simg)){
							$data['simg'] = '/Public/upfile/'.$simg;
							$img->add($data);
						}
					}				
				}	
				if (I('post.ids')){
					$ids = explode(',', I('post.ids'));
					$message = M('message');
					$user = M('user');
					$userinfo = $user->find(I('post.uid'));
					$data1['title'] = $userinfo['username'].' 发布了新动态';
					$data1['content'] = $where['title'];
					$data1['type'] = 'dynamic';
					$data1['typeid'] = $data['pid'];
					$data1['state'] = 0;
					$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
					foreach ($ids as $val){
						$data1['uid'] = $val;
						if ($message->add($data1)){
							$userdata = $user->field('jpushid')->find($val);
							if ($userdata['jpushid']){
								$jpushid[] = $userdata['jpushid'];
							}
						}
					}
					$array['type'] = 'message';
					$content = $data1['title'];
					if ($jpushid){
						$jpush->push($jpushid, $this->title,$content,$array);
					}
				}		
				json('200','成功');
			}else {
				json('400','发布失败');
			}
		}
		json('404');
	}
	
	//系统消息
	public function message(){
		if (I('post.id')){
			$id = I('post.id');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1 )*15;
			$groupuser = M('groupuser');
			$table = M('message');
			$where['uid'] = I('post.id');
			$where['_string'] = '(type = "user" or type = "system" or type="activity")';
			$data = $table->where($where)->order('addtime desc')->limit($pages,15)->select();
			if ($data){
				$groups= M('groups');
				foreach ($data as $key=>$val){
					if ($val['type'] == 'user'){
						$data[$key]['simg'] = $groups->where("id = '{$val['typeid']}'")->getField('simg') ? $groups->where("id = '{$val['typeid']}'")->getField('simg') : '';
					}else {
						$data[$key]['simg'] = '';
					}
				}
				json('200','成功',$data);
			}else {
				json('400','没有消息');
			}
		}
		json('404');
	}

	//群组成员
	public function groupuser(){
		if (I('post.id')){
			$id = I('post.id');
			$uid = I('post.uid');
			$table = M('groupuser');
			$user = M('user');
			$fans = M('fans');
			$userinfo = $user->find($uid);
			$data['user5'] = $table->field('t_groupuser.user_id,t_groupuser.level,t_groupuser.username,t_user.simg,t_user.sex,t_user.logintime,t_user.sign,t_user.phone,t_user.latitude,t_user.longitude,t_user.state')
			->join('left join t_user on t_user.id = t_groupuser.user_id')
			->where("t_groupuser.group_id = $id and t_groupuser.level = 5")->select();
			foreach ($data['user5'] as $key => $val){
				$data['user5'][$key]['isfans'] = $fans->where("uid = '$uid' and fid = '{$val['user_id']}'")->find() ? 1 : 0;
				$data['user5'][$key]['di'] = powc($userinfo['latitude'],$userinfo['longitude'], $val['latitude'], $val['longitude']);
				if ($data['user5'][$key]['di'] >= 1000){
					$data['user5'][$key]['distance'] = ceil($data['user5'][$key]['di']/1000).'km';
				}else {
					$data['user5'][$key]['distance'] = $data['user5'][$key]['di'].'m';
				}
			}
			$data['user4'] = $table->field('t_groupuser.user_id,t_groupuser.level,t_groupuser.username,t_user.simg,t_user.sex,t_user.logintime,t_user.sign,t_user.phone,t_user.latitude,t_user.longitude,t_user.state')
			->join('left join t_user on t_user.id = t_groupuser.user_id')
			->where("t_groupuser.group_id = $id and t_groupuser.level = 4")->order('t_groupuser.poll desc')->select();
			foreach ($data['user4'] as $key => $val){
				$data['user4'][$key]['isfans'] = $fans->where("uid = '$uid' and fid = '{$val['user_id']}'")->find() ? 1 : 0;
				$data['user4'][$key]['di'] = powc($userinfo['latitude'],$userinfo['longitude'], $val['latitude'], $val['longitude']);
				if ($data['user4'][$key]['di'] >= 1000){
					$data['user4'][$key]['distance'] = ceil($data['user4'][$key]['di']/1000).'km';
				}else {
					$data['user4'][$key]['distance'] = $data['user4'][$key]['di'].'m';
				}
			}
			$data['user3'] = $table->field('t_groupuser.user_id,t_groupuser.level,t_groupuser.username,t_user.simg,t_user.sex,t_user.logintime,t_user.sign,t_user.phone,t_user.latitude,t_user.longitude,t_user.state')
			->join('left join t_user on t_user.id = t_groupuser.user_id')
			->where("t_groupuser.group_id = $id and t_groupuser.level = 3")->order('t_groupuser.poll desc')->select();
			foreach ($data['user3'] as $key => $val){
				$data['user3'][$key]['isfans'] = $fans->where("uid = '$uid' and fid = '{$val['user_id']}'")->find() ? 1 : 0;
				$data['user3'][$key]['di'] = powc($userinfo['latitude'],$userinfo['longitude'], $val['latitude'], $val['longitude']);
				if ($data['user3'][$key]['di'] >= 1000){
					$data['user3'][$key]['distance'] = ceil($data['user3'][$key]['di']/1000).'km';
				}else {
					$data['user3'][$key]['distance'] = $data['user3'][$key]['di'].'m';
				}
			}
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有成员'); 
			}
		}
		json('404');
	}
	
	//群花群草
	public function groupsexuser(){
		if (I('post.id')){
			$id = I('post.id');
			$uid = I('post.uid');
			$sex = I('post.sex');
			$table = M('groupuser');
			$user = M('user');
			$userinfo = $user->find($uid);
			$data['user'] = $table->field('t_groupuser.user_id,t_groupuser.level,t_groupuser.poll,t_groupuser.username,t_user.simg,t_user.sex,t_user.logintime,t_user.sign,t_user.phone,t_user.latitude,t_user.longitude,t_user.state')
			->join('left join t_user on t_user.id = t_groupuser.user_id')
			->where("t_groupuser.group_id = $id and t_user.sex = $sex")->order('t_groupuser.poll desc')->select();
			foreach ($data['user'] as $key => $val){
				$data['user'][$key]['di'] = powc($userinfo['latitude'],$userinfo['longitude'], $val['latitude'], $val['longitude']);
				if ($data['user'][$key]['di'] >= 1000){
					$data['user'][$key]['distance'] = ceil($data['user'][$key]['di']/1000).'km';
				}else {
					$data['user'][$key]['distance'] = $data['user'][$key]['di'].'m';
				}
			}
			$userdata = $table->where("user_id = $uid and group_id = $id")->find();
			if ($userdata){
				$data['vote'] = $userdata['vote'];
				$data['level'] = $userdata['level'];
			}else {
				$data['vote'] = 0;
				$data['level'] = 0;
			}
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有成员');
			}
		}
		json('404');
	}
	
	//群花群草投票
	public function addpoll(){
		if (I('post.')){
			$where = I('post.');
			if ($where['user_id'] == $where['uid']){
				json('400','不能投自己');
			}
			unset($where['uid']);
			$table = M('groupuser');
			$id = I('post.uid');
			$data = $table->where("user_id = $id and group_id = '{$where['group_id']}'")->find();
			if (!$data){
				json('400','您不能投票');
			}
			if ($data['vote'] > 0){
				if ($table->where($where)->setInc('poll')){
					$table->where("user_id = $id and group_id = '{$where['group_id']}'")->setDec('vote');
					json('200','成功');
				}else {
					json('投票失败');
				}
			}else {
				json('400','您的票已全部投完');
			}
		}
		json('404');
	}
	
	//群动态
	public function groupdtlist(){
		if (I('post.')){
			$table =M('dynamic');
			$id = I('post.id');
			$uid = I('post.uid');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			$data = $table->field('t_dynamic.id,t_dynamic.address,t_dynamic.content,t_dynamic.upper,t_dynamic.addtime,t_dynamic.state,t_dynamic.uid,t_user.simg,t_user.sex,t_groupuser.username')
			->join('left join t_user on t_user.id = t_dynamic.uid')
			->join("left join t_groupuser on t_groupuser.user_id = t_user.id")
			->where("t_dynamic.type = 'group' and t_dynamic.typeid = $id  and t_groupuser.group_id = $id")->order('t_dynamic.state desc,t_dynamic.addtime desc')->limit($pages,15)->select();	
			if ($data){
				$comment = M('comment');
				$upper = M('upper');	
				$img = M('img');
				foreach ($data as $key => $val){
					$data[$key]['count'] = $comment->where("type = 'dynamic' and typeid = '{$val['id']}'")->count();				
					$data[$key]['isupper'] = $upper->where("type = 'dynamic' and pid = '{$val['id']}' and uid = '$uid'")->find() ? 1 : 0;
					$data[$key]['img'] = $img->field('simg')->where("type = 'dynamic' and pid = '{$val['id']}'")->select();
				}
				json('200','成功',$data);
			}else {
				json('400','没有群动态');
			}
		}
		json('404');
	}
	
	//动态详情
	public function dtinfo(){
		if (I('post.')){
			$table =M('dynamic');
			$id = I('post.id');
			$uid = I('post.uid');
			$data = $table->field('t_dynamic.id,t_dynamic.address,t_dynamic.content,t_dynamic.typeid,t_dynamic.upper,t_dynamic.addtime,t_dynamic.state,t_dynamic.type,t_dynamic.uid,t_user.simg,t_user.sex,t_groupuser.username')
			->join('left join t_user on t_user.id = t_dynamic.uid')
			->join('left join t_groupuser on t_groupuser.user_id = t_user.id')
			->where("t_dynamic.id = $id")->find();
			if ($data){
				$comment = M('comment');
				$upper = M('upper');
				$img = M('img');
				if ($data['type'] == 'group'){
					$groupuser = M('groupuser');
					$res = $groupuser->where("user_id = $uid and group_id = '{$data['typeid']}'")->find();
					if ($res){
						if ($res['level'] == 5 || $data['uid'] ==$uid ){
							$data['isdel'] = 1;
						}else {
							$data['isdel'] = 0;
						}
					}else {
						$data['isdel'] = 0;
					}
				}elseif ($data['type'] == 'user'){
					if ($data['uid'] ==$uid ){
						$data['isdel'] = 1;
					}else {
						$data['isdel'] = 0;
					}
				}				
				$data['count'] = $comment->where("type = 'dynamic' and typeid = '{$data['id']}'")->count();
				$data['isupper'] = $upper->where("type = 'dynamic' and pid = '{$data['id']}' and uid = $uid")->find() ? 1 : 0;
				$data['img'] = $img->field('simg')->where("type = 'dynamic' and pid = '{$data['id']}'")->select();
				json('200','成功',$data);
			}else {
				json('400','参数错误');
			}
		}
		json('404');
	}
	
	//删除群动态
	public function deldt(){
		if (I('post.id')){
			$table = M('dynamic');
			if ($table->delete(I('post.id'))){
				json('200','成功');
			}else {
				json('400','操作失败');
			}
		}
		json('404');
	}
	
	//删除消息
	public function delmessage(){
		if (I('post.id')){
			$table = M('message');
			if ($table->delete(I('post.id'))){
				json('200','成功');
			}else {
				json('400','操作失败');
			}
		}
		json('404');
	}
	
	//个人动态
	public function userdtlist(){
		if (I('post.')){
			$table =M('dynamic');
			$id = I('post.id');
			$uid = I('post.uid');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			$friend = M('friend');
			if ($uid == $id){
				$data = $table->field('t_dynamic.id,t_dynamic.address,t_dynamic.content,t_dynamic.upper,t_dynamic.addtime,t_dynamic.state,t_dynamic.status,t_dynamic.uid,t_user.simg,t_user.sex,t_user.username')
				->join('left join t_user on t_user.id = t_dynamic.uid')
				->where("t_dynamic.type = 'user' and t_dynamic.uid = $id")->order('t_dynamic.state desc,t_dynamic.addtime desc')->limit($pages,15)->select();
			}elseif ($friend->where("uid = $uid and fid = $id")->find()){
				$data = $table->field('t_dynamic.id,t_dynamic.address,t_dynamic.content,t_dynamic.upper,t_dynamic.addtime,t_dynamic.state,t_dynamic.status,t_dynamic.uid,t_user.simg,t_user.sex,t_user.username')
				->join('left join t_user on t_user.id = t_dynamic.uid')
				->where("t_dynamic.type = 'user' and t_dynamic.uid = '$id' and (t_dynamic.status = '1' or t_dynamic.status = '2')")->order('t_dynamic.state desc,t_dynamic.addtime desc')->limit($pages,15)->select();
			}else {
				$data = $table->field('t_dynamic.id,t_dynamic.address,t_dynamic.content,t_dynamic.upper,t_dynamic.addtime,t_dynamic.state,t_dynamic.status,t_dynamic.uid,t_user.simg,t_user.sex,t_user.username')
				->join('left join t_user on t_user.id = t_dynamic.uid')
				->where("t_dynamic.type = 'user' and t_dynamic.uid = '$id' and t_dynamic.status = '1'")->order('t_dynamic.state desc,t_dynamic.addtime desc')->limit($pages,15)->select();
			}
			if ($data){
				$comment = M('comment');
				$upper = M('upper');
				$img = M('img');
				$friendinfo = $friend->where("fid = $id and uid = $uid")->find();
				foreach ($data as $key => $val){
					if($friendinfo['username']){
						$data[$key]['username'] = $friendinfo['username'];
					}
					$data[$key]['count'] = $comment->where("type = 'dynamic' and typeid = '{$val['id']}'")->count();
					$data[$key]['isupper'] = $upper->where("type = 'dynamic' and pid = '{$val['id']}' and uid = '$uid'")->find() ? 1 : 0;
					$data[$key]['img'] = $img->field('simg')->where("type = 'dynamic' and pid = '{$val['id']}'")->select();
				}
				json('200','成功',$data);
			}else {
				json('400','没有群动态');
			}
		}
		json('404');
	}
	
	//好友列表
	public function friendlist(){
		if (I('post.')){
			$id = I('post.id');
			$user = M('user');
			$userinfo = $user->find($id);
			$table = M('friend');
			$tab = M('changephone');
			$data = $table->field('t_user.id,t_user.username,t_friend.username as username1,t_user.simg,t_user.sex,t_user.logintime,t_user.phone,t_user.latitude,t_user.longitude,t_user.state')
			->join('left join t_user on t_user.id = t_friend.fid')
			->where("t_friend.uid = $id")->order('t_user.username asc')->select();
			foreach ($data as $key => $val){
				$res1 = $tab->where("uid = '$id' and userid = '{$val['id']}' and state = '2'")->find();
				$res2 = $tab->where("userid = '$id' and uid = '{$val['id']}' and state = '2'")->find();
				if (!($res1 || $res2)){
					$data[$key]['phone'] = '';
				}
				if ($val['username1']){
					$data[$key]['username'] = $val['username1'];
				}
				$data[$key]['di'] = powc($userinfo['latitude'],$userinfo['longitude'], $val['latitude'], $val['longitude']);
				if ($data[$key]['di'] >= 1000){
					$data[$key]['distance'] = ceil($data[$key]['di']/1000).'km';
				}else {
					$data[$key]['distance'] = $data[$key]['di'].'m';
				}
			}
			json('200','成功',$data);
		}
		json('404');
	}
	
	//我的关注
	public function fansto(){
		if (I('post.')){
			$id = I('post.id');
			$user = M('user');
			$userinfo = $user->find($id);
			$table = M('fans');
			$friend = M('friend');
			$tab = M('changephone');
			$data = $table->field('t_user.id,t_user.username,t_user.simg,t_user.sex,t_user.logintime,t_user.phone,t_user.latitude,t_user.longitude,t_user.state')
			->join('left join t_user on t_user.id = t_fans.fid')
			->where("t_fans.uid = $id")->order('t_user.username asc')->select();
			foreach ($data as $key => $val){
				$res1 = $tab->where("uid = '$id' and userid = '{$val['id']}' and state = '2'")->find();
				$res2 = $tab->where("userid = '$id' and uid = '{$val['id']}' and state = '2'")->find();
				if (!($res1 || $res2)){
					$data[$key]['phone'] = '';
				}
				$friendinfo = $friend->where("fid = '{$val['id']}' and uid = $id")->find();
				if ($friendinfo['username']){
					$data[$key]['username'] = $friendinfo['username'];
				}
				$data[$key]['di'] = powc($userinfo['latitude'],$userinfo['longitude'], $val['latitude'], $val['longitude']);
				if ($data[$key]['di'] >= 1000){
					$data[$key]['distance'] = ceil($data[$key]['di']/1000).'km';
				}else {
					$data[$key]['distance'] = $data[$key]['di'].'m';
				}
			}
			json('200','成功',$data);
		}
		json('404');
	}
	
	//我的粉丝
	public function myfans(){
		if (I('post.')){
			$id = I('post.id');
			$user = M('user');
			$userinfo = $user->find($id);
			$table = M('fans');
			$friend = M('friend');
			$tab = M('changephone');
			$data = $table->field('t_user.id,t_user.username,t_user.simg,t_user.sex,t_user.logintime,t_user.phone,t_user.latitude,t_user.longitude,t_user.state')
			->join('left join t_user on t_user.id = t_fans.uid')
			->where("t_fans.fid = $id")->order('t_user.username asc')->select();
			foreach ($data as $key => $val){
				$res1 = $tab->where("uid = '$id' and userid = '{$val['id']}' and state = '2'")->find();
				$res2 = $tab->where("userid = '$id' and uid = '{$val['id']}' and state = '2'")->find();
				if (!($res1 || $res2)){
					$data[$key]['phone'] = '';
				}
				$friendinfo = $friend->where("fid = '{$val['id']}' and uid = $id")->find();
				if ($friendinfo['username']){
					$data[$key]['username'] = $friendinfo['username'];
				}
				$data[$key]['isfans'] = $table->where("uid = $id and fid = '{$val['id']}'")->find() ? 1 : 0;
				$data[$key]['di'] = powc($userinfo['latitude'],$userinfo['longitude'], $val['latitude'], $val['longitude']);
				if ($data[$key]['di'] >= 1000){
					$data[$key]['distance'] = ceil($data[$key]['di']/1000).'km';
				}else {
					$data[$key]['distance'] = $data[$key]['di'].'m';
				}
			}
			json('200','成功',$data);
		}
		json('404');
	}
	
	//邀请好友列表
	public function yqfriendlist(){
		if (I('post.')){
			$id = I('post.id');
			$groupid = I('groupid');
			$user = M('user');
			$userinfo = $user->find($id);
			$table = M('friend');
			$tab = M('changephone');
			$groupuser = M('groupuser');
			$groupuserinfo = $groupuser->where("group_id = $groupid")->getField('user_id',true);
			$message = M('message');
			$mess = $message->where("typeid = '$groupid' and (type = 'group' or type = 'user') and state = 1")->getField('uid',true);
			$data = $table->field('t_user.id,t_user.username,t_friend.username as username1,t_user.simg,t_user.sex,t_user.logintime,t_user.phone,t_user.latitude,t_user.longitude,t_user.state')
			->join('left join t_user on t_user.id = t_friend.fid')
			->where("t_friend.uid = $id")->order('t_user.username asc')->select();
			foreach ($data as $key => $val){
				if ($mess){
					if (in_array($val['id'], $mess)){
						continue;
					}
				}
				if ($groupuserinfo){
					if (in_array($val['id'], $groupuserinfo)){
						continue;
					}
				}
				$res1 = $tab->where("uid = '$id' and userid = '{$val['id']}' and state = '2'")->find();
				$res2 = $tab->where("userid = '$id' and uid = '{$val['id']}' and state = '2'")->find();
				if (!($res1 || $res2)){
					$data[$key]['phone'] = '';
				}
				if ($val['username1']){
					$data[$key]['username'] = $val['username1'];
				}
				$data[$key]['di'] = powc($userinfo['latitude'],$userinfo['longitude'], $val['latitude'], $val['longitude']);
				if ($data[$key]['di'] >= 1000){
					$data[$key]['distance'] = ceil($data[$key]['di']/1000).'km';
				}else {
					$data[$key]['distance'] = $data[$key]['di'].'m';
				}
				$res[] = $data[$key];
			}
			json('200','成功',$res);
		}
		json('404');
	}
	
	//爱好群分类
	public function groupclass(){
		$city = M('selfcity');
		$data = $city->order('isred desc,prefix asc')->select();
		$table = M('class');
		$tab = M('groups');
		foreach ($data as $k => $v){
			$data[$k]['class'] = $table->field('id,title,simg')->order('ord asc')->select();			
			foreach ($data[$k]['class'] as $key => $val){
				$data[$k]['class'][$key]['count'] = $tab->where("cityid = '{$v['id']}' and class = '{$val['id']}'")->count();
			}
		}			
		json('200','成功',$data);
	}
	
	//修改群昵称
	public function editgroupname(){
		if (I('post.')){
			$table = M('groupuser');
			$where = I('post.');
			unset($where['username']);
			if ($table->where($where)->setField('username',I('post.username'))){
				json('200','修改成功');
			}else {
				json('400','修改失败');
			}
		}
		json('404');
	}
	
	//修改群资料
	public function editgroups(){
		if (I('post.')){
			$table = M('groups');
			$where = I('post.');
			if($_FILES){
				$data1 = $_FILES['simg'];
				$rand = '';
				for ($i=0;$i<6;$i++){
					$rand.=rand(0,9);
				}
				$type = explode('.', $data1['name']);
				$simg = date('YmdHis').$rand.'.'.end($type);
				if (move_uploaded_file($data1['tmp_name'], './Public/upfile/'.$simg)){
					$where['simg'] = '/Public/upfile/'.$simg;
				}else {
					json('400','图片上传失败');
				}
			}
			if ($table->save($where)){
				json('200','修改成功');
			}else {
				json('400','修改失败');
			}
		}
		json('404');
	}
	
	//举报
	public function feedback(){
		if (I('post.')){
			$table = M('feedback');
			$where = I('post.');
			$where['addtime'] = time();
			if ($table->add($where)){
				json('200','举报成功');
			}else {
				json('400','举报失败');
			}
		}
		json('404');
	}
	
	//爱好群列表
	public function huodonggroup(){
		if (I('post.id')){
			$id = I('post.id');
			$table = M('groups');
			$user = M('user');
			$userinfo = $user->find($id);
			$where['state'] = 2;
			$where['type'] =3;
			$where['cityid'] = I('post.cityid');
			$where['class'] = I('post.classid');
			$data = $table->field('id,title,addtime,condition,description as descript,type,simg,uid,address,longitude,latitude')->where($where)->select();
			$page = I('post.page') ? I('post.page') : 1;
			$tab = M('groupuser');
			$tab1 = M('message');
			foreach ($data as $key=>$val){
				if ($tab->where("group_id = '{$val['id']}' and user_id = '{$id}'")->find()){
					$data[$key]['state'] = 2;
				}elseif ($tab1->where("type = 'group' and typeid =  '{$val['id']}' and uid = '{$id}' and state =1")->find()){
					$data[$key]['state'] = 1;
				}else {
					$data[$key]['state'] = 0;
				}
				$data[$key]['count'] = $tab->where("group_id = '{$val['id']}'")->count();
				$distance[] = $data[$key]['count'];
				$data[$key]['user'] = $tab->field('t_user.id,t_groupuser.username,t_user.simg,t_user.sex')
				->join('left join t_user on t_user.id = t_groupuser.user_id')
				->where("t_groupuser.group_id = '{$val['id']}'")->order('t_groupuser.poll desc,t_groupuser.level')->limit(6)->select();
			}
			array_multisort($distance,SORT_DESC,$data);
			$data = array_page($data,$page);
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有群组');
			}
		}
		json('404');
	}
	
	//群活动列表
	public function groupactivitylist(){
		if (I('post.')){
			$table =M('activity');
			$id = I('post.id');
			$uid = I('post.uid');
			$user = M('user');
			$selfcity = M('selfcity');
			$class = M('class');
			$userinfo = $user->find($uid);
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			$img = M('img');
			$data = $table->field('t_activity.id,t_activity.title,t_activity.state,t_activity.description as descript,t_activity.shopname,t_activity.simg,t_activity.address,t_activity.longitude,t_activity.latitude,t_activity.starttime,t_activity.type,t_activity.price,t_activity.addtime,t_activity.groupid,t_groups.title as name,t_groups.type as type1,t_groups.class as classes,t_groups.cityid')
			->join('left join t_groups on t_activity.groupid = t_groups.id')
			->where("t_activity.groupid = $id")->order('t_activity.addtime desc')->limit($pages,15)->select();			
			if ($data){
				$tab = M('groupuser');
				$tab1 = M('message');
				$activityuser = M('activityuser');
				foreach ($data as $key => $val){
					if ($tab->where("group_id = $id and user_id = '{$uid}'")->find()){
						$data[$key]['status'] = 2;
					}elseif ($tab1->where("type = 'group' and typeid =  $id and uid = '{$uid}' and state =1")->find()){
						$data[$key]['status'] = 1;
					}else {
						$data[$key]['status'] = 0;
					}
					if ($val['cityid']){
					
						$data[$key]['address'] = $selfcity->where("id = '{$val['cityid']}'")->getField('title');
					}
					if ($val['type1'] == 3){
					
						$data[$key]['classes'] = $class->where("id = '{$val['classes']}'")->getField('title');
					}
					$data[$key]['user'] = $activityuser->field('t_user.id,t_groupuser.username,t_user.simg,t_user.sex')
					->join('left join t_user on t_user.id = t_activityuser.uid')
					->join("left join t_groupuser on t_user.id = t_groupuser.user_id and t_groupuser.group_id = $id")
					->where("t_activityuser.pid = '{$val['id']}'")->order('t_activityuser.id asc')->limit(6)->select();
					$data[$key]['isactivityuser'] = $activityuser->where("pid = '{$val['id']}' and uid = '{$uid}'")->find() ? 1 : 0;
					$data[$key]['di'] = powc($userinfo['latitude'],$userinfo['longitude'], $val['latitude'], $val['longitude']);			
					if ($data[$key]['di'] >= 1000){
						$data[$key]['distance'] = ceil($data[$key]['di']/1000).'km';
					}else {
						$data[$key]['distance'] = $data[$key]['di'].'m';
					}
					if ($val['state'] == 2){
						$data[$key]['img'] = $img->field('id,simg')->where("type = 'activitys' and pid = '{$val['id']}'")->select() ? $img->where("type = 'activitys' and pid = '{$val['id']}'")->select() : array();
					}else {
						$data[$key]['img'] = array();
					}
				}
				json('200','成功',$data);
			}else {
				json('400','没有群动态');
			}
		}
		json('404');
	}
	
	//普通群活动报名取消
	public function addactivityuser(){
		if (I('post.')){		
			$table = M('activityuser');
			$where = I('post.');
			if (!$where['uid']){
				json('400','登陆后才能执行此操作！');
			}
			$activity = M('activity');
			$user = M('user');
			$groupuser = M('groupuser');
			$activityinfo = $activity->find(I('post.pid'));
			$userinfo = $groupuser->where("group_id = '{$activityinfo['groupid']}' and user_id='{$where['uid']}'")->find();
			if ($table->where($where)->find()){
				if ($table->where($where)->delete()){
					$tab = M('message');
					$where1['addtime'] = time();
					$where1['type'] = 'group';
					$where1['typeid'] = $activityinfo['groupid'];
					$where1['title'] = $userinfo['username'].' 退出了群组活动 '.$activityinfo['title'];
					$where1['content'] = $userinfo['username'].' 退出了群组活动 '.$activityinfo['title'];
					$where1['state'] = 0;
					$where1['uid'] = I('post.uid');
					$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
					$tab->add($where1);
					
					$userdata = $groupuser->field('t_user.jpushid')
					->join('left join t_user on t_user.id = t_groupuser.user_id')
					->where("t_groupuser.group_id = '{$where1['typeid']}' and t_groupuser.level > 3")->select();
					foreach ($userdata as $val){
						if ($val['jpushid']){
							$jpushid[] = $val['jpushid'];
						}
					}
					$array['type'] = 'groupmessage';
					$content = $where1['title'];
					if ($jpushid){
						$jpush->push($jpushid, $this->title,$content,$array);
					}
					json('200','取消报名成功');
				}else {
					json('400','操作失败');
				}
			}else {
				$where['addtime']= time();
				if ($table->add($where)){
					$tab = M('message');
					$where1['addtime'] = time();
					$where1['type'] = 'group';
					$where1['typeid'] = $activityinfo['groupid'];
					$where1['title'] = $userinfo['username'].' 报名了群组活动 '.$activityinfo['title'];
					$where1['content'] = $userinfo['username'].' 报名了群组活动 '.$activityinfo['title'];
					$where1['state'] = 0;
					$where1['uid'] = I('post.uid');
					$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
					$tab->add($where1);
					$groupuser = M('groupuser');
					$userdata = $groupuser->field('t_user.jpushid')
					->join('left join t_user on t_user.id = t_groupuser.user_id')
					->where("t_groupuser.group_id = '{$where1['typeid']}' and t_groupuser.level > 3")->select();
					foreach ($userdata as $val){
						if ($val['jpushid']){
							$jpushid[] = $val['jpushid'];
						}
					}
					$array['type'] = 'groupmessage';
					$content = $where1['title'];
					if ($jpushid){
						$jpush->push($jpushid, $this->title,$content,$array);
					}
					json('200','报名成功');
				}else {
					json('400','操作失败');
				}
			}					
		}
		json('404');
	}
	
	//修改群活动
	public function editgroupact(){
		if (I('post.')){
			$where = I('post.');
			$id = $where['id'];			
			$where['addtime'] = $data['addtime'] = $data2['addtime'] = time();
			$table = M('activity');
			$tab = M('activityuser');
			$ids = $tab->where("pid = $id")->getField('uid',true);
			if ( $table->save($where)){		
				if ($ids){
					$res = $table->find(I('post.id'));
					$message = M('message');
					$user = M('user');
					$data['title'] = '您报名的活动 '.$res['title'].' 修改了信息';
					$data['content'] = '您报名的活动 '.$res['title'].' 修改了信息';
					$data['type'] = 'activity';
					$data['typeid'] = $id;
					$data['state'] = 0;
					$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
					foreach ($ids as $val){
						$data['uid'] = $val;
						$userdata = $user->field('jpushid')->find($val);						
						if ($message->add($data)){
							$userdata = $user->field('jpushid')->find($val);
							if ($userdata['jpushid']){
								$jpushid[] = $userdata['jpushid'];
							}
						}
					}
					$array['type'] = 'message';
					$content = $data['title'];
					if ($jpushid){
						$jpush->push($jpushid, $this->title,$content,$array);
					}
				}
				json('200','成功');
			}else {
				json('400','修改失败');
			}
		}
		json('404');
	}
	
	//获取群相册
	public function groupimglist(){
		if (I('post.')){
			$table = M('img');
			$id = I('post.id');
			$data = $table->field('id,simg')->where("type = 'group' and pid = '{$id}'")->select();
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','没有群相册');
			}
		}
		json('404');
	}
	
	//删除群相册
	public function delgroupimg(){
		if (I('post.')){
			$table = M('img');
			$id = I('post.id');
			if ($table->delete($id)){
				json('200','成功');
			}else {
				json('400','删除失败');
			}
		}
		json('404');
	}
	
	//新增群相册
	public function addgroupimg(){
		if (I('post.')){
			$table = M('img');
			$where = I('post.');
			if($_FILES){
				$data1 = $_FILES['simg'];
				$rand = '';
				for ($i=0;$i<6;$i++){
					$rand.=rand(0,9);
				}
				$type = explode('.', $data1['name']);
				$simg = date('YmdHis').$rand.'.'.end($type);
				if (move_uploaded_file($data1['tmp_name'], './Public/upfile/'.$simg)){
					$where['simg'] = '/Public/upfile/'.$simg;
				}else {
					json('400','图片上传失败');
				}
			}else{
				json('400','请上传图片');
			}
			$where['addtime'] = time();
			$where['type'] = 'group';
			$res['id'] = $table->add($where);
			if ($res['id']){
				$res['simg'] = $where['simg'];
				json('200','成功',$res);
			}else {
				json('400','添加失败');
			}
		}
		json('404');
	}
	
	//获取个人群昵称头像
	public function groupusername(){
		if (I('post.')){
			$where['t_groupuser.group_id'] = I('group_id');
			$where['t_groupuser.user_id'] = I('post.user_id');
			$table = M('groupuser');
			$data = $table->field('t_user.id,t_groupuser.username,t_user.simg')
			->join('left join t_user on t_user.id = t_groupuser.user_id')
			->where($where)->find();
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','获取失败');
			}
		}
		json('404');
	}
	
	//获取群名称图标
	public function groupsname(){
		if (I('post.')){
			$table = M('groups');
			$data = $table->field('id,title,simg')->find(I('post.id'));
			if ($data){
				json('200','成功',$data);
			}else {
				json('400','获取失败');
			}
		}
		json('404');
	}
	
	//群活动详情
	public function groupactivityinfo(){
		if (I('post.')){
			$table =M('activity');
			$id = I('post.id');
			$uid = I('post.uid');
			$user = M('user');
			$img = M('img');
			$groups = M('groups');
			$userinfo = $user->find($uid);
			$data = $table->field('t_activity.id,t_activity.title,t_activity.number,t_activity.description as descript,t_activity.shopname,t_activity.simg,t_activity.address,t_activity.longitude,t_activity.latitude,t_activity.starttime,t_activity.type,t_activity.price,t_activity.addtime,t_activity.groupid,t_groups.title as name,t_groups.type as type1,t_groups.cityid,t_groups.class as classes')
			->join('left join t_groups on t_activity.groupid = t_groups.id')
			->where("t_activity.id = $id")->find();			
			if ($data){
				$tab = M('groupuser');
				$tab1 = M('message');
				$activityuser = M('activityuser');
				if ($tab->where("group_id = '{$data['groupid']}' and user_id = '{$uid}'")->find()){
					$data['status'] = 2;
				}elseif ($tab1->where("type = 'group' and typeid =  '{$data['groupid']}' and uid = '{$uid}' and state =1")->find()){
					$data['status'] = 1;
				}else {
					$data['status'] = 0;
				}
				if ($data['cityid']){
					$selfcity = M('selfcity');
					$data['address'] = $selfcity->where("id = '{$data['cityid']}'")->getField('title');
				}
				if ($data['type1'] == 3){
					$class = M('class');
					$data['classes'] = $class->where("id = '{$data['classes']}'")->getField('title');
				}
				$data['manage'] = $groups->field('t_user.id,t_user.simg,t_user.sex,t_user.phone,t_groupuser.username')
				->join('left join t_user on t_user.id = t_groups.uid')
				->join('left join t_groupuser on t_groupuser.user_id = t_user.id')
				->where("t_groups.id = '{$data['groupid']}' and t_groupuser.group_id = '{$data['groupid']}'")->find();
				$data['img'] = $img->field('id,simg')->where("type = 'activity' and pid = '{$id}'")->select();
				$data['count'] = $activityuser->where("t_activityuser.pid = '{$id}'")->count();
				$data['user'] = $activityuser->field('t_user.id,t_groupuser.username,t_user.simg,t_user.sex')
				->join('left join t_user on t_user.id = t_activityuser.uid')
				->join("left join t_groupuser on t_user.id = t_groupuser.user_id and t_groupuser.group_id = '{$data['groupid']}'")
				->where("t_activityuser.pid = '{$id}'")->order('t_activityuser.id asc')->select();
				$data['isactivityuser'] = $activityuser->where("pid = '{$id}' and uid = '{$uid}'")->find() ? 1 : 0;
				$data['di'] = powc($userinfo['latitude'],$userinfo['longitude'], $data['latitude'], $data['longitude']);			
				if ($data['di'] >= 1000){
					$data['distance'] = ceil($data['di']/1000).'km';
				}else {
					$data['distance'] = $data['di'].'m';
				}
				json('200','成功',$data);
			}else {
				json('400','没有群动态');
			}
		}
		json('404');
	}
	
	//群活动账单页
	public function groupactivityorder(){
		if (I('post.')){
			$table =M('activity');
			$id = I('post.id');
			$user = M('user');
			$groups = M('groups');
			$data = $table->field('t_activity.id,t_activity.title,t_activity.number,t_activity.state,t_activity.money,t_activity.description as descript,t_activity.shopname,t_activity.simg,t_activity.address,t_activity.longitude,t_activity.latitude,t_activity.starttime,t_activity.type,t_activity.price,t_activity.addtime,t_activity.groupid,t_groups.title as name,t_groups.type as type1')
			->join('left join t_groups on t_activity.groupid = t_groups.id')
			->where("t_activity.id = $id")->find();		
			$activityuser = M('activityuser');
			$data['total'] = $activityuser->where("pid = '{$id}'")->count();
			$data['total1'] = $activityuser->where("pid = '{$id}' and type = 1")->count();	
			$data['total2'] = $activityuser->where("pid = '{$id}' and type = 2")->count();			
// 			if ($data['type1'] == 3 and $data['state'] == 2){
// 				$activityuser = M('activityuser');
// 				$t1 = $activityuser->join('left join t_user on t_user.id = t_activityuser.uid')->where("t_activityuser.pid = '{$id}' and t_user.sex = 1")->count();
// 				$t2 = $activityuser->join('left join t_user on t_user.id = t_activityuser.uid')->where("t_activityuser.pid = '{$id}' and t_user.sex = 2")->count();
// 				$t3 = $activityuser->join('left join t_user on t_user.id = t_activityuser.uid')->where("t_activityuser.pid = '{$id}' and t_user.sex = 1 and t_activityuser.state = 2")->count();
// 				$t4 = $activityuser->join('left join t_user on t_user.id = t_activityuser.uid')->where("t_activityuser.pid = '{$id}' and t_user.sex = 2 and t_activityuser.state = 2")->count();
// 				if ($data['type'] == 1){
// 					$data['num']['total1'] = ($t1+$t2)*$data['price'];
// 					$data['num']['total2'] = ($t3 + $t4)*$data['price'];
// 					$data['num']['total3'] = $data['money'];
// 					$data['num']['total4'] = $data['num']['total2'] - $data['money'];
// 				}elseif ($data['type'] == 2){
					
// 				}elseif ($data['type'] == 3){
					
// 				}
// 			}else {
// 				$data['num'] = 0;
// 			}
			if ($data['state'] == 2){
				$img = M('img');
				$data['img'] = $img->field('id,simg')->where("type = 'activitys' and pid = '{$id}'")->select() ? $img->field('id,simg')->where("type = 'activitys' and pid = '{$id}'")->select() : array();
			}else {
				$data['img'] = array();
			}
			json('200','成功',$data);
		}
		json('404');
	}
	
	//普通群活动完成
	public function ptactivitystate(){
		if (I('post.')){
			$table = M('activity');
			$id = I('post.id');
			$res = $table->find(I('post.id'));
			if ($table->where("id = '{$res['id']}'")->setField('state',2)){
				$data2['addtime'] = time();
				$data2['type'] = 'activitys';
				$data2['pid'] = $id;
				if($_FILES){
					$img = M('img');
					$data1 = $_FILES;
					foreach ($data1 as $val){
						$rand = '';
						for ($i=0;$i<6;$i++){
							$rand.=rand(0,9);
						}
						$type = explode('.', $val['name']);
						$simg = date('YmdHis').$rand.'.'.end($type);
						if (move_uploaded_file($val['tmp_name'], './Public/upfile/'.$simg)){
							$data2['simg'] = '/Public/upfile/'.$simg;
							$img->add($data2);
						}else {
							json('400','图片上传失败');
						}
					}
				}
				$message = M('message');
				$user = M('user');
				$data['title'] = '您参加的活动 '.$res['title'].' 已经结束了';
				$data['content'] = '您参加的活动 '.$res['title'].' 已经结束了';
				$data['type'] = 'activity';
				$data['typeid'] = $id;
				$data['state'] = 0;
				$data['addtime'] = time();
				$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
				$activityuser = M('activityuser');
				$ids = $activityuser->where("pid = $id")->getField('uid',true);
				if ($ids){
					foreach ($ids as $val){
						$data['uid'] = $val;
						$userdata = $user->field('jpushid')->find($val);
						if ($message->add($data)){
							$userdata = $user->field('jpushid')->find($val);
							if ($userdata['jpushid']){
								$jpushid[] = $userdata['jpushid'];
							}
						}
					}
					$array['type'] = 'message';
					$content = $data['title'];
					if ($jpushid){
						$jpush->push($jpushid, $this->title,$content,$array);
					}
				}
				json('200','成功');
			}else {
				json('400','操作失败');
			}
		}
		json('404');
	}
	
	//查看活动人员
	public function activityuser(){
		if (I('post.')){
			$id = I('post.id');
			$uid = I('post.uid');
			$user = M('user');
			$userinfo = $user->find($uid);
			$table = M('activityuser');
			$where['t_activityuser.id'] = $id;
			if(I('post.type')){
				$where['t_activityuser.type'] = I('post.type');
			}
			$data = $table->field('t_user.id,t_groupuser.username,t_user.simg,t_user.sex,t_user.logintime,t_user.latitude,t_user.longitude,t_user.state')
			->join('left join t_user on t_user.id = t_activityuser.uid')
			->join("left join t_groupuser on t_groupuser.user_id = t_user.id and t_groupuser.group_id = $id")
			->where($where)->order('t_user.username asc')->select();
			foreach ($data as $key => $val){
				$data[$key]['di'] = powc($userinfo['latitude'],$userinfo['longitude'], $val['latitude'], $val['longitude']);
				if ($data[$key]['di'] >= 1000){
					$data[$key]['distance'] = ceil($data[$key]['di']/1000).'km';
				}else {
					$data[$key]['distance'] = $data[$key]['di'].'m';
				}
			}
			json('200','成功',$data);
		}
		json('404');
	}
	
	//普通群取消活动
	public function ptactivitydel(){
		if (I('post.')){
			$table = M('activity');
			$id = I('post.id');
			$res = $table->find(I('post.id'));
			if ($table->where("id = '{$res['id']}'")->setField('state',3)){
				$message = M('message');
				$user = M('user');
				$data['title'] = '您参加的活动 '.$res['title'].' 已经取消了';
				$data['content'] = '您参加的活动 '.$res['title'].' 已经取消了';
				$data['type'] = 'activity';
				$data['typeid'] = $id;
				$data['state'] = 0;
				$data['addtime'] = time();
				$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
				$activityuser = M('activityuser');
				$ids = $activityuser->where("pid = $id")->getField('uid',true);
				if ($ids){
					foreach ($ids as $val){
						$data['uid'] = $val;
						$userdata = $user->field('jpushid')->find($val);
						if ($message->add($data)){
							$userdata = $user->field('jpushid')->find($val);
							if ($userdata['jpushid']){
								$jpushid[] = $userdata['jpushid'];
							}
						}
					}
					$array['type'] = 'message';
					$content = $data['title'];
					if ($jpushid){
						$jpush->push($jpushid, $this->title,$content,$array);
					}
				}
				json('200','成功');
			}else {
				json('400','操作失败');
			}
		}
		json('404');
	}
	
	//催签到
	public function cuiqiandao(){
		if (I('post.')){
			$table = M('activity');
			$activityuser = M('activityuser');
			$id = I('post.id');
			$res = $table->find(I('post.id'));
			$ids = $activityuser->where("pid = '{$id}' and type = 1")->getField('uid',true);
			if ($ids){
				$message = M('message');
				$user = M('user');
				$data['title'] = '您参加的活动 '.$res['title'].' 马上开始了，您还没有签到';
				$data['content'] = '您参加的活动 '.$res['title'].' 马上开始了，您还没有签到';
				$data['type'] = 'activity';
				$data['typeid'] = $id;
				$data['state'] = 0;
				$data['addtime'] = time();
				$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
				foreach ($ids as $val){
					$data['uid'] = $val;
					$userdata = $user->field('jpushid')->find($val);
					if ($message->add($data)){
						$userdata = $user->field('jpushid')->find($val);
						if ($userdata['jpushid']){
							$jpushid[] = $userdata['jpushid'];
						}
					}
				}
				$array['type'] = 'message';
				$content = $data['title'];
				if ($jpushid){
					$jpush->push($jpushid, $this->title,$content,$array);
				}
				json('200','成功');
			}else {
				json('400','已经没有未签到人员');
			}
		}
		json('404');
	}
	
	//签到
	public function qiandao(){
		if (I('post.')){
			$activityuser = M('activityuser');
			$where = I('post.');
			$ids = $activityuser->where($where)->setField('type',2);
			if ($ids){
				json('200','成功');
			}else {
				json('400','签到失败');
			}
		}
		json('404');
	}
	
	//隐身
	public function yinshen(){
		if (I('post.')){
			$table = M('user');
			$id = I('post.id');
			$ids = $table->where("id = $id")->setField('state',I('post.state'));
			if ($ids){
				json('200','成功');
			}else {
				json('400','操作失败');
			}
		}
		json('404');
	}
	
	//我的群活动列表
	public function mygroupactivitylist(){
		if (I('post.')){
			$table =M('activity');
			$uid = I('post.uid');
			$user = M('user');
			$userinfo = $user->find($uid);
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			$img = M('img');
			$tab = M('groupuser');
			$selfcity = M('selfcity');
			$class = M('class');
			$res = $tab->where("user_id = $uid")->getField('group_id',true);
			$where['t_activity.groupid'] = array('in',$res);
			$data = $table->field('t_activity.id,t_activity.title,t_activity.state,t_activity.description as descript,t_activity.shopname,t_activity.simg,t_activity.address,t_activity.longitude,t_activity.latitude,t_activity.starttime,t_activity.type,t_activity.price,t_activity.addtime,t_activity.groupid,t_groups.title as name,t_groups.type as type1,t_groups.class as classes,t_groups.cityid')
			->join('left join t_groups on t_activity.groupid = t_groups.id')
			->where($where)->order('t_activity.addtime desc')->limit($pages,15)->select();
			if ($data){
				$activityuser = M('activityuser');
				foreach ($data as $key => $val){
					$data[$key]['user'] = $activityuser->field('t_user.id,t_groupuser.username,t_user.simg,t_user.sex')
					->join('left join t_user on t_user.id = t_activityuser.uid')
					->join("left join t_groupuser on t_user.id = t_groupuser.user_id and t_groupuser.group_id = '{$val['groupid']}'")
					->where("t_activityuser.pid = '{$val['id']}'")->order('t_activityuser.id asc')->limit(6)->select();
					$data[$key]['isactivityuser'] = $activityuser->where("pid = '{$val['id']}' and uid = '{$uid}'")->find() ? 1 : 0;
					$data[$key]['di'] = powc($userinfo['latitude'],$userinfo['longitude'], $val['latitude'], $val['longitude']);
					if ($data[$key]['di'] >= 1000){
						$data[$key]['distance'] = ceil($data[$key]['di']/1000).'km';
					}else {
						$data[$key]['distance'] = $data[$key]['di'].'m';
					}
					if ($val['cityid']){
						
						$data[$key]['address'] = $selfcity->where("id = '{$val['cityid']}'")->getField('title');
					}
					if ($val['type1'] == 3){
						
						$data[$key]['classes'] = $class->where("id = '{$val['classes']}'")->getField('title');
					}
					if ($val['state'] == 2){
						$data[$key]['img'] = $img->field('id,simg')->where("type = 'activitys' and pid = '{$val['id']}'")->select() ? $img->where("type = 'activitys' and pid = '{$val['id']}'")->select() : array();
					}else {
						$data[$key]['img'] = array();
					}
				}
				json('200','成功',$data);
			}else {
				json('400','没有群动态');
			}
		}
		json('404');
	}
	
	//朋友圈动态
	public function fansdtlist(){
		if (I('post.')){
			$table =M('dynamic');
			$uid = I('post.uid');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			$friend = M('friend');
			$ids = $friend->where("uid = $uid")->getField('fid',true);
			$ids[] = $uid;
			$where['t_dynamic.uid'] = array('in',$ids);
			$where['t_dynamic.type'] = 'user';
			$where['t_dynamic.status'] = array('neq',3);
			$data = $table->field('t_dynamic.id,t_dynamic.address,t_dynamic.content,t_dynamic.upper,t_dynamic.addtime,t_dynamic.state,t_dynamic.status,t_dynamic.uid,t_user.simg,t_user.sex,t_user.username')
			->join('left join t_user on t_user.id = t_dynamic.uid')
			->where($where)->order('t_dynamic.addtime desc')->limit($pages,15)->select();
			if ($data){
				$comment = M('comment');
				$upper = M('upper');
				$img = M('img');
				foreach ($data as $key => $val){
					$friendinfo = $friend->where("fid = '{$val['uid']}' and uid = $uid")->find();
					if($friendinfo['username']){
						$data[$key]['username'] = $friendinfo['username'];
					}
					$data[$key]['count'] = $comment->where("type = 'dynamic' and typeid = '{$val['id']}'")->count();
					$data[$key]['isupper'] = $upper->where("type = 'dynamic' and pid = '{$val['id']}' and uid = '$uid'")->find() ? 1 : 0;
					$data[$key]['img'] = $img->field('simg')->where("type = 'dynamic' and pid = '{$val['id']}'")->select();
				}
				json('200','成功',$data);
			}else {
				json('400','没有群动态');
			}
		}
		json('404');
	}
	
	//附近人动态
	public function fujindtlist(){
		if (I('post.')){
			$table =M('dynamic');
			$uid = I('post.uid');
			$page = I('post.page') ? I('post.page') : 1;
			$pages = ($page - 1)*15;
			$friend = M('friend');
			$user = M('user');
			$userinfo = $user->find($uid);
			$where['t_dynamic.type'] = 'user';
			$where['t_dynamic.status'] = 1;
			$where['t_dynamic.uid'] = array('neq',$uid);
			$where["round((2 * 6378.137* ASIN(SQRT(POW(SIN(PI()*('{$userinfo['latitude']}'-t_dynamic.latitude)/360),2)+COS(PI()*'{$userinfo['latitude']}'/180)* COS(t_dynamic.latitude * PI()/180)*POW(SIN(PI()*('{$userinfo['longitude']}'-t_dynamic.longitude)/360),2))))*1000)"] = array('lt',5000);
			$data = $table->field("round((2 * 6378.137* ASIN(SQRT(POW(SIN(PI()*('{$userinfo['latitude']}'-t_dynamic.latitude)/360),2)+COS(PI()*'{$userinfo['latitude']}'/180)* COS(t_dynamic.latitude * PI()/180)*POW(SIN(PI()*('{$userinfo['longitude']}'-t_dynamic.longitude)/360),2))))*1000) as juli,t_dynamic.id,t_dynamic.address,t_dynamic.content,t_dynamic.upper,t_dynamic.addtime,t_dynamic.state,t_dynamic.status,t_dynamic.uid,t_user.simg,t_user.sex,t_user.username")
			->join('left join t_user on t_user.id = t_dynamic.uid')
			->where($where)->order('t_dynamic.addtime desc')->limit($pages,15)->select();
			if ($data){
				$comment = M('comment');
				$upper = M('upper');
				$img = M('img');
				foreach ($data as $key => $val){
					$friendinfo = $friend->where("fid = '{$val['uid']}' and uid = $uid")->find();
					if($friendinfo['username']){
						$data[$key]['username'] = $friendinfo['username'];
					}
					$data[$key]['count'] = $comment->where("type = 'dynamic' and typeid = '{$val['id']}'")->count();
					$data[$key]['isupper'] = $upper->where("type = 'dynamic' and pid = '{$val['id']}' and uid = '$uid'")->find() ? 1 : 0;
					$data[$key]['img'] = $img->field('simg')->where("type = 'dynamic' and pid = '{$val['id']}'")->select();
				}
				json('200','成功',$data);
			}else {
				json('400','没有群动态');
			}
		}
		json('404');
	}
	
	//设置好友备注
	public function friendusername(){
		if (I('post.')){
			$where['uid'] = I('post.id');
			$where['fid'] = I('post.uid');
			$table = M('friend');
			if ($table->where($where)->setField('username',I('post.username'))){
				json('200','成功');
			}else {
				json('400','设置失败');
			}	
		}
		json('404');
	}
	
	//全局搜索
	public function search(){
		if(I('post.keyword')){
			
		}
		json('400','请输入搜索关键字');
	}
}