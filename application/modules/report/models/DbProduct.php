<?php 
Class report_Model_DbProduct extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getBranch($id){
		$db = $this->getAdapter();
		$sql ="SELECT b.`name` FROM `tb_sublocation` AS b WHERE b.`id`='".$id."'";
		return $db->fetchOne($sql);
	}
	
	function getAllProduct($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT
				  p.`id`,
				  p.`barcode`,
				  p.`item_code`,
				  p.`item_name` ,
	  			  p.`serial_number`,
	  			  p.`status`,
	  			  p.`unit_label`,
				  p.`qty_perunit`,
				   p.`price`,
				  pl.`location_id`,
				   (SELECT b.`name` FROM `tb_sublocation` AS b WHERE b.`id`=pl.`location_id` LIMIT 1) AS branch,
				  (SELECT b.`name` FROM `tb_brand` AS b WHERE b.`id`=p.`brand_id` LIMIT 1) AS brand,
				  (SELECT c.name FROM `tb_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
				  (SELECT m.name FROM `tb_model` AS m WHERE m.id=p.`model_id` LIMIT 1) AS model,
				  (SELECT s.name FROM `tb_size` AS s WHERE s.id=p.`size_id` LIMIT 1) AS size,
				  (SELECT c.name FROM `tb_color` AS c WHERE c.id=p.`color_id` LIMIT 1) AS color,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
				  (SELECT pp.`price` FROM `tb_product_price` AS pp WHERE pp.`pro_id`=p.`id` AND `type_id`=1 LIMIT 1) AS master_price,
				(SELECT pp.`price` FROM `tb_product_price` AS pp WHERE pp.`pro_id`=p.`id` AND `type_id`=2 LIMIT 1) AS dealer_price,
				  SUM(pl.`qty`) AS qty
				FROM
				  `tb_product` AS p ,
				  `tb_prolocation` AS pl
				WHERE 
			    p.status=1
				AND p.`id`=pl.`pro_id` ";
		$where = '';
		if($data["ad_search"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['ad_search']));
			$s_where[]= " p.item_name LIKE '%{$s_search}%'";
			$s_where[]=" p.barcode LIKE '%{$s_search}%'";
			$s_where[]= " p.item_code LIKE '%{$s_search}%'";
			$s_where[]= " p.serial_number LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["branch"]!=""){
			$where.=' AND pl.`location_id`='.$data["branch"];
		}
		if($data["brand"]!=""){
			$where.=' AND p.brand_id='.$data["brand"];
		}
		if($data["category"]!=""){
			$where.=' AND p.cate_id='.$data["category"];
		}
		if($data["category"]!=""){
			$where.=' AND p.cate_id='.$data["category"];
		}
		if($data["model"]!=""){
			$where.=' AND p.model_id='.$data["model"];
		}
		if($data["size"]!=""){
			$where.=' AND p.size_id='.$data["size"];
		}
		if($data["color"]!=""){
			$where.=' AND p.color_id='.$data["color"];
		}
		if($data["status_qty"]>-1){
			if($data["status_qty"]==1){
				$where.=' AND pl.qty>0';
			}else{
				$where.=' AND pl.qty=0';
			}
			
		}
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		$group = " GROUP BY p.`id` ORDER BY p.`item_name`";
		return $db->fetchAll($sql.$where.$location.$group);
	}
	
	function getQtyProductByProIdLoca($id,$loc_id){
		$db = $this->getAdapter();
		$sql = "SELECT pl.`qty` FROM `tb_prolocation` AS pl  WHERE pl.`pro_id`=$id AND pl.`location_id`=$loc_id";
		return $db->fetchOne($sql);
	}
	function getAllLOcation(){
		$db = $this->getAdapter();
		$sql = "SELECT s.`prefix`,s.`id`  FROM `tb_sublocation` AS s WHERE s.`status`=1";
		return $db->fetchAll($sql);
	}
	
	function getAllAdjustStock($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT 
				  m.* ,
				  p.`item_name`,
				  p.`barcode`,
				  p.`item_code`,
				  m.`cur_qty`,
				  m.`qty_adjust`,
				  (m.`qty_adjust`-m.`cur_qty`) AS defer_qty,
				  (SELECT b.`name` FROM `tb_brand` AS b WHERE b.`id` = p.`brand_id`) AS brand ,
				  (SELECT b.`name` FROM `tb_category` AS b WHERE b.`id` = p.`cate_id`) AS cat ,
				  (SELECT v.`name_en` FROM `tb_view` AS v WHERE v.id = p.`color_id` AND v.`type`=4) AS color,
				  (SELECT v.`name_en` FROM `tb_view` AS v WHERE v.id = p.`color_id` AND v.`type`=2) AS model,
				  (SELECT v.`name_en` FROM `tb_view` AS v WHERE v.id = p.`color_id` AND v.`type`=3) AS size,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
				  (SELECT s.`name` FROM `tb_sublocation` AS s WHERE s.id=m.`location_id` LIMIT 1) AS location,
				  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=m.`user_id` LIMIT 1) AS `username`,
				   m.`date`
				FROM
				  `tb_product_adjust` AS m ,
				  `tb_product` AS p
				WHERE m.`pro_id`=p.`id`";
	    $where = '';
		if($data["ad_search"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['ad_search']));
			$s_where[]= " p.item_name LIKE '%{$s_search}%'";
			$s_where[]=" p.barcode LIKE '%{$s_search}%'";
			$s_where[]= " p.item_code LIKE '%{$s_search}%'";
			$s_where[]= " p.serial_number LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["branch"]!=""){
			$where.=' AND m.`location_id`='.$data["branch"];
		}
		if($data["brand"]!=""){
			$where.=' AND p.brand_id='.$data["brand"];
		}
		if($data["category"]!=""){
			$where.=' AND p.cate_id='.$data["category"];
		}
		if($data["category"]!=""){
			$where.=' AND p.cate_id='.$data["category"];
		}
		if($data["model"]!=""){
			$where.=' AND p.model_id='.$data["model"];
		}
		if($data["size"]!=""){
			$where.=' AND p.size_id='.$data["size"];
		}
		if($data["color"]!=""){
			$where.=' AND p.color_id='.$data["color"];
		}
// 		 
		$location = $db_globle->getAccessPermission('m.`location_id`');
		//echo $location;
		return $db->fetchAll($sql.$where.$location);
			
	}
	
	function getAllProductQtyWarning($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT
		p.`id`,
		p.`barcode`,
		p.`item_code`,
		p.`item_name` ,
		p.`serial_number`,
		p.`status`,
		p.`unit_label`,
		p.`qty_perunit`,
		p.`price`,
		pl.qty_warning,
		pl.`location_id`,
		(SELECT b.`name` FROM `tb_sublocation` AS b WHERE b.`id`=pl.`location_id` LIMIT 1) AS branch,
		(SELECT b.`name` FROM `tb_brand` AS b WHERE b.`id`=p.`brand_id` LIMIT 1) AS brand,
		(SELECT c.name FROM `tb_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
		(SELECT m.name FROM `tb_model` AS m WHERE m.id=p.`model_id` LIMIT 1) AS model,
		(SELECT s.name FROM `tb_size` AS s WHERE s.id=p.`size_id` LIMIT 1) AS size,
		(SELECT c.name FROM `tb_color` AS c WHERE c.id=p.`color_id` LIMIT 1) AS color,
		(SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
		(SELECT pp.`price` FROM `tb_product_price` AS pp WHERE pp.`pro_id`=p.`id` AND `type_id`=1 LIMIT 1) AS master_price,
		(SELECT pp.`price` FROM `tb_product_price` AS pp WHERE pp.`pro_id`=p.`id` AND `type_id`=2 LIMIT 1) AS dealer_price,
		SUM(pl.`qty`) AS qty
		FROM
		`tb_product` AS p ,
		`tb_prolocation` AS pl
		WHERE
		p.status=1
		AND p.`id`=pl.`pro_id`
		AND pl.qty<=pl.`qty_warning` ";
		$where = '';
		if($data["ad_search"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['ad_search']));
			$s_where[]= " p.item_name LIKE '%{$s_search}%'";
			$s_where[]=" p.barcode LIKE '%{$s_search}%'";
			$s_where[]= " p.item_code LIKE '%{$s_search}%'";
			$s_where[]= " p.serial_number LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["branch"]!=""){
			$where.=' AND pl.`location_id`='.$data["branch"];
		}
		if($data["brand"]!=""){
			$where.=' AND p.brand_id='.$data["brand"];
		}
		if($data["category"]!=""){
			$where.=' AND p.cate_id='.$data["category"];
		}
		if($data["category"]!=""){
			$where.=' AND p.cate_id='.$data["category"];
		}
		if($data["model"]!=""){
			$where.=' AND p.model_id='.$data["model"];
		}
		if($data["size"]!=""){
			$where.=' AND p.size_id='.$data["size"];
		}
		if($data["color"]!=""){
			$where.=' AND p.color_id='.$data["color"];
		}
		if($data["status_qty"]>-1){
			if($data["status_qty"]==1){
				$where.=' AND pl.qty>0';
			}else{
				$where.=' AND pl.qty=0';
			}
	
		}
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		$group = " GROUP BY p.`id` ORDER BY p.`item_name`";
		return $db->fetchAll($sql.$where.$location.$group);
	}
	
	function getAllSummaryStock($data=null){
	    $db = $this->getAdapter();
	    $db_globle = new Application_Model_DbTable_DbGlobal();
	   // $date="  AND cd.`close_date` BETWEEN '".$data['fil_start_date']."' AND '".$data['fil_end_date']."'";
	    
	    $sql ="SELECT (SELECT l.name FROM  `tb_sublocation` AS l WHERE l.`id`=pur.`branch_id` LIMIT 1)AS location_name, pur.`date_in`,
               (SELECT tb_vendor.v_name FROM `tb_vendor` WHERE tb_vendor.`vendor_id`=pur.`vendor_id` LIMIT 1)AS vendor_name,
               p.`item_name`,p.`item_code`,
               (SELECT cd.qty_adjust FROM `tb_closelist_detail` AS cd WHERE cd.pro_id=p.id LIMIT 1) AS cl_qty,
               purd.`qty_receive` AS qty_pur,
               (SELECT srd.receive_qty FROM `tb_staff_request_detail` AS srd WHERE srd.pro_id=p.id LIMIT 1) AS req_qty,
               pl.`qty` AS curr_qty,
               (SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
               p.`price`
       
              FROM `tb_purchase_order` AS pur,`tb_purchase_order_item` AS purd,
                   `tb_product` AS p,`tb_prolocation` AS pl
              WHERE pur.`id`=purd.`purchase_id`
              AND purd.`pro_id`=p.`id`
              AND p.`id`=pl.`pro_id`";
	    $from_date =(empty($data['start_date']))? '1': " pur.`date_in` >= '".$data['start_date']." 00:00:00'";
	    $to_date = (empty($data['end_date']))? '1': " pur.`date_in` <= '".$data['end_date']." 23:59:59'";
	    $where = "  AND ".$from_date." AND ".$to_date;
	   // $where="  ";
	    if($data["ad_search"]!=""){
	        $s_where=array();
	        $s_search = addslashes(trim($data['ad_search']));
	        $s_where[]= " p.item_name LIKE '%{$s_search}%'";
	        $s_where[]=" p.barcode LIKE '%{$s_search}%'";
	        $s_where[]= " p.item_code LIKE '%{$s_search}%'";
	        $s_where[]= " p.serial_number LIKE '%{$s_search}%'";
	        $where.=' AND ('.implode(' OR ', $s_where).')';
	    }
	    if($data["branch"]!=""){
	        $where.=' AND pl.`location_id`='.$data["branch"];
	    }
	    
	    if(!empty($data["suppliyer_id"])){
	        $where.=' AND pur.`vendor_id`='.$data["suppliyer_id"];
	    }
	    
	    if($data["measure"]!=""){
	        $where.=' AND p.`measure_id`='.$data["measure"];
	    }
	    echo $sql.$where;
	    $location = $db_globle->getAccessPermission('pl.`location_id`');
	    $group = "  ORDER BY pur.`date_in` ASC";
	    return $db->fetchAll($sql.$where.$location.$group);
	}
	
	
	function getAllProductSummary($search){
	    $db= $this->getAdapter();
	    $user_info = $this->GetuserInfo();
	    $loc = $user_info["branch_id"];
	    $sql="SELECT  
            p.`item_code`,
    	    p.`id`,
    	    p.`item_name` ,
    	    pl.`qty`,
    	    p.price,
    	    pl.`location_id`,
    	    (SELECT m.name FROM `tb_measure` AS m WHERE m.id=p.`measure_id`) AS measure,
      	    pur.`date_in`,
  	    (SELECT v.v_name FROM `tb_vendor` AS v WHERE v.vendor_id=pur.`vendor_id` LIMIT 1) AS vendor_name
	    FROM `tb_purchase_order` AS pur,`tb_purchase_order_item` AS purd,
             `tb_product` AS p,`tb_prolocation` AS pl
         WHERE pur.`id`=purd.`purchase_id`
         AND purd.`pro_id`=p.`id`
         AND p.`id`=pl.`pro_id`
         AND pl.`location_id`=$loc
	     AND p.`status`=1 ";
	    $where='';
	    $from_date =(empty($search['start_date']))? '1': "  pur.`date_in` >= '".$search['start_date']."'";
	    $to_date = (empty($search['end_date']))? '1': "     pur.`date_in` <= '".$search['end_date']."'";
	    $where = " AND ".$from_date." AND ".$to_date;
	    
	    if(!empty($search['ad_search'])){
	        $s_where = array();
	        $s_search=addslashes(trim($search['ad_search']));
	        $s_search = str_replace(' ', '', $s_search);
	        $s_where[]="REPLACE(p.`item_code`,' ','')   LIKE '%{$s_search}%'";
	        $s_where[]="REPLACE(p.`item_name`,' ','')   LIKE '%{$s_search}%'";
	        $where .=' AND ('.implode(' OR ',$s_where).')';
	    }
	    if($search['measure']>0){
	        $where .= " AND p.`measure_id` = ".$search['measure'];
	    }
	    
// 	    if($search['branch']>0){
// 	        $where .= " AND pl.`location_id` = ".$search['branch'];
// 	    }
	    
// 	    if($search['product_id']>0){
// 	        $where .= " AND p.`id` = ".$search['product_id'];
// 	    }
	    
	    if($search['suppliyer_id']>0){
	        $where .= " AND pur.`vendor_id` = ".$search['suppliyer_id'];
	    }
	    
	    $order="  GROUP BY p.`id` ORDER BY p.`item_code` ASC";
	    return $db->fetchAll($sql.$where.$order);
	}
	
	function getReceiveByPro($pro_id,$data){
	    //print_r($data);exit();
	    $db= $this->getAdapter();
	    $user_info = $this->GetuserInfo();
	    $loc = $user_info["branch_id"];
	    $from_date =(empty($data['start_date']))? '1': "  r.`date_in` >= '".$data['start_date']."'";
	    $to_date = (empty($data['end_date']))? '1': "   r.`date_in` <= '".$data['end_date']."'";
	    $where = " AND ".$from_date." AND ".$to_date;
	    $sql="SELECT
	    r.`date_in`,
	    SUM(rt.`qty_receive`) AS qty_receive
	    FROM
	    `tb_purchase_order` AS r,
	    `tb_purchase_order_item` AS rt
	    WHERE r.`id` = rt.`purchase_id`
	    AND rt.`pro_id` =$pro_id";
	    $groupby = "  GROUP BY rt.`pro_id`";
	    return $db->fetchRow($sql.$where.$groupby);
	}
	
	function getDeliByPro($id,$data){
	    $db= $this->getAdapter();
	    $user_info = $this->GetuserInfo();
	    $loc = $user_info["branch_id"];
	    $from_date =(empty($data['start_date']))? '1': "  d.`date_request` >= '".$data['start_date']."'";
	    $to_date = (empty($data['end_date']))? '1': "    d.`date_request` <= '".$data['end_date']."'";
	    $where = " AND ".$from_date." AND ".$to_date;
	    $sql=" SELECT
	    d.`date_request`,
	    SUM(dd.`receive_qty`) AS deli_qty
	    FROM
	    `tb_staff_request` AS d,
	    `tb_staff_request_detail` AS dd
	    WHERE d.`id` = dd.`staff_request_id` AND dd.`pro_id` = $id ";
	    $groupby = " GROUP BY dd.`pro_id`";
	    return $db->fetchRow($sql.$where.$groupby);
	}
	
}

?>