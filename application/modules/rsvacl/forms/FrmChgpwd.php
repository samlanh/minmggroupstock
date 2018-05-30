<?php
class RsvAcl_Form_FrmChgpwd extends Zend_Form
{
	public function init($rs=null)
	{
//current password
		$current_password=new Zend_Form_Element_Password('current_password');
    	$current_password->setAttribs(array(
    		'id'=>'current_password',
    		'class'=>'form-control',
    	));	
    	$this->addElement($current_password);
//password    	
    	$password=new Zend_Form_Element_Password('password');
    	$password->setAttribs(array(
    		'id'=>'password',
    		'class'=>'form-control',
    	));
    	$this->addElement($password);
//confirm password   
    	$confirm_password=new Zend_Form_Element_Password('confirm_password');
    	$confirm_password->setAttribs(array(
    		'id'=>'confirm_password',
    		'class'=>'form-control',
    	));
    	if($rs!=""){
    		print_r($rs);exit();
    	
    	}
    	
    	$this->addElement($confirm_password);
	}
	 
	
}