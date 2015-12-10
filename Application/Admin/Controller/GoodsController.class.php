<?php
namespace Admin\Controller;

class GoodsController extends CommonController {
	
	public function index(){
		
		$table = M('goods'); // 实例化User对象
		
		//接收查询数据
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['t_goods.name'] = array('like',"%{$keyword}%");
		}elseif (I('get.verify')){
			$data['t_goods.groupid'] = I('get.verify');
			$this->assign('verify',I('get.verify'));
		}
		if ($_SESSION['level'] != 0){
			switch ($_SESSION['level']){
				case  1:
					$data['t_goods.provinceid'] = $_SESSION['provinceid'];
					break;
				case  2:
					$data['t_goods.cityid'] = $_SESSION['cityid'];
					break;
				case  3:
					$data['t_goods.areaid'] = $_SESSION['areaid'];
					break;
			}
		}
		$count = $table->where($data)->count();
		$Page       = new \Think\Page($count,14);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $table->field('t_goods.*,t_shop.name as title')
		->join('left join t_shop on t_goods.pid = t_shop.id')
		->where($data)->order('ord asc,addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}
	
	public function add(){
		$shop = M('shop');
		if (IS_POST){
			if (I('post.pid') == 0){
				alertReplace('请选择所在属店铺');
			}
			$where = I('post.');
			$data = $shop->where("id = '{$where['pid']}'")->find();
			$where['cityid'] = $data['cityid'];
			$where['areaid'] = $data['areaid'];
			$where['provinceid'] = $data['provinceid'];
			$where['starttime'] = strtotime($where['starttime']);
			$where['stoptime'] = strtotime($where['stoptime']);
			$where['addtime'] = time();
			$table = M('goods');	
			$where1['goodsid'] = $table->add($where);	
			if ($where1['goodsid']){
				if ($where['istab'] == 2){
					$tab = M('tab');
					$where1['content'] = '';
					foreach ($where['tab'] as $key=>$val){				
					  if (($key+1)%3 == 0){
					  	$where1['content'].= $val;
					  	$tab->add($where1);
					  	$where1['content'] = '';
					  }else {
					  	$where1['content'].=$val.',';
					  }
					}
				}
				alertLocation('添加成功！', '/Admin/Goods');
			}else {
				$this->error('添加失败！');
			}		
		}	
		$type1 = $shop->select();
		$this->assign('type1',$type1);
		$this->display();
	}
	
	
	public function edit(){	
		$id = $where['id'] = $where1['goodsid'] = I('get.id');
		$table = M('goods');
		$tab = M('tab');
		$shop = M('shop');
		if (IS_POST){
			if (I('post.pid') == 0){
				alertReplace('请选择所在属店铺');
			}
			$where = I('post.');
			$data = $shop->where("id = '{$where['pid']}'")->find();
			$where['cityid'] = $data['cityid'];
			$where['areaid'] = $data['areaid'];
			$where['provinceid'] = $data['provinceid'];
			$where['starttime'] = strtotime($where['starttime']);
			$where['stoptime'] = strtotime($where['stoptime']);
			$table->save($where);
			$tab->where("goodsid = $id")->delete();
			if ($where['istab'] == 2){				
				$where1['content'] = '';
				foreach ($where['tab'] as $key=>$val){
					if (($key+1)%3 == 0){
						$where1['content'].= $val;
						$tab->add($where1);
						$where1['content'] = '';
					}else {
						$where1['content'].=$val.',';
					}
				}						
			}
			alertBack('修改成功！');
		}
		$t = $tab->where("goodsid = $id")->select();
		foreach ($t as $key => $val){
			$t[$key]['content'] = explode(',', $val['content']);
		}
		$data = $table->where("id = $id")->find();
		$type1 = $shop->select();
		$this->assign('type1',$type1);
		$this->assign('ttt',$t);
		$this->assign($data);
		$this->display();
	}
	
	public function state(){
		$data['id'] = I('get.id');
		if (I('get.isred')){
			$data['isred'] = I('get.isred');
		}
		if (I('get.istop')){
			$data['istop'] = I('get.istop');
		}
		if (I('get.iscomment')){
			$data['iscomment'] = I('get.iscomment');
		}
		$table = M('goods');
		$str ='/';
		$p = I('get.p');
		$verify = I('get.verify');
		$keyword = I('get.keyword');
		if (I('get.p')){
			$str.= 'p/'.I('get.p').'/';
		}
		if (I('get.verify')){
			$str.= 'verify/'.I('get.verify').'/';
		}
		if (I('get.keyword')){
			$str.= 'keyword/'.I('get.keyword').'/';
		}
		if ($table->save($data)){
			$this->redirect('/Admin/Goods/index'.$str);
		}else {
			$this->error('没有任何修改！');
		}
	}
	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('goods');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}
	
	public function ajaxstate(){
		$data = I('get.');
		$table = M('goods');
		if ($table->save($data)){
			echo 1;
		}else {
			echo 0;
		}
	}
	
	public function selectajax(){
		$id = I('get.id');
		$table = M('city');
		$data = $table->where("provinceid = $id")->select();
		$res['str'] = "<option value='0'>请选择在市级单位</option>";
		$res['str1'] = "<li class='sel' onclick='sel(this)'>请选择在市级单位</li>";
		foreach ($data as $val){
			$res['str'].="<option value='".$val['cityid']."'>".$val['city']."</option>";
		}
		foreach ($data as $val){
			$res['str1'].="<li class='sel' onclick='sel(this)'>".$val['city']."</li>";
		}
		echo json_encode($res);
	}
	
	public function ajax(){
		if (!empty($_POST['param'])){
			$table = M('goods');
			$data[$_POST['name']] = $_POST['param'];
			$return = $table->where($data)->find();
			if ($return){
				echo '手机号已存在！';
			}else {
				echo 'y';
			}
		}
	}
	
} 