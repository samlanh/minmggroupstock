<?php

class Purchase_Model_DbTable_DbRequestpurchasing extends Zend_Db_Table_Abstract
{	
	function getAllPurchaseOrder($search){//new
		$db= $this->getAdapter();
		$sql=" SELECT id,
		(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
		(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=tb_purchase_request.vendor_id LIMIT 1 ) AS vendor_name,
		order_number,DATE_FORMAT(date_order,'%d-%b-%Y')AS date_order,DATE_FORMAT(date_in,'%d-%b-%Y')AS date_in,
		invoice_no,
		net_total,paid,(net_total-paid) AS balance,
		(SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1) As purchase_statuss,
		(SELECT name_en FROM `tb_view` WHERE key_code =tb_purchase_request.status AND type=5 LIMIT 1) AS `status`,
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = user_mod LIMIT 1 ) AS user_name,purchase_status,is_approve
		FROM `tb_purchase_request` ";
		$from_date =(empty($search['start_date']))? '1': " date_order >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_order <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " order_number LIKE '%{$s_search}%'";
			$s_where[] = " net_total LIKE '%{$s_search}%'";
			$s_where[] = " paid LIKE '%{$s_search}%'";
			$s_where[] = " balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['suppliyer_id']>0){
			$where .= " AND vendor_id = ".$search['suppliyer_id'];
		}
		if($search['purchase_status']>0){
			$where .= " AND purchase_status =".$search['purchase_status'];
		}
		if($search['branch_id']>0){
			$where .= " AND branch_id = ".$search['branch_id'];
		}
		if($search['status_paid']>0){
			if($search['status_paid']==1){
				$where .= " AND balance <=0 ";
			}
			elseif($search['status_paid']==2){
				$where .= " AND balance >0 ";
			}
			
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getProductCostAndQty($pro_id){
		$db = $this->getAdapter();
		$sql="SELECT p.id,p.`price`,sum(pl.`qty`) as qty 
		FROM `tb_product` AS p ,`tb_prolocation` AS pl 
			WHERE 
			p.id=pl.`pro_id` AND p.id=$pro_id ";
		return $db->fetchRow($sql);
	}
	public function addPurchaseOrder($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$idrecord=$data['v_name'];
			$date= new Zend_Date();
			$number_transaction = $date->get('hh-mm-ss');
			$PO = "RQ";
			if($data['txt_order']==""){
				//$po["key_value"];
				$order_add=$PO.$number_transaction;
			}
			else{
				$order_add=$data['txt_order'];
			}
			$info_purchase_order=array(
					"vendor_id"      => 	$data['v_name'],
					"branch_id"      => 	$data["LocationId"],
					"order_number"   => 	$order_add,
					"date_order"     => 	date("Y-m-d",strtotime($data['order_date'])),
					"date_in"     	 => 	date("Y-m-d",strtotime($data['date_in'])),
					"modify_date"	 => 	date("Y-m-d H:i:s"),
					"purchase_status"=> 	0,
					"remark"         => $data['remark'],
					"all_total"      => $data['totalAmoun'],
					"discount_value" => $data['dis_value'],
					'discount_after' => $data['dis_value'],
					"discount_real"  => $data['global_disc'],
					"net_total"      => $data['all_totalpayment'],
					"net_totalafter" => $data['all_totalpayment'],
					"paid"           => $data['paid'],
					"paid_after"     => $data['paid'],
					"balance"        => $data['remain'],
					"balance_after"  => $data['remain'],
					//'invoice_no' 	 => $data['invoice_no'],
					"user_mod"       => $GetUserId,
					"date"      	 => new Zend_Date(),
					'commission'	 => $data['commission'],
					'commission_ensur'=>$data['commission_ensur'],
					'bank_name'		 => $data['bank_name'],
					'is_completed'	 => ($data['remain']==0)?1:0,
					"modify_date"	 => 	date("Y-m-d H:i:s"),
					'is_approve'	 => 0,
			);
			$this->_name="tb_purchase_request";
			$purchase_id = $this->insert($info_purchase_order);
			unset($info_purchase_order);
			 
			$ids=explode(',',$data['identity']);
			$locationid=$data['LocationId'];
			foreach ($ids as $i)
			{
				$data_item= array(
						'purchase_id' => 	$purchase_id,
						'pro_id'	  => 	$data['item_id_'.$i],
                        'qty_unit' 	  => 	$data['qty_unit_'.$i],
						'qty_order'	  => 	$data['qty'.$i],
						'qty_detail'  => 	$data['qty_per_unit_'.$i],
						'price'		  => 	$data['price'.$i],
						'disc_value'	  => $data['real-value'.$i],
						'sub_total'	  => $data['total'.$i],
						//'remark'	  => $data['remark_'.$i]
						//'total_befor' => 	$data['total'.$i],
				);
				$this->_name='tb_purchase_request_item';
				$this->insert($data_item);
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	public function updatePurchaseOrder($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$id=$data['id'];
			$this->_name='tb_purchase_request_item';
			$where =" purchase_id=".$data['id'];
			$this->delete($where);
				
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
	
			$idrecord=$data['v_name'];
			$order_add=$data['txt_order'];
			$info_purchase_order=array(
					"vendor_id"      => 	$data['v_name'],
					"branch_id"      => 	$data["LocationId"],
					"order_number"   => 	$order_add,
					"date_order"     => 	date("Y-m-d",strtotime($data['order_date'])),
					"date_in"     	 => 	date("Y-m-d",strtotime($data['date_in'])),
					"purchase_status"=> 	0,
					"remark"         => 	$data['remark'],
					"all_total"      => 	$data['totalAmoun'],
					"invoice_no"	 =>     $data['invoice_no'],
					"discount_value" => 	$data['dis_value'],
					'discount_after' => 	$data['dis_value'],
					"discount_real"  => 	$data['global_disc'],
					"net_total"      => 	$data['all_totalpayment'],//$data['all_totalpayment'] which 1 choose .
					"net_totalafter" =>     $data['all_totalpayment'],
					"paid"           => 	$data['paid'],
					"paid_after"     => 	$data['paid'],
					"balance"        => 	$data['remain'],
					"balance_after"  => 	$data['remain'],
					"user_mod"       => 	$GetUserId,
					"date"      	=> 	new Zend_Date(),
					'invoice_no' 	=> 	$data['invoice_no'],
					'status'	    => $data['status_use'],
					'commission'    => 	$data['commission'],
					'commission_ensur'=> 	$data['commission_ensur'],
					'bank_name'		=> 	$data['bank_name'],
					'is_approve'	=> 0,
			);
			$this->_name="tb_purchase_request";
			$where=" id=".$data['id'];
			$purchase_id = $this->update($info_purchase_order, $where);
			$purchase_id=$data['id'];
			unset($info_purchase_order);
				
			$ids=explode(',',$data['identity']);
			$locationid=$data['LocationId'];
			if(!empty($data['identity']))foreach ($ids as $i)
			{
				$data_item= array(
						'purchase_id' => $data['id'],
						'pro_id'	  => $data['item_id_'.$i],
						'qty_order'	  => $data['qty'.$i],
						'qty_unit'    => $data['qty_unit_'.$i],
						'qty_detail'  => $data['qty_per_unit_'.$i],
						'price'		  => $data['price'.$i],
						'disc_value'  => $data['real-value'.$i],
						'sub_total'	  => $data['total'.$i],
				);
				$this->_name='tb_purchase_request_item';
				$this->insert($data_item);
			}		
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	
	public function getVendorId($id){//just new
		$db=$this->getAdapter();
		$sql = "SELECT p.*,(SELECT title FROM `tb_income_expense` WHERE po_id=p.`id` LIMIT 1)  AS po_x FROM `tb_purchase_nonstock` AS p WHERE id=$id LIMIT 1 ";
		$rows=$db->fetchRow($sql);
		return $rows;
	}
	
	public function getPurchaseID($id){
		$db = $this->getAdapter();
		$sql = "SELECT CONCAT(p.item_name,'(',p.item_code,' )') AS item_name , p.qty_perunit,od.order_id, od.pro_id, od.qty_order,
		od.price, od.total_befor, od.disc_type,	od.disc_value, od.sub_total, od.remark 
		FROM tb_purchase_order_item AS od
		INNER JOIN tb_product AS p ON p.pro_id=od.pro_id WHERE od.order_id=".$id;
		$row = $db->fetchAll($sql);
		return $row;
	}
	
	public function getPurchaseById($id){//just new 
		$db=$this->getAdapter();
		$sql = "SELECT p.*,(SELECT title FROM `tb_income_expense` WHERE po_id=p.`id` LIMIT 1)  AS po_x FROM `tb_purchase_request` AS p WHERE id=$id LIMIT 1 ";
		$rows=$db->fetchRow($sql);
		return $rows;
	}
	public function getPurchaseDetailById($id){//just new
		$db=$this->getAdapter();
		$sql = "SELECT * FROM `tb_purchase_request_item` WHERE purchase_id=$id ";
		$rows=$db->fetchAll($sql);
		return $rows;
	}
	public function recieved_info($order_id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_recieve_order WHERE order_id=".$order_id." LIMIT 1";		
		$row =$db->fetchRow($sql);
		return $row;
	}
	//for get left order address change form showsaleorder to below
	public function showPurchaseOrder(){
		$db= $this->getAdapter();
		$sql = "SELECT p.order_id, p.order, p.date_order, p.status, v.v_name, p.all_total,p.paid,p.balance
		FROM tb_purchase_order AS p  INNER JOIN tb_vendor AS v ON v.vendor_id=p.vendor_id";
		$row=$db->fetchAll($sql);
		return $row;
		
	}
	public function getVendorInfo($post){
		$db=$this->getAdapter();
		$sql="SELECT contact_name,phone, add_name AS address 
		FROM tb_vendor WHERE vendor_id = ".$post['vendor_id']." LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	
}