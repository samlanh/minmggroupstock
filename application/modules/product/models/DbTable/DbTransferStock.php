<?php

class Product_Model_DbTable_DbTransferStock extends Zend_Db_Table_Abstract
{
	protected $_name = "rms_transferstock";
	public function setName($name)
	{
		$this->_name=$name;
	}
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	
	function getAllTransferStock($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$start_date = date("Y-m-d",strtotime($data["start_date"]));
		$end_date = date("Y-m-d",strtotime($data["end_date"]));
		$sql ="SELECT t.id,t.transfer_no,DATE_FORMAT(t.transfer_date, '%d-%b-%Y')AS transfer_date,
			      (SELECT l.name FROM `tb_sublocation` AS l WHERE l.id=t.`from_location` AND l.status=1 LIMIT 1) AS location_name,
			      (SELECT l.name FROM `tb_sublocation` AS l WHERE l.id=t.`to_location` AND l.status=1 LIMIT 1) AS to_location_name,
			      (SELECT SUM(qty) FROM `rms_transferstock_detail` AS td WHERE t.id=td.transferid ) AS total_qty,
			      t.note,(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=t.`is_approve` AND v.type=17 LIMIT 1) AS approve,
			      (SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id=t.user_id LIMIT 1 )AS user_id,
			      (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=t.status LIMIT 1 )AS `status`
			    FROM `rms_transferstock` AS t  ";
				$from_date =(empty($data['start_date']))? '1': " t.transfer_date >= '".$data['start_date']." 00:00:00'";
				$to_date = (empty($data['end_date']))? '1': " t.transfer_date <= '".$data['end_date']." 23:59:59'";
				$where = " where ".$from_date." AND ".$to_date;
		 		if($data["ad_search"]!=""){
		 			$s_where=array();
		 			$s_search=addslashes(trim($data['ad_search']));
		 			$s_search = str_replace(' ', '', $s_search);
		 			$s_where[]="REPLACE(t.transfer_no,' ','')   LIKE '%{$s_search}%'";
		 			$where.=' AND ('.implode(' OR ', $s_where).')';
		 		} 
// 		$location = $db_globle->getAccessPermission('m.`location_id`');
		$order=' ORDER BY t.id DESC';
		//echo $sql;
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function addTransferStock($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$user_id = $this->getUserId();
			$date =new Zend_Date();
			$request = array(
					'transfer_no'		=>	$data["transfer_no"],
					'transfer_date'		=>	date("Y-m-d",strtotime($data["reques_date"])),
					'create_date'		=>	date("Y-m-d H:i:s"),
					'modify_date'		=>	date("Y-m-d H:i:s"),
					'from_location'		=>	$data["from_loc"],
					'to_location'		=>	$data["branch"],
					'is_approve'		=>	0,
					'note'				=>	$data["note"],
					'status'			=>	$data["status"],
					'user_id'			=>	$user_id,
			);
			$this->_name="rms_transferstock";
			$transfer_id=$this->insert($request);
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr = array(
							'transferid'=>$transfer_id,
							'pro_id'		=>	$data["pro_id_".$i],
							'curr_qty'		=>	$data["current_qty_".$i],
							'qty'			=>	$data["qty_unit_".$i],
							'cost'			=>	$data["cost_".$i],
							'note'			=>	$data["remark_".$i],
					);
					$this->_name="rms_transferstock_detail";
					$this->insert($arr);
				/*			$rs = $this->getProductQtyById($data["pro_id_".$i],$data["from_loc"]);
							if(!empty($rs)){
								$arr_p = array(
									'qty'=>($rs['qty'])-($data["qty_".$i]),
								);
								$this->_name="tb_prolocation";
								$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$data["from_loc"]);
								$this->update($arr_p, $where);
							}else{
								$arr_p = array(
										'pro_id'			=>	$data["pro_id_".$i],
										'location_id'		=>	$data["from_loc"],
										'qty'				=>	$data["qty_".$i],
										'damaged_qty'		=>	0,
										'qty_warning'		=>	0,
										'last_mod_userid'	=>	$user_id,
										'last_mod_date'		=>	date('Y-m-d'),
								);
								$this->_name="tb_prolocation";
								$this->insert($arr_p);
							}*/
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	
	public function updateTransfer($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$user_id = $this->getUserId();
			$date =new Zend_Date();
			$request = array(
					'transfer_no'		=>	$data["transfer_no"],
					'transfer_date'		=>	date("Y-m-d",strtotime($data["reques_date"])),
					'modify_date'		=>	date("Y-m-d H:i:s"),
					'from_location'		=>	$data["from_loc"],
					'to_location'		=>	$data["branch"],
					'is_approve'		=>	0,
					'note'				=>	$data["note"],
					'status'			=>	$data["status"],
					'user_id'			=>	$user_id,
			);
			$this->_name="rms_transferstock";
			$where=" id=".$data['id'];
			$this->update($request, $where);
			
			$sql = "DELETE FROM rms_transferstock_detail WHERE transferid=".$data["id"];
			$db->query($sql);
			
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr = array(
							'transferid'	=>  $data['id'],
							'pro_id'		=>	$data["pro_id_".$i],
							'curr_qty'		=>	$data["current_qty_".$i],
							'qty'			=>	$data["qty_unit_".$i],
							'cost'			=>	$data["cost_".$i],
							'note'			=>	$data["remark_".$i],
					);
					$this->_name="rms_transferstock_detail";
					$this->insert($arr);
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
		$location = $db_globle->getAccessPermission('pl.`location_id`');
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
	
	function getTransferById($id){
		$db = $this->getAdapter();
		$sql = " SELECT * FROM `rms_transferstock` WHERE id=$id";
	  	return $db->fetchRow($sql);
	}
	
	function getTransferItemsbyId($id){
		$db = $this->getAdapter();
		$sql = "SELECT  td.*,(SELECT m.`name` FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure,
			        p.`item_name` ,
					p.`qty_perunit` ,
					p.`item_code`,
					p.`unit_label`
					
				FROM `rms_transferstock_detail` AS td,`tb_product` AS p
				WHERE td.`pro_id`=p.`id`
				AND td.`transferid`=$id";	
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
	
}