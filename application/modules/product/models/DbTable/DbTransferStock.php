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
	
	function getAllTransferStock($data=null){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT t.id,t.transfer_no,DATE_FORMAT(t.transfer_date, '%d-%b-%Y')AS transfer_date,
			      (SELECT l.name FROM `tb_sublocation` AS l WHERE l.id=t.`from_location` AND l.status=1 LIMIT 1) AS location_name,
			      (SELECT l.name FROM `tb_sublocation` AS l WHERE l.id=t.`to_location` AND l.status=1 LIMIT 1) AS to_location_name,
			      (SELECT SUM(qty) FROM `rms_transferstock_detail` AS td WHERE t.id=td.transferid ) AS total_qty,
			      t.note,(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=t.`is_approve` AND v.type=17 LIMIT 1) AS approve,
			      (SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id=t.user_id LIMIT 1 )AS user_id,
			      (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=t.status LIMIT 1 )AS `status`,
			      t.`is_approve`,(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=t.is_receive  AND v.type=18 LIMIT 1)AS `receive`
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
	
	function getAllTransferReceiveStock($data=null){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT t.id,(SELECT tr.transfer_no FROM `rms_transferstock` AS tr WHERE tr.id=t.`transfer_id` LIMIT 1) AS transfer_no,
         t.`transfer_re_no`,t.transfer_re_date, 
		(SELECT l.name FROM `tb_sublocation` AS l WHERE l.id=t.`from_location` AND l.status=1 LIMIT 1) AS location_name,
		(SELECT l.name FROM `tb_sublocation` AS l WHERE l.id=t.`to_location` AND l.status=1 LIMIT 1) AS to_location_name,
		(SELECT SUM(qty) FROM `rms_transfer_received_detail` AS td WHERE t.id=td.transfer_re_id  ) AS total_qty,
		t.note,(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=t.`is_approve` AND v.type=17 LIMIT 1) AS approve,
		(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id=t.user_id LIMIT 1 )AS user_id,
		(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=t.status LIMIT 1 )AS `status`,
		t.`is_approve`,(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=t.is_receive  AND v.type=18 LIMIT 1)AS `receive`
		FROM `rms_transfer_receive` AS t  ";
		$from_date =(empty($data['start_date']))? '1': " t.transfer_re_date >= '".$data['start_date']." 00:00:00'";
		$to_date = (empty($data['end_date']))? '1': " t.transfer_re_date <= '".$data['end_date']." 23:59:59'";
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
	
	public function addApproveTransfer($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$user_id = $this->getUserId();
			$date =new Zend_Date();
			$db_global = new Application_Model_DbTable_DbGlobal();
			$receive_no=$db_global->getTransferReceiveNo();
			//print_r($receive_no);exit();
			$tran_row=$this->getTransferById($data['id']);
			if (!empty($tran_row)){
				$receive = array(
						'transfer_re_no'	=>	$receive_no,
						'transfer_id'		=>	$tran_row['id'],
						'transfer_re_date'	=>	date("Y-m-d"),
						'create_date'		=>	date("Y-m-d H:i:s"),
						'modify_date'		=>	date("Y-m-d H:i:s"),
						'from_location'		=>	$tran_row['from_location'],
						'to_location'		=>	$tran_row['to_location'],
						'is_receive'		=>	$data["approve"],
						'note'				=>	$data["remark"],
						'status'			=>	1,
						'user_id'			=>	$user_id,
				);
				$this->_name="rms_transfer_receive";
				if ($data["approve"]==1){
					$receive_id=$this->insert($receive);
					$transfer = array(
							'modify_date'		=>	date("Y-m-d H:i:s"),
							'is_approve'		=>	1,
							'is_receive'		=>	$data["approve"],
							'user_id'			=>	$user_id,
					);
					$this->_name="rms_transferstock";
					$where=" id=".$tran_row['id'];
					$this->update($transfer, $where);
				
				$row_detail=$this->getStaffRequestDetail($tran_row['id']);
				foreach($row_detail as $row)
				{
					$arr = array(
							'transfer_re_id'=>  $receive_id,
							'pro_id'		=>	$row["pro_id"],
							'curr_qty'		=>	$row["curr_qty"],
							'qty'			=>	$row["qty"],
							'cost'			=>	$row["cost"],
							'note'			=>	$row["note"],
					);
					$this->_name="rms_transfer_received_detail";
					$this->insert($arr);
					//form_location
					$rs = $this->getProductQtyById($row["pro_id"],$tran_row['from_location']);
					if(!empty($rs)){
					$qty=($rs['qty'])-($row["qty"]);
					$arr = array(
							'qty'=>$qty,
					);
					$this->_name="tb_prolocation";
					$where = array('pro_id=?'=>$row["pro_id"],"location_id=?"=>$tran_row['from_location']);
					$this->update($arr, $where);
					}else{
					$arr = array(
							'pro_id'			=>	$row["pro_id"],
							'location_id'		=>	$tran_row['from_location'],
							'qty'				=>	$row["qty"],
							'damaged_qty'		=>	0,
							'qty_warning'		=>	0,
							'last_mod_userid'	=>	$user_id,
							'last_mod_date'		=>	date('Y-m-d'),
					);
					$this->_name="tb_prolocation";
					$this->insert($arr);
					} 
					//to_location
					$rs_to = $this->getProductQtyById($row["pro_id"],$tran_row['to_location']);
					if(!empty($rs_to)){
						$qty_to=($rs_to['qty'])+($row["qty"]);
						$arr= array(
								'qty'=>$qty_to,
						);
						$this->_name="tb_prolocation";
						$where_to = array('pro_id=?'=>$row["pro_id"],"location_id=?"=>$tran_row['to_location']);
						$this->update($arr, $where_to);
					}else{
						$arr = array(
								'pro_id'			=>	$row["pro_id"],
								'location_id'		=>	$tran_row['to_location'],
								'qty'				=>	$row["qty"],
								'damaged_qty'		=>	0,
								'qty_warning'		=>	0,
								'last_mod_userid'	=>	$user_id,
								'last_mod_date'		=>	date("Y-m-d H:i:s"),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr);
					}
				 }
				}else{
					if($data["approve"]==0){
						$request = array(
								'modify_date'		=>	date("Y-m-d H:i:s"),
								'is_approve'		=>	0,
								'is_receive'		=>	$data["approve"],
								'user_id'			=>	$user_id,
						);
						$this->_name="rms_transferstock";
						$where=" id=".$tran_row['id'];
						$this->update($request, $where);
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
	
	public function addTransferStockpsst($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_arr = array(
					'transfer_no'=>$_data['transfer_no'],
					'transfer_date'=>$_data['date'],
					'from_location'=>$_data['f_branch'],
					'to_location'=>$_data['branch'],
					'note'=>$_data['remark'],
					'user_id'=>$this->getUserId(),
					'status'=>1,
			);
			$tranid= $this->insert($_arr);
			 
			$ids = explode(',', $_data['identity']);
			foreach ($ids as $i){
				$_arr = array(
						'transferid'=>$tranid,
						'pro_id'=>$_data['pro_id_'.$i],
						'qty'=>$_data['qty_'.$i],
						'note'=>$_data['remark_'.$i],);
				$this->_name='rms_transferdetail';
				$this->insert($_arr);
	
				$rs = $this->checkisProductSet($_data['pro_id_'.$i]);
				if(empty($rs)){//normal product
					$qty_stock = $this->getProductLocation($_data['pro_id_'.$i],$_data['f_branch']);
					$this->_name="rms_product_location";
					if(!empty($qty_stock)){
						$qty = $qty_stock['pro_qty'] - $_data['qty_'.$i];
						$array = array(
								'pro_qty'=>$qty,
						);
						$where = " id = ".$qty_stock['id'];
						$this->update($array, $where);
					}
					//to location
					$qty_stock = $this->getProductLocation($_data['pro_id_'.$i],$_data['branch']);
					if(!empty($qty_stock)){
						$qty = $qty_stock['pro_qty'] + $_data['qty_'.$i];
						$array = array(
								'pro_qty'=>$qty,
						);
						$where = " id = ".$qty_stock['id'];
						$this->update($array, $where);
					}else{
						$data = array(
								'pro_id'=>$_data['pro_id_'.$i],
								'brand_id'=>$_data['branch'],
								'pro_qty'=>$_data['qty_'.$i],
								'note'=>'ពីផ្ទេរទំនិញចូល',
								'date' =>date("Y-m-d"),
								'user_id'=>$this->getUserId(),
								'status'=>1);
						$this->insert($data);
					}
				}//prodcut set
				else{
					$rsset = $this->getProductSet($_data['pro_id_'.$i]);
					if(!empty($rsset)){
						foreach($rsset as $rs){
							$qty_stock = $this->getProductLocation($rs['subpro_id'],$_data['f_branch']);
							$this->_name="rms_product_location";
							if(!empty($qty_stock)){
								$qty = $qty_stock['pro_qty'] - $_data['qty_'.$i];
								$array = array(
										'pro_qty'=>$qty,
								);
								$where = " id = ".$qty_stock['id'];
								$this->update($array, $where);
							}
							//to location
							$qty_stock = $this->getProductLocation($rs['subpro_id'],$_data['branch']);
							if(!empty($qty_stock)){
								$qty = $qty_stock['pro_qty'] + $_data['qty_'.$i];
								$array = array(
										'pro_qty'=>$qty,
								);
								$where = " id = ".$qty_stock['id'];
								$this->update($array, $where);
							}else{
								$data = array(
										'pro_id'=>$_data['pro_id_'.$i],
										'brand_id'=>$_data['branch'],
										'pro_qty'=>$_data['qty_'.$i],
										'note'=>'ពីផ្ទេរទំនិញចូល',
										'date' =>date("Y-m-d"),
										'user_id'=>$this->getUserId(),
										'status'=>1
								);
								$this->insert($data);
							}
						}
					}
				}
			}
			$db->commit();
			return true;
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();exit();
			return false;
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
		$sql = " SELECT t.* ,
				(SELECT l.name FROM `tb_sublocation` AS l WHERE l.id=t.`from_location` AND l.status=1 LIMIT 1) AS location_name,
				(SELECT l.name FROM `tb_sublocation` AS l WHERE l.id=t.`to_location` AND l.status=1 LIMIT 1) AS to_location_name,
				(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=t.is_receive  AND v.type=18 LIMIT 1)AS `receive`
			     FROM `rms_transferstock` AS t
			     WHERE id=$id";
	  	return $db->fetchRow($sql);
	}
	
	function getTransferItemsbyId($id){
		$db = $this->getAdapter();
		$sql = "SELECT  td.*,(SELECT m.`name` FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure,
					(SELECT c.`name` FROM `tb_category` AS c WHERE c.id=p.`cate_id` LIMIT 1) AS type,
			        p.`item_name` ,
					p.`qty_perunit` ,
					p.`item_code`,
					p.`unit_label`
					
				FROM `rms_transferstock_detail` AS td,`tb_product` AS p
				WHERE td.`pro_id`=p.`id`
				AND td.`transferid`=$id";	
		return $db->fetchAll($sql);
	}
	
	function getTransferReceiveById($id){
		$db = $this->getAdapter();
		$sql = " SELECT t.* ,(SELECT tr.transfer_no FROM `rms_transferstock` AS tr WHERE tr.id=t.`transfer_id` LIMIT 1) AS transfer_no,
		(SELECT l.name FROM `tb_sublocation` AS l WHERE l.id=t.`from_location` AND l.status=1 LIMIT 1) AS location_name,
		(SELECT l.name FROM `tb_sublocation` AS l WHERE l.id=t.`to_location` AND l.status=1 LIMIT 1) AS to_location_name,
		(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=t.is_receive  AND v.type=18 LIMIT 1)AS `receive`
		FROM `rms_transfer_receive` AS t
		WHERE id=$id";
		return $db->fetchRow($sql);
	}
	
	function getReceiveItemsbyId($id){
		$db = $this->getAdapter();
		$sql = "SELECT  td.*,(SELECT m.`name` FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure,
		(SELECT c.`name` FROM `tb_category` AS c WHERE c.id=p.`cate_id` LIMIT 1) AS TYPE,
		p.`item_name` ,
		p.`qty_perunit` ,
		p.`item_code`,
		p.`unit_label`
			
		FROM `rms_transfer_received_detail` AS td,`tb_product` AS p
		WHERE td.`pro_id`=p.`id`
		AND td.`transfer_re_id`=$id";
		return $db->fetchAll($sql);
	}
	
	function getStaffRequestDetail($id){
		$db = $this->getAdapter();
		$sql = "SELECT  td.*
				FROM `rms_transferstock_detail` AS td,`rms_transferstock` AS t
				WHERE td.`transferid`=t.`id`
				AND td.`transferid`=$id";
		return $db->fetchAll($sql);
	}
	
	function getReceivedDetail($id){
		$db = $this->getAdapter();
		$sql = "SELECT  td.*
		FROM `rms_transfer_receive` AS td,`rms_transfer_received_detail` AS t
		WHERE td.`transferid`=t.`id`
		AND t.`transferid`=$id";
		return $db->fetchAll($sql);
	}
	
}