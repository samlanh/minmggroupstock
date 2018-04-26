<?php
class Product_RequeststockController extends Zend_Controller_Action
{
	const REDIRECT_URL_ADD ='/product/damagredstock/add';
	const REDIRECT_URL_ADD_CLOSE ='/product/damagredstock/';
public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	
    } 
   	//view transfer index
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    
   	public function indexAction()
   	{
   		$data = $this->getRequest()->getPost();
   		$list = new Application_Form_Frmlist();
   		$db = new Product_Model_DbTable_DbRequestStock();
		$date =new Zend_Date();
   		if($this->getRequest()->isPost()){   
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
			$data = array(
    			'ad_search'		=>	'',
    			'start_date'	=>	date("Y-m-d"),
				'end_date'		=>	date("Y-m-d"),
    		);
		}
   
   		$rows=$db->getAllRequestStock($data);
   		$columns=array("LOCATION","REQUEST_NO","REQUEST_NAME","TOTAL_QTY","REQUEST_DATE","RECEIVE_DTE","NOTE","BY_USER","STATUS");
   		
   		
   		$link=array(
   				'module'=>'product','controller'=>'requeststock','action'=>'edit',
   		);
   		$this->view->list=$list->getCheckList(0, $columns, $rows,array('location_name'=>$link,'reques_no'=>$link,'staff_name'=>$link));
   		$frm = new Product_Form_FrmRequestStock();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->formFilter = $frm->filter();
   	}
   	
    public function addAction()
    {   
    	if($this->getRequest()->isPost()){   
    		$post=$this->getRequest()->getPost();
    		$db_request= new Product_Model_DbTable_DbRequestStock();
    		$db_result = $db_request->addRequest($post);
    		if(isset($post["saveclose"])){
    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/product/requeststock/');
    		}else{
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/product/requeststock/add');
			}
    	}
    	$frm = new Product_Form_FrmRequestStock();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->formFilter = $frm->add();
	}
	
	public function editAction()
	{
		$id=$this->getRequest()->getParam('id');
		$db_request= new Product_Model_DbTable_DbRequestStock();
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$post['id']=$id;
			$db_result = $db_request->updateRequest($post);
			if(isset($post["saveclose"])){
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS", '/product/requeststock/');
			}else{
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS", '/product/requeststock/');
			}
		}
		$row=$db_request->getStaffRequestById($id);
		$this->view->rows=$db_request->getStaffRequestItemsbyId($id);
		$frm = new Product_Form_FrmRequestStock();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->formFilter = $frm->add($row);
	}
	
	/// Ajax Section
	public function getproductAction(){
		if($this->getRequest()->isPost()) {
			$db = new Product_Model_DbTable_DbDamagedStock();
			$data = $this->getRequest()->getPost();
			$rs = $db->getProductQtyById($data["id"],$data["location_ids"]);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
 
	public function getStaffinfoAction(){
		if($this->getRequest()->isPost()) {
			$db = new Product_Model_DbTable_DbRequestStock();
			$data = $this->getRequest()->getPost();
			$rs = $db->getAllStafInfo($data["staff_id"]);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
}