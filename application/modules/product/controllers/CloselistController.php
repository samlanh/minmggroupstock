<?php
class Product_CloselistController extends Zend_Controller_Action
{
	const REDIRECT_URL_ADD ='/product/closelist/add';
	const REDIRECT_URL_ADD_CLOSE ='/product/closelist/';
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
   		$db = new Product_Model_DbTable_DbCloselist();
		$date =new Zend_Date();
   		if($this->getRequest()->isPost()){   
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data["start_date"]));
    		$data['end_date']=date("Y-m-d",strtotime($data["end_date"]));
    	}else{
			$data = array(
    			'ad_search'		=>	'',
			    'branch'        =>	'',
    			'start_date'	=>	date("Y-m-d"),
				'end_date'		=>	date("Y-m-d"),
    		);
		}
   
		$rows=$db->getAllCloseList($data);
   		$columns=array("LOCATION_NAME","CLOSE_DATE","MODIFY_DATE","NOTE","USER_NAME");
   		$link=array(
   		    'module'=>'product','controller'=>'closelist','action'=>'edit',
   		);
   		$this->view->list=$list->getCheckList(0, $columns, $rows,array('location'=>$link,'close_date'=>$link,'modify_date'=>$link,));
   		$frm = new Product_Form_FrmCloselist();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->formFilter = $frm->filter();
   	}
    
    public function addAction()
    {   
    	if($this->getRequest()->isPost()){   
    		$post=$this->getRequest()->getPost();
    		$db_adjust= new Product_Model_DbTable_DbCloselist();
    		$db_result = $db_adjust->addCloseList($post);
    		if(isset($post["save_close"])){
    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL_ADD_CLOSE);
    		}else{
    		    Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL_ADD);
			}
    	}
    	//for add location
    	$frm = new Product_Form_FrmCloselist();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->formFilter = $frm->add();
	}
	
	public function editAction()
	{
	    $id=$this->getRequest()->getParam('id');
	    $db_adjust= new Product_Model_DbTable_DbCloselist();
	    if($this->getRequest()->isPost()){
	        $post=$this->getRequest()->getPost();
	        $post['id']=$id;
	        $db_result = $db_adjust->updateCloseList($post);
	        if(isset($post["save_close"])){
	            Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS",self::REDIRECT_URL_ADD_CLOSE);
	        }else{
	            Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS",self::REDIRECT_URL_ADD_CLOSE);
	        }
	    }
	    //for add location
	    if(empty($id)){
	        Application_Form_FrmMessage::Sucessfull("ID not found!",self::REDIRECT_URL_ADD_CLOSE);
	    }
	    $row=$db_adjust->getCloselistById($id);
	    $this->view->rows=$db_adjust->getCloselistDetail($id);
	    $frm = new Product_Form_FrmCloselist();
	    Application_Model_Decorator::removeAllDecorator($frm);
	    $this->view->formFilter = $frm->add($row);
	   
	}
	
	public function getprocloselistAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Product_Model_DbTable_DbCloselist();
			$rs = $db->getAllProductCloselist($post['pro_id'],$post['location_id'],$post['type_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
}