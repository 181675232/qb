<?php
namespace Admin\Controller;

class OrderController extends CommonController {
	
	public function index(){
		
		$table = M('service_request'); // 实例化User对象
		
		//接收查询数据
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['owner.PHONE'] = array('like',"%{$keyword}%");
			$table = $table->join("left join owner ON service_request.OWNER_ID = owner.ID")->where($data);
		}elseif (I('get.state')){
			$data['service_request.REQUEST_STATUS_ID'] = I('get.state');
			$table = $table->join("left join owner ON service_request.OWNER_ID = owner.ID")->where($data);
			$this->assign('verify',I('get.state'));
		}
		$count      = $table->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $table
		->join("left join owner ON service_request.OWNER_ID = owner.ID")
		->join("left join owner_address ON service_request.ADDR_ID = owner_address.ID")
		->join("left join owner_vehicle ON service_request.VEHICLE_ID = owner_vehicle.ID")
		->join("left join vehicle_model_type ON vehicle_model_type.ID = owner_vehicle.VEHICLE_MODEL_TYPE_ID")
		->join("left join vehicle_model_year ON vehicle_model_year.ID = vehicle_model_type.MODEL_YEAR_ID")
		->join("left join vehicle_model ON vehicle_model.ID = vehicle_model_year.MODEL_ID")
		->join("left join vehicle_mfg ON vehicle_mfg.ID = vehicle_model.MFG_ID")
		->where($data)->field("service_request.ID,owner_address.CITY,service_request.REQUEST_STATUS_ID,service_request.DATE_CREATED,owner.PHONE,vehicle_model_type.MODEL_TYPE,vehicle_model_year.MODEL_YEAR,vehicle_model.MODEL,vehicle_mfg.MFG_ORIG,vehicle_mfg.MFG")
		->order('service_request.DATE_CREATED desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}
	
// 	public function add(){
// 		if (IS_POST){
// 			$table = D('order');
// 			if ($table->create()){
// 				if ($table->add()){
// 					alertLocation('添加会员成功！', '/Admin/Activity');
// 				}else {
// 					$this->error('添加失败！');
// 				}
// 			}else {
// 				$this->error($table->getError());
// 			}
			
// 		}
// 		$this->display();
// 	}
	
	public function edit(){
		$id = I('get.id');
		if (IS_POST){
			$table = M('service_request');
			if ($table->create()){
				$data = $table->create();
				$data['create_ts'] = strtotime($data['create_ts']);
				$data['avail_day'] = implode(',', $data['avail_day']);
				if ($table->save($data)){
					alertBack('修改成功！');
				}else {
					$this->error('没有任何修改！');
				}
			}else {
				$this->error($table->getError());
			}
				
		}
		$table = M('service_request');
		$bid = M('service_request_bid');
		$servicetype = M('service_type');
		$owner = M('owner');
		$vehicle = M('owner_vehicle');
		$address = M('owner_address');
		$data['service'] = $table->where("ID = $id")->find();
		$data['pro'] = $bid->field('service_request_bid.ID,service_request_bid.TOTAL_BEFORE_DISCOUNT,service_request_bid.NET_TOTAL,service_request_bid.DISCOUNT,provider.NAME,provider.ADDR1,provider.PHONE')
		->join('left join provider on provider.ID = service_request_bid.PROVIDER_ID')
		->where("service_request_bid.REQUEST_ID = $id and (BID_STATUS = 2 or BID_STATUS =4)")->find();
		$bid_service_detail = M('bid_service_detail');
		$data['bid_service'] = $bid_service_detail->where("BID_ID = '{$data['pro']['id']}'")->select();
		$bid_material_detail = M('bid_material_detail');
		$data['bid_material'] = $bid_material_detail->where("BID_ID = '{$data['pro']['id']}'")->select();
		$data['owner'] = $owner->find($data['service']['owner_id']);
		$data['address'] = $address->find($data['service']['addr_id']);
		$data['vehicle'] = $vehicle->field("vehicle_model_type.MODEL_TYPE,vehicle_model_year.MODEL_YEAR,vehicle_model.MODEL,vehicle_mfg.MFG_ORIG,vehicle_mfg.MFG")
		->join("left join vehicle_model_type ON vehicle_model_type.ID = owner_vehicle.VEHICLE_MODEL_TYPE_ID")
		->join("left join vehicle_model_year ON vehicle_model_year.ID = vehicle_model_type.MODEL_YEAR_ID")
		->join("left join vehicle_model ON vehicle_model.ID = vehicle_model_year.MODEL_ID")
		->join("left join vehicle_mfg ON vehicle_mfg.ID = vehicle_model.MFG_ID")
		->where("owner_vehicle.ID = '{$data['service']['vehicle_id']}'")->find();
		$where['ID'] = array('in',$data['service']['service']);
		$data['service_list'] = $servicetype->where($where)->select();
		$this->assign($data);
		$this->display();
	}
	
	public function state(){
		$data = I('get.');			
		$table = M('service_request');
		if ($table->save($data)){
			$this->redirect("/Admin/Order");
		}else {
			$this->error('没有任何修改！');
		}
	}
	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('service_request');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}
	
	public function EditOrderRemark(){
		$table = M('service_request');
		$where['ID'] = I('post.order_no');
		$where['NOTE'] = I('post.remark');
		if ($table->save($where)){
			$data['status'] = 1;
			$data['msg'] = '提示：修改成功！';		
			echo json_encode($data);
		}else {
			$data['msg'] = '提示：没有任何修改！';
			echo json_encode($data);
		}
	}
	
	public function EditRealAmount(){
		$table = M('service_request_bid');		
		$request_id = I('post.order_no');
		$net_total = $table->where("REQUEST_ID = $request_id and (BID_STATUS = 2 or BID_STATUS =4)")->find();
		$where['DISCOUNT'] = I('post.real_amount');
		$where['NET_TOTAL'] = $net_total['total_before_discount'] - I('post.real_amount');
		if ($table->where("REQUEST_ID = $request_id and (BID_STATUS = 2 or BID_STATUS =4)")->save($where)){
			$data['status'] = 1;
			$data['msg'] = '提示：修改成功！';
			echo json_encode($data);
		}else {
			$data['msg'] = '提示：没有任何修改！';
			echo json_encode($data);
		}
	}
	
	public function OrderCancel(){
		$table = M('service_request');
		$where['ID'] = I('post.order_no');
		$where['REQUEST_STATUS_ID'] = I('post.check_revert');
		if ($table->save($where)){
			$data['status'] = 1;
			$data['msg'] = '提示：取消成功！';
			echo json_encode($data);
		}else {
			$data['msg'] = '提示：已经是取消状态！';
			echo json_encode($data);
		}
	}
	
	
} 