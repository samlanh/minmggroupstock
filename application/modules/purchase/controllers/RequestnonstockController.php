<?php
class Purchase_RequestnonstockController extends Zend_Controller_Action
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
				$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
				$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'=>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
					'suppliyer_id'=>0,
					'purchase_status'=>0,
					'branch_id'=>-1,
					'status_paid'=>-1,
					);
		}
		$db = new Purchase_Model_DbTable_DbPurchaseNonstock();
		$rows = $db->getAllPurchaseOrder($search);
		$list = new Application_Form_Frmlist();
		$columns=array("BRANCH_NAME","VENDOR_NAME","PURCHASE_ORDER","ORDER_DATE","DATE_IN",
				 "INVOICE_NO","TOTAL_AMOUNT","PAID","BALANCE","ORDER_STATUS","STATUS","BY_USER");
		$link=array(
				'module'=>'purchase','controller'=>'requestnonstock','action'=>'edit',
		);
		
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'vendor_name'=>$link,'order_number'=>$link,'date_order'=>$link));
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
			$db = new Purchase_Model_DbTable_DbPurchaseNonstock();
			 if(!empty($data['identity'])){
				$db->addPurchaseOrder($data);
			 }
			Application_Form_FrmMessage::message("Purchase has been Saved!");
				if(!empty($data['btnsavenew'])){
					Application_Form_FrmMessage::redirectUrl("/purchase/requestnonstock/add");
				}
				Application_Form_FrmMessage::redirectUrl("/purchase/requestnonstock/");
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
	
	public function editAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db = new Purchase_Model_DbTable_DbPurchaseNonstock();
				$db->updatePurchaseOrder($data);
				Application_Form_FrmMessage::message("Purchase has been Saved!");
				Application_Form_FrmMessage::redirectUrl("/purchase/requestnonstock/");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			Application_Form_FrmMessage::redirectUrl("/purchase/index");
		}
		$db = new Purchase_Model_DbTable_DbPurchaseNonstock();
		$row = $db->getPurchaseById($id);
		$this->view->rs = $db->getPurchaseDetailById($id);
		//print_r($db->getPurchaseDetailById($id));
		
		$frm_purchase = new Application_Form_purchase();
		$form_add_purchase = $frm_purchase->productOrder($row);
		
		Application_Model_Decorator::removeAllDecorator($form_add_purchase);
		$this->view->form_purchase = $form_add_purchase;
	
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
	}
	
	public function addNewproudctAction(){
		$post=$this->getRequest()->getPost();
		$add_new_product = new Product_Model_DbTable_DbAddProduct();
		$pid = $add_new_product->addNewItem($post);
		$result = array("pid"=>$pid);
		echo Zend_Json::encode($result);
		exit();
	}
 
	public function getTaxAction(){//dynamic by customer
	
		$post=$this->getRequest()->getPost();
		$get_tax = new purchase_Model_DbTable_DbPurchaseVendor();
		$result = $get_tax->getTax($post["item_id"]);
		if(!$result){
			$result = array('tax'=>'0');
		}
		echo Zend_Json::encode($result);
		exit();
	}
   
   public function checkAction(){
   	$db = new Application_Model_DbTable_DbGlobal();
   	$this->_helper->layout->disableLayout();
   	$invoice = @$_POST['username'];
   	if($this->getRequest()->isPost()){
   		$post = $this->getRequest()->getPost();   			
   	}
   	if(isset($invoice)){
   		$sql = "SELECT `order` FROM `tb_purchase_order` WHERE `order`= '$invoice' LIMIT 1 ";
   		$row=$db->getGlobalDbRow($sql);
   		if($row){
   			Application_Form_FrmMessage::message("Order Is Exist !Please check again");
   		}
   		else{
   			echo "Is available!";
   		}
   	}
   	else{
   		echo "Invoice Number";
   }
   exit();
   }
   public function checkPurchasenoAction(){
   	if($this->getRequest()->isPost()){
   		$_data = $this->getRequest()->getPost();
   		$_invoiceno = $_data["pur_no"];
   		$db = new Application_Model_DbTable_DbGlobal();
   		$sql = "SELECT `order` FROM `tb_purchase_order` WHERE status=4 AND `order`= '$_invoiceno' LIMIT 1 ";
   		$row=$db->getGlobalDbRow($sql);
   		$db_table = new Product_Model_DbTable_DbAddLocation();
   		$items_code = $db_table->getCodeItem($_invoiceno);
   		echo Zend_Json::encode($row);
   		exit();
   	}
   }
   
  public function getCustomerInfoAction(){
  	$post=$this->getRequest()->getPost();
  	$sql = "SELECT `order` FROM `tb_purchase_order` WHERE `order`= '$invoice' LIMIT 1 ";
   		$row=$db->getGlobalDbRow($sql);
  	if(!$result){
  		$result = array('contact'=>'','phone'=>'');
  	}
  	echo Zend_Json::encode($result);
  	exit();
  } 
  
  public function getpobyidAction(){
  	if($this->getRequest()->isPost()){
  		$db = new Application_Model_DbTable_DbGlobal();
  		$post=$this->getRequest()->getPost();
  		$invoice = $post['invoice_id'];
  		$sql = "SELECT `order` FROM `tb_purchase_order` WHERE `order`= '$invoice' LIMIT 1 ";
  		$row=$db->getGlobalDbRow($sql);
  		/*if(!$result){
  			$result = array('contact'=>'','phone'=>'');
  		}*/
  		echo Zend_Json::encode($row);
  		exit();
  	}
  }
  public function getqtybyidAction(){
  	if($this->getRequest()->isPost()){
  		$post=$this->getRequest()->getPost();
  		$item_id = $post['item_id'];
  		$branch_id = $post['branch_id'];
  		  		$sql="  SELECT `qty_perunit`,price,
  		                (SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=measure_id) as measue_name,
  		                unit_label,
						(SELECT qty FROM `tb_prolocation` WHERE location_id=$branch_id AND pro_id=$item_id LIMIT 1 ) AS qty 
						FROM tb_product WHERE id= $item_id LIMIT 1  ";
  		$db = new Application_Model_DbTable_DbGlobal();
  		$row=$db->getGlobalDbRow($sql);
  		echo Zend_Json::encode($row);
  		exit();
  	}
  }

}