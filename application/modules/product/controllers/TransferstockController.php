<?php
class Product_TransferstockController extends Zend_Controller_Action
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
   		$db = new Product_Model_DbTable_DbTransferStock();
		$date =new Zend_Date();
   		if($this->getRequest()->isPost()){   
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
			$data = array(
    			'ad_search'		=>	'',
    			'start_date'	=>	date("Y-m-01"),
				'end_date'		=>	date("Y-m-d"),
    		);
		}
   		$rows=$db->getAllTransferStock($data);
   		$this->view->rs=$rows;
   		$columns=array("TRANSFER_NO","TRANSFER_DATE","FROM_LOCATION","TO_LOCATION","TOTAL_QTY","NOTE","APPROVE","BY_USER","STATUS");
   		$link=array(
   				'module'=>'product','controller'=>'transferstock','action'=>'edit',
   		);
   		$this->view->list=$list->getCheckList(0, $columns, $rows,array('transfer_no'=>$link,'transfer_date'=>$link,'location_name'=>$link));
   		$frm = new Product_Form_FrmSearchInfomation();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->formFilter = $frm->filter();
    	
   	}
   	
    public function addAction()
    {   
    	if($this->getRequest()->isPost()){   
    		$post=$this->getRequest()->getPost();
    		$db_request= new Product_Model_DbTable_DbTransferStock();
    		$db_result = $db_request->addTransferStock($post);
    		if(isset($post["saveclose"])){
    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/product/transferstock/');
    		}else{
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/product/transferstock/add');
			}
    	}
    	$frm = new Product_Form_FrmTransferStock();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->formFilter = $frm->add();
	}
	
	public function editAction()
	{
		$id=$this->getRequest()->getParam('id');
		$db_request= new Product_Model_DbTable_DbTransferStock();
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$post['id']=$id;
			$db_result = $db_request->updateTransfer($post);
			if(isset($post["saveclose"])){
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS", '/product/transferstock/');
			}else{
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS", '/product/transferstock/');
			}
		}
		$row=$db_request->getTransferById($id);
		if($row['is_approve']==1){
			Application_Form_FrmMessage::Sucessfull("Can not edit!!!", '/product/transferstock/');
		}
		$this->view->rows=$db_request->getTransferItemsbyId($id);
		$frm = new Product_Form_FrmTransferStock();
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
			$db = new Product_Model_DbTable_DbTransferStock();
			$data = $this->getRequest()->getPost();
			$rs = $db->getAllStafInfo($data["staff_id"]);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
	public function viewAction()
	{
		$data = $this->getRequest()->getPost();
		$list = new Application_Form_Frmlist();
		$db = new Product_Model_DbTable_DbTransferStock();
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
		$rows=$db->getAllTransferStock($data);
		$columns=array("TRANSFER_NO","TRANSFER_DTE","FROM_LOCATION","TO_LOCATION","TOTAL_QTY","NOTE","APPROVE","BY_USER","STATUS");
		$link=array(
				'module'=>'product','controller'=>'transferstock','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $columns, $rows,array('transfer_no'=>$link,'transfer_date'=>$link,'location_name'=>$link));
		$frm = new Product_Form_FrmTransferStock();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->formFilter = $frm->filter();
	}
}