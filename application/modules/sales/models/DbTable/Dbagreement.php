<?php

class Sales_Model_DbTable_Dbagreement extends Zend_Db_Table_Abstract
{
	protected $_name="tb_agreement";
	function getAllProductName(){
		$sql=" SELECT id,branch_id,customer_id,agreement_no,
			user_id,all_total,STATUS,
			agreement_date,is_completed 
			FROM 
			`tb_agreement` ";
		return $this->getAdapter()->fetchAll($sql);
	}
	function getAllagreement($search){
		$db= $this->getAdapter();
		$sql="
		SELECT id,
		  (SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id  LIMIT 1) AS branch_name
		  ,(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=tb_agreement.customer_id LIMIT 1 ) AS customer_name
		  ,(SELECT phone FROM `tb_customer` WHERE tb_customer.id=tb_agreement.customer_id LIMIT 1 ) AS phone
		  ,agreement_no
		  ,agreement_date
		  ,all_total
		  ,(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = tb_agreement.user_id LIMIT 1) AS user_name
		  ,'បោះពុម្ភ'
		FROM 
		  `tb_agreement` ";
			
		$from_date =(empty($search['start_date']))? '1': " agreement_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " agreement_date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " agreement_no LIKE '%{$s_search}%'";
			$s_where[] = " remark LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch_id']>0){
			$where .= " AND branch_id = ".$search['branch_id'];
		}
		if($search['customer_id']>0){
			$where .= " AND customer_id =".$search['customer_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
// 	function getAllCustomerName(){
// 		$sql="SELECT id,CONCAT(cust_name,contact_name) AS name FROM `tb_customer` WHERE status=1 ";
// 		return $this->getAdapter()->fetchAll($sql);
// 	}
// 	function getProductById($product_id,$branch_id){
// 		$sql=" SELECT *,price as cost_price, 
// 			(SELECT qty FROM `tb_prolocation` WHERE pro_id=$product_id AND location_id=$branch_id LIMIT 1) AS qty,
// 			(SELECT price FROM `tb_product_price` WHERE type_id=1 AND pro_id=$product_id AND location_id=$branch_id LIMIT 1) as price,
// 			(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=measure_id) as measue_name
// 	 		FROM tb_product WHERE id=$product_id LIMIT 1"; 
// 		return $this->getAdapter()->fetchRow($sql);
// 	}
// 	function getProductByProductId($product_id,$location){
// 		$sql=" SELECT * FROM tb_prolocation WHERE pro_id = $product_id AND location_id = $location ";
// 		return $this->getAdapter()->fetchRow($sql);
// 	 }
	public function addAgreement($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
			$so = $data['invoice'];
	
			$agreement=array(
					"customer_id"   => $data['customer_id'],
					"branch_id"     => $data["branch_id"],
					"agreement_no"  => $data["agreement_no"],
					"net_total"     => $data['sub_total'],
					"tax"			=> $data["tax"],
					"all_total"     => $data['sub_total'],
					//"discount_value" => $data['discount'],
					"user_id"       => $GetUserId,
					"agreement_date" => date("Y-m-d",strtotime($data["agreement_date"])),
					"date"     => 	date("Y-m-d"),
					"term_condition" => $data['term_condition'],
			);
			$this->_name="tb_agreement";
			$agree_id = $this->insert($agreement);
			
			$ids=explode(',',$data['identity']);
			foreach ($ids as $i)
			{
				$data_item= array(
						'agreement_id'=> $agree_id,
						'pro_id'	  => $data['product_id'.$i],
						'qty_order'	  => $data['qty_sold'.$i],
						'price'		  => $data['price_'.$i],
						'erkta'		  => $data['erkta_'.$i],
 						//'disc_value'  => str_replace("%",'',$data['discount'.$i]),//check it
						'sub_total'	  => $data['sub_total'.$i],
				);
				$this->_name='tb_agreement_detail';
				$this->insert($data_item);
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			//echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function editAgreement($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
// 			$dbc=new Application_Model_DbTable_DbGlobal();
// 			$so = $data['invoice'];
			
			$agreement=array(
					"agreement_no"  => $data["agreement_no"],
					"customer_id"   => $data['customer_id'],
					"branch_id"     => $data["branch_id"],
					"net_total"     => $data['sub_total'],
					"tax"			=> $data["tax"],
					"all_total"     => $data['sub_total'],
					"user_id"       => $GetUserId,
					"agreement_date" => date("Y-m-d",strtotime($data["agreement_date"])),
					"date"          => 	date("Y-m-d"),
					"term_condition" => $data['term_condition'],
			);
			$this->_name="tb_agreement";
			$where="id = ".$data['id'];
			$this->update($agreement, $where);
			
			$this->_name='tb_agreement_detail';
			$where=" agreement_id = ".$data['id'];
			$this->delete($where);
			
			$ids=explode(',',$data['identity']);
			foreach ($ids as $i)
			{
				$data_item= array(
						'agreement_id'=> $data['id'],
						'pro_id'	  => $data['product_id'.$i],
						'qty_order'	  => $data['qty_sold'.$i],
						'price'		  => $data['price_'.$i],
						'erkta'		  => $data['erkta_'.$i],
						'sub_total'	  => $data['sub_total'.$i],
				);
				$this->_name='tb_agreement_detail';
				$this->insert($data_item);
			}
			
			$db->commit();
	
		}catch(Exception $e){
			$db->rollBack();
		}
	}
	function getAgreementById($id){
		$sql=" SELECT id,
		   customer_id,
		  (SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id  LIMIT 1) AS branch_name
		  ,(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=tb_agreement.customer_id LIMIT 1 ) AS customer_name
		  ,(SELECT phone FROM `tb_customer` WHERE tb_customer.id=tb_agreement.customer_id LIMIT 1 ) AS phone
		  ,agreement_no
		  ,agreement_date
		  ,all_total,term_condition
		FROM 
		  `tb_agreement` WHERE id = ".$id;
		return $this->getAdapter()->fetchRow($sql);
	}
	function getagreementDetailById($id){
		$sql=" SELECT g.*,
			item_name AS pro_name,erkta,
		   (SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=p.measure_id) as measue_name
		FROM tb_agreement_detail as g,
			tb_product AS p
		WHERE g.pro_id=p.id AND g.agreement_id= ".$id;
		return $this->getAdapter()->fetchAll($sql);
	}

	
}