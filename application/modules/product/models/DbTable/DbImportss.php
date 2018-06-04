<?php

class Product_Model_DbTable_DbImportss extends Zend_Db_Table_Abstract
{

   // protected $_name = 'tb_category';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
	
	function getSubCategory($title){
		$db = $this->getAdapter();
		$sql = "SELECT c.id FROM `tb_category` AS c WHERE c.`name` = '".$title."'";
		return $db->fetchOne($sql);
	}
	function getMeasure($title){
		$db = $this->getAdapter();
		$sql = "SELECT m.`id` FROM `tb_measure` AS m WHERE m.`name`= '".$title."'";
		return $db->fetchOne($sql);
	}
	function getProduct($cate_title,$title){
		$db = $this->getAdapter();
		$sql = "SELECT p.`id` FROM `tb_product` AS p WHERE p.`cate_id`='".$cate_title."' AND p.`item_name`= '".$title."'";
		return $db->fetchOne($sql);
	}
	
	function getParentCat($title){
		$db = $this->getAdapter();
		$sql = "SELECT c.id FROM `tb_category` AS c WHERE c.`name` = '".$title."' AND c.`parent_id`=0";
		return $db->fetchOne($sql);
	}
	
	function productImport($data){
		$db = $this->getAdapter();
    	$db->beginTransaction();
    	$location=1;
    	$count = count($data);
		$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
		try{
			for($i=2; $i<=$count; $i++){
// 				$rs_paren = $this->getParentCat($data[$i]['F']);
// 				if(empty($rs_paren)){
// 					$arr_parent = array(
// 						'name'	=>	$data[$i]['F'],
// 						'status'	=>	1,
// 						'parent_id'	=>	0,
// 						'date'		=>	date("Y-m-d H:i:s"),
// 						'user_id'	=>	$GetUserId,	
// 					);
// 					$this->_name="tb_category";
// 					$parent_id = $this->insert($arr_parent);
// 				}else{
// 					$parent_id = $rs_paren;
// 				}
//add category
				$parent_id=0;
				$rs_sub_cat=0;
				if($data[$i]['E']!=''){
					$rs_sub_cat = $this->getSubCategory($data[$i]['E']);
					if(empty($rs_sub_cat)){
						$arr_sub = array(
								'name'	=>	$data[$i]['E'],
								'status'	=>	1,
								'parent_id'	=>	0,
								'date'		=>	date("Y-m-d"),
								'user_id'	=>	$GetUserId,
								//'start_code'=>	$data[$i]['M'],
								//'prefix'	=>	$data[$i]['O'],
						);
						$db->getProfiler()->setEnabled(true);
						$this->_name="tb_category";
						$sub_id = $this->insert($arr_sub);
					}else{
						$sub_id = $rs_sub_cat;
					}
				}else{
					$sub_id=0;
				}
//add Measure
				$rs_measure=0;
				if($data[$i]['D']!=''){
					$rs_measure = $this->getMeasure($data[$i]['D']);
					if(empty($rs_measure)){
						$arr_measure = array(
								'name'	=>	$data[$i]['D'],
								'status'	=>	1,
								'date'		=>	date("Y-m-d"),
								'user_id'	=>	$GetUserId,
						);
						$this->_name="tb_measure";
						$measur_id = $this->insert($arr_measure);
					}else{
						$measur_id = $rs_measure;
					}
				}else{
					$measur_id=0;
				}
// 				if($data[$i]['L']=="Use"){
// 					$status = 1;
// 				}else{
// 					$status = 0;
// 				}
//add product
				$rs_product = $this->getProduct($sub_id,$data[$i]['B']);
				if(empty($rs_product)){
					$arr_product = array(
						'item_name'	=>	$data[$i]['B'],
						//'item_code'	=>	$data[$i]['D'],
						//'note'		=>	$data[$i]['G'],
						'measure_id'=>	$measur_id,
						'cate_id'	=>	$sub_id,
						'status'	=>	1,
						//'price'		=>	$data[$i]['F'],
						//'date'		=>	date("Y-m-d"),
						'user_id'	=>	$GetUserId,	
						//'product_type'	=>	$data[$i]['A'],
						//'is_convertor'	=>	$data[$i]['P'],
						//'convertor_measure'	=>	$data[$i]['Q'],
						//'sign'		=> $data[$i]['R'],
					);
// 					$db->getProfiler()->setEnabled(true);
					$this->_name="tb_product";
					$pro_id = $this->insert($arr_product);
// 					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 					$db->getProfiler()->setEnabled(false);
				}else{
					$pro_id = $rs_product;
				}
				$row=$this->getLocation($pro_id, $location);
				if(empty($row)){
					$arr_pro_loc = array(
							'pro_id'			=>	$pro_id,
							'location_id'		=>	1,
							//'qty'				=>	number_format($data[$i]['C'],2),
							'qty'				=>	$data[$i]['C'],
							'qty_warning'		=>	0,
							//'price'				=>	$data[$i]['F'],
							'damaged_qty'		=>	0,
							'last_mod_userid'	=>	$GetUserId,
					);
					$this->_name = "tb_prolocation";
					$this->insert($arr_pro_loc);
				}else{
					$arr_pro_loc = array(
							'pro_id'			=>	$pro_id,
							'location_id'		=>	1,
							//'qty'				=>	number_format($data[$i]['C'],2),
							'qty'				=>	$data[$i]['C'],
							'qty_warning'		=>	0,
							//'price'				=>	$data[$i]['F'],
							'damaged_qty'		=>	0,
							'last_mod_userid'	=>	$GetUserId,
					);
					$this->_name = "tb_prolocation";
					$where=" id=".$row['id'];
					$this->update($arr_pro_loc, $where);
				}
			}
		//exit();	
		$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
     		echo $e->getMessage();exit();
		}
	}
	
	function getLocation($pro_id,$location){
		$db = $this->getAdapter();
		$sql = "SELECT p.`id` FROM `tb_product` AS p,`tb_prolocation` AS l
			     WHERE p.`id`=l.`pro_id`
			     AND p.id= $pro_id
			     AND l.`location_id`= $location LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function oldproductImport($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$count = count($data);
	
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
	
		try{
			for($i=1; $i<=$count; $i++){
				$rs_paren = $this->getParentCat($data[$i]['H']);
				if(empty($rs_paren)){
					$arr_parent = array(
							'name'	=>	$data[$i]['H'],
							'status'	=>	1,
							'parent_id'	=>	0,
							'date'		=>	date("Y-m-d"),
							'user_id'	=>	$GetUserId,
							//'start_code'=>	$data[$i]['M'],
							//'prefix'	=>	$data[$i]['N'],
					);
					$db->getProfiler()->setEnabled(true);
					$this->_name="tb_category";
					$parent_id = $this->insert($arr_parent);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
					$db->getProfiler()->setEnabled(false);
	
				}else{
					$parent_id = $rs_paren;
				}
	
				$rs_sub_cat = $this->getSubCategory($parent_id,$data[$i]['J']);
				if(empty($rs_sub_cat)){
					$arr_sub = array(
							'name'	=>	$data[$i]['J'],
							'status'	=>	1,
							'parent_id'	=>	$parent_id,
							'date'		=>	date("Y-m-d"),
							'user_id'	=>	$GetUserId,
							//'start_code'=>	$data[$i]['M'],
							//'prefix'	=>	$data[$i]['O'],
					);
					$db->getProfiler()->setEnabled(true);
					$this->_name="tb_category";
					$sub_id = $this->insert($arr_sub);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
					$db->getProfiler()->setEnabled(false);
					//echo count($rs_sub_cat);
				}else{
					$sub_id = $rs_sub_cat;
				}
	
	
				$rs_measure = $this->getMeasure($data[$i]['C']);
				if(empty($rs_measure)){
					$arr_measure = array(
							'name'	=>	$data[$i]['C'],
							'status'	=>	1,
							'date'		=>	date("Y-m-d"),
							'user_id'	=>	$GetUserId,
					);
					$db->getProfiler()->setEnabled(true);
					$this->_name="tb_measure";
					$measur_id = $this->insert($arr_measure);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
					$db->getProfiler()->setEnabled(false);
					//echo count($rs_sub_cat);
				}else{
					$measur_id = $rs_measure;
				}
	
				if($data[$i]['L']=="Use"){
					$status = 1;
				}else{
					$status = 0;
				}
				$rs_product = $this->getProduct($data[$i]['J'],$data[$i]['B']);
				if(empty($rs_product)){
					$arr_product = array(
							'item_name'	=>	$data[$i]['B'],
							'item_code'	=>	$data[$i]['A'],
							'note'		=>	$data[$i]['G'],
							'measure_id'=>	$measur_id,
							'cate_id'	=>	$sub_id,
							'status'	=>	$status,
							'price'		=>	$data[$i]['F'],
							//'date'		=>	date("Y-m-d"),
							'user_id'	=>	$GetUserId,
	
							//'is_convertor'	=>	$data[$i]['P'],
							//'convertor_measure'	=>	$data[$i]['Q'],
							//'sign'		=> $data[$i]['R'],
					);
					$db->getProfiler()->setEnabled(true);
					$this->_name="tb_product";
					$pro_id = $this->insert($arr_product);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
					$db->getProfiler()->setEnabled(false);
					//echo count($rs_sub_cat);
				}else{
					$pro_id = $rs_product;
				}
	
			}
			//exit();
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
				
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();exit();
		}
	}
	
	function getProductByCode($code){
		$db = $this->getAdapter();
		$sql = "SELECT p.id FROM `tb_product` AS p WHERE p.`item_code`= '".$code."'";
		return $db->fetchOne($sql);
	}
	function getLocations($code){
		$db = $this->getAdapter();
		$sql = "SELECT s.id FROM `tb_sublocation` AS s WHERE s.`name`= '".$code."'";
		return $db->fetchOne($sql);
	}
	
	function ProLocation($data){
		
		$db = $this->getAdapter();
    	$db->beginTransaction();
    	$count = count($data);
		$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
		
		try{
			for($i=2; $i<=$count; $i++){
				$rs_pro = $this->getProductByCode($data[$i]['A']);
				if(empty($rs_pro)){
					$arr_product = array(
						'item_name'	=>	$data[$i]['B'],
						'item_code'	=>	$data[$i]['A'],
						'note'		=>	$data[$i]['G'],
						//'measure_id'=>	$measur_id,
						//'cate_id'	=>	$sub_id,
						'status'	=>	1,
						'date'		=>	date("Y-m-d"),
						'user_id'	=>	$GetUserId,	
					);
					$db->getProfiler()->setEnabled(true);
					$this->_name="tb_product";
					$pro_id = $this->insert($arr_product);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);

				}else{
					$pro_id = $rs_pro;
				}
				
				/*$rs_loc = $this->getLocation($data[$i]['G']);
				if(empty($rs_loc)){
					$arr_loc = array(
						'name'				=>	$data[$i]['G'],
						'branch_code'		=>	$data[$i]['I'],
						'contact'			=>	$data[$i]['H'],
						'phone'				=>	$data[$i]['K'],
						'address'			=>	$data[$i]['J'],
						'email'				=>	$data[$i]['L'],
						//'office_tel'		=>  $data[$i]['H'],
						'fax'				=>	$data[$i]['M'],
						'status'			=>	1,
						'user_id'			=>	$GetUserId,	
					);
					$db->getProfiler()->setEnabled(true);
					$this->_name="tb_sublocation";
					$loc_id = $this->insert($arr_loc);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);

				}else{
					$loc_id = $rs_loc;
				}*/
				
				$arr_pro_loc = array(
					'pro_id'			=>	$pro_id,
					'location_id'		=>	1,
					'qty'				=>	number_format($data[$i]['D'],2),
					'qty_warning'		=>	0,
					'price'				=>	$data[$i]['F'],
					'damaged_qty'		=>	0,
					'last_mod_userid'	=>	$GetUserId,	
				);
				$db->getProfiler()->setEnabled(true);
				$this->_name = "tb_prolocation";
				$this->insert($arr_pro_loc);
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
				
				
			}
		//exit();	
		$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
     		echo $e->getMessage();exit();
		}
	}
	
 
}  
	  

