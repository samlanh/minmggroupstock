<?php 
class Rsvacl_Form_FrmUser extends Zend_Form
{
	public function init($data=null)
    {
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();	
    	$db=new Application_Model_DbTable_DbGlobal();

    	//user typefilter
		$sql = 'SELECT user_type_id,user_type FROM tb_acl_user_type';
		$rs=$db->getGlobalDb($sql);
		$options=array('All User Type');
		$usertype = $request->getParam('user_type_filter');
		foreach($rs as $read) $options[$read['user_type_id']]=$read['user_type'];
		$user_type_filter=new Zend_Form_Element_Select('user_type_filter');
    	$user_type_filter->setMultiOptions($options);
    	$user_type_filter->setAttribs(array(
    		'id'=>'user_type_filter',
    		'class'=>'form-control',
    		'onchange'=>'this.form.submit()',
    	));
    	$user_type_filter->setValue($usertype);
    	$this->addElement($user_type_filter);
		
		$sql = 'SELECT p.`id`,p.`name` FROM `tb_sublocation` AS p';
		$rs=$db->getGlobalDb($sql);
		$options=array('All Location');
		$location_r = $request->getParam('location');
		foreach($rs as $read) $options[$read['id']]=$read['name'];
		$location=new Zend_Form_Element_Select('location');
    	$location->setMultiOptions($options);
    	$location->setAttribs(array(
    		'id'=>'user_type_filter',
    		'class'=>'form-control',
    		'onchange'=>'this.form.submit()',
    	));
    	$location->setValue($location_r);
    	$location->setValue($request->getParam('location'));
    	$this->addElement($location);
		
		$options=array(''=>'All Status',1=>"ACTIVE",'0'=>"DEACTIVE");
		$status_r = $request->getParam('status_se');
		$status_se=new Zend_Form_Element_Select('status_se');
    	$status_se->setMultiOptions($options);
    	$status_se->setAttribs(array(
    		'id'=>'user_type_filter',
    		'class'=>'form-control',
    		'onchange'=>'this.form.submit()',
    	));
    	$status_se->setValue($status_r);
    	$this->addElement($status_se);
		
		$ad_search=new Zend_Form_Element_Text('ad_search');
    	$ad_search->setAttribs(array(
    		'id'=>'username',
    		'class'=>'form-control',
    	));
    	$ad_search->setValue($request->getParam('ad_search'));
    	$this->addElement($ad_search);

    	//uer title
    	$user_title = new Zend_Form_Element_Select("title");
    	$user_title->setAttribs(array('class'=>'form-control'));
    	$user_title->setMultiOptions(array("Mr"=>"Mr","Ms"=>"Ms"));
    	$this->addElement($user_title);

    	//user full name
    	$user_fullname = new Zend_Form_Element_Text("fullname");
    	$user_fullname->setAttribs(array(
    		'id'=>'fullname',
    		'class'=>'form-control',
    	));
    	$this->addElement($user_fullname);
    	
    	//user name
    	$user_name=new Zend_Form_Element_Text('username');
    	$user_name->setAttribs(array(
    		'id'=>'username',
    		'class'=>'form-control  centerRight',
    		'required'=>true
    	));
    	$this->addElement($user_name);
    	
    	//email
    	$email=new Zend_Form_Element_Text('email');
    	$email->setAttribs(array(
    		'id'=>'email',
    		'class'=>'form-control centerRight',
    	));
    	$this->addElement($email);
    	 
    	
//password    	
    	$password=new Zend_Form_Element_Password('password');
    	$password->setAttribs(array(
    		'id'=>'password',
    		'class'=>'form-control',
    	));
    	$this->addElement($password);
//confirm password    	
    	$confirm_password=new Zend_Form_Element_Password('confirm_password');
    	$confirm_password->setAttribs(array(
    		'id'=>'confirm_password',
    		'class'=>'form-control',
    	));
    	$this->addElement($confirm_password);
    	
    	//user type
		$sql = 'SELECT user_type_id,user_type FROM tb_acl_user_type';
		$rs=$db->getGlobalDb($sql);
		$options=array(''=>$tr->translate('Please_Select'));
		foreach($rs as $read) $options[$read['user_type_id']]=$read['user_type'];
		$user_type_id=new Zend_Form_Element_Select('user_type_id');		
    	$user_type_id->setMultiOptions($options);
    	$user_type_id->setAttribs(array(
    		'id'=>'user_type_id',
    		'class'=>'form-control',
    		'required'=>true
    	));
    	$this->addElement($user_type_id);
		
		$status = new Zend_Form_Element_Select("status");
    	$status->setAttribs(array('class'=>'form-control'));
    	$status->setMultiOptions(array("1"=>"Active","0"=>"Deactive"));
    	$this->addElement($status);
        
    	$photo = new Zend_Form_Element_File('pic');
    	$this->addElement($photo);
    	//location 
    	$rs=$db->getGlobalDb('SELECT id, name FROM tb_sublocation WHERE name!="" AND status=1 ORDER BY id DESC');
    	$option =array("1"=>$tr->translate("Please_Select"),"-1"=>$tr->translate("Add_New_Location"));
    	if(!empty($rs)) foreach($rs as $read) $option[$read['id']]=$read['name'];
    	$locationID= new Zend_Form_Element_Select('LocationId');
    	$locationID->setMultiOptions($option);
    	$locationID->setattribs(array('id'=>'LocationId','Onchange'=>'AddLocation()','class'=>'form-control'));
    	$this->addElement($locationID);
    	
    	if($data!=null){
			$user_title->setValue($data["title"]);
			$user_fullname->setValue($data["fullname"]);
			$user_name->setValue($data["username"]);
			$email->setValue($data["email"]);
			$user_type_id->setValue($data["user_type_id"]);
			$status->setValue($data["status"]);
		}
    	
    	return $this;
    }
    
    public function showSaleAgentForm($data=null, $stockID=null) {
    
    	$db=new Application_Model_DbTable_DbGlobal();
    	$db_sale = new Sales_Model_DbTable_DbSalesAgent();
    	$codes = $db_sale->getSaleAgentCode(1);
    	$user_code=$db_sale->getUserCode();
    	
    	$date =new Zend_Date();
    	$nameElement = new Zend_Form_Element_Text('name');
    	$nameElement->setAttribs(array('class'=>'form-control','placeholder'=>'Enter Agent Name'));
    	$this->addElement($nameElement);
    	 
    	$phoneElement = new Zend_Form_Element_Text('phone');
    	$phoneElement->setAttribs(array('class'=>'form-control','placeholder'=>'Enter Phone Number'));
    	$this->addElement($phoneElement);
    	 
    	$emailElement = new Zend_Form_Element_Text('email_detail');
    	$emailElement->setAttribs(array('class'=>'form-control','placeholder'=>'Enter Email Address'));
    	$this->addElement($emailElement);
    	 
    	$addressElement = new Zend_Form_Element_Text('address');
    	$addressElement->setAttribs(array('placeholder'=>'Enter Current Address',"class"=>"form-control"));
    	$this->addElement($addressElement);
    	 
    	$jobTitleElement = new Zend_Form_Element_Text('job_title');
    	$jobTitleElement->setAttribs(array('placeholder'=>'Enter Position',"class"=>"form-control"));
    	$this->addElement($jobTitleElement);
    	 
    	$descriptionElement = new Zend_Form_Element_Textarea('description');
    	$descriptionElement->setAttribs(array('placeholder'=>'Descrtion Here...',"class"=>"form-control","rows"=>3));
    	$this->addElement($descriptionElement);
    	 
    	$rowsStock = $db->getGlobalDb('SELECT id,name FROM tb_sublocation WHERE name!=""  ORDER BY id DESC ');
    	$optionsStock = array('1'=>'Default Location','-1'=>'Add New Location');
    	if(count($rowsStock) > 0) {
    		foreach($rowsStock as $readStock) $optionsStock[$readStock['id']]=$readStock['name'];
    	}
    	$mainStockElement = new Zend_Form_Element_Select('branch_id');
    	$mainStockElement->setAttribs(array('OnChange'=>'getSaleCode()','class'=>'form-control select2me'));
    	$mainStockElement->setMultiOptions($optionsStock);
    	$this->addElement($mainStockElement);
    	 
    	$user_name = new Zend_Form_Element_Text('user_name');
    	$user_name->setAttribs(array('placeholder'=>'Enter User Name',"class"=>"form-control",'required'=>'required'));
    	$this->addElement($user_name);
    	 
    	$password = new Zend_Form_Element_Password('password');
    	$password->setAttribs(array('placeholder'=>'Enter Password',"class"=>"form-control",
    			                     'required'=>true
    			));
    	$this->addElement($password);
    	 
    	$pob= new Zend_Form_Element_Text('pob');
    	$pob->setAttribs(array('placeholder'=>'Enter Place of Birdth',"class"=>"form-control"));
    	$this->addElement($pob);
    	 
    	$dob= new Zend_Form_Element_Text('dob');
    	$dob->setAttribs(array('placeholder'=>'Enter Position',"class"=>"form-control date-picker"));
    	$dob->setValue($date->get('MM/dd/1990'));
    	$this->addElement($dob);
    	 
    	$photo = new Zend_Form_Element_File("photo");
    	$this->addElement($photo);
    	 
    	$document = new Zend_Form_Element_File("document");
    	$this->addElement($document);
    	 
    	$signature = new Zend_Form_Element_File("signature");
    	$this->addElement($signature);
    	 
    	$bank_acc = new Zend_Form_Element_Text("bank_acc");
    	$bank_acc->setAttribs(array('placeholder'=>'Enter Bank Account',"class"=>"form-control"));
    	$this->addElement($bank_acc);
    	 
    	$refer_name = new Zend_Form_Element_Text("refer_name");
    	$refer_name->setAttribs(array('placeholder'=>'Enter Reference Name',"class"=>"form-control"));
    	$this->addElement($refer_name);
    	 
    	$refer_phone = new Zend_Form_Element_Text("refer_phone");
    	$refer_phone->setAttribs(array('placeholder'=>'Enter Reference Phone',"class"=>"form-control"));
    	$this->addElement($refer_phone);
    	 
    	$refer_addres = new Zend_Form_Element_Textarea("refer_address");
    	$refer_addres->setAttribs(array('placeholder'=>'Enter Reference Address',"class"=>"form-control","style"=>"height:40px"));
    	$this->addElement($refer_addres);
    	 
    	$satrt_working_date = new Zend_Form_Element_Text("start_working_date");
    	$satrt_working_date->setAttribs(array('placeholder'=>'Enter Bank Account',"class"=>"form-control date-picker"));
    	$satrt_working_date->setValue($date->get('MM/dd/YYYY'));
    	$this->addElement($satrt_working_date);
    	 
    	$row_user_type = $db->getGlobalDb('SELECT u.`user_type_id`,u.`user_type`,u.`parent_id` FROM `tb_acl_user_type` AS u WHERE u.`status`=1');
    	$option_user = array('-1'=>'Select User Type');
    	if(count($row_user_type) > 0) {
    		foreach($row_user_type as $rs) $option_user[$rs['user_type_id']]=$rs['user_type'];
    	}
    	$user_type = new Zend_Form_Element_Select('user_type');
    	$user_type->setAttribs(array('class'=>'form-control select2me'));
    	$user_type->setMultiOptions($option_user);
    	$this->addElement($user_type);
    	
    	$department = new Zend_Form_Element_Select('department');
    	$department->setAttribs(array('class'=>'form-control select2me'));
    	$department->setMultiOptions($option_user);
    	$this->addElement($department);
    	 
    	$row_manger = $db->getGlobalDb('SELECT u.`user_id`,u.`fullname` FROM `tb_acl_user` AS u,`tb_acl_user_type` AS ut WHERE u.`status`=1 AND u.`user_type_id`=ut.`user_type_id` AND u.`user_type_id`=5');
    	$option_user = array('-1'=>'Select User Type');
    	if(count($row_manger) > 0) {
    		foreach($row_manger as $rs) $option_user[$rs['user_id']]=$rs['fullname'];
    	}
    	$manage_by = new Zend_Form_Element_Select('manage_by');
    	$manage_by->setAttribs(array('class'=>'form-control select2me'));
    	$manage_by->setMultiOptions($option_user);
    	$this->addElement($manage_by);
    	 
    	 
    	
    	$code = new Zend_Form_Element_Text("code");
    	$code->setAttribs(array("class"=>"form-control"));
    	$code->setValue($user_code);
    	$this->addElement($code);
    	 
    	$old_photo = new Zend_Form_Element_Hidden("old_photo");
    	$this->addElement($old_photo);
    	 
    	$old_document = new Zend_Form_Element_Hidden("old_document");
    	$this->addElement($old_document);
    	 
    	$old_signature = new Zend_Form_Element_Hidden("old_signature");
    	$this->addElement($old_signature);
    	 
    	$user_id = new Zend_Form_Element_Hidden("user_id");
    	$this->addElement($user_id);
    
    	$row_status = $db->getGlobalDb('SELECT v.key_code,v.name_kh FROM tb_view as v WHERE v.type=5 AND v.status=1');
    	$option_status = array();
    	if(count($row_status) > 0) {
    		foreach($row_status as $rs) $option_status[$rs['key_code']]=$rs['name_kh'];
    	}
    	$status=new Zend_Form_Element_Select("status");
    	$status->setAttribs(array('class'=>'form-control select2me'));
    	$status->setMultiOptions($option_status);
    	$this->addElement($status);
    
    	if($data != null) {
    		$idElement = new Zend_Form_Element_Hidden('id');
    		 //print_r($data);exit();
    		$code->setValue($data['code']);
    		$nameElement->setValue($data['name']);
    		
    		$phoneElement->setValue($data['phone']);
    		$pob->setValue($data['pob']);
    		$code->setValue($data['code']);
    		$dob->setValue($data['dob']);
    		
    		$addressElement->setValue($data['address']);
    		$emailElement->setValue($data['email']);
    		$jobTitleElement->setValue($data['position']);
    		$descriptionElement->setValue($data['decription']);
    		$department->setValue($data['department']);
    	}
    	return $this;
    }
    
   function frmPassWord()
    {
    	//current password
    	$current_password=new Zend_Form_Element_Password('current_password');
    	$current_password->setAttribs(array(
    			'id'=>'current_password',
    			'class'=>'validate[required] form-control',
    	));
    	$this->addElement($current_password);
    	//password
    	$password=new Zend_Form_Element_Password('password');
    	$password->setAttribs(array(
    			'id'=>'password',
    			'class'=>'validate[required] form-control',
    	));
    	$this->addElement($password);
    	//confirm password
    	$confirm_password=new Zend_Form_Element_Password('confirm_password');
    	$confirm_password->setAttribs(array(
    			'id'=>'confirm_password',
    			'class'=>'validate[required,equals[password]] form-control',
    	));
    	$this->addElement($confirm_password);
    	
    }
}
?>
