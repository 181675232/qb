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
	private $app_key='36b3dc718f373f05082ef383';
	private $master_secret = '359dfb9592f02079d7759f0b';
	
	//融云
	private $appKey = 'pwe86ga5ede86';
	private $appSecret = '96EvBT4wxIvCL';	
	
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
			$where['uid'] = I('post.uid');
			$where['newsid'] = I('post.id');
			$cityid = I('post.cityid');
			if (!$cityid){
				json('400','参数不合法');
			}
			$res = $trace->where($where)->find();
			if ($res){
				$trace->where($where)->setField('addtime',time());
			}else {
				$where['addtime'] = time();
				$trace->add($where);
			}
			$data = $table->field('id,tag,lower,upper,url,groupid')->find(I('post.id'));		
			$data['trace'] = $table->field('t_news.id,t_news.title,t_news.origin,t_news.addtime,t_news.addtime')
			->join('left join t_trace on t_news.id = t_trace.newsid')
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
			unset($where['state']);
			$table = M('upper');
			$tab = M(I('post.type'));
			if ($table->where($where)->find()){
				json('400','不可更改！');
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
					json('400','操作失败！');
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
			if (!checkNum($where['pid']) || !checkNum($where['uid']) || checkNull($where['type'])){
				json('400','参数不合法！');
			}
			if ($table->where($where)->find()){
				if ($table->where($where)->delete()){
					json('200','取消成功！');
				}else {
					json('400','操作失败！');
				}
			}else {
				$where['addtime']= time();
				if ($table->add($where)){
					json('200','收藏成功！');
				}else {
					json('400','操作失败！');
				}
			}					
		}
		json('404');
	}
	
	//关注
	public function fans(){
		if (I('post.')){
			$table = M('fans');
			$where = I('post.');
			$where1['uid'] = I('post.fid');
			$where1['fid'] = I('post.uid');
			if ($table->where($where)->find()){
				if ($table->where($where)->delete()){
					$tab = M('friend');
					if ($tab->where($where)->find()){
						$tab->where($where)->delete();
						$tab->where($where1)->delete();
					}
					json('200','取消成功！');
				}else {
					json('400','操作失败！');
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
					json('200','关注成功！');
				}else {
					json('400','操作失败！');
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
				$data = $table->field('id,title,description,simg,img,istop,origin,addtime,type')->where("isred = 2 and istop != 2 and (cityid = 0 or cityid = $cityid)")->order('id desc')->limit($page1,10)->select();	
				$data1 = $table->field('id,title,description,simg,img,istop,origin,addtime,type')->where("istop = 2 and (cityid = 0 or cityid = $cityid)")->order('isred desc,ord asc,id desc')->limit($page2,1)->select();
				if (count($data) >= 5 && $data1){
					$res = array_splice($data,5,5,$data1);
					array_push($data, $res);
				}
			}elseif ($groupid == 'hot'){
				$data = $table->field('id,title,description,simg,img,istop,origin,addtime,type')->where("istop != 2 and (cityid = 0 or cityid = $cityid)")->order('ord asc,id desc')->limit($page1,10)->select();	
				$data1 = $table->field('id,title,description,simg,img,istop,origin,addtime,type')->where("istop = 2 and (cityid = 0 or cityid = $cityid)")->order('isred desc,ord asc,id desc')->limit($page2,1)->select();
				if (count($data) >= 5 && $data1){
					$res = array_splice($data,5,5,$data1);
					array_push($data, $res);
				}
			}elseif ($groupid == 'news'){
				$data = $table->field('id,title,description,simg,img,istop,origin,addtime,type')->where("istop != 2 and (cityid = 0 or cityid = $cityid)")->order('id desc')->limit($page1,10)->select();
			}else {
				$data = $table->field('id,title,description,simg,img,istop,origin,addtime,type')->where("groupid = $groupid and istop != 2 and (cityid = 0 or cityid = $cityid)")->order('id desc')->limit($page1,10)->select();
			}
			if ($data){
				$comment = M('comment');
				$friend = M('friend');
				$arr = $friend->where("uid = $id")->getField('fid',true);
				$where['uid'] = array('in',$arr);
				$where['type'] = 'news';		
				$img = M('img');
				foreach ($data as $key=>$val){			
					$data[$key]['comment'] = $comment->where("type = 'news' and typeid = '{$val['id']}'")->count();
					$where['typeid'] = $val['id'];
					$data[$key]['friend'] = count($comment->where($where)->group('uid')->select());
					if ($val['type'] == 2){
						$data[$key]['simg3'] = $img->field('simg')->where("type = 'news' and pid = '{$val['id']}'")->order('id asc')->limit(3)->select();
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
			$state = $where['state'];
			unset($where['state']);
			$where['addtime'] = time();
			if ($table->add($where)){
				if ($where['senduid'] != 0 and $state == 1){
					$friend = M('friend');
					if ($friend->where("uid = '{$where['uid']}' and fid = '{$where['senduid']}'")->find()){
						$user = M('user');
						$res = $user->find($where['senduid']);
						$jpushid =$res['jpushid'];
						$array['type'] = $where['type'];
						$array['id'] = $where['typeid'];
						$jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
						$content = '您有一条群组申请';
						$jpush->push($jpushid, $this->title,$content,$array);
					}
				}
				json('200','成功！');
			}else {
				json('400','评论失败！');
			}	
		}
		json('404');
	}
	
	//启动更新
	public function startsave(){
		if (I('post.')){
			$table = M('user');
			$where = I('post.');
			if ($table->save($where)){
				json('200','成功');
			}else {
				json('400','启动失败');
			}
		}
		json('404');
	}
	
	//帖子首页评论
	public function comment(){
		if (I('post.')){
			$table = M('comment');
			$uid= I('post.uid');
			$where['t_comment.typeid'] = I('post.typeid');
			$where['t_comment.type'] = I('post.type');
			$where['t_comment.pid'] = 0;
			$data['hot'] = $table->field('t_comment.id,t_comment.description,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude')
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
					
					$data['hot'][$key]['data'] = $table->field('t_comment.id,t_comment.description,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude,u.id as uid2,u.username as username2,u.simg as simg2')
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
			}
			
			$data['news'] = $table->field('t_comment.id,t_comment.description,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude')
			->join('left join t_user on t_user.id = t_comment.uid')
			->where($where)->order('id desc')->limit(10)->select();
			if ($data['news']){
				foreach ($data['news'] as $key=>$val){
					$data['news'][$key]['count'] = $table->where("pid = '{$val['id']}'")->count();
					$data['news'][$key]['di'] = powc(I('post.latitude'),I('post.longitude'), $val['latitude'], $val['longitude']);
					$data['news'][$key]['data'] = $table->field('t_comment.id,t_comment.description,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude,u.id as uid2,u.username as username2,u.simg as simg2')
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
			$uid= I('post.uid');
			$where['t_comment.typeid'] = I('post.typeid');
			$where['t_comment.type'] = I('post.type');
			$where['t_comment.pid'] = 0;
			$data = $table->field('t_comment.id,t_comment.description,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude')
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
						
					$data[$key]['data'] = $table->field('t_comment.id,t_comment.description,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude,u.id as uid2,u.username as username2,u.simg as simg2')
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
			$uid= I('post.uid');
			$where['t_comment.typeid'] = I('post.typeid');
			$where['t_comment.type'] = I('post.type');
			$where['t_comment.pid'] = 0;
			$page = I('post.page') ? I('post.page') : 1;
			$page = ($page-1)*10;
			$data = $table->field('t_comment.id,t_comment.description,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude')
			->join('left join t_user on t_user.id = t_comment.uid')
			->where($where)->order('id desc')->limit($page,10)->select();
			if ($data){
				foreach ($data as $key=>$val){
					$data[$key]['count'] = $table->where("pid = '{$val['id']}'")->count();
					$data[$key]['di'] = powc(I('post.latitude'),I('post.longitude'), $val['latitude'], $val['longitude']);
					$data[$key]['data'] = $table->field('t_comment.id,t_comment.description,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude,u.id as uid2,u.username as username2,u.simg as simg2')
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
			$uid= I('post.uid');
			$where['t_comment.pid'] = I('post.pid');
			$where['t_comment.type'] = I('post.type');
			$page = I('post.page') ? I('post.page') : 1;
			$page = ($page-2)*8+2;
			$data = $table->field('t_comment.id,t_comment.description,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude,u.id as uid2,u.username as username2,u.simg as simg2')
			->join('left join t_user on t_user.id = t_comment.uid')
			->join('left join t_user u on u.id = t_comment.senduid')
			->where($where)->order('t_comment.id desc')->limit($page,8)->select();
			//计算二级评论距离
			foreach ($data as $k=>$v){
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
			$friend = M('friend');
			$arr = $friend->where("uid = $uid")->getField('fid',true);
			$map['t_comment.uid'] = array('in',$arr);
			$map['c.uid'] = array('in',$arr);
			$map['_logic'] = 'or';
			$where['_complex'] = $map;
			$where['t_comment.typeid'] = I('post.typeid');
			$where['t_comment.type'] = I('post.type');
			$where['t_comment.pid'] = 0;			
			$page = I('post.page') ? I('post.page') : 1;
			$page = ($page-1)*10;
			$data = $table->field('t_comment.id,t_comment.description,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude')
			->join('left join t_user on t_user.id = t_comment.uid')
			->join('left join t_comment as c on c.pid = t_comment.id')
			->where($where)->order('id desc')->limit($page,10)->select();
			if ($data){
				foreach ($data as $key=>$val){
					$data[$key]['count'] = $table->where("pid = '{$val['id']}'")->count();
					$data[$key]['di'] = powc(I('post.latitude'),I('post.longitude'), $val['latitude'], $val['longitude']);
					$data[$key]['data'] = $table->field('t_comment.id,t_comment.description,t_comment.upper,t_comment.addtime,t_comment.uid,t_user.simg,t_user.username,t_user.longitude,t_user.latitude,u.id as uid2,u.username as username2,u.simg as simg2')
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
	
	
		
}