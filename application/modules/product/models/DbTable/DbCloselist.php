<?php

class Product_Model_DbTable_DbCloselist extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_product";
	public function setName($name)
	{
		$this->_name=$name;
	}
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	
	function getAllCloseList($data=null){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT id,
                       (SELECT l.name FROM `tb_sublocation` AS l WHERE l.id=tb_closelist.`location_id` LIMIT 1) AS location, 
                        DATE_FORMAT(close_date, '%d-%M-%Y') as close_date,
                        DATE_FORMAT(modify_date, '%d-%M-%Y') as modify_date,
                        note,
                        (SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id=tb_closelist.`user_id` LIMIT 1) AS user_name
                FROM `tb_closelist`";
				$from_date =(empty($data['start_date']))? '1': " close_date >= '".$data['start_date']." 00:00:00'";
				$to_date = (empty($data['end_date']))? '1': " close_date <= '".$data['end_date']." 23:59:59'";
				$where = " where ".$from_date." AND ".$to_date;
		 		if($data["ad_search"]!=""){
		 			$s_where=array();
		 			$s_search=addslashes(trim($data['ad_search']));
		 			$s_search = str_replace(' ', '', $s_search);
		 			$s_where[]="REPLACE(note,' ','')   LIKE '%{$s_search}%'";
		 			$where.=' AND ('.implode(' OR ', $s_where).')';
		 		} 
		 		if(!empty($data["branch"])){
		 			$where.=' AND `location_id`='.$data["branch"];
		 		}
// 		 		if(!empty($data["staff_id"])){
// 		 			$where.=' AND sr.`staff_id`='.$data["staff_id"];
// 		 		}
		$location = $db_globle->getAccessPermission('`location_id`');
		$order=' ORDER BY id DESC';
		//echo $sql;
		return $db->fetchAll($sql.$where.$order.$location);
	}
	
	function getRequestStockByids($reques_id){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT sr.id,(SELECT l.name FROM `tb_sublocation` AS l WHERE l.id=sr.`branch_id` AND l.status=1 LIMIT 1) AS location_name,
		sr.reques_no,(SELECT s.staff_name FROM `tb_staff` AS s WHERE s.id=sr.`staff_id` LIMIT 1) AS staff_name,
		(SELECT SUM(sd.`total_qty`) FROM `tb_staff_request_detail` AS sd WHERE sd.staff_request_id=sr.id AND sr.`status`=1 LIMIT 1)AS qty,
		(SELECT SUM(sd.`request_qty`) FROM `tb_staff_request_detail` AS sd WHERE sd.staff_request_id=sr.id AND sr.`status`=1 LIMIT 1)AS qty_receive,
		sr.date_request,sr.receive_date,sr.note,
		(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id=sr.user_id LIMIT 1 )AS user_id,
		(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=sr.status LIMIT 1 )AS `status`,
		(SELECT s.staff_name FROM `tb_staff` AS s WHERE s.id=sr.`staff_id` LIMIT 1) AS staff_name,
		(SELECT s.staff_no FROM `tb_staff` AS s WHERE s.id=sr.`staff_id` LIMIT 1) AS staff_no,
		(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=(SELECT s.position_id FROM `tb_staff` AS s WHERE s.id=sr.`staff_id`) AND v.type=16 ) AS staff_position
		
		FROM `tb_staff_request` AS sr where sr.id=$reques_id";
		return $db->fetchRow($sql);
	}
	
	public function addCloseList($data){
	    $db = $this->getAdapter();
	    $db->beginTransaction();
	    try{
	        $user_info = new Application_Model_DbTable_DbGetUserInfo();
	        $result = $user_info->getUserInfo();
	        $date =new Zend_Date();
	        
	        $arr = array(
	            'close_num'	    =>	$data["close_no"],
	            'location_id'	=>	$data["from_loc"],
	            'close_date'	=>	date("Y-m-d",strtotime($data["close_date"])),
	            'create_date'	=>	date("Y-m-d H:i:s"),
	            'modify_date'	=>	date("Y-m-d H:i:s"),
	            'note'	        =>	$data["note"],
	            'status'		=>	1,
	            'user_id'		=>	$result["user_id"],
	        );
	        $this->_name="tb_closelist";
	       $close_id=$this->insert($arr);
	        
	        if(!empty($data['identity'])){
	            $identitys = explode(',',$data['identity']);
	            foreach($identitys as $i)
	            {
	                $arr = array(
	                    'close_id'		=>	$close_id,
	                    'pro_id'		=>	$data["pro_id_".$i],
	                    'curr_qty'		=>	$data["current_qty_".$i],
	                    'qty_unit'		=>	$data["qty_unit_".$i],
	                    'qty_per_unit'	=>	$data["qty_per_unit_".$i],
	                    'qty_measure'	=>	$data["qty_measure_".$i],
	                    'qty_adjust'	=>	$data["qty_".$i],
	                    'create_date'	=>	date('Y-m-d'),
	                    'remark'		=>	$data["remark_".$i],
	                    'user_id'		=>	$result["user_id"],
	                );
	                $this->_name="tb_closelist_detail";
	                $this->insert($arr);
	                
	                if($data["current_qty_".$i]!=$data["qty_".$i]){
	                    $rs = $this->getProductQtyById($data["pro_id_".$i],$data["from_loc"]);
	                    if(!empty($rs)){
	                        $arr_p = array(
	                            'qty'			=>	$data["qty_".$i],
	                            //'damaged_qty'	=>	$rs["damaged_qty"]+$data["qty_".$i],
	                        );
	                        $this->_name="tb_prolocation";
	                        $where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$data["from_loc"]);
	                        $this->update($arr_p, $where);
	                    }else{
	                        $arr_p = array(
	                            'pro_id'			=>	$data["pro_id_".$i],
	                            'location_id'		=>	$result["branch_id"],
	                            'qty'				=>	$data["qty_".$i],
	                            'damaged_qty'		=>	0,
	                            'qty_warning'		=>	0,
	                            'last_mod_userid'	=>	$result["user_id"],
	                            'last_mod_date'		=>	date('Y-m-d'),
	                        );
	                        $this->_name="tb_prolocation";
	                        $this->insert($arr_p);
	                    }
	                }
	            }
	        }
	        
	        $db->commit();
	    }catch (Exception $e){
	        $db->rollBack();
	        Application_Model_DbTable_DbUserLog::writeMessageError($e);
	        echo $e->getMessage();exit();
	    }
	}
	
	public function updateCloseList($data){
	    $db = $this->getAdapter();
	    $db->beginTransaction();
	    try{
	        $user_info = new Application_Model_DbTable_DbGetUserInfo();
	        $result = $user_info->getUserInfo();
	        $date =new Zend_Date();
	        $rows=$this->getCloselistDetailByid($data['id'],$data['from_loc']);
	        if(!empty($rows))foreach ($rows as $row){
	            $rs = $this->getProductQtyById($row["pro_id"],$data["from_loc"]);
	            if(!empty($rs)){
	                $arr_p = array(
	                    'qty'			=>	$row["curr_qty"],
	                    //'damaged_qty'	=>	$rs["damaged_qty"]+$data["qty_".$i],
	                );
	                $this->_name="tb_prolocation";
	                $where = array('pro_id=?'=>$row["pro_id"],"location_id=?"=>$data["from_loc"]);
	                $this->update($arr_p, $where);
	            }
	        }
	        
	        $arr = array(
	            'close_num'	    =>	$data["close_no"],
	            'location_id'	=>	$data["from_loc"],
	            'close_date'	=>	date("Y-m-d",strtotime($data["close_date"])),
	            'modify_date'	=>	date("Y-m-d H:i:s"),
	            'note'	        =>	$data["note"],
	            'status'		=>	1,
	            'user_id'		=>	$result["user_id"],
	        );
	        $this->_name="tb_closelist";
	        $where=" id=".$data['id'];
	        $close_id=$data['id'];
	        $this->update($arr, $where);
	        
	        $sql = "DELETE FROM tb_closelist_detail WHERE close_id=".$data["id"];
	        $db->query($sql);
	        if(!empty($data['identity'])){
	            $identitys = explode(',',$data['identity']);
	            foreach($identitys as $i)
	            {
	                $arr = array(
	                    'close_id'		=>	$close_id,
	                    'pro_id'		=>	$data["pro_id_".$i],
	                    'curr_qty'		=>	$data["current_qty_".$i],
	                    'qty_unit'		=>	$data["qty_unit_".$i],
	                    'qty_per_unit'	=>	$data["qty_per_unit_".$i],
	                    'qty_measure'	=>	$data["qty_measure_".$i],
	                    'qty_adjust'	=>	$data["qty_".$i],
	                    'create_date'	=>	date('Y-m-d'),
	                    'remark'		=>	$data["remark_".$i],
	                    'user_id'		=>	$result["user_id"],
	                );
	                $this->_name="tb_closelist_detail";
	                $this->insert($arr);
	                
	                if($data["current_qty_".$i]!=$data["qty_".$i]){
	                    $rs = $this->getProductQtyById($data["pro_id_".$i],$data["from_loc"]);
	                    if(!empty($rs)){
	                        $arr_p = array(
	                            'qty'			=>	$data["qty_".$i],
	                            //'damaged_qty'	=>	$rs["damaged_qty"]+$data["qty_".$i],
	                        );
	                        $this->_name="tb_prolocation";
	                        $where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$data["from_loc"]);
	                        $this->update($arr_p, $where);
	                    }else{
	                        $arr_p = array(
	                            'pro_id'			=>	$data["pro_id_".$i],
	                            'location_id'		=>	$result["branch_id"],
	                            'qty'				=>	$data["qty_".$i],
	                            'damaged_qty'		=>	0,
	                            'qty_warning'		=>	0,
	                            'last_mod_userid'	=>	$result["user_id"],
	                            'last_mod_date'		=>	date('Y-m-d'),
	                        );
	                        $this->_name="tb_prolocation";
	                        $this->insert($arr_p);
	                    }
	                }
	            }
	        }
	        
	        $db->commit();
	    }catch (Exception $e){
	        $db->rollBack();
	        Application_Model_DbTable_DbUserLog::writeMessageError($e);
	        echo $e->getMessage();exit();
	    }
	}
	
	function getProductName(){
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`id`,
				  p.`item_name` ,
				  p.`item_code`,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id` limit 1) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id` limit 1) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.key_code=p.`model_id` and type=2 limit 1) AS model,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.key_code=p.`color_id` and type=4 limit 1) AS color,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.key_code=p.`size_id` and type=3 limit 1) AS size
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl 
				WHERE p.`id` = pl.`pro_id` AND p.status=1 ";
		//$location = $db_globle->getAccessPermission('pl.`location_id`');
		return $db->fetchAll($sql);
	}
	function getProductById($id){
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$db = $this->getAdapter();
		$sql = "SELECT
				  p.`id`,
				  p.`item_name` ,
				  p.`qty_perunit` ,
				  p.`item_code`,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size,
				  pl.`qty`
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl
				WHERE p.`id` = pl.`pro_id` AND p.`id`=$id ";
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		return $db->fetchRow($sql.$location);
	}
	
	function getProductQtyById($id,$location){
		$db = $this->getAdapter();
		$sql = "SELECT
				  p.`id`,
				  pl.id as pl_id,
				  pl.pro_id,
				  pl.`location_id`,
				  p.`item_name` ,
				  p.`qty_perunit` ,
				  p.`item_code`,
				  p.`unit_label`,
				  (SELECT m.`name` FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size,
				  pl.`qty`,
				  pl.damaged_qty
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl
				WHERE p.`id` = pl.`pro_id` AND p.`id`=$id AND pl.`location_id` = $location ";
		
		return $db->fetchRow($sql);
	}
	
	function getProductCloseList($location){
		$db = $this->getAdapter();
		$sql = "SELECT
		p.`id`,
		pl.id as pl_id,
		pl.pro_id,
		pl.`location_id`,
		p.`item_name` ,
		p.`qty_perunit` ,
		p.`item_code`,
		p.`unit_label`,
		(SELECT m.`name` FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure,
		(SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
		(SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
		(SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
		(SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
		(SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size,
		pl.`qty`,
		pl.damaged_qty
		FROM
		`tb_product` AS p,
		`tb_prolocation` AS pl
		WHERE p.`id` = pl.`pro_id` AND pl.`location_id` = $location ";
		return $db->fetchRow($sql);
	}
	
	//for get current qty time /26-8-13
	public function getCurrentItem($post){
		$db=$this->getAdapter();
		$sql = "SELECT qty FROM tb_prolocation WHERE pro_id =" .$post['item_id'] ." AND LocationId = ".$post['location_id']." LIMIT 1";
		$row=$db->fetchRow($sql);
		return($row);
	}
	
	
	function getStaffRequestById($id){
		$db = $this->getAdapter();
		$sql = " SELECT * FROM `tb_staff_request` WHERE id=$id";
	  	return $db->fetchRow($sql);
	}
	
	function getAllProductCloselist($pro_id,$location,$type){
		$db= $this->getAdapter();
		$sql="  SELECT
		p.`id`,
		p.`item_name` ,
		p.`qty_perunit` ,
		p.`item_code`,
		p.`unit_label`,
		p.price,
		(SELECT m.`name` FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure,
		(SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
		(SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
		(SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
		(SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
		(SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size,
		pl.`qty`,
		pl.damaged_qty
		FROM
		`tb_product` AS p,
		`tb_prolocation` AS pl WHERE p.`id` = pl.`pro_id`  ";
		if($type==1){//by Product and Branch
			$sql.=" AND p.`id`=$pro_id AND pl.`location_id` = $location";
// 			$sql.=" ORDER BY p.id DESC ";
		}else{//by Branch
			$sql.=" AND  pl.`location_id`=$location ";
		}
		return  $db->fetchAll($sql);
	}
	
	function getCloselistById($id){
	    $db = $this->getAdapter();
	    $sql = " SELECT * FROM `tb_closelist` WHERE id=$id";
	    return $db->fetchRow($sql);
	}
	
	function getCloselistDetail($id){
	    $db = $this->getAdapter();
	    $sql = " SELECT  p.`id`,
    	p.`item_name` ,
    	p.`qty_perunit` ,
    	p.`item_code`,
    	p.`unit_label`,
    	p.price,
    	(SELECT m.`name` FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure,
    	(SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
    	(SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
    	(SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
    	(SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
    	(SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size,
    	 cd.`pro_id`,cd.`curr_qty`,cd.`qty_unit`,cd.`qty_per_unit`,`qty_measure`,`qty_adjust`,`remark`
        FROM `tb_closelist` AS c,`tb_closelist_detail` AS cd,`tb_product` AS p
        WHERE c.id=cd.`close_id`
        AND p.`id`=cd.`pro_id`
        AND cd.`close_id`=$id";
	    return $db->fetchAll($sql);
	}
	
	function getCloselistDetailByid($id,$location){
	    $db = $this->getAdapter();
	    $sql = " SELECT  p.`id`,
	    p.`item_name` ,
	    p.`qty_perunit` ,
	    p.`item_code`,
	    p.`unit_label`,
	    p.price,
	    (SELECT m.`name` FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure,
	    (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
	    (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
	    (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
	    (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
	    (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size,
	    cd.`pro_id`,cd.`curr_qty`,cd.`qty_unit`,cd.`qty_per_unit`,`qty_measure`,`qty_adjust`,`remark`
	    FROM `tb_closelist` AS c,`tb_closelist_detail` AS cd,`tb_product` AS p
	    WHERE c.id=cd.`close_id`
	    AND p.`id`=cd.`pro_id`
	    AND cd.`close_id`=$id
	    AND c.`location_id`=$location";
	    return $db->fetchAll($sql);
	}
	
	
}