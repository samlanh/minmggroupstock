<?php

class Rsvacl_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		$db = new Application_Model_DbTable_DbGlobal();
		$rs = $db->getValidUserUrl();
		if(empty($rs)){
			//Application_Form_FrmMessage::Sucessfull("YOU_NO_PERMISION_TO_ACCESS_THIS_SECTION","/index/dashboad");
		}
    }

    public function indexAction()
    {
    
    if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			 
			try {
				$_dbcar = new Rsvacl_Model_DbTable_DbUserType();
				$_dbcar->updateUser($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/rsvacl/user");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/rsvacl/user");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err = $e->getMessage(); 
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
	}		 
    function userProfileAction(){
    	$frm = new Rsvacl_Form_FrmUser();
    	$frm_request=$frm->init();
    	Application_Model_Decorator::removeAllDecorator($frm_request);
    	$this->view->frm_pic = $frm_request;
    	
    	$id=$this->getRequest()->getParam('id');
    	
		$db=new Rsvacl_Model_DbTable_DbUserType();
		$rs=$this->view->rs=$db->getUserProfileById($id);
		
		
		//$rs=$db->getUserProfileById($id);
		
    	 
    }
}

