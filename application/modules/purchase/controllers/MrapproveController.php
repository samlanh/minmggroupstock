<?php
class Purchase_MrapproveController extends Zend_Controller_Action
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
   		$db = new Purchase_Model_DbTable_DbMrapprove();
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
				'suppliyer_id'=>0,
			    'purchase_status'=>0,
				'branch_id'=>-1,
				'status_paid'=>-1,
    		);
		}
   		$rows=$db->getAllPurchaseOrder($data);
   		$this->view->rs=$rows;
   		$columns=array("TRANSFER_NO","TRANSFER_DATE","FROM_LOCATION","TO_LOCATION","TOTAL_QTY","NOTE","APPROVE","BY_USER","STATUS");
   		$link=array(
   				'module'=>'product','controller'=>'transferstock','action'=>'edit',
   		);
   		$this->view->list=$list->getCheckList(0, $columns, $rows,array('transfer_no'=>$link,'transfer_date'=>$link,'location_name'=>$link));
   		$formFilter = new Application_Form_Frmsearch();
   		$this->view->formFilter = $formFilter;
   		Application_Model_Decorator::removeAllDecorator($formFilter);
   	}
   	
   	public function purchaseorderAction()
   	{
   		$data = $this->getRequest()->getPost();
   		$list = new Application_Form_Frmlist();
   		$db = new Purchase_Model_DbTable_DbMrapprove();
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
   					'suppliyer_id'=>0,
   					'purchase_status'=>0,
   					'branch_id'=>-1,
   					'status_paid'=>-1,
   						
   			);
   		}
   		$rows=$db->getAllPurchaseOrder($data);
   		$this->view->rs=$rows;
   		$columns=array("TRANSFER_NO","TRANSFER_DATE","FROM_LOCATION","TO_LOCATION","TOTAL_QTY","NOTE","APPROVE","BY_USER","STATUS");
   		$link=array(
   				'module'=>'product','controller'=>'transferstock','action'=>'edit',
   		);
   		$this->view->list=$list->getCheckList(0, $columns, $rows,array('transfer_no'=>$link,'transfer_date'=>$link,'location_name'=>$link));
   		$formFilter = new Application_Form_Frmsearch();
   		$this->view->formFilter = $formFilter;
   		Application_Model_Decorator::removeAllDecorator($formFilter);
   	}
   	
    public function addpurchaseAction()
    {
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
			$db = new Purchase_Model_DbTable_DbMrapprove();
			 if(!empty($data['identity'])){
				$db->addPurchaseOrder($data);
			 }
			Application_Form_FrmMessage::message("Purchase has been Saved!");
				if(!empty($data['btnsavenew'])){
					Application_Form_FrmMessage::redirectUrl("/purchase/index");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		///link left not yet get from DbpurchaseOrder 	
		$frm_purchase = new Application_Form_purchase(null);
		$form_add_purchase = $frm_purchase->productOrder();
		Application_Model_Decorator::removeAllDecorator($form_add_purchase);
		$this->view->form_purchase = $form_add_purchase;
		
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();;
		
		$formProduct = new Product_Form_FrmProduct();
		$formStockAdd = $formProduct->add(null);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form = $formStockAdd;
		
		$formpopup = new Application_Form_FrmPopup(null);
		//for add vendor
		$formStockAdd = $formpopup->popupVendor(null);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form_vendor = $formStockAdd;
		
		//for add location
// 		$formAdd = $formpopup->popuLocation(null);
// 		Application_Model_Decorator::removeAllDecorator($formAdd);
// 		$this->view->form_branch = $formAdd;	
	}
	
	public function approveAction()
	{
		$id=$this->getRequest()->getParam('id');
		$db_request= new Purchase_Model_DbTable_DbMrapprove();
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$post['id']=$id;
			$db_result = $db_request->updateRequestpurchase($post);
			if(isset($post["saveclose"])){
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS", '/purchase/mrapprove/');
			}else{
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS", '/purchase/mrapprove/');
			}
		}
		$row=$db_request->getPurchaseById($id);
		$this->view->row_tran=$row;
// 		if($row['is_approve']==1){
// 			Application_Form_FrmMessage::Sucessfull("Can not edit!!!", '/product/transferstock/');
// 		}
		$this->view->rows=$db_request->getPurchaseDetailById($id);
		$frm = new Product_Form_FrmTransferStock();
		Application_Model_Decorator::removeAllDecorator($frm);
		//$this->view->formFilter = $frm->add($row);
	}
	
	public function returnAction()
	{
		$id=$this->getRequest()->getParam('id');
		$db_request= new Purchase_Model_DbTable_DbMrapprove();
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$post['id']=$id;
			$db_result = $db_request->returntProductTransfer($post);
			if(isset($post["saveclose"])){
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS", '/product/mrcheck/');
			}else{
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS", '/product/mrcheck/');
			}
		}
		$row=$db_request->getTransferById($id);
		$this->view->row_tran=$row;
		// 		if($row['is_approve']==1){
		// 			Application_Form_FrmMessage::Sucessfull("Can not edit!!!", '/product/transferstock/');
		// 		}
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
			$db = new Purchase_Model_DbTable_DbMrapprove();
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
		$db = new Purchase_Model_DbTable_DbMrapprove();
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