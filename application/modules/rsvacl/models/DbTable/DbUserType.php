<?php

class Rsvacl_Model_DbTable_DbUserType extends Zend_Db_Table_Abstract
{

    protected $_name = 'tb_acl_user_type';
	//function for getting record user_type by user_type_id
	
	public function deleteUserType($id){
		$db = $this->getAdapter();
		$sql = "DELETE FROM tb_acl_user_type WHERE user_type_id IN($id)";
		$db->query($sql);
		
		$sql ="DELETE FROM tb_acl_user_access WHERE user_type_id IN($id)";
		$db->query($sql);
	}
	public function getAclByUserType($acl_id,$user_type_id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  ua.`acl_id` 
				FROM
				  `tb_acl_user_access` AS ua 
				WHERE ua.`user_type_id` = '".$user_type_id."' 
				  AND ua.`acl_id` = '".$acl_id."'";
		return $db->fetchRow($sql);
	}
	public function getAclBySubParent($parent,$user_type_id){
		$db = $this->getAdapter();
		$sql = "SELECT 
		  aa.`lable` ,
		  aa.`acl_id`,
		  aa.status
		FROM
		  `tb_acl_acl` AS aa 
		WHERE aa.`sub_parent` = $parent AND aa.`is_sub_parent`=0 AND aa.status!=2 ORDER BY aa.rank";
		return $db->fetchAll($sql);
	}
	function getAllAclUserByUserType($user_type_id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  ua.`acl_id` 
				FROM
				  `tb_acl_user_access` AS ua 
				WHERE ua.`user_type_id` = '".$user_type_id."'" ;
		 $rs_acl = $db->fetchAll($sql);
		 $value='';
		 
		 if(!empty($rs_acl)){
			foreach($rs_acl as $key=>$row){$index =$key+1;
				if($index==1){
					$value = $rs_acl[0]["acl_id"];
				}else{
					$value = $value.",".$row["acl_id"];
				}
			}
		}
		
		return $value;
	}
	public function getAllAclSubParentUserType($parent){
		$db=$this->getAdapter();
		$sql = "SELECT 
				  aa.`sub_parent`,
				  aa.`lable` ,
				  (SELECT ac.`lable` FROM `tb_acl_acl` AS ac WHERE ac.`acl_id`=aa.`parent` LIMIT 1) AS title
				FROM
				  `tb_acl_acl` AS aa 
				WHERE aa.parent=$parent
				  AND aa.status = 1 
					AND aa.is_sub_parent=1
				  
				   ";
  		$row=$db->fetchAll($sql);
  		return $row;
	}
	public function getUserType($user_id)
	{
		$select=$this->select();		
		$select->where('user_type_id=?',$user_id);
		$row=$this->fetchRow($select);
		if(!$row) return NULL;
		return $row->toArray();
	}
	//get user name
	public function getAclParent(){
		$db = $this->getAdapter();
		$sql ="SELECT c.`acl_id`,c.`lable` FROM `tb_acl_acl` AS c WHERE c.`is_parent`=1";
		return $db->fetchAll($sql);
	}
	
	public function getAclByParent($id){
		$db = $this->getAdapter();
		$sql ="SELECT c.* FROM `tb_acl_acl` AS c WHERE c.`status`!=2 AND c.`parent`=$id";
		return $db->fetchAll($sql);
	}
	public function getUserTypeName($user_id)
	{
		$select=$this->select();
		$select->from($this,'user_type')
			->where("user_type_id=?",$user_id);
		$row=$this->fetchRow($select);
		if(!$row) return null; 
		return $row['user_type'];
	}
	//get infomation of user
	public function getUserTypeInfo($sql)
	{
		$db=$this->getAdapter();
  		$stm=$db->query($sql);
  		$row=$stm->fetchAll();
  		if(!$row) return NULL;
  		return $row;
	}
	//function get user id from database
	public function getUserTypeID($username)
	{
		$select=$this->select();
			$select->from($this,'user_type_id')
			->where('user_type=?',$username);
		$row=$this->fetchRow($select);
		if(!$row) return NULL;
		return $row['user_type_id'];
	}
	//function retrieve record users by column 
	public function getUsers($column)
	{		
		$sql='user_id not in(select user_id from pdbs_acl) AND status=1 ';	
		$select=$this->select();
		$select->from($this,$column)
			   ->where($sql);
		$row=$this->fetchAll($select);
		if(!$row) return NULL;		
		return $row->toArray();
	}
	//function check user have exist
	public function isUserTypeExist($username)
	{
		$select=$this->select();
		$select->from($this,'user_type')
			->where("user_type=?",$username);
		$row=$this->fetchRow($select);
		if(!$row) return false;
		return true;
	}
	//function check id number have exist
	public function isIdNubmerExist($id_number)
	{
		$select=$this->select();
		$select->from($this,'id_number')
			->where("id_number=?",$id_number);
		$row=$this->fetchRow($select);
		if(!$row) return false;
		return true;
	}
	//add user
	public function insertUserType($arr)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
		$identity = $arr["identity"];
		$ids=explode(',',$arr['identity']);
		$data=array(); 
		
		$data['user_type']=$arr['user_type'];  
		$data['parent_id']=$arr['parent_id'];  	
     	$data['status']='1';
     	//print_r($data);exit;
    	 $id = $this->insert($data); 
		 
		 
		 if($identity!=""){
			 foreach($ids as $i){
				  $sql_sub_parent = "SELECT a.`sub_parent` FROM `tb_acl_acl` AS a WHERE a.`acl_id`='".@$arr["check_".$i]."'";
				 // echo $sql_sub_parent;exit();
				  $rs_sub_parent = $db->fetchOne($sql_sub_parent);
				  $sql_rs = "SELECT au.`acl_id` FROM `tb_acl_user_access` AS au WHERE au.`user_type_id`=$id AND au.`acl_id`='$rs_sub_parent'";
				  $rs = $db->fetchOne($sql_rs);
				  if(empty($rs)){
					 $arr_ac = array(
						'acl_id'		=>	$rs_sub_parent,
						'user_type_id'	=>	$id,
						'status'		=>	1,
					 );
					 $this->_name = "tb_acl_user_access";
					 $this->insert($arr_ac);
				  }
				 $arr_ac = array(
					'acl_id'		=>	$arr["check_".$i],
					'user_type_id'	=>	$id,
					'status'		=>	1,
				 );
				 $this->_name = "tb_acl_user_access";
				 $this->insert($arr_ac);
			 }
			 
			
			 
		 }
		 $db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);	
		}
	}	
	//update user
	public function updateUserType($arr,$user_type_id)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$identity = $arr["identity"];
			$ids=explode(',',$arr['identity']);
			$data=array(); 	
			
			$data['user_type']=$arr['user_type'];   
			$data['parent_id']=$arr['parent_id']; 	
			$where=$this->getAdapter()->quoteInto('user_type_id=?',$user_type_id);
			$this->update($data,$where); 
			
			$sql = "DELETE FROM tb_acl_user_access WHERE user_type_id="."'".$user_type_id."'";
			$db->query($sql);
			if($identity!=""){
				 foreach($ids as $i){
					 $sql_sub_parent = "SELECT a.`sub_parent`,a.acl_id FROM `tb_acl_acl` AS a WHERE a.`acl_id`='".@$arr["check_".$i]."'";
					$rs_sub_parent = $db->fetchRow($sql_sub_parent);
				  
				  $sql_rs = "SELECT au.`acl_id` FROM `tb_acl_user_access` AS au WHERE au.`user_type_id`=$user_type_id AND au.`acl_id`= (SELECT a.`acl_id` FROM `tb_acl_acl` AS a WHERE a.`is_sub_parent`=1 AND a.`sub_parent`='".$rs_sub_parent["sub_parent"]."')";
				  $rs = $db->fetchOne($sql_rs);
				  
				  $sub_parent_id =  "SELECT a.acl_id FROM `tb_acl_acl` AS a WHERE a.is_sub_parent=1 AND a.`sub_parent`='".$rs_sub_parent["sub_parent"]."'";
				  $rs_sub_parent_id = $db->fetchOne($sub_parent_id);
				  if(empty($rs)){
					 $arr_ac = array(
						'acl_id'		=>	$rs_sub_parent_id,
						'user_type_id'	=>	$user_type_id,
						'status'		=>	1,
					 );
					 $this->_name = "tb_acl_user_access";
					 $this->insert($arr_ac);
				  }
					 $arr_ac = array(
						'acl_id'		=>	@$arr["check_".$i],
						'user_type_id'	=>	$user_type_id,
						'status'		=>	1,
					 );
					 $this->_name = "tb_acl_user_access";
					 $this->insert($arr_ac);
				 }
				 
			 }
			 //exit();
			 $db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);	
		}
	}
	
	public function updateUserTypeAccess($arr,$user_type_id)
	{
		//print_r($arr); exit;
		$data=array(); 	  		
        $data['user_type']=$arr;	
        //echo $data['user_type'].$user_type_id; exit;
    	$where=$this->getAdapter()->quoteInto('user_type_id=?',$user_type_id);
		$this->update($data,$where); 
	}
	//function dupdate field status user to force use become inaction
	public function inactiveUser($user_id)
	{
		$data=array('status'=>0);
		$where=$this->getAdapter()->quoteInto('user_id=?',$user_id);
		$this->update($data,$where);
	}
	function  getUserProfileById($id){
		$db=$this->getAdapter();
		$sql="SELECT 
				  u.user_id,
				  u.fullname,
				  u.username,
				  u.email,
				  u.photo,
				  (SELECT ud.`pob` FROM `tb_acl_user_detail` AS ud WHERE ud.`user_id`=u.`user_id`) AS pob,
				  (SELECT ud.`dob` FROM `tb_acl_user_detail` AS ud WHERE ud.`user_id`=u.`user_id`) AS dob,
				  (SELECT ud.`phone` FROM `tb_acl_user_detail` AS ud WHERE ud.`user_id`=u.`user_id`) AS phone,
				  (SELECT ud.`address` FROM `tb_acl_user_detail` AS ud WHERE ud.`user_id`=u.`user_id`) AS address,
				  (SELECT ud.`position` FROM `tb_acl_user_detail` AS ud WHERE ud.`user_id`=u.`user_id`) AS `position`,
				  (SELECT ud.`decription` FROM `tb_acl_user_detail` AS ud WHERE ud.`user_id`=u.`user_id`) AS decription
				FROM
				  tb_acl_user AS u
				WHERE  u.user_id =$id";
		return $db->fetchRow($sql);
	}
	
	//update user profile 
	public function updateUser($arr)
	{
		//check update password
		$id_user=$arr['id'];
		if(!empty($arr['old_password']) && !empty($arr['password']) && !empty($arr['confirm_password'])){
			$arr['password']=md5($arr['password']);
			$arr['confirm_password']=md5($arr['confirm_password']);
			
			$arr=array(
					"password"			=>	$arr["password"],
					"confirm_pass"		=>	$arr["confirm_password"],
			);
			$this->_name="tb_acl_user";
			$where=$this->getAdapter()->quoteInto('user_id=?',$id_user);
			$this->update($arr, $where);
		}else if(!empty($arr['olde_user_name'])){
			
			$photoname = str_replace(" ", "_", $arr['olde_user_name']).rand(). '.jpg';
			$upload = new Zend_File_Transfer();
			$upload->addFilter('Rename',
					array('target' => PUBLIC_PATH . '/images/'. $photoname, 'overwrite' => true) ,'pic');
			$receive = $upload->receive();
			if($receive)
			{
				$arr['photo'] = $photoname;
			}
			else{
				$arr['photo']=$arr['old_pic'];
			}
			$data = array(
					"photo"		=>	$arr['photo'],
			);
			$where=$this->getAdapter()->quoteInto('user_id=?',$arr['id']);
			$this->_name="tb_acl_user";
			$id=$this->update($data, $where);
			
		}else{
			try{
				$db=$this->getAdapter();
				$db->beginTransaction();
				$data = array(
						"fullname"		=>	$arr["full_name"],
						"username"		=>	$arr["user_name"],
						"email"			=>	$arr["email"],
						"created_date"	=>	date("Y-m-d H:i:s"),
						"modified_date" =>  date("Y-m-d H:i:s")
				);
				$where=$this->getAdapter()->quoteInto('user_id=?',$arr['id']);
				$this->_name="tb_acl_user";
				$id=$this->update($data, $where);
				///inset to tb_acl_user_detail
				$data = array(
						"user_id"		=>  $arr['id'],
						"phone"			=>	$arr["phone"],
						"pob"			=>	$arr['pob'],
						"dob"			=>	$arr["dob"],
						"email"			=>	$arr["email"],
						"decription"			=>	$arr["descript"],
				);
				$this->_name="tb_acl_user_detail";
				$where="user_id=".$arr['id'];
				$this->_name="tb_acl_user_detail";
				$this->update($data,$where);
				$db->commit();
				//exit();
			}
			catch (Exception $e){
				$db->rollBack();
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				echo $err;exit();
			}
		}
	}
}

