<?php
class Purchase_PaymentController extends Zend_Controller_Action
{	
	protected $tr = null;
    public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db = new Application_Model_DbTable_DbGlobal();
    	$rs = $db->getValidUserUrl();
    	if(empty($rs)){
    		Application_Form_FrmMessage::Sucessfull("YOU_NO_PERMISION_TO_ACCESS_THIS_SECTION","/index/dashboad");
    	}
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    
   	public function indexAction()
	{
		$session_user=new Zend_Session_Namespace('auth');
		$level = $session_user->level;
		
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
					'branch_id'=>-1,
					'customer_id'=>-1,
					);
		}
		$db = new Purchase_Model_DbTable_Dbpayment();
		$rows = $db->getAllReciept($search);
    	$link=array(
    			'module'=>'purchase','controller'=>'payment','action'=>'edit',
    	);
		
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","RECEIPT_NO","EXPENSE_DATE",
				"TOTAL","PAID","BALANCE","PAYMENT_TYPE","CHEQUE_NUMBER","BANK_NAME","WITHDRAWER","CHEQ_ISSUE","CHEQ_WIDRAW","PAYMENT_METHOD","PRINT","BY_USER");
		

		$link_print=array(
				'module'=>'report','controller'=>'index','action'=>'rpt-vendor-paymentdetail',
		);
			
		if($level==1){
			$link_delete=array(
					'module'=>'purchase','controller'=>'payment','action'=>'delete');
		}else{
			$link_delete=array(
					'module'=>'purchase','controller'=>'payment','action'=>'index');
		}
		$delete_inv=$this->tr->translate("DELETE_RE");
		$print=$this->tr->translate("PRINT");
		
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link, $print=>$link_print,));
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}	
	
	function addAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Purchase_Model_DbTable_Dbpayment();
				if(!empty($data['identity'])){
					$dbq->addReceiptPayment($data);
				}
				Application_Form_FrmMessage::message("INSERT_SUCESS");
				if(!empty($data['btnsavenew'])){
					Application_Form_FrmMessage::redirectUrl("/purchase/payment/add");
				}
				Application_Form_FrmMessage::redirectUrl("/purchase/payment/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		///link left not yet get from DbpurchaseOrder
		$frm = new Purchase_Form_FrmPayment(null);
		$form_pay = $frm->Payment(null);
		Application_Model_Decorator::removeAllDecorator($form_pay);
		$this->view->form_sale = $form_pay;
		 
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
	}
	
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$dbq = new Purchase_Model_DbTable_Dbpayment();
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data['id']=$id;
			try {
				if(!empty($data['identity'])){
					$dbq->updatePayment($data);
					Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/purchase/payment");
				}
				if(!empty($data['btnsavenew'])){
					Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/purchase/payment/add");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $dbq->getVendorPaymentById($id);
		$this->view->reciept_detail = $row;
		$row_edit=$dbq->getPurchaseOrderByid($id);
		if($row[0]['id']!=$row_edit['payment_edit_id']){
			Application_Form_FrmMessage::Sucessfull("Can not edit !!!","/purchase/payment");
		}

// if($row['is_approved']==1){
// 			Application_Form_FrmMessage::Sucessfull("SALE_ORDER_WARNING","/sales/payment");
// 		}
// 		$this->view->rs = $dbq->getSaleorderItemDetailid($id);
// 		$this->view->rsterm = $dbq->getTermconditionByid($id);
		
		///link left not yet get from DbpurchaseOrder
		$frm = new Purchase_Form_FrmPayment(null);
		$form_pay = $frm->Payment($row);
		Application_Model_Decorator::removeAllDecorator($form_pay);
		$this->view->form_sale = $form_pay;
		 
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$this->view->term_opt = $db->getAllTermCondition(1);
	}
	
	public function getinvoiceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db->getAllInvoicePaymentPurchase($post['post_id'], $post['type_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}

	public function deleteAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Purchase_Model_DbTable_Dbpayment();
		echo "<script language='javascript'>
		var txt;
		var r = confirm('តើលោកអ្នកពិតចង់លុបបង្កាន់ដៃមួយនេះឫ?');
		if (r == true) {";
		//$db->deleteSale($id);
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/purchase/payment/deleteitem/id/".$id."'";
		echo"}";
		echo"else {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/purchase/payment/'";
		echo"}
		</script>";
	}
	
	function deleteitemAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Purchase_Model_DbTable_Dbpayment();
		$db->deleteInvoice($id);
		$this->_redirect("purchase/payment");
	}
	
	
}