<?php

class Product_Model_DbTable_DbBranch extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_sublocation";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function add($data){
		//print_r($data);exit();
		$db = $this->getAdapter();
		$part= PUBLIC_PATH.'/images/logo/';
		if (!file_exists($part)) {
			mkdir($part, 0777, true);
		}
		$photo = $_FILES['logo'];
			if($photo["name"]!=""){
				$temp = explode(".", $photo["name"]);
				$newfilename ="logo-".date("Y").date("m").date("d").time(). '.' . end($temp);
				move_uploaded_file($_FILES['logo']["tmp_name"], $part . $newfilename);
				$photo_name = $newfilename;
			}
		$arr = array(
			'name'			=>	$data["branch_name"],
			'branch_code'	=>	$data["code"],
			'prefix'		=>	$data["prefix"],
			'contact'		=>	$data["contact"],
			'phone'			=>	$data["contact_num"],
			'email'			=>	$data["email"],
			'office_tel'	=>	$data["office_num"],
			'fax'			=>	$data["fax"],
			'address'		=>	$data["address"],
			'user_id'		=>	$this->getUserId(),
			'last_mod_date'	=>	new Zend_Date(),
			'status'		=>	$data["status"],
			'remark'		=>	$data["remark"],
			'show_by'		=>	$data["show_by"],
			'logo'			=>	$photo_name,
		);
		$this->_name = "tb_sublocation";
		$this->insert($arr);
		
	}
	
	public function edit($data){
		//print_r($data);exit();
		$db = $this->getAdapter();
		$part= PUBLIC_PATH.'/images/logo/';
		if (!file_exists($part)) {
			mkdir($part, 0777, true);
		}
		
		$arr = array(
				'name'			=>	$data["branch_name"],
				'branch_code'	=>	$data["code"],
				'prefix'		=>	$data["prefix"],
				'contact'		=>	$data["contact"],
				'phone'			=>	$data["contact_num"],
				'email'			=>	$data["email"],
				'office_tel'	=>	$data["office_num"],
				'fax'			=>	$data["fax"],
				'address'		=>	$data["address"],
				'user_id'		=>	$this->getUserId(),
				'last_mod_date'	=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
				'show_by'		=>	$data["show_by"],
// 				'logo'			=>	$photo_name,
		);
		$photo = $_FILES['logo'];
		if($photo["name"]!=""){
			$temp = explode(".", $photo["name"]);
			$newfilename ="logo-".$data["id"].date("Y").date("m").date("d").time().'.' . end($temp);
			$tmp = $_FILES['logo']['tmp_name'];
			if(move_uploaded_file($tmp, $part.$newfilename)){
				if (file_exists($part.$data["old_photo"])) {
					unlink($part.$data["old_photo"]);//delete old file
				}
				$arr['logo']=$newfilename;
			}
		}
		$this->_name = "tb_sublocation";
		$where = $db->quoteInto("id=?", $data["id"]);
		$this->update($arr, $where);
	
	}
	
	public function getAllBranch($data){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  s.`id`,
				  s.`name`,
				  s.`logo`,
				  s.`branch_code`,
				  s.prefix,
				  s.`contact`,
				  s.`phone`,
				  s.`email`,
				  s.`office_tel`,
				  s.`address`,
				  s.`status` 
				FROM
				  `tb_sublocation` AS s where 1 ";
		$where='';
		if($data["branch_name"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['branch_name']));
			$s_where[]= " s.`name` LIKE '%{$s_search}%'";
			$s_where[]=" s.`contact` LIKE '%{$s_search}%'";
			$s_where[]=" s.`phone` LIKE '%{$s_search}%'";
			$s_where[]=" s.`branch_code` LIKE '%{$s_search}%'";
			$s_where[]=" s.`prefix` LIKE '%{$s_search}%'";
			$s_where[]=" s.`email` LIKE '%{$s_search}%'";
			$s_where[]=" s.`office_tel` LIKE '%{$s_search}%'";
			$s_where[]=" s.`address` LIKE '%{$s_search}%'";
			//$s_where[]= " cate LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["status"]!=""){
			$where.=' AND s.status='.$data["status"];
		}
		//echo $sql;
		return $db->fetchAll($sql.$where);
	}
	
	public function getBranchById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  s.`id`,
				  s.`branch_code` AS code,
				  s.prefix,
				  s.`name`,
				  s.`contact`,
				  s.`phone`,
				  s.`fax`,
				  s.remark,
				  s.`email`,
				  s.`office_tel`,
				  s.`address`,
				  s.`status`,
					s.show_by,
					s.logo
				FROM
				  `tb_sublocation` AS s 
				WHERE s.id = $id";
		return $db->fetchRow($sql);
	}
}