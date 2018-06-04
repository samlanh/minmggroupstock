<?php
class Product_ImportssController extends Zend_Controller_Action {
	
	const REDIRECT_URL_ADD ='/Product/measure/add';
	const REDIRECT_URL_CLOSE ='/Product/measure/index';
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$db = new Application_Model_DbTable_DbGlobal();
    	$rs = $db->getValidUserUrl();
    	if(empty($rs)){
    		Application_Form_FrmMessage::Sucessfull("YOU_NO_PERMISION_TO_ACCESS_THIS_SECTION","/index/dashboad");
    	}
	}
	public function indexAction(){
		try{
			include  PUBLIC_PATH.'/Classes/PHPExcel/IOFactory.php';
			$db=new Product_Model_DbTable_DbImportss();
			if($this->getRequest()->isPost()){
				$data=$this->getRequest()->getPost();
				
				$adapter = new Zend_File_Transfer_Adapter_Http();
				$part= PUBLIC_PATH.'/images';
				$adapter->setDestination($part);
				$adapter->receive();
				$file = $adapter->getFileInfo();
				//print_r($file['file_excel']['tmp_name']);exit();
				$inputFileName = $file['file_excel']['tmp_name'];
 				try {
					$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
				} catch(Exception $e) {
					die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
				}
				
				$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
				//print_r($sheetData);exit();
				$db->productImport($sheetData);
				//Application_Form_FrmMessage::message("Import Successfully");
				Application_Form_FrmMessage::Sucessfull("Import Successfully", '/product/index');
			}
			else{
			}
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
		Application_Model_Decorator::removeAllDecorator($formFilter);
		
	}
	
}

