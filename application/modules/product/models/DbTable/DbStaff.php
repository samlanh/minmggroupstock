<?php

class Product_Model_DbTable_DbStaff extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_staff";
	public function setName($name)
	{
		$this->_name=$name;
	}
	function getAllStaff($search=null){
		$db = $this->getAdapter();
		$sql="SELECT s.id,s.`staff_no`,s.`staff_name`,
	       s.`phone`,(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=s.sex AND v.type=19 LIMIT 1)   AS `sex`,
           s.`car_number`,
           (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=s.`position_id` AND v.type=16 LIMIT 1)   AS `position`,
           s.`note`, 
           (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=s.`status` AND v.type=5 LIMIT 1)   AS `status`     
           FROM `tb_staff` AS s ";		
// 		$from_date =(empty($search['start_date']))? '1': " s.create_date >= '".$search['start_date']." 00:00:00'";
// 		$to_date = (empty($search['end_date']))? '1': " s.create_date <= '".$search['end_date']." 23:59:59'";
// 		$where = " AND ".$from_date." AND ".$to_date;
		$where=' WHERE  1 ';
		if($search['ad_search']!=""){
		    $s_search=addslashes(trim($search['ad_search']));
			$s_search = str_replace(' ', '', $s_search);
			$s_where[]="REPLACE(s.`staff_no`,' ','')   LIKE '%{$s_search}%'";
			$s_where[]="REPLACE(s.`staff_name`,' ','')   LIKE '%{$s_search}%'";
			$s_where[]="REPLACE(s.`phone`,' ','')   LIKE '%{$s_search}%'";
			$s_where[]="REPLACE(s.`car_number`,' ','')   LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search["status"])){
			$where.=' AND s.`status`='.$search["status"];
		}
		$order=" ORDER BY s.id DESC ";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
 	}
 	
	public function addCustomer($post)
	{
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$db=$this->getAdapter();
		$data=array(
 				'staff_name'		=> $post['staff_name'],
				'staff_no'			=> $post['staff_no'],
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
		$this->insert($data);
	}
	
	public function updateStaff($post){
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$db=$this->getAdapter();
		$data=array(
 				'staff_name'		=> $post['staff_name'],
				'staff_no'			=> $post['staff_no'],
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
		$where=" id=".$post['id'];
		$this->update($data, $where);
	}
	function getStaffByid($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM tb_staff WHERE id=$id";
    	return $db->fetchRow($sql);
    }
}