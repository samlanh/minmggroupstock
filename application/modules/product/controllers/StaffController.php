<?php
class Product_StaffController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }
	public function indexAction()
	{
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
// 			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
// 			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}else{
			$search =array(
					'ad_search'=>'',
					'status'=>1,
// 					'start_date'=>date("Y-m-d"),
// 					'end_date'=>date("Y-m-d"),
			);
		}
		$db = new Product_Model_DbTable_DbStaff();
		$rows = $db->getAllStaff($search);
		$list = new Application_Form_Frmlist();
		
		$columns=array("STAFF_NO","EMPLOYEE_NAME","PHONE","SEX","CAR_NUMBER","STAFF_POSITION","NOTE","STATUS");
		$link=array(
				'module'=>'product','controller'=>'staff','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('staff_name'=>$link,'staff_no'=>$link,'level'=>$link));
		
       $frm = new Product_Form_FrmSearchInfomation();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->formFilter = $frm->filter();
		
	}
	public function addAction()
	{
		if($this->getRequest()->isPost())
		{
			$post = $this->getRequest()->getPost();
			try{
				$db = new Product_Model_DbTable_DbStaff();
				$db->addCustomer($post);
				if(!empty($post['saveclose']))
				{
 					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS','/product/staff/index');
				}else{
					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS','/product/staff/add');
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$db_g=new Application_Model_DbTable_DbGlobal();
		$this->view->staff_no=$db_g->getStaffIdNo();
		$this->view->staff_pos=$db_g->getVewOptoinTypeByTypes(16);
	
	}	
	public function editAction() {
		$db = new Product_Model_DbTable_DbStaff();
		$id=$this->getRequest()->getParam('id');
		if($this->getRequest()->isPost())
		{
			$post = $this->getRequest()->getPost();
			$post['id']=$id;
			try{
				$db->updateStaff($post);
				if(!empty($post['saveclose']))
				{
 					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS','/product/staff/index');
				}else{
					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS','/product/staff/index');
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$this->view->row=$db->getStaffByid($id);
		$db_g=new Application_Model_DbTable_DbGlobal();
		$this->view->staff_no=$db_g->getStaffIdNo();
		$this->view->staff_pos=$db_g->getVewOptoinTypeByTypes(16);
	
	}
	
	public function customertypelistAction()
    {
    	try{
    		$db = new Product_Model_DbTable_DbStaff();
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'adv_search' => '',
    					'status_search' => '',
    					'type' => ''
    			);
    		}
    		$rs_rows= $db->getCustomerType($search);//call frome model
    		$this->view->rs = $rs_rows;
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$fm = new Product_Form_FrmOther();
    	$frm = $fm->search();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->Form = $frm;
    }
    public function addcustomertypeAction()
    {
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		
    		$db = new Product_Model_DbTable_DbStaff();
    		try {
    			$db->addCustomerType($data);
    			if(isset($data['save_new'])){
    
    				Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
    			}
    			if(isset($data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ',"/sales/customer/customertypelist");
    				//Application_Form_FrmMessage::redirectUrl('/other/loantype');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			$err = $e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	$fm = new Sales_Form_FrmCustomerType();
    	$frm = $fm->add();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->Form = $frm;
    }

	public function editcustomertypeAction()
    {
    	$id = $this->getRequest()->getParam("id");
    	$db = new Product_Model_DbTable_DbStaff();
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$data["id"] = $id;
    		try {
    			$db->editCustomerType($data);
    			if(isset($data['save_new'])){
    
    				Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
    			}
    			if(isset($data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ',"/sales/customer/customertypelist");
    				//Application_Form_FrmMessage::redirectUrl('/other/loantype');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			$err = $e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	$rs = $db->getCustomerTypeId($id);
    	$fm = new Sales_Form_FrmCustomerType();
    	$frm = $fm->add($rs);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->Form = $frm;
    }
	
	public function addCustomerAction(){
		if($this->getRequest()->isPost()){
			try {
			$post=$this->getRequest()->getPost();
			$add_customer = new Product_Model_DbTable_DbStaff();
			$customer_id = $add_customer->addNewCustomer($post);
			$result = array('cus_id'=>$customer_id);
			echo Zend_Json::encode($result);
			exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}
	public function deleteCustomerAction() {
		$id = ($this->getRequest()->getParam('id'));
		$sql = "DELETE FROM tb_customer WHERE customer_id IN ($id)";
		$deleteObj = new Application_Model_DbTable_DbGlobal();
		$deleteObj->deleteRecords($sql);
		$this->_redirect('/sales/customer/index');
	}
	
	public function getCuCodeAction(){//dynamic by customer
	
		$post=$this->getRequest()->getPost();
		$get_code = new Product_Model_DbTable_DbStaff();
		$result = $get_code->getCustomerCode($post["id"]);
		echo Zend_Json::encode($result);
		exit();
	}
	
	function getcustomerlimitAction(){
		$post=$this->getRequest()->getPost();
		$get_code = new Product_Model_DbTable_DbStaff();
		$result = $get_code->getCustomerLimit($post["id"]);
		echo Zend_Json::encode($result);
		exit();
	}
	function getCustomerinfoAction(){
		$post=$this->getRequest()->getPost();
		$get_code = new Product_Model_DbTable_DbStaff();
		$result = $get_code->getCustomerinfo($post["customer_id"]);
		echo Zend_Json::encode($result);
		exit();
	}
}