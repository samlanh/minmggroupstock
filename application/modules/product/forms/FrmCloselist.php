<?php 
class Product_Form_FrmCloselist extends Zend_Form
{
	public function init()
    {

	}
	function add(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Product_Model_DbTable_DbAdjustStock();
		$db_global = new Application_Model_DbTable_DbGlobal();
		$rs_from_loc = $db_global -> getAllLocation();
		$date =new Zend_Date();
		
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		
		$pro_name =new Zend_Form_Element_Select("pro_name");
		$pro_name->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'getProCloseList(1);'
		));
		$opt= array(''=>$tr->translate("SELECT PRODUCT"));
		$row_product = $db->getProductName();
		if(!empty($row_product)){
			foreach ($row_product as $rs){
				$opt[$rs["id"]] = $rs["item_name"]." ".$rs["model"]." ".$rs["size"]." ".$rs["color"];
			}
		}
		
		$pro_name->setMultiOptions($opt);
		
		$from_loc = new Zend_Form_Element_Select("from_loc");
    	$from_loc->setAttribs(array(
    			'class'=>'form-control select2me',
    			'onChange'=>'getProCloseList(2);'
    	));
		
		$opt = array(''=>$tr->translate("SELECT BRANCH"));
		if(!empty($rs_from_loc)){
    		foreach ($rs_from_loc as $rs){
    			$opt[$rs["id"]] = $rs["name"];
    		}
    	}
    	$from_loc->setMultiOptions($opt);
		$from_loc->setValue($result["branch_id"]);
		
		
		$note = new Zend_Form_Element_Textarea('note');
		$note->setAttribs(array("class"=>'form-control',"rows"=>2));
		$this->addElement($note);
		
		$close_no = New Zend_Form_Element_Text("close_no");
		$c_cod=$db_global->getCloselistNo();
		$close_no->setAttribs(array(
				'class'=>'form-control',
				'readonly'=>true
		));
		$close_no->setValue($c_cod);
		
		$start_date = New Zend_Form_Element_Text("close_date");
		$start_date->setAttribs(array(
				'class'=>'validate[required] form-control form-control-inline date-picker',
				'placeholder' => 'Click to Choose Start Date'
		));
		$start_date ->setValue($date->get('MM/d/Y'));
		
		$this->addElements(array($start_date,$close_no,$pro_name,$from_loc));
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
			$start_date ->setValue($date->get('MM/d/Y'));
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