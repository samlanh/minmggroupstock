<?php
class Sales_AgreementController extends Zend_Controller_Action
{	
    public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
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
					'branch_id'=>-1,
					'customer_id'=>-1,
					);
		}
		$db = new Sales_Model_DbTable_Dbagreement();
		$rows = $db->getAllagreement($search);
		$columns=array("BRANCH_NAME","Com.Name","CON_NAME","AGREEMENT_NO","AGREEMENT_DATE","TOTAL_AMOUNT","BY_USER","PRINT",);
		$link=array(
				'module'=>'sales','controller'=>'agreement','action'=>'edit',
		);
		$invoice=array(
				'module'=>'sales','controller'=>'agreement','action'=>'agreement',);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('បោះពុម្ភ'=>$invoice,'contact_name'=>$link,'branch_name'=>$link,'customer_name'=>$link,
				'sale_no'=>$link));
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}	
	
	public function addAction()
	{
		$db = new Sales_Model_DbTable_Dbagreement();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				if(!empty($data['identity'])){
					$db->addAgreement($data);
				}
				Application_Form_FrmMessage::message("INSERT_SUCESS");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->rsproduct = $db->getAllProductName();
		$this->view->rscustomer = $db->getAllCustomerName();
// 		$db = new Application_Model_DbTable_DbGlobal();
// 		$this->view->term_opt = $db->getAllTermCondition();
	
		$formpopup = new Sales_Form_FrmCustomer(null);
		$formpopup = $formpopup->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->agreement_no = $db->getAgreementNo(1);
	
// 		$db = new Sales_Model_DbTable_Dbexchangerate();
// 		$this->view->rsrate= $db->getExchangeRate();
	}
	
	public function editAction()
	{
		$db = new Sales_Model_DbTable_Dbagreement();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				if(!empty($data['identity'])){
					$db->editAgreement($data);
				}
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/agreement");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/agreement");
		}
// 		$db = new Sales_Model_DbTable_Dbagreement();
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->rsproduct = $db->getAllProductName();
		$this->view->rscustomer = $db->getAllCustomerName();
		$this->view->rsproduct = $db->getAllProductName();
	
		$formpopup = new Sales_Form_FrmCustomer(null);
		$formpopup = $formpopup->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
// 		$db = new Application_Model_DbTable_DbGlobal();
// 		$this->view->invoice = $db->getSalesNumber(1);
		
// 		$db = new Sales_Model_DbTable_Dbpos();
		$db = new Sales_Model_DbTable_Dbagreement();
		$rs = $db->getAgreementById($id);
		$this->view->rs = $rs;
		$this->view->rsdetail =  $db->getagreementDetailById($id);
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->term_opt = $db->getAllTermCondition();
		
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->rscustomer = $db->getAllCustomerName();
		if(empty($rs)){
			$this->_redirect("/sales/agreement");
		}
	}
	public function deleteAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Sales_Model_DbTable_Dbpos();
		echo "<script language='javascript'>
		var txt;
		var r = confirm('តើលោកអ្នកពិតចង់លុបវិក្កយបត្រនេះឫ!');
		if (r == true) {";
			//$db->deleteSale($id);
			echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/sales/possale/deleteitem/id/".$id."'";
		echo"}";
		echo"else {";
			echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/sales/index/'";
		echo"}
		</script>";
	}
	function deleteitemAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Sales_Model_DbTable_Dbpos();
		$db->deleteSale($id);
		$this->_redirect("sales/index");
	}
	function agreementAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/agreement");
		}
		$query = new Sales_Model_DbTable_Dbagreement();
		$rs = $query->getAgreementById($id);
		$this->view->rs = $rs;
		$this->view->rsdetail =  $query->getagreementDetailById($id);
		if(empty($rs)){
			$this->_redirect("/sales/agreement");
		}
	}		
	function getproductAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpos();
			$rs =$db->getProductById($post['product_id'],$post['branch_id']);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
		
}