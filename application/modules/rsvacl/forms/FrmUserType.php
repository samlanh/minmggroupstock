<?php 
class Rsvacl_Form_FrmUserType extends Zend_Form
{

	public function init()
    {
    	//user name    	
    	$user_type=new Zend_Form_Element_Text('user_type');
    	$user_type->setAttribs(array(
    		'id'=>'user_type',
    		'required'=>true,
    		'readonly'=>true,
    		'class'=>'form-control',
    		'required'=>true
    	));
    	$this->addElement($user_type);
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	//Main parent of user type
		$db=new Application_Model_DbTable_DbGlobal();
		$rs=$db->getGlobalDb('SELECT user_type_id,user_type FROM tb_acl_user_type');
		$options=array(''=>$tr->translate('PLEASE_SELECT'));
		foreach($rs as $read) $options[$read['user_type_id']]=$read['user_type'];
		$user_type_id=new Zend_Form_Element_Select('parent_id');		
    	$user_type_id->setMultiOptions($options);
    	$user_type_id->setAttribs(array(
    		'id'=>'parent_id',
    		'class'=>'form-control',
    	));
    	$this->addElement($user_type_id);
    }
}

?>