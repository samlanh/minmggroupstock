<?php

class Product_Model_DbTable_DbRequestStock extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_product";
	public function setName($name)
	{
		$this->_name=$name;
	}
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	
	function getAllRequestStock($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$start_date = date("Y-m-d",strtotime($data["start_date"]));
		$end_date = date("Y-m-d",strtotime($data["end_date"]));
		$sql ="SELECT sr.id,(SELECT l.name FROM `tb_sublocation` AS l WHERE l.id=sr.`branch_id` AND l.status=1 LIMIT 1) AS location_name,
		        	sr.reques_no,(SELECT s.staff_name FROM `tb_staff` AS s WHERE s.id=sr.`staff_id` LIMIT 1) AS staff_name,
		        	(SELECT SUM(sd.`request_qty`) FROM `tb_staff_request_detail` AS sd WHERE sd.staff_request_id=sr.id AND sr.`status`=1 LIMIT 1)AS qty,
		        	(SELECT SUM(sd.`receive_qty`) FROM `tb_staff_request_detail` AS sd WHERE sd.staff_request_id=sr.id AND sr.`status`=1 LIMIT 1)AS qty_receive,
		            sr.date_request,sr.receive_date,sr.note,
			        (SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id=sr.user_id LIMIT 1 )AS user_id,
			        (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=sr.status LIMIT 1 )AS `status`,
			        'បោះពុម្ព'
			     FROM `tb_staff_request` AS sr ";
				$from_date =(empty($data['start_date']))? '1': " sr.date_request >= '".$data['start_date']." 00:00:00'";
				$to_date = (empty($data['end_date']))? '1': " sr.date_request <= '".$data['end_date']." 23:59:59'";
				$where = " where ".$from_date." AND ".$to_date;
		 		if($data["ad_search"]!=""){
		 			$s_where=array();
		 			$s_search=addslashes(trim($data['ad_search']));
		 			$s_search = str_replace(' ', '', $s_search);
		 			$s_where[]="REPLACE(sr.reques_no,' ','')   LIKE '%{$s_search}%'";
		 			$s_where[]="REPLACE((SELECT s.staff_name FROM `tb_staff` AS s WHERE s.id=sr.`staff_id`),' ','')   LIKE '%{$s_search}%'";
		 			$s_where[]="REPLACE((SELECT SUM(sd.`total_qty`) FROM `tb_staff_request_detail` AS sd WHERE sd.staff_request_id=sr.id AND sr.`status`=1),' ','')   LIKE '%{$s_search}%'";
		 			
		 			$where.=' AND ('.implode(' OR ', $s_where).')';
		 		} 
		 		if(!empty($data["branch"])){
		 			$where.=' AND sr.`branch_id`='.$data["branch"];
		 		}
		 		if(!empty($data["staff_id"])){
		 			$where.=' AND sr.`staff_id`='.$data["staff_id"];
		 		}
		$location = $db_globle->getAccessPermission('sr.`branch_id`');
		$order=' ORDER BY sr.id DESC';
		//echo $sql;
		return $db->fetchAll($sql.$where.$location.$order);
			
	}
	
	function getRequestStockByids($reques_id){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT sr.id,(SELECT l.name FROM `tb_sublocation` AS l WHERE l.id=sr.`branch_id` AND l.status=1 LIMIT 1) AS location_name,
		sr.reques_no,(SELECT s.staff_name FROM `tb_staff` AS s WHERE s.id=sr.`staff_id` LIMIT 1) AS staff_name,
		(SELECT SUM(sd.`total_qty`) FROM `tb_staff_request_detail` AS sd WHERE sd.staff_request_id=sr.id AND sr.`status`=1 LIMIT 1)AS qty,
		(SELECT SUM(sd.`request_qty`) FROM `tb_staff_request_detail` AS sd WHERE sd.staff_request_id=sr.id AND sr.`status`=1 LIMIT 1)AS qty_receive,
		sr.date_request,sr.receive_date,sr.note,
		(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id=sr.user_id LIMIT 1 )AS user_id,
		(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=sr.status LIMIT 1 )AS `status`,
		(SELECT s.staff_name FROM `tb_staff` AS s WHERE s.id=sr.`staff_id` LIMIT 1) AS staff_name,
		(SELECT s.staff_no FROM `tb_staff` AS s WHERE s.id=sr.`staff_id` LIMIT 1) AS staff_no,
		(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=(SELECT s.position_id FROM `tb_staff` AS s WHERE s.id=sr.`staff_id`) AND v.type=16 ) AS staff_position
		
		FROM `tb_staff_request` AS sr where sr.id=$reques_id";
		return $db->fetchRow($sql);
	}
	
	public function addRequest($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$user_id = $this->getUserId();
			$date =new Zend_Date();
			$request = array(
					'branch_id'		=>	$data["from_loc"],
					'reques_no'		=>	$data["request_no"],
					'staff_id'		=>	$data["staff_id"],
					'date_request'	=>	date("Y-m-d",strtotime($data["reques_date"])),
					'receive_date'	=>	date("Y-m-d",strtotime($data["receive_date"])),
					'create_date'	=>	date("Y-m-d H:i:s"),
					'purpose'		=>	$data["purpose"],
					'note'			=>	$data["note"],
					'status'		=>	$data["status"],
					'user_id'		=>	$user_id,
			);
			$this->_name="tb_staff_request";
			$reques_id=$this->insert($request);
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr = array(
							'staff_request_id'=>$reques_id,
							'pro_id'		=>	$data["pro_id_".$i],
							'curr_qty'		=>	$data["current_qty_".$i],
							'request_qty'	=>	$data["qty_unit_".$i],
							'qty_per_unit'	=>	$data["qty_per_unit_".$i],
							'total_qty'		=>	$data["qty_".$i],
							'receive_qty'	=>	$data["re_qty_".$i],
							'qty_perunit'	=>	$data["qty_measure_".$i],
							'defer_qty'	    =>	$data["remain_qty_".$i],
							'cost'	    	=>	$data["cost_".$i],
							'note'			=>	$data["remark_".$i],
							'user_id'		=>	$user_id,
							'status'		=>	$data["status"],
					);
					$this->_name="tb_staff_request_detail";
					$this->insert($arr);
					$rs = $this->getProductQtyById($data["pro_id_".$i],$data["from_loc"]);
					if(!empty($rs)){
						$arr_p = array(
							'qty'=>($rs['qty'])-($data["re_qty_".$i]),
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$data["from_loc"]);
						$this->update($arr_p, $where);
					}else{
						$arr_p = array(
								'pro_id'			=>	$data["pro_id_".$i],
								'location_id'		=>	$data["from_loc"],
								'qty'				=>	$data["re_qty_".$i],
								'damaged_qty'		=>	0,
								'qty_warning'		=>	0,
								'last_mod_userid'	=>	$user_id,
								'last_mod_date'		=>	date('Y-m-d'),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr_p);
					}
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	
	public function updateRequest($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$user_id = $this->getUserId();
			$date =new Zend_Date();
			///sum qty to stock 
			$row_detail=$this->getStaffRequestDetail($data['id']);
			if(!empty($row_detail)){
				foreach($row_detail as $row){
				    $rs = $this->getProductQtyById($row["pro_id"],$data["from_loc"]);
				    //print_r($rs);exit();
					if(!empty($rs)){
						$arr_qty = array(
								'qty'=>($rs['qty'])+($row["request_qty"]),
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$rs["pro_id"],"location_id=?"=>$rs["location_id"]);
						$this->update($arr_qty, $where);
					} 
				}
			}
			
			$request = array(
					'branch_id'		=>	$data["from_loc"],
					'reques_no'		=>	$data["request_no"],
					'staff_id'		=>	$data["staff_id"],
					'date_request'	=>	date("Y-m-d",strtotime($data["reques_date"])),
					'receive_date'	=>	date("Y-m-d",strtotime($data["receive_date"])),
					'create_date'	=>	date("Y-m-d H:i:s"),
					'purpose'		=>	$data["purpose"],
					'note'			=>	$data["note"],
					'status'		=>	$data["status"],
					'user_id'		=>	$user_id,
			);
			$this->_name="tb_staff_request";
			$where=" id=".$data['id'];
			$this->update($request, $where);
			
			
			$sql = "DELETE FROM tb_staff_request_detail WHERE staff_request_id=".$data["id"];
			$db->query($sql);
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr = array(
							'staff_request_id'=>$data['id'],
							'pro_id'		=>	$data["pro_id_".$i],
							'curr_qty'		=>	$data["current_qty_".$i],
							'request_qty'	=>	$data["qty_unit_".$i],
							'qty_per_unit'	=>	$data["qty_per_unit_".$i],
							'total_qty'		=>	$data["qty_".$i],
							'receive_qty'	=>	$data["re_qty_".$i],
							'qty_perunit'	=>	$data["qty_measure_".$i],
							'defer_qty'	    =>	$data["remain_qty_".$i],
							'cost'	    	=>	$data["cost_".$i],
							'note'			=>	$data["remark_".$i],
							'user_id'		=>	$user_id,
							'status'		=>	$data["status"],
					);
					$this->_name="tb_staff_request_detail";
					$this->insert($arr);
					$rs = $this->getProductQtyById($data["pro_id_".$i],$data["from_loc"]);
					if(!empty($rs)){
						$arr_p = array(
								'qty'=>($rs['qty'])-($data["re_qty_".$i]),
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$data["from_loc"]);
						$this->update($arr_p, $where);
					}else{
						$arr_p = array(
								'pro_id'			=>	$data["pro_id_".$i],
								'location_id'		=>	$data["from_loc"],
								'qty'				=>	$data["re_qty_".$i],
								'damaged_qty'		=>	0,
								'qty_warning'		=>	0,
								'last_mod_userid'	=>	$user_id,
								'last_mod_date'		=>	date('Y-m-d'),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr_p);
					}
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	
	function getProductName(){
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`id`,
				  p.`item_name` ,
				  p.`item_code`,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id` limit 1) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id` limit 1) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.key_code=p.`model_id` and type=2 limit 1) AS model,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.key_code=p.`color_id` and type=4 limit 1) AS color,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.key_code=p.`size_id` and type=3 limit 1) AS size
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl 
				WHERE p.`id` = pl.`pro_id` AND p.status=1 ";
		//$location = $db_globle->getAccessPermission('pl.`location_id`');
		return $db->fetchAll($sql);
	}
	function getProductById($id){
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$db = $this->getAdapter();
		$sql = "SELECT
				  p.`id`,
				  p.`item_name` ,
				  p.`qty_perunit` ,
				  p.`item_code`,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size,
				  pl.`qty`
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl
				WHERE p.`id` = pl.`pro_id` AND p.`id`=$id ";
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		return $db->fetchRow($sql.$location);
	}
	
	function getProductQtyById($id,$location){
		$db = $this->getAdapter();
		$sql = "SELECT
				  p.`id`,
				  pl.id as pl_id,
				  pl.pro_id,
				  pl.`location_id`,
				  p.`item_name` ,
				  p.`qty_perunit` ,
				  p.`item_code`,
				  p.`unit_label`,
				  (SELECT m.`name` FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size,
				  pl.`qty`,
				  pl.damaged_qty
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl
				WHERE p.`id` = pl.`pro_id` AND p.`id`=$id AND pl.`location_id` = $location ";
		
		return $db->fetchRow($sql);
	}
	
	//for get current qty time /26-8-13
	public function getCurrentItem($post){
		$db=$this->getAdapter();
		$sql = "SELECT qty FROM tb_prolocation WHERE pro_id =" .$post['item_id'] ." AND LocationId = ".$post['location_id']." LIMIT 1";
		$row=$db->fetchRow($sql);
		return($row);
	}
 
	
	function getAllStaffName(){
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$db = $this->getAdapter();
		$sql = "SELECT id,`staff_name` AS `name` FROM `tb_staff` WHERE STATUS=1 AND `staff_name`!=''";
// 		$location = $db_globle->getAccessPermission('pl.`location_id`');
		$location="";
		return $db->fetchAll($sql.$location);
	}
	
	function getAllStafInfo($id){
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$db = $this->getAdapter();
		$sql = "SELECT  id,`staff_name`,`staff_no`, 
		  (SELECT name_en FROM `tb_view` AS v  WHERE v.key_code= tb_staff.`position_id` AND v.type=16 LIMIT 1) AS  `position`
		   FROM `tb_staff` WHERE STATUS=1 AND `staff_name`!='' AND id=$id";
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		return $db->fetchRow($sql.$location);
	}
	
	function getStaffRequestById($id){
		$db = $this->getAdapter();
		$sql = " SELECT * FROM `tb_staff_request` WHERE id=$id";
	  	return $db->fetchRow($sql);
	}
	
	function getStaffRequestItemsbyId($id){
		$db = $this->getAdapter();
		$sql = "SELECT  sd.*,(SELECT m.`name` FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure,
        			p.`item_name` ,
					p.`qty_perunit` ,
					p.`item_code`,
					p.`unit_label`
        		FROM `tb_staff_request_detail` AS sd,`tb_product` AS p
    			WHERE sd.`pro_id`=p.`id`
    			AND sd.`staff_request_id`=$id";	
		return $db->fetchAll($sql);
	}
	
	function getStaffRequestDetail($id){
		$db = $this->getAdapter();
		$sql = "SELECT  sd.*,(SELECT m.`name` FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure,
			p.`item_name` ,
			p.`qty_perunit` ,
			p.`item_code`,
			p.`unit_label`
			FROM `tb_staff_request_detail` AS sd,`tb_product` AS p,`tb_staff_request` AS sr
			WHERE sd.`pro_id`=p.`id`
			AND sr.id=sd.`staff_request_id`
			AND sd.`staff_request_id`=$id";
		return $db->fetchAll($sql);
	}
	
	public function addNewStaff($post){
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$db=$this->getAdapter();
		$db_g=new Application_Model_DbTable_DbGlobal();
		$staff_no=$db_g->getStaffIdNo();
		$data=array(
 				'staff_name'		=> $post['staff_name'],
				'staff_no'			=> $staff_no,
				'sex'				=> $post['txt_sex'],
				'phone'				=> $post['phone'],//test
				'position_id'		=> $post['staff_position'],//test
				'car_number'		=>	$post['car_number'],
				'pob'				=> $post['pob'],
				'note'				=> $post['note'],
				'create_date'		=> date("Y-m-d H:i:sa"),
				'modify_date'		=> date("Y-m-d H:i:sa"),
 				'status'			=>	$post["status"],
				'user_id'			=> $GetUserId,
		);
		$this->_name="tb_staff";
		return $this->insert($data);
	}
}