<?php

class Product_Model_DbTable_DbGetBranch extends Zend_Db_Table_Abstract
{
	public function getBranchbyUser($user_id){
		$db=$this->getAdapter();
		$sql=" SELECT name,location_id 
		FROM tb_sublocation AS l, tb_acl_ubranch AS ul
		WHERE  l.id = ul.location_id AND ul.user_id= $user_id AND name!='' ";
		$row=$db->fetchAll($sql);
		return $row;
	}
	
	//fucntion sum product smaller than 10 in stock
	public function getSumNumProduct($user_id){
		$db=$this->getAdapter();
		$sql="SELECT COUNT(p.id)
				FROM tb_product AS p,tb_prolocation AS pl
				WHERE p.id=pl.pro_id  
				AND pl.qty<=pl.`qty_warning` ";
		$row=$db->fetchOne($sql);
		return $row;
	}
   
    
}