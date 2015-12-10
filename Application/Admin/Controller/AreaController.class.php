<?php
namespace Admin\Controller;

use Think;

class AreaController extends CommonController {
	
	public function index(){ 
		
		$table = M('area'); // 实例化User对象
		
		//接收查询数据
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['area'] = array('like',"%{$keyword}%");
			$table = $table->where($data);		
		}elseif (I('get.verify')){
			$data = I('get.');
			$table = $table->where($data);
			$this->assign('verify',I('get.verify'));
		}
		$count      = $table->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $table->where($data)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}
	
	public function add(){
		if (IS_POST){
			if (I('post.MODEL_ID') == 0){
				alertReplace('请选择所属分类');
			}
			$table = M('vehicle_model_year');
			if ($table->add(I('post.'))){
				alertLocation('添加成功！', '/Admin/Year');
			}else {
				$this->error('添加失败！');
			}		
		}
		$table = M('vehicle_mfg');
		$mfg = $table->select();
		$this->assign('mfg',$mfg);
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		if (IS_POST){
			print_r(I('post.'));
			if (I('post.cityid') == 0){
				alertReplace('请选择市级单位');
			}
			$table = M('area');
			if ($table->save(I('post.'))){
				alertBack('修改成功！');
			}else {
				$this->error('没有任何修改！');
			}			
		}
		$table = M('area');
		$data = $table->where("id = $id")->find();
		$city = M('city');
		$data1 = $city->where("cityid = '{$data['cityid']}'")->find();
		$table = M('province');
		$province = $table->select();
		$this->assign('city',$data1);
		$this->assign('province',$province);
		$this->assign($data);
		$this->display();
	}
	
//	public function state(){
//		$data = I('get.');			
//		$user = M('User');
//
//		if ($user->save($data)){
//			$this->redirect("/Admin/User/show");
//		}else {
//			$this->error('没有任何修改！');
//		}
//	}

	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('area');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}

	public function selectajax(){
		$id = I('get.id');
		$table = M('city');
		$data = $table->where("provinceid = $id")->select();
		$res['str'] = "<option value=' '>请选择市级单位</option>";
		$res['str1'] = "<li class='sel' onclick='sel(this)'>请选择市级单位</li>";
		foreach ($data as $val){
			$res['str'].="<option value='".$val['cityid']."'>".$val['city']."</option>";
		}
		foreach ($data as $val){
			$res['str1'].="<li class='sel' onclick='sel(this)'>".$val['city']."</li>";
		}
		echo json_encode($res);
	}
	
} 