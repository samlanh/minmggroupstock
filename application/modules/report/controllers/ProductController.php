<?php
class report_ProductController extends Zend_Controller_Action
{
	
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db = new Application_Model_DbTable_DbGlobal();
    	$rs = $db->getValidUserUrl();
    	if(empty($rs)){
    		Application_Form_FrmMessage::Sucessfull("YOU_NO_PERMISION_TO_ACCESS_THIS_SECTION","/index/dashboad");
    	}
    }
    protected function GetuserInfo(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function indexAction()
    {
    	
    
    }
    public function rptcurrentstockAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'branch'	=>	'',
    				'brand'		=>	'',
    				'category'	=>	'',
    				'model'		=>	'',
    				'color'		=>	'',
    				'size'		=>	'',
    				'status_qty'=>	-1
    		);
    	}
		$this->view->search = $db->getBranch($data["branch"]);
    	$this->view->product = $db->getAllProduct($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    
    }
	
	public function rptallcurrentstockAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'branch'	=>	'',
    				'brand'		=>	'',
    				'category'	=>	'',
    				'model'		=>	'',
    				'color'		=>	'',
    				'size'		=>	'',
    				'status_qty'=>	-1
    		);
    	}
    	$this->view->product = $db->getAllProduct($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    
    }
    public function rptproductlistAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'branch'	=>	'',
    				'brand'		=>	'',
    				'category'	=>	'',
    				'model'		=>	'',
    				'color'		=>	'',
    				'size'		=>	'',
    				'status'	=>	1,
    				'status_qty'=>-1
    		);
    	}
		$this->view->search = $db->getBranch($data["branch"]);
    	$this->view->product = $db->getAllProduct($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);    
    }    
    public function rptadjuststockAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'branch'	=>	'',
    				'brand'		=>	'',
    				'category'	=>	'',
    				'model'		=>	'',
    				'color'		=>	'',
    				'size'		=>	'',
    				'status'	=>	1,
    				'status_qty'=>-1
    		);
    	}
    	$this->view->search = $db->getBranch($data["branch"]);
    	$this->view->product = $db->getAllAdjustStock($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    
    }
    public function rpttransferAction()
    {
    	$db = new Product_Model_DbTable_DbTransfer();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'tran_num'	=>	'',
    			'tran_date'	=>	date("m/d/Y"),
    			'type'		=>	'',
    			'status'	=>	1,
    			'to_loc'	=>	'',
    		);
    	}
    	$this->view->product = $db->getTransfer($data);
    	$formFilter = new Product_Form_FrmTransfer();
    	$this->view->formFilter = $formFilter->frmFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter); 
    }
    function showbarcodeAction(){
    	$id = ($this->getRequest()->getParam('id'));
    	$sql ="SELECT id,barcode,item_name,cate_id,
			((SELECT name FROM `tb_category` WHERE id=cate_id)) as cate_name
    	FROM `tb_product` WHERE id IN (".$id.")";
    	$db = new Application_Model_DbTable_DbGlobal();
//     	print_r($db->getGlobalDb($sql));
    	$this->view->rsproduct = $db->getGlobalDb($sql);
    }
    public function generateBarcodeAction(){
    	$loan_code = $this->getRequest()->getParam('pro_code');
    	header('Content-type: image/png');
    	$this->_helper->layout()->disableLayout();
    	//$barcodeOptions = array('text' => "$_itemcode",'barHeight' => 30);
    	$barcodeOptions = array('text' => "$loan_code",'barHeight' => 40);
    	//'font' => 4(set size of label),//'barHeight' => 40//set height of img barcode
    	$rendererOptions = array();
    	$renderer = Zend_Barcode::factory(
    			'Code128', 'image', $barcodeOptions, $rendererOptions
    	)->render();
    
    }
    
    function rptRequestProductAction(){
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'branch'	=>	'',
    				'brand'		=>	'',
    				'category'	=>	'',
    				'model'		=>	'',
    				'color'		=>	'',
    				'size'		=>	'',
    				'status'	=>	1,
    				'status_qty'=>-1
    		);
    	}
    	$this->view->search = $db->getBranch($data["branch"]);
    	$this->view->product = $db->getAllProduct($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    
    function rptTransferProductAction(){
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
    	$this->view->rssearch=$data;
    	$db = new Product_Model_DbTable_DbTransferStock();
    	$this->view->rs=$db->getAllTransferStock($data);
    	$frm = new Product_Form_FrmSearchInfomation();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->formFilter = $frm->filter();
    }
    
    function rptTransferProductdetailAction(){
    	$id=$this->getRequest()->getParam('id');
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
    	$db = new Product_Model_DbTable_DbTransferStock();
    	$this->view->row=$db->getTransferById($id);
    	$this->view->rs=$db->getTransferItemsbyId($id);
    	$frm = new Product_Form_FrmSearchInfomation();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->formFilter = $frm->filter();
    }
    
    function rptTransferRecproductAction(){
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
    	$this->view->rssearch=$data;
    	$db = new Product_Model_DbTable_DbTransferStock();
    	$this->view->rs=$db->getAllTransferReceiveStock($data);
    	$frm = new Product_Form_FrmSearchInfomation();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->formFilter = $frm->filter();
    }
    
    function rptTransferReceivedetailAction(){
    	$id=$this->getRequest()->getParam('id');
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
    	$db = new Product_Model_DbTable_DbTransferStock();
    	$this->view->row=$db->getTransferReceiveById($id);
    	$rs=$this->view->rs=$db->getReceiveItemsbyId($id);
    	if(empty($rs)){
    		Application_Form_FrmMessage::Sucessfull("Transfer don't have approve!!!", '/product/mrcheck/');
    	}
    	$frm = new Product_Form_FrmSearchInfomation();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->formFilter = $frm->filter();
    }
    
    function rptRequestproductsAction(){
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
    			'start_date'	=>	date("Y-m-01"),
				'end_date'		=>	date("Y-m-d"),
    		);
		}
		$this->view->rssearch=$data;
   
   		$rows=$db->getAllRequestStock($data);
   		$this->view->rs=$rows;
    	$frm = new Product_Form_FrmSearchInfomation();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->formFilter = $frm->filter();
    }
    
    function rptRequestProductdetailAction(){
    	$id=$this->getRequest()->getParam('id');
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
    	$db = new Product_Model_DbTable_DbRequestStock();
    	$this->view->row=$db->getRequestStockByids($id);
    	$this->view->rs=$db->getStaffRequestItemsbyId($id);
    	$frm = new Product_Form_FrmSearchInfomation();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->formFilter = $frm->filter();
    }
    
    function rptProcutQtywarningAction(){
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'branch'	=>	'',
    				'brand'		=>	'',
    				'category'	=>	'',
    				'model'		=>	'',
    				'color'		=>	'',
    				'size'		=>	'',
    				'status_qty'=>	-1
    		);
    	}
    	$this->view->search = $db->getBranch($data["branch"]);
    	$this->view->product = $db->getAllProductQtyWarning($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
	
}