<?php
namespace Admin\Controller;

class GroupController extends CommonController {
	public function index(){
		$table = D('Group');
		$group = $table->group_info();
		$this->assign('group',$group);
		$this->display();
	}
	
	public function add(){
		if (IS_POST){
			$user = D('Group');
			$data = I('post.');			
			if ($user->add($data)){
				alertLocation('添加成功！', '/Admin/Group');
			}else {
				$this->error('添加失败！');
			}			
		}
		if (I('get.id')){
			$this->assign('pid',I('get.id'));
		}else {
			$this->assign('pid',0);
		}
		$group = D('Group');
		$groups = $group->group_info();
		$this->assign('group',$groups);
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		$group = D('Group');
		if (IS_POST){
			$where = I('post.');
			if ($group->save($where)){
				alertBack('修改成功！');		
			}else {
				$this->error('没有任何改动！');
			}
		}
		$data = $group->find($id);
		$groups = $group->group_info();
		$this->assign($data);
		$this->assign('group',$groups);
		$this->display();
	}
	
	public function state(){
		$data = I('get.');
		$user = M('Group');
		if ($user->save($data)){
			$this->redirect(geturl());
		}else {
			$this->error('没有任何修改！');
		}
	}
	
	public function ajaxstate(){
		$data = I('get.');
		$table = M('Group');
		if ($table->save($data)){
			echo 1;
		}else {
			echo 0;
		}
	}
	
	public function delete(){
		$post = implode(',',$_POST['id']);
		$user = M('Group');
		$data = $user->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}
	
}