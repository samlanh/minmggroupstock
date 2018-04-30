<?php 
class Product_Form_FrmSearchInfomation extends Zend_Form
{
	public function init()
    {

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
		
		$db_pro = new Product_Model_DbTable_DbProduct();
		$branch = new Zend_Form_Element_Select("branch");
		$opt = array(''=>$tr->translate("SELECT_BRANCH"));
		$row_branch = $db_pro->getBranch();
		if(!empty($row_branch)){
			foreach ($row_branch as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$branch->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$branch->setMultiOptions($opt);
		$branch->setValue($request->getParam("branch"));
		
		$sub_location = new Zend_Form_Element_Select("sub_location");
		$opt = array(''=>$tr->translate("SELECT_BRANCH"));
		$row_branch = $db_pro->getBranch();
		if(!empty($row_branch)){
			foreach ($row_branch as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$sub_location->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$sub_location->setMultiOptions($opt);
		$sub_location->setValue($request->getParam("sub_location"));
		
		$opt = array(''=>$tr->translate("SELECT_CATEGORY"));
		$category = new Zend_Form_Element_Select("category");
		$category->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$row_cat = $db_pro->getCategory();
		if(!empty($row_cat)){
			foreach ($row_cat as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$category->setMultiOptions($opt);
		$category->setValue($request->getParam("category"));
		
		$db = new Product_Model_DbTable_DbRequestStock();
		$staff_id =new Zend_Form_Element_Select("staff_id");
		$staff_id->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'setStaffInfo();'
		));
		$opt= array(''=>$tr->translate("SELECT_REQUEST_NAME"));
		$row_staff= $db->getAllStaffName();
		if(!empty($row_staff)){
			foreach ($row_staff as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$staff_id->setMultiOptions($opt);
		
		$this->addElements(array($staff_id,$category,$branch,$pro_name,$end_date,$start_date));
		return $this;
	}
	
	
}