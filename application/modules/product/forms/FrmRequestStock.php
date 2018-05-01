<?php 
class Product_Form_FrmRequestStock extends Zend_Form
{
	public function init()
    {

	}
	function add($data=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Product_Model_DbTable_DbRequestStock();
		$db_global = new Application_Model_DbTable_DbGlobal();
		$rs_from_loc = $db_global -> getAllLocation();
		
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		
		$pro_name =new Zend_Form_Element_Select("pro_name");
		$pro_name->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'addNew();'
		));
		$opt= array(''=>$tr->translate("SELECT PRODUCT"));
		$row_product = $db->getProductName();
		if(!empty($row_product)){
			foreach ($row_product as $rs){
				$opt[$rs["id"]] = $rs["item_name"]." ".$rs["model"]." ".$rs["size"]." ".$rs["color"];
			}
		}
		$pro_name->setMultiOptions($opt);
		
		$staff_id =new Zend_Form_Element_Select("staff_id");
		$staff_id->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'setStaffInfo();getPopupStaff();'
		));
		$opt= array(''=>$tr->translate("SELECT_REQUEST_NAME"),'-1'=>$tr->translate("ADD_NEW"));
		$row_staff= $db->getAllStaffName();
		if(!empty($row_staff)){
			foreach ($row_staff as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$staff_id->setMultiOptions($opt);
		
		$status =new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control select2me',
		));
		
		$opt= array();
		$row_status= $db_global->getVewOptoinTypeByTypes(5);
		if(!empty($row_status)){
			foreach ($row_status as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$status->setMultiOptions($opt);
		
		
		$from_loc = new Zend_Form_Element_Select("from_loc");
    	$from_loc->setAttribs(array(
    			'class'=>'form-control select2me',
    	));
		
		$opt = array(''=>$tr->translate("SELECT BRANCH"));
		if(!empty($rs_from_loc)){
    		foreach ($rs_from_loc as $rs){
    			$opt[$rs["id"]] = $rs["name"];
    		}
    	}
    	$from_loc->setMultiOptions($opt);
		$from_loc->setValue($result["branch_id"]);
		
		$request_staff_no =new Zend_Form_Element_Text("request_staff_no");
		$request_staff_no->setAttribs(array(
				'class'=>'form-control',
				'readonly'=>true
		));
		
		$position_text =new Zend_Form_Element_Text("positiont_text");
		$position_text->setAttribs(array(
				'class'=>'form-control',
				'readonly'=>true
		));
		
		$position =new Zend_Form_Element_Select("position");
		$position->setAttribs(array(
				'class'=>'form-control select2me',
				'readOnly'=>true
		));
		$opt=  array(''=>$tr->translate("SELECT_POSIONT"));
		$row_pos= $db_global->getVewOptoinTypeByTypes(16);
		if(!empty($row_pos)){
			foreach ($row_pos as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$position->setMultiOptions($opt);
		
		$purpose = new Zend_Form_Element_Textarea('purpose');
		$purpose->setAttribs(array("class"=>'form-control',"rows"=>2));
		$this->addElement($purpose);
		
		$note = new Zend_Form_Element_Textarea('note');
		$note->setAttribs(array("class"=>'form-control',"rows"=>2));
		$this->addElement($note);
		
		$date =new Zend_Date();
		$reques_date = new Zend_Form_Element_Text('reques_date');
		$reques_date ->setAttribs(array('format:'=>'DD/MM/YYYY','class'=>'col-md-3 validate[required] form-control form-control-inline date-picker','placeholder' => 'Click to Choose Date'));
		$reques_date ->setValue($date->get('M/d/Y'));
		$this->addElement($reques_date);
		
		$date =new Zend_Date();
		$receive_date = new Zend_Form_Element_Text('receive_date');
		$receive_date ->setAttribs(array('format:'=>'DD/MM/YYYY','class'=>'col-md-3 validate[required] form-control form-control-inline date-picker','placeholder' => 'Click to Choose Date'));
		$receive_date ->setValue($date->get('M/d/Y'));
		$this->addElement($receive_date);
		
		$request_no = New Zend_Form_Element_Text("request_no");
		$re_cod=$db_global->getRequestNo();
		$request_no->setAttribs(array(
				'class'=>'form-control',
				'readonly'=>true
		));
		$request_no->setValue($re_cod);
		
		if($data != null) {
			$from_loc	->setValue($data["branch_id"]);
			$request_no	->setValue($data["reques_no"]);
			$staff_id	->setValue($data["staff_id"]);
			$reques_date->setValue(date("m/d/Y",strtotime($data["date_request"])));
			$receive_date->setValue(date("m/d/Y",strtotime($data["receive_date"])));
			$purpose	->setValue($data["purpose"]);
			$note	->setValue($data["note"]);
			$status	->setValue($data["status"]);
		}
		
		$this->addElements(array($position_text,$request_staff_no,$request_no,$position,
				$purpose,$staff_id,$position,$request_no,$pro_name,$from_loc,$status));
		return $this;
	}
	
	function filter(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Product_Model_DbTable_DbAdjustStock();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$date =new Zend_Date();
		$pro_name =new Zend_Form_Element_Text("ad_search");
		$pro_name->setAttribs(array(
				'class'=>'form-control',
		));
		$pro_name ->setValue($request->getParam("ad_search"));
		
		$start_date = New Zend_Form_Element_Text("start_date");
		$start_date->setAttribs(array(
				'class'=>'validate[required] form-control form-control-inline date-picker',
				'placeholder' => 'Click to Choose Start Date'
		));
		$re_start_date = $request->getParam("start_date");
		if(!empty($re_start_date)){
			$start_date ->setValue($re_start_date);
		}else{
			//$start_date ->setValue($date->get('MM/d/Y'));
		}
		
		$end_date = New Zend_Form_Element_Text("end_date");
		$end_date->setAttribs(array(
				'class'=>'validate[required] form-control form-control-inline date-picker',
				'placeholder' => 'Click to Choose End Date'
		));
		$re_end_date = $request->getParam("end_date");
		if(!empty($re_end_date)){
			$end_date ->setValue($re_end_date);
		}else{
			$end_date ->setValue($date->get('MM/d/Y'));
		}
		
		$this->addElements(array($pro_name,$end_date,$start_date));
		return $this;
	}
	
	
}