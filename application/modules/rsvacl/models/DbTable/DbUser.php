<?php

class RsvAcl_Model_DbTable_DbUser extends Zend_Db_Table_Abstract
{

    protected $_name = 'tb_acl_user';
	//function for getting record user by user_id
	public function getUser($user_id)
	{
		$select=$this->select();		
		$select->where('user_id=?',$user_id);
		$row=$this->fetchRow($select);
		if(!$row) return NULL;
		return $row->toArray();
	}
	function deleteUser($id){
		$db = $this->getAdapter();
		$sql = "DELETE FROM tb_acl_user WHERE user_id in($id)";
		$db->query($sql);
	}
	//get user name
	public function getUserName($user_id)
	{
		$select=$this->select();
		$select->from($this,'username')
			->where("user_id=?",$user_id);
		$row=$this->fetchRow($select);
		if(!$row) return null; 
		return $row['username'];
	}
	//change password user wanted
	public function changePassword($user_id,$password)
	{
		$data=array('password'=>$password);
		$where=$this->getAdapter()->quoteInto('user_id=?',$user_id);
		$this->update($data,$where);
	}
	//is valid password
	public function isValidCurrentPassword($user_id,$current_password)
	{
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$level = $result["level"];
		if($level==1){
			return true;
		}else{
		$select=$this->select();
		$select->from($this,'password')
			->where("user_id=?",$user_id);
		$row=$this->fetchRow($select);
		if($row){
			$current_password=md5($current_password);
			$password=$row['password'];			 
			if($password==$current_password)
				return true;
		}
		return false;
		}
	}
	//get infomation of user
	public function getUserInfo($sql)
	{
		$db=$this->getAdapter();
  		$stm=$db->query($sql);
  		$row=$stm->fetchAll();
  		if(!$row) return NULL;
  		//print_r($row.''.$fiel);exit();
  		return $row;
	}
	function getUserById($user_id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM tb_acl_user where user_id=$user_id";
		return $db->fetchRow($sql);
	}
	//function get user id from database
	public function getUserID($username)
	{
		$select=$this->select();
			$select->from($this,'user_id')
			->where('username=?',$username);
		$row=$this->fetchRow($select);
		if(!$row) return NULL;
		return $row['user_id'];
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
	public function isUserExist($username)
	{
		$select=$this->select();
		$select->from($this,'username')
			->where("username=?",$username);
		$row=$this->fetchRow($select);
		if(!$row) return false;
		return true;
	}
	public function ifUserExist($username)
	{
		$db=$this->getAdapter();
		$names=str_replace(' ','',$username);
		$sql = "SELECT user_id FROM tb_acl_user WHERE REPLACE(`username`,' ','')= '".$names."' LIMIT 1";
		$row = $db->fetchRow($sql);
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
	public function insertUser($arr)
	{ 
		$part= PUBLIC_PATH.'/images/user/';
		if (!file_exists($part)) {
			mkdir($part, 0777, true);
		}
		$photoname = str_replace(" ", "_", $arr['username']);
		$photo = "";
		$name = $_FILES['signature']['name'];
		if (!empty($name)){
			$ss = 	explode(".", $name);
			$image_name = "signature_".date("Y").date("m").date("d").time()."_".$photoname.".".end($ss);
			$tmp = $_FILES['signature']['tmp_name'];
			if(move_uploaded_file($tmp, $part.$image_name)){
				$photo = $image_name;
			}
			else
				$string = "Image Upload failed";
		}
		
// 		$photoname = str_replace(" ", "_", $arr['username']). '.jpg';
// 		$upload = new Zend_File_Transfer();
// 		$upload->addFilter('Rename',
// 				array('target' => PUBLIC_PATH . '/images/'. $photoname, 'overwrite' => true) ,'signature');
// 		$receive = $upload->receive();
// 		if($receive)
// 		{
// 			$arr['photo'] = $photoname;
// 		}
// 		else{
// 			$arr['photo']="";
// 		}
		
		try{
			$db=$this->getAdapter();
			$db->beginTransaction();
			$arr['password']=md5($arr['password']);
			$arr['confirm_password']=md5($arr['confirm_password']);
	     	$array_data = array(
	     			"title"			=>	$arr["title"],
	     			"fullname"		=>	$arr["fullname"],
	     			"username"		=>	$arr["username"],
	     			"password"		=>	$arr['password'],
	     			//"confirm_pass"	=>	$arr['confirm_password'],
// 	     			"photo"			=>	$arr['photo'],
	     			
	     			"email"			=>	$arr["email"],
	     			"user_type_id"	=>	$arr["user_type_id"],
	     			"LocationId"	=>	$arr["LocationId"],
	     			"status"		=>	$arr["status"],
	     			"photo"		=>	$photo,
	     			"created_date"	=>	date("Y-m-d H:i:s")
	     			);
	     	$id=$this->insert($array_data);
	     	$ids = explode(",", $arr["identity"]);
	     	foreach ($ids as $i){
	     		$exist=$this->getUserBranchExist($id, $arr["location_id_".$i]);
	     		if($exist=="" AND $arr["LocationId"]!==$arr["location_id_".$i]){
	     			$_arrdata = array(
	     					"user_id"=>$id,
	     					"location_id"=>$arr["location_id_".$i]
	     			);
	     			$db->insert("tb_acl_ubranch", $_arrdata);
	     		}
	     	}
	     	$_arrdata = array(
	     			"user_id"=>$id,
	     			"location_id"=>$arr["LocationId"]
	     	);
	     	$db->insert("tb_acl_ubranch", $_arrdata);
	     	
	     	///inset to tb_acl_user_detail
	     	$data = array(
	     			"user_id"		=>	$id,
	     			"department"	=>	$arr["department"],
	     			"code"			=>	$arr["code"],
	     			"name"	=>	$arr["name"],
	     			"phone"			=>	$arr["phone"],
	     			"pob"			=>	$arr['pob'],
	     			"dob"			=>	$arr["dob"],
	     			"address"			=>	$arr["address"],
	     			"email"			=>	$arr["email_detail"],
	     			"position"			=>	$arr["job_title"],
	     			"decription"			=>	$arr["description"],
	     	);
	     	$this->_name="tb_acl_user_detail";
	     	$id=$this->insert($data);
	     	$db->commit();
     	}catch (Exception $e){
     		$db->rollBack();
     		$err = $e->getMessage();
     		Application_Model_DbTable_DbUserLog::writeMessageError($err);
     	}
	}
	
	public function getUserBranchExist($user_id, $location_id){
		$db=$this->getAdapter();
		$sql="SELECT user_loca FROM tb_acl_ubranch WHERE user_id = $user_id AND location_id = $location_id LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	function checkPassword($data){
		$db=$this->getAdapter();
		$password = str_replace(' ','',$data);
		$password = md5($password);
		//print_r($password);exit();
		$sql="SELECT `password` FROM tb_acl_user WHERE REPLACE(`password`,' ','')='$password' LIMIT 1 ";
		$row=$db->fetchRow($sql);
		if(empty($row)){
			return 0;
		}else{
			return 1;
		}
	}
	public function updateUser($arr,$user_id)
	{
		//check update password
		if(!empty($arr['current_password']) && !empty($arr['password']) && !empty($arr['confirm_password'])){
			$arr['password']=md5($arr['password']);
			$arr['confirm_password']=md5($arr['confirm_password']);
				$arr=array(
						"password"			=>	$arr["password"],
						"confirm_pass"		=>	$arr["confirm_password"],
						);
				$where="user_id=".$user_id;
				$this->_name="tb_acl_user";
				$this->update($arr, $where);
		}
		$part= PUBLIC_PATH.'/images/user/';
		if (!file_exists($part)) {
			mkdir($part, 0777, true);
		}
		
// 	    $photoname = str_replace(" ", "_", $arr['username']). '.jpg';
// 		$upload = new Zend_File_Transfer();
// 		$upload->addFilter('Rename',
// 				array('target' => PUBLIC_PATH . '/images/'. $photoname, 'overwrite' => true) ,'signature');
// 		$receive = $upload->receive();
// 		if($receive)
// 		{
// 			$arr['photo'] = $photoname;
// 		}
// 		else{
// 			$arr['photo']=$arr["old_pic"];
// 		}
		try{
			$db=$this->getAdapter();
			$db->beginTransaction();
			$data = array(
					"title"			=>	$arr["title"],
					"fullname"		=>	$arr["fullname"],
					"username"		=>	$arr["username"],
// 					"photo"			=>	$arr['photo'],
					"email"			=>	$arr["email"],
					"user_type_id"	=>	$arr["user_type_id"],
					"LocationId"	=>	$arr["LocationId"],
					"status"		=>	$arr["status"],
					"created_date"	=>	date("Y-m-d H:i:s")
			);
			
			
			$photoname = str_replace(" ", "_", $arr['username']);
			$photo = "";
			$name = $_FILES['signature']['name'];
			if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "signature_".date("Y").date("m").date("d").time()."_".$photoname.".".end($ss);
				$tmp = $_FILES['signature']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					if (file_exists($part.$arr["old_photo"])) {
						unlink($part.$arr["old_photo"]);//delete old file
					}
// 					$photo = $image_name;
					$data["photo"] = $image_name;
				}
				else
					$string = "Image Upload failed";
			}
			
			$where=$this->getAdapter()->quoteInto('user_id=?',$user_id);
			$id=$this->update($data, $where);
			$ids = explode(",", $arr["identity"]);
			$db->query("DELETE FROM tb_acl_ubranch WHERE user_id = $user_id");
			foreach ($ids as $i){
				$exist=$this->getUserBranchExist($user_id, $arr["location_id_".$i]);
				if($exist=="" AND $arr["LocationId"]!==$arr["location_id_".$i]){
					$_arrdata = array(
							"user_id"=>$user_id,
							"location_id"=>$arr["location_id_".$i]
					);
					$db->insert("tb_acl_ubranch", $_arrdata);
				}
					
			}
			$_arrdata = array(
					"user_id"=>$user_id,
					"location_id"=>$arr["LocationId"]
			);
			$db->insert("tb_acl_ubranch", $_arrdata);
			
			///inset to tb_acl_user_detail
			$data = array(
					"user_id"		=>	$user_id,
					"department"	=>	$arr["department"],
					"code"			=>	$arr["code"],
					"name"	=>	$arr["name"],
					"phone"			=>	$arr["phone"],
					"pob"			=>	$arr['pob'],
					"dob"			=>	$arr["dob"],
					"address"			=>	$arr["address"],
					"email"			=>	$arr["email_detail"],
					"position"			=>	$arr["job_title"],
					"decription"			=>	$arr["description"],
			);
			$this->_name="tb_acl_user_detail";
			$where="user_id=".$user_id;
			$this->update($data,$where);
			$db->commit();
		}
		catch (Exception $e){
			$db->rollBack();
			$err = $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
		
		
	}
	
	function getUserDetailById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM tb_acl_user_detail WHERE user_id=$id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	//function dupdate field status user to force use become inaction
	public function inactiveUser($user_id)
	{
		$data=array('status'=>0);
		$where=$this->getAdapter()->quoteInto('user_id=?',$user_id);
		$this->update($data,$where);
	}
	public function userAuthenticate($username,$password)
	{
		try{
	              $db_adapter = $this->getDefaultAdapter(); 
	              $auth_adapter = new Zend_Auth_Adapter_DbTable($db_adapter);
	              
	              $auth_adapter->setTableName('rsv_acl_user') // table where users are stored
	                           ->setIdentityColumn('username') // field name of user in the table
	                           ->setCredentialColumn('password') // field name of password in the table
	                           ->setCredentialTreatment('MD5(?) AND status=1'); // optional if password has been hashed
	 
	              $auth_adapter->setIdentity($username); // set value of username field
	              $auth_adapter->setCredential($password);// set value of password field
	 
	              //instantiate Zend_Auth class
	              $auth = Zend_Auth::getInstance();
	 
	              $result = $auth->authenticate($auth_adapter);
	 
	              if($result->isValid()){
	              	  return true;
	              }else{
	                  // validation errors here
					  return false;
	              }
		}catch(Zend_Exception $ex){}
	}
  
}

