<?php

class Application_Model_DbTable_DbGlobal extends Zend_Db_Table_Abstract
{
    // set name value
// 	public function setName($name){
// 		$this->_name=$name;
// 	}
	protected $_name = 'tb_purchase_order';
	//global $tr = Application_Form_FrmLanguages::getCurrentlanguage();
	/**
	 * get selected record of $sql
	 * @param string $sql
	 * @return array $row;
	 */
	 function getAllProduct(){
		 $db = $this->getAdapter();
		 $sql = "SELECT p.`item_code`,p.`item_name` FROM tb_product AS p";
		 return $db->fetchAll($sql);
	 }
	 
	 function getAllCategory(){
		$db = $this->getAdapter();
		$sql = "SELECT c.`name`,c.`start_code`FROM `tb_category` AS c";
		 return $db->fetchAll($sql);
	 }
	 function getAllMeasure(){
		 $db = $this->getAdapter();
		 $sql ="SELECT m.`name` FROM `tb_measure` AS m";
		 return $db->fetchAll($sql);
	 }
	function getBranch(){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql = "SELECT l.id,l.`name` FROM `tb_sublocation` AS l WHERE l.`status`=1";
		$location = $db_globle->getAccessPermission('l.`id`');
		return $db->fetchAll($sql.$location);
	}
	
	function getPurchasePedding(){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql = "SELECT v.`key_code` as id,v.`name_en` as name FROM `tb_view` AS v WHERE v.`type`=11 AND v.`status`=1 AND v.`key_code`!=0 ORDER BY v.`key_code`";
		return $db->fetchAll($sql);
	}
	public function getValidUserUrl(){
		$db=$this->getAdapter();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$action=$request->getActionName();
		$controller=$request->getControllerName();
		$module=$request->getModuleName();
		
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$level = $result["level"];
		$sql = "SELECT 
				  ua.`acl_id` 
				FROM
				  `tb_acl_user_access` AS ua,
				  `tb_acl_acl` AS a 
				WHERE ua.`acl_id` = a.`acl_id` 
				  AND ua.`user_type_id` = $level 
				  AND a.`module` = '".$module."' 
				  AND a.`controller` = '".$controller."' 
				  AND a.`action` = '".$action."'";
		return $db->fetchRow($sql);
	}
	
	public function getAclByUserType($acl_id,$user_type_id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  ua.`acl_id` 
				FROM
				  `tb_acl_user_access` AS ua 
				WHERE ua.`user_type_id` = '".$user_type_id."' 
				  AND a.`acl_id` = '".$acl_id."'";
		return $db->fetchRow($sql);
	}
	public function getAllAclParentUserType($id){
		$db=$this->getAdapter();
		$sql = "SELECT 
				  aa.`parent`,
				  aa.`lable` ,
				  aa.module,
				  (SELECT ac.`lable` FROM `tb_acl_acl` AS ac WHERE ac.`acl_id`=aa.`parent` LIMIT 1) AS title,
				  (SELECT ac.`icon` FROM `tb_acl_acl` AS ac WHERE ac.`acl_id`=aa.`parent` LIMIT 1) AS icon
				FROM
				  `tb_acl_user_access` AS a,
				  `tb_acl_acl` AS aa 
				WHERE a.`acl_id` = aa.`acl_id` 
				  AND a.`user_type_id` = $id 
				  AND a.status = 1  
				  GROUP BY aa.`parent`";
  		$row=$db->fetchAll($sql);
  		return $row;
	}
	
	public function getAllAclSubParentUserType($id,$parent){
		$db=$this->getAdapter();
		$sql = "SELECT 
				  aa.`sub_parent`,
				  aa.`lable` ,
				  (SELECT ac.`lable` FROM `tb_acl_acl` AS ac WHERE ac.`acl_id`=aa.`parent` LIMIT 1) AS title,
				  aa.icon
				FROM
				  `tb_acl_user_access` AS a,
				  `tb_acl_acl` AS aa 
				WHERE a.`acl_id` = aa.`acl_id` 
				  AND a.`user_type_id` = $id 
				  AND aa.parent=$parent
				  AND aa.status = 1 
					AND aa.is_sub_parent=1
				  
				   ";
  		$row=$db->fetchAll($sql);
  		return $row;
	}
	public function getAllAclUserTypeAndParent($parent,$id,$sub_parent){
		$db=$this->getAdapter();
		$sql = "SELECT 
				  aa.*
				FROM
				  `tb_acl_user_access` AS a,
				  `tb_acl_acl` AS aa 
				WHERE a.`acl_id` = aa.`acl_id` 
				  AND a.`user_type_id` = $id AND aa.`parent`=$parent AND aa.sub_parent=$sub_parent AND aa.`status`=1 AND aa.is_sub_parent=0 ORDER BY aa.rank";
  		$row=$db->fetchAll($sql);
  		return $row;
	}
	public function getAllAclUserType($parent,$id){
		$db=$this->getAdapter();
		$sql = "SELECT 
				  aa.*
				FROM
				  `tb_acl_user_access` AS a,
				  `tb_acl_acl` AS aa 
				WHERE a.`acl_id` = aa.`acl_id` 
				  AND a.`user_type_id` = $id AND aa.`parent`=$parent AND aa.`status`=1 ORDER BY a.id";
  		$row=$db->fetchAll($sql);
  		return $row;
	}
	public function getAllStaff(){
		$db=$this->getAdapter();
		$sql = "SELECT s.id,s.`name` FROM `tb_staff` AS s WHERE s.`status`=1";
  		$row=$db->fetchAll($sql);
  		return $row;
	}
	public function getAllStaffByID($id){
		$db=$this->getAdapter();
		$sql = "SELECT s.`position` FROM `tb_staff` AS s WHERE s.`id`=$id";
  		$row=$db->fetchOne($sql);
  		return $row;
	}
	public function insertStaff($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$GetUserId= $session_user->user_id;
			$info=array(
					"name"    	  => $data['name'],
					"position"    => $data['position'],
					"phone"       => $data['phone'],
					"email"       => $data['email'],
					"status"      => $data['status'],
					"user_id"     => $GetUserId,
					"date"        => date("Y-m-d"),
			);
			$this->_name="tb_staff";
			$id = $this->insert($info);
			$db->commit();
			return $id;
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	
	function getAllWorkPlanByID($id,$opt=null){
		$db=$this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="SELECT p.`id`,p.`name` FROM `tb_work_plan` AS p WHERE p.`plan_id`=$id";
		///echo $sql;
		$row = $db->fetchAll($sql);
		if($opt==null){
			return $row;
		}else{
			$options='<option value="">'.$tr->translate("SELECT").'</option>';
			$options.='<option value="-1">'.$tr->translate("បន្ថែមថ្មី").'</option>';
			//$options=array(0=>  $tr->translate("SELECT"));
			if(!empty($row)){ foreach($row as $key=> $rs){
				$options .= '<option value="'.$rs['id'].'" >'.($key+1)." - ".htmlspecialchars($rs['name'], ENT_QUOTES)
    					.'</option>';
			}}
			return $options;
		}
		
		//echo $options;
    }
	public function getGlobalDb($sql)
  	{
  		$db=$this->getAdapter();
  		$row=$db->fetchAll($sql);
  		if(!$row) return NULL;
  		return $row;
  	}
  	public function getGlobalDbRow($sql)
  	{
  		$db=$this->getAdapter();
  		$row=$db->fetchRow($sql);
  		if(!$row) return NULL;
  		return $row;
  	}
  	
  	public static function getActionAccess($action)
    {
    	$arr=explode('-', $action);
    	return $arr[0];    	
    }    
function getAllVendor($opt=null){
   	$db=$this->getAdapter();
	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$sql=" SELECT vendor_id, v_name FROM tb_vendor WHERE v_name!='' AND status = 1 ORDER BY vendor_id DESC";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array(0=>  $tr->translate("SELECT_VENDOR"),-1=>$tr->translate("ADD_NEW_VENDOR"));
   		if(!empty($row)) foreach($row as $read) $options[$read['vendor_id']]=$read['v_name'];
   		return $options;
   	}
   }
   function getallDN(){
	   	$db=$this->getAdapter();
		$sql = "SELECT d.`id`,d.`deliver_no` FROM `tb_deliverynote` AS d WHERE d.`is_invoice`=0";
		return $db->fetchAll($sql);
   }
   
 public function getReceiptNumber($branch_id = 1){
    	$this->_name='tb_receipt';
    	$db = $this->getAdapter();
    	$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$branch_id." LIMIT 1 ";
    	$pre = $this->getPrefixCode($branch_id)."R";
    	$acc_no = $db->fetchOne($sql);
    
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
   
   function getAllInvoicePaymentPurchase($post_id,$type){
   	$db= $this->getAdapter();
   	if($type==1){//by customer
   		$sql=" SELECT p.*,
   		(SELECT SUM(paid) FROM `tb_vendorpayment_detail` WHERE tb_vendorpayment_detail.invoice_id=p.id) as paid
   		FROM tb_purchase_order AS p
   		WHERE p.vendor_id= $post_id AND status=1  ";
   		$sql.=" AND p.is_completed=0 AND p.balance_after>0 ";
   		$sql.=" ORDER BY p.id DESC ";
   	}else{//by invoice
   		$sql=" SELECT p.*,
   		(SELECT SUM(paid) FROM `tb_vendorpayment_detail` WHERE tb_vendorpayment_detail.invoice_id=p.id) as paid
   		FROM tb_purchase_order AS p WHERE p.id= $post_id AND p.status=1  ";
   		$sql.=" AND p.is_completed=0 AND p.balance_after>0 LIMIT 1 ";
   	}
   	return  $db->fetchAll($sql);
   }
   
   
// function getAllInvoicePO($completed=null,$opt=null){
//    	$db= $this->getAdapter();
// 	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
//    	$sql="SELECT i.`id`,i.`invoice_id`,i.`invoice_no`,i.`invoice_controlling_date`,i.`receive_invoice_date`,i.`invoice_date` FROM `tb_invoice_controlling` AS i WHERE 1";
//    	if($completed!=null){
//    		$sql.="  AND i.is_completed=0 ";
//    	}
//    	$sql.=" ORDER BY id DESC ";
//    	$row =  $db->fetchAll($sql);
//    	if($opt==null){
//    		return $row;
//    	}else{
//    		$options=array(-1=>$tr->translate("SELECT_INVOICE"));
//    		if(!empty($row)) foreach($row as $read) $options[$read['invoice_id']]=$read['invoice_no'];
//    		return $options;
//    	}
//    }
   
   function getAllInvoicePO($completed=null,$opt=null){
	   	$db= $this->getAdapter();
	   	$sql=" SELECT id,invoice_no,(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id = p.vendor_id) AS vendor_name FROM `tb_purchase_order` AS p WHERE  p.status=1 and p.balance_after>0 ";
	   	if($completed!=null){
	   		$sql.="  AND p.is_completed=0 ";
	   	}
	   	$sql.=" ORDER BY id DESC ";
	   	$row =  $db->fetchAll($sql);
	   	if($opt==null){
	   		return $row;
	   	}else{
	   		$options=array(-1=>"Select Invoice");
	   		if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['invoice_no']."-".$read['vendor_name'];
	   		return $options;
	   	}
   }

function getDnNo($completed=null,$opt=null){
   	$db= $this->getAdapter();
	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$sql="SELECT r.`order_id`,r.`dn_number`,r.`recieve_number` FROM `tb_recieve_order` AS r WHERE 1";
   	if($completed!=null){
   		$sql.="  AND r.is_invoice_controlling=0 ";
   	}
   	$sql.=" ORDER BY r.order_id DESC ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array(-1=>$tr->translate("SELECT_DN"));
   		if(!empty($row)) foreach($row as $read) $options[$read['order_id']]=$read['dn_number']."(".$read['recieve_number'].")";
   		return $options;
   	}
   }   
    
    /**
     * get CSO options for select box
     * @return array $options
     */
    public function getOptionCSO(){
    	$options = array('Please select');
    	$sql = "SELECT id, name_en FROM fi_cso ORDER BY name_en";
    	$rows = $this->getGlobalDb($sql);
    	foreach($rows as $ele){
    		$options[$ele['id']] = $ele['name_en'];
    	}
    	return $options;
    }
    
    /**
     * boolean true mean record exist already
     * @param string $conditions
     * @param string $tbl_name
     * @return boolean
     */
    public function isRecordExist($conditions,$tbl_name){
		$db=$this->getAdapter();
		$sql="SELECT * FROM ".$tbl_name." WHERE ".$conditions; 
		$stm = $db->query($sql);
		$row = $stm->fetchAll();
    	if(!$row) return false;
    	return true;    	
    }
	function getRejectExist($id,$type){
		$db=$this->getAdapter();
		$sql="SELECT p.`re_id`,p.type FROM `tb_purchase_request_remark` AS p WHERE p.re_id=$id AND p.type=$type";
		$row = $db->fetchRow($sql);
    	return $row;  
	}
	function getPriceCompareRejectExist($id,$type){
		$db=$this->getAdapter();
		$sql="SELECT p.`re_id`,p.type FROM `tb_price_compare_remark` AS p WHERE p.re_id=$id AND p.type=$type";
		$row = $db->fetchRow($sql);
    	return $row;  
	}
	
	function getSaleRejectExist($id,$type){
		$db=$this->getAdapter();
		$sql="SELECT p.`re_id`,p.type FROM `tb_sale_request_remark` AS p WHERE p.re_id=$id AND p.type=$type";
		$row = $db->fetchRow($sql);
    	return $row;  
	}
	public function getTitleReportNew($id){
		$db=$this->getAdapter();
		$sql="SELECT 
				  s.`name`,
				  s.`branch_code`,
				  s.`prefix`,
				  s.`logo`,
				  s.`address`,
				  s.`phone`,
				  s.`contact`,
				  s.`email`,
				  s.`title_report_en`,
				  s.`title_report_kh`
				FROM
				  `tb_sublocation` AS s 
				WHERE s.id =$id";
		$row = $db->fetchRow($sql);
    	return $row;  
	}
	
	public function getTitleReport($id){
		$db=$this->getAdapter();
		$sql="SELECT 
				  s.`name`,
				  s.`prefix`,
				  s.`logo`,
				  s.`title_report_en`,
				  s.`title_report_kh`
				FROM
				  `tb_location` AS s 
				WHERE s.id =$id";
		$row = $db->fetchRow($sql);
    	return $row;  
	}
    
    public function getDeliverProductExist($id_order_update){
    	$db= $this->getAdapter();
    	$sql="SELECT 
						  SUM(so.`qty_delivery`) AS qty,
						  soi.qty_order,
						  (SELECT p.pro_id FROM tb_product AS p WHERE p.pro_id = so.`pro_id`) AS pro_id,
						  (SELECT p.qty_onhand FROM tb_product AS p WHERE p.pro_id = so.`pro_id`) AS qty_onhand,
						  (SELECT p.qty_available FROM tb_product AS p WHERE p.pro_id = so.`pro_id`) AS qty_available,
						  (SELECT p.qty_onsold  FROM tb_product AS p WHERE p.pro_id = so.`pro_id`) AS qty_onsold 
						FROM
						  tb_sale_order_delivery AS so ,
						  `tb_sales_order_item` AS soi
						WHERE so.sale_order_id = $id_order_update 
						AND so.`pro_id`=soi.`pro_id`
						AND so.`sale_order_id`=soi.`order_id`
						GROUP BY so.pro_id ,soi.`pro_id`";
    	$rows= $db->fetchAll($sql);
    	return $rows; 
    }
    public function porductLocationExist($pro_id, $location_id){//used
    	$db=$this->getAdapter();
    	
    	$sql="SELECT ProLocationID,qty,qty_onorder,qty_avaliable,LocationId,pro_id,qty_onsold FROM tb_prolocation WHERE pro_id = ".$pro_id." AND LocationId = ".$location_id." LIMIT 1";
    	try{
    	$row = $db->fetchRow($sql);
    	}catch (Exception $e){
    		var_dump($sql);
    		die($e->getMessage());
    	}
    	//echo $sql;exit();
    	
    	return $row;
    }
    //get value in product inventory with product location (Joint)
    
    public function productLocationInventory($pro_id, $location_id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,pro_id,location_id,qty,qty_warning,user_id,last_mod_date,last_mod_userid
    	 FROM tb_prolocation WHERE pro_id =".$pro_id." AND location_id=".$location_id." LIMIT 1 "; 

    	
    	$row = $db->fetchRow($sql);
    	
    	if(empty($row)){
    		$session_user=new Zend_Session_Namespace('auth');
    		$userName=$session_user->user_name;
    		$GetUserId= $session_user->user_id;
    		
    		$array = array(
    				"pro_id"=>$pro_id,
    				"location_id"=>$location_id,
    				"qty"=>0,
    				"qty_warning"=>0,
    				"last_mod_userid"=>$GetUserId,
    				"user_id"=>$GetUserId,
    				"last_mod_date"=>date("Y-m-d")
    				);
    		$this->_name="tb_prolocation";
    		$this->insert($array);
    		
    		$sql="SELECT id,pro_id,location_id,qty,qty_warning,user_id,last_mod_date,last_mod_userid
    		FROM tb_prolocation WHERE pro_id =".$pro_id." AND location_id=".$location_id." LIMIT 1 ";
    		return $row = $db->fetchRow($sql);
    	}else{
    		
    	return $row; 
    	}  	
    }
    //for get product product inventory but if have in prodcut location
    public function productInventoryExist($itemId){
    	$db=$this->getAdapter();
    	$sql="SELECT pl.ProLocationID, pl.qty, iv.QuantityOnHand, iv.QuantityAvailable
				FROM tb_prolocation AS pl
				INNER JOIN tb_inventorytotal AS iv ON iv.ProdId = pl.pro_id WHERE pl.pro_id=".$itemId." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	if(!$row) return false;
    	return $row;    	
    }
    
    //to get and check if product total inventory exist 8/26/13
    public function InventoryExist($pro_id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM tb_product WHERE pro_id =".$pro_id." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	if(!$row) return false;
    	return $row;
    }
    public function productLocation($pro_id,$location_id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM tb_prolocation WHERE pro_id =".$pro_id." AND LocationId = ".$location_id." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	if(!$row) return false;
    	return $row;
    }
    public function QtyProLocation($pro_id,$location_id){//get qty location
    	$db=$this->getAdapter();
    	$sql="SELECT ProLocationID,pro_id,qty FROM tb_prolocation WHERE pro_id =".$pro_id." AND LocationId = ".$location_id." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
	//if myProductExist
    public function myProductExist($pro_id){
    	$db=$this->getAdapter();
    	$sql="SELECT pro_id FROM tb_product WHERE pro_id =".$pro_id." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    
    public function userSaleOrderExist($order_id , $location_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT order_id FROM tb_sales_order WHERE order_id =".$order_id." AND LocationId = $location_id LIMIT 1";
    	$row= $db->fetchRow($sql);
    	return $row;
    }
    
    public function userPurchaseOrderExist($order_id , $location_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT order_id FROM tb_purchase_order WHERE order_id =".$order_id." AND LocationId = $location_id LIMIT 1";
    	$row= $db->fetchRow($sql);
    	return $row;
    }
    //if user purchase exist
    public function userPurchaseExist($order_id , $location_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT order_id FROM tb_purchase_order WHERE order_id =".$order_id." AND LocationId = $location_id"." LIMIT 1";
    	$row= $db->fetchRow($sql);
    	return $row;
    }  
    //check product location history exit(for update prodcut) 28/8
    public function prodcutHistoryExist($location_id,$id){
    	$db=$this->getAdapter();
    	$sql="SELECT pl.ProLocationID, pl.qty FROM tb_prolocation_history AS pl
    	INNER JOIN tb_product AS p ON p.pro_id = pl.pro_id
    	WHERE pl.LocationId = ".$location_id." AND p.pro_id=".$id." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    //check if not have in product location history
    public function proLocationHistoryNoExist($id){
    	$db=$this->getAdapter();
    	$sql="SELECT qty,locationID FROM tb_prolocation_history
    	WHERE pro_id= $id AND LocationId NOT IN
    	( SELECT LocationId FROM tb_prolocation where pro_id = $id) ";
    	$row = $db->fetchAll($sql);
    	if(!$row) return false;
    	return $row;
    	
    }
    //for check order history exist 
    final public function orderHistoryExitRow($order_id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM `tb_order_history` WHERE `order`= ".$order_id." LIMIT 1";
    	$row=$db->fetchRow($sql);
    	return $row;
    	
    }
    final public function purchaseOrderHistoryExitAll($order_id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM `tb_purchase_order_history` WHERE type=1 AND `order`=". $order_id;
    	$rows=$db->fetchAll($sql);
    	//if(!$rows) return false;
    	return $rows;
    	 
    }
    final public function purchaseOrderHistory($order_id){
    	$db=$this->getAdapter();
   	$sql="SELECT * FROM `tb_purchase_order_history` WHERE type=1 AND `order`=". $order_id;
    	$rows=$db->fetchAll($sql);
    	//if(!$rows) return false;
    	return $rows;
    
    }
    final public function salesOrderHistoryExitAll($order_id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM `tb_order_history` WHERE type=2 AND `order`= ".$order_id;
    	$rows=$db->fetchAll($sql);
    	if(!$rows) return false;
    	return $rows;
    
    }
    final public function inventoryLocation($locationid, $itemId){
    	$db=$this->getAdapter();
    	$sql="SELECT pl.ProLocationID, pl.`qty_onorder` ,pl.qty, p.qty_onhand,p.qty_available
    	FROM tb_prolocation AS pl
    	INNER JOIN tb_product AS p ON p.pro_id = pl.pro_id
    	WHERE pl.LocationId = ".$locationid. " AND pl.pro_id= ".$itemId." LIMIT 1";
    	$row=$db->fetchRow($sql);
    	return $row;
    }
    final public function productInvetoryLocation($locationid, $itemId){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    			p.pro_id,
    			pl.ProLocationID, 
    			pl.`qty_onorder`,
    			pl.qty_onsold as prol_qty_onsold ,
    			pl.qty,
    			pl.qty_avaliable,
    			p.qty_onsold,
    			p.qty_onorder as pro_qty_onorder, 
    			p.qty_onhand,
    			p.qty_available
    			
    	FROM tb_prolocation AS pl
    	INNER JOIN tb_product AS p ON p.pro_id = pl.pro_id
    	WHERE pl.LocationId = ".$locationid. " AND pl.pro_id= ".$itemId." LIMIT 1";
    	
//     	$sql="SELECT pl.ProLocationID, pl.`qty_onorder` ,pl.qty, p.qty_onhand,p.qty_available
//     	FROM tb_prolocation AS pl
//     	INNER JOIN tb_product AS p ON p.pro_id = pl.pro_id
//     	WHERE pl.LocationId = ".$locationid. " AND pl.pro_id= ".$itemId." LIMIT 1";
    	$row=$db->fetchRow($sql);
    	return $row;
    }
    
    
    
    /**
     * insert record to table $tbl_name
     * @param array $data
     * @param string $tbl_name
     */
    public function addRecord($data,$tbl_name){
    	$this->setName($tbl_name);
    	return $this->insert($data);
    }
    
    
    /**
     * update record to table $tbl_name
     * @param array $data
     * @param int $id
     * @param string $tbl_name
     */
	public function updateRecord($data,$id,$updateby,$tbl_name){
		$tb = $this->setName($tbl_name);
		$where=$this->getAdapter()->quoteInto($updateby.'=?',$id);
		$rs = $this->update($data,$where);
		//echo $rs;//exit();
		
	}
    
    public function DeleteRecord($tbl_name,$id){
    	$db = $this->getAdapter();
		$sql = "UPDATE ".$tbl_name." SET status=0 WHERE id=".$id;
		return $db->query($sql);
    }

    public function deleteRecords($sql){
    	$db = $this->getAdapter();
		return $db->query($sql);
    } 

     public function DeleteData($tbl_name,$where){
    	$db = $this->getAdapter();
		$sql = "DELETE FROM ".$tbl_name.$where;
		return $db->query($sql);
    } 
    
    public function convertStringToDate($date, $format = "Y-m-d H:i:s")
    {
    	if(empty($date)) return NULL;
    	$time = strtotime($date);
    	return date($format, $time);
    }
    /* @Desc: add or sub qty of item depend on item and stock
     * @param $stockID stock id
     * @param $itemQtys array of item id and item qty
     * @param $sign: + | -
     * */
    public function query($sql){
    	$db = $this->getAdapter();
    	return $db->query($sql);	
    }
    public function fetchArray($result){
    	$db = $this->getAdapter();
    	return mysql_fetch_assoc($result);
    }
    public function getUserInfo(){
    	$session_user=new Zend_Session_Namespace('auth');
    	$userName=$session_user->user_name;
    	$GetUserId= $session_user->user_id;
    	$level = $session_user->level;
    	$location_id = $session_user->location_id;
    	$info = array("user_name"=>$userName,"user_id"=>$GetUserId,"level"=>$level,"branch_id"=>$location_id);
    	return $info;
    }
    
    public function getAccessPermission($branch='branch_id'){
    	$result = $this->getUserInfo();
    	if($result['level']==1){
    		$result = "";
    		return $result;
    	}
    	else{
    		$sql_string = $this->getAllLocationByUser($result['user_id'],$branch);
//     		$result = " AND (".$sql_string.")";
    		$result = " AND ".$sql_string;
    		return $result;
    	} 
    }
	 function getAllLocation($opt=null){
   		   		$db=$this->getAdapter();
   		$sql=" SELECT id,`name` FROM `tb_sublocation` WHERE `name`!='' AND status=1  ";
//    		$sql.=$this->getAccessPermission("id");
   		$result = $this->getUserInfo();
   		$sql.= " AND ".$this->getAllLocationByUser($result['user_id']);
		//echo $sql;
   		$row =  $db->fetchAll($sql);
   		if($opt==null){
   			return $row;
   		}else{
   			$options=array();
   			if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['name'];
   			return $options;
   		}
   }
     function getAllLocationByUser($user_id,$branch_name='id'){
    	$db = $this->getAdapter();
    	$result = $this->getUserInfo();
    	if($result['level']==1){
    		return " 1 ";
    	}
    	$sql=" SELECT * FROM `tb_acl_ubranch` WHERE user_id=$user_id ";
    	
    	$rows = $db->fetchAll($sql);
    	$s_where = array();
    	$where='';
    	if(!empty($rows)){
//     		foreach ($rows as $rs){
//     			$s_where[] = $branch_name." = {$rs['location_id']}";
//     		}
//     		$where .=' '.implode(' OR ',$s_where).'';
    		foreach ($rows as $rs){
    			if (empty($where)){
    				$where=$rs['location_id'];
    			}else{
    				$where=$where.",".$rs['location_id'];
    			}
    		}
    		$where = $branch_name." IN ($where) ";
    	}
    	return $where;
    }
    public function getSetting(){
    	$DB = $this->getAdapter();
    	$sql="SELECT * FROM `tb_setting` ";
    	RETURN $DB->fetchAll($sql);
    }
    public static function GlobalgetUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    public static function writeMessageErr($err=null)
    {
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$action=$request->getActionName();
    	$controller=$request->getControllerName();
    	$module=$request->getModuleName();
    
    	$session = new Zend_Session_Namespace('auth');
    	$user_name = $session->user_name;
    
    	$file = "../logs/error.log";
    	if (!file_exists($file)) touch($file);
    	$Handle = fopen($file, 'a');
    	$stringData = "[".date("Y-m-d H:i:s")."]"." [user]:".$user_name." [module]:".$module." [controller]:".$controller. " [action]:".$action." [Error]:".$err. "\n";
    	fwrite($Handle, $stringData);
    	fclose($Handle);
    
    }
    
    // ****************** Check Product Location  **************************
    public function productLocationInventoryCheck($pro_id, $location_id){
    	$db=$this->getAdapter();
    	$sql="SELECT pl.ProLocationID, pl.qty, p.qty_onorder
			FROM tb_prolocation AS pl
			INNER JOIN tb_product AS p ON p.pro_id = pl.pro_id
			WHERE pl.LocationId =".$location_id." AND pl.pro_id = ".$pro_id." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    public function getQtyFromProductById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT `qty_onhand`,`qty_onsold`,`qty_onorder`,`qty_available`
    	      FROM `tb_product` where `pro_id`= ".$db->quote($id);
    	return $db->fetchRow($sql);
    }
    public function getSettingById($id){
    	$sql = "SELECT CODE,key_name,key_value FROM tb_setting where code = ".$id;
    	return $this->getAdapter()->fetchRow($sql);
    }
    public function getMeasureById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT `qty_perunit` FROM tb_product WHERE pro_id= '$item_id' LIMIT 1 ";
    }
    public function getQuoationNumber($branch_id = 1){
    	$this->_name='tb_quoatation';
    	$db = $this->getAdapter();
    	$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$branch_id." LIMIT 1 ";
    	$pre = $this->getPrefixCode($branch_id)."QO-";
    	$acc_no = $db->fetchOne($sql);
    
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    public function getSalesNumber($branch_id = 1){
    	$this->_name='tb_sales_order';
    	$db = $this->getAdapter();
    	$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$branch_id." AND type=1 LIMIT 1 ";
    	$pre = $this->getPrefixCode($branch_id)."SO-";
    	$acc_no = $db->fetchOne($sql);
    
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
	
	public function getRequestNumber($branch_id = 1){
    	$this->_name='tb_sales_order';
    	$db = $this->getAdapter();
    	$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$branch_id." AND type=2 LIMIT 1 ";
    	$pre = $this->getPrefixCode($branch_id)."RO-";
    	$acc_no = $db->fetchOne($sql);
    
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
	
	 public function getDeliverNumber($branch_id = 1){
    	$this->_name='tb_deliverynote';
    	$db = $this->getAdapter();
    	$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$branch_id." LIMIT 1 ";
    	$pre = $this->getPrefixCode($branch_id)."DN-";
    	$acc_no = $db->fetchOne($sql);
    
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
	public function getInvoiceNumber($branch_id = 1){
    	$this->_name='tb_invoice';
    	$db = $this->getAdapter();
    	$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$branch_id." LIMIT 1 ";
    	$pre = $this->getPrefixCode($branch_id)."IV";
    	$acc_no = $db->fetchOne($sql);
    
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
	public function getPuInvoiceNumber($branch_id = 1){
    	$this->_name='tb_purchase_invoice';
    	$db = $this->getAdapter();
    	$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$branch_id." LIMIT 1 ";
    	$pre = $this->getPrefixCode($branch_id)."IV";
    	$acc_no = $db->fetchOne($sql);
    
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    function getPrefixCode($branch_id){
    	$db  = $this->getAdapter();
    	$sql = " SELECT branch_code FROM `tb_sublocation` WHERE id = $branch_id  LIMIT 1";
    	return $db->fetchOne($sql);
    }
    function getAllTermCondition($opt=null,$type=null){
    	$db = $this->getAdapter();
    	$sql = " SELECT id,con_khmer,con_english FROM `tb_termcondition` WHERE con_khmer!='' AND status = 1 ";
    	if($type!=null){
    		$sql.=" AND type = $type";
    	}
    	$rows =  $db->fetchAll($sql);
    	if($opt!=null){
    		$option='';
    		if(!empty($rows)){foreach ($rows as $key =>$rs){ 
    			$option .= '<option value="'.$rs['id'].'" >'.($key+1)." - ".htmlspecialchars($rs['con_khmer'], ENT_QUOTES)
    					.'</option>';
    		}
    		return $option;
    	  }
    	}else{
    		return $rows;
    	}
    }
   function getProductPriceBytype($customer_id,$product_id){//BY Customer Level and Product id
   	$db = $this->getAdapter();
   	$sql=" SELECT price,pro_id FROM `tb_product_price` WHERE type_id = 
   		(SELECT customer_level FROM `tb_customer` WHERE id=$customer_id limit 1) AND pro_id=$product_id LIMIT 1 ";
   	return $db->fetchRow($sql);
   }
   function getTermConditionById($term_type,$record_id=null){
   	$db = $this->getAdapter();
   	$sql=" SELECT t.con_khmer,t.con_english FROM `tb_termcondition` AS t,`tb_quoatation_termcondition` AS tc 
   		WHERE t.id=tc.condition_id AND tc.term_type=$term_type ";
		if($record_id!=null){$sql.=" AND quoation_id=$record_id ";}
   	return $db->fetchAll($sql); 
   }
   function getTermConditionByIdIinvocie($term_type,$record_id=null){
   	$db = $this->getAdapter();
   	$sql=" SELECT t.con_khmer,t.con_english FROM `tb_termcondition` AS t
   		WHERE t.type=$term_type ";
		
   	return $db->fetchAll($sql); 
   }
   
   function getTermConditionByType($id){
	    $db = $this->getAdapter();
	   $session_lang=new Zend_Session_Namespace('lang');
		$lang = $session_lang->lang_id;
	  
	   if($lang==1){
			$sql = "SELECT t.`con_khmer` AS title FROM `tb_termcondition` AS t WHERE t.`type`=$id";
	   }else{
		   $sql = "SELECT t.`con_khmer` AS title FROM `tb_termcondition` AS t WHERE t.`type`=$id";
	   }
	   
	   return $db->fetchAll($sql);
   }
   function getWorkType(){
	    $options=array(''=>"SELECT_WORK",'1'=>'E','2'=>"W",3=>"G",4=>"M",5=>"P",6=>"S",7=>"MT",8=>"K",9=>"OT");
			
		return $options;
   }
   function getAllInvoice($completed=null,$opt=null){
   	$db= $this->getAdapter();
   	$sql=" SELECT id,invoice_no FROM `tb_invoice` WHERE status=1  ";
   	if($completed!=null){ $sql.="  AND is_fullpaid=0 ";} 
   	$sql.=" ORDER BY id DESC ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		  $options=array(-1=>"Select Invoice");
   		 if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['invoice_no'];
   		 return $options;
   	}
   }
   function getAllInvoicePayment($post_id,$type){
   	$db= $this->getAdapter();
	if($type==1){
		$sql=" SELECT * FROM `tb_invoice` AS v,tb_sales_order as s WHERE v.sale_id = s.id AND s.customer_id = $post_id AND v.status=1  ";
		$sql.="  AND v.is_fullpaid=0 ";
		$sql.=" ORDER BY v.id DESC ";
   }else{
		$sql=" SELECT * FROM `tb_invoice` AS v  WHERE v.id=$post_id AND v.status=1  ";
		$sql.="  AND v.is_fullpaid=0 LIMIT 1";
	}
	//return $sql;
   	return  $db->fetchAll($sql);
   }
   
   function getAllDnNo($post_id,$type){
   	$db= $this->getAdapter();
	if($type==1){
		$sql=" SELECT 
				  d.id,
				  d.`deliver_no`,
				  d.`all_total`,
				  d.`all_total_after`,
				  d.`paid`,
				  d.`paid_after`,
					d.`deli_date`,
				  d.`balance`,
  d.`customer_id`,
				  d.`balance_after` ,
  d.`so_id`
				FROM
				  `tb_deliverynote` AS d 
				WHERE d.`customer_id`=$post_id AND d.`is_invoice`=0";
		//$sql.="  AND v.is_fullpaid=0 ";
		$sql.=" ORDER BY d.id DESC ";
   }else{
		$sql=" SELECT 
				  d.id,
				  d.`deliver_no`,
				  d.`all_total`,
				  d.`all_total_after`,
				  d.`paid`,
				  d.`paid_after`,
  d.`deli_date`,
				  d.`balance`,
  d.`customer_id`,
				  d.`balance_after` ,
  d.`so_id`
				FROM
				  `tb_deliverynote` AS d 
				WHERE d.`id` = $post_id  ";
		//$sql.="  AND v.is_fullpaid=0 LIMIT 1";
	}
	//return $sql;
   	return  $db->fetchAll($sql);
   }
   	function getAllCustomer($opt=null){
   		$db=$this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   		$sql=" SELECT id, CONCAT(cust_name,',',contact_name) AS cust_name FROM tb_customer WHERE cust_name!=''
   		 AND status=1 ORDER BY id DESC";
   		
   		$row =  $db->fetchAll($sql);
   		if($opt==null){
   			return $row;
   		}else{
   			$options=array(0=>$tr->translate("Select Customer"),-1=>"Add New Customer");
   			if(!empty($row)) foreach($row as $read) $options[$read['id']]=str_replace("-","",$read['cust_name']);
   			return $options;
   		}
   }
   public function addDepartment($data){
   	$db = $this->getAdapter();
   	$db->beginTransaction();
   	try{
   		$db_global = new Application_Model_DbTable_DbGlobal();
   		$session_user=new Zend_Session_Namespace('auth');
   		$GetUserId= $session_user->user_id;
   		$info=array(
   				"name"    	  => $data['name_department'],
   				"plan_id"    => $data['plan_id'],
   				"status"     => 1,
   		);
   		$this->_name="tb_work_plan";
   		$id = $this->insert($info);
   		$db->commit();
   		return $id;
   	}catch(Exception $e){
   		$db->rollBack();
   		Application_Form_FrmMessage::message('INSERT_FAIL');
   		$err =$e->getMessage();
   		Application_Model_DbTable_DbUserLog::writeMessageError($err);
   	}
   }
   
   function getAllExpense($opt=null){
   	$db=$this->getAdapter();
   	$sql=" SELECT * FROM tb_expensetitle where status=1 and title!='' ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array(0=>"Select Expense",-1=>"Add New Expense Title");
   		if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['title'];
   		return $options;
   	}
   }
   
   function getAllExpensePu($opt=null){
   	$db=$this->getAdapter();
   	$sql=" SELECT * FROM tb_expensetitle where status=1 and title!='' ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array(0=>"Select Expense",-1=>"Add New Expense Title");
   		if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['title'];
   		return $options;
   	}
   }
   
   function getAllPaymentmethod($opt=null){
   	$db=$this->getAdapter();
   	$sql=" SELECT * FROM tb_paymentmethod ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array();
   		if(!empty($row)) foreach($row as $read) $options[$read['payment_typeId']]=$read['payment_name'];
   		return $options;
   	}
   }
   
   public function getVewOptoinTypeByTypes($type=null,$limit =null){
   	$db = $this->getAdapter();
   	$lang = $this->getCurrentLang();
   	$array = array(1=>"name_en",2=>"name_kh");
   	$sql="SELECT key_code as id,".$array[$lang]." AS name ,displayby FROM `tb_view` WHERE status =1 AND name_en!='' ";//just concate
   	if($type!=null){
   		$sql.=" AND type = $type ";
   	}
   	if($limit!=null){
   		$sql.=" LIMIT $limit ";
   	}
   	$rows = $db->fetchAll($sql);
   	return $rows;
   }
   
   function getAllCurrency($opt=null){
   	$db=$this->getAdapter();
   	$sql=" SELECT id, description,symbal FROM tb_currency WHERE status = 1 ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array();
   		if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['description'].$read['symbal'];
   		return $options;
   	}
   }
   
   	function getAllProvince($opt=null){
   		$db=$this->getAdapter();
   		$sql=" SELECT province_id,province_en_name FROM ln_province WHERE province_en_name!='' ";
   		
   		$row =  $db->fetchAll($sql);
   		if($opt==null){
   			return $row;
   		}else{
   			$options=array();
   			if(!empty($row)) foreach($row as $read) $options[$read['province_id']]=str_replace("-","",$read['province_en_name']);
   			return $options;
   		}
   }
   /*function getAllLocation($opt=null){
   		$db=$this->getAdapter();
   		$sql=" SELECT id,`name` FROM `tb_sublocation` WHERE `name`!='' AND STATUS=1  ";
   		
   		$row =  $db->fetchAll($sql);
   		if($opt==null){
   			return $row;
   		}else{
   			$options=array();
   			if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['name'];
   			return $options;
   		}
   }*/
   	public function getHours($key = ''){
  $tr = Application_Form_FrmLanguages::getCurrentlanguage();
  $am = $tr->translate('AM');
  $pm = $tr->translate('PM');
  $hours = array(
    '12:00 '. $pm,'12:30 '. $pm,
    '01:00 '. $am,'01:30 '. $am,
    '02:00 '. $am,'02:30 '. $am,
    '03:00 '. $am,'03:30 '. $am,
    '04:00 '. $am,'04:30 '. $am,
    '05:00 '. $am,'05:30 '. $am,
    '06:00 '. $am,'06:30 '. $am,
    '07:00 '. $am,'07:30 '. $am,
    '08:00 '. $am,'08:30 '. $am,
    '09:00 '. $am,'09:30 '. $am,
    '10:00 '. $am,'10:30 '. $am,
    '11:00 '. $am,'11:30 '. $am,
    '12:00 '. $am,'12:30 '. $am,
    '01:00 '. $pm,'01:30 '. $pm,
    '02:00 '. $pm,'02:30 '. $pm,
    '03:00 '. $pm,'03:30 '. $pm,
    '04:00 '. $pm,'04:30 '. $pm,
    '05:00 '. $pm,'05:30 '. $pm,
    '06:00 '. $pm,'06:30 '. $pm,
    '07:00 '. $pm,'07:30 '. $pm,
    '08:00 '. $pm,'08:30 '. $pm,
    '09:00 '. $pm,'09:30 '. $pm,
    '10:00 '. $pm,'10:30 '. $pm,
    '11:00 '. $pm,'11:30 '. $pm
  );
  if(empty($key)){
   return $hours;
  }
  return  $hours[$key];
 }
 
 function getAgreementNo($branch_id=1){
 	$db = $this->getAdapter();
 	$sql="SELECT id FROM tb_agreement ORDER BY id DESC";
 	$acc_no = $db->fetchOne($sql);
 	$new_acc_no= (int)$acc_no+1;
 	$acc_no= strlen((int)$acc_no+1);
 	$pre="";
 	for($i = $acc_no;$i<5;$i++){
 		$pre.='0';
 	}
 	return $pre.$new_acc_no;
 }
 function getAllSaleAgreement($opt=null,$type=null,$defual=null){
 	$db = $this->getAdapter();
 	$sql = " SELECT id,
 	(SELECT  cust_name FROM `tb_customer` AS c WHERE c.id=customer_id ) AS customer_name,
 	agreement_no
 	FROM `tb_agreement` WHERE status = 1 ";
 	 
 	$rows =  $db->fetchAll($sql);
 	if($opt!=null){
 		$option='';
 		if(!empty($rows)){
 			foreach ($rows as $key =>$rs){
 				$option .= '<option value="'.$rs['id'].'" >'.htmlspecialchars($rs['agreement_no'].'-'.$rs['customer_name'], ENT_QUOTES)
 				.'</option>';
 			}
 			return $option;
 		}
 	}else{
 		return $rows;
 	}
 }
  
 static function getCurrentLang(){
 	$session_lang=new Zend_Session_Namespace('lang');
 	if(!empty($session_lang->lang_id)){
 		if ($session_lang->lang_id>2){
 			return 2;
 		}
 		return $session_lang->lang_id;
 	}else{
 		return 2;
 	}
 }
  
 public function getRequestNo(){
 	$this->_name='tb_staff_request';
 	$db = $this->getAdapter();
 	$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
 	$acc_no = $db->fetchOne($sql);
 	$new_acc_no= (int)$acc_no+1;
 	$acc_no= strlen((int)$acc_no+1);
 	$pre ='R';
 	for($i = $acc_no;$i<4;$i++){
 		$pre.='0';
 	}
 	return $pre.$new_acc_no;
 }
 
 public function getCloselistNo(){
 	$this->_name='tb_closelist';
 	$db = $this->getAdapter();
 	$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
 	$acc_no = $db->fetchOne($sql);
 	$new_acc_no= (int)$acc_no+1;
 	$acc_no= strlen((int)$acc_no+1);
 	$pre ='cl';
 	for($i = $acc_no;$i<4;$i++){
 		$pre.='0';
 	}
 	return $pre.$new_acc_no;
 }
  
 public function getStaffNo(){
 	$this->_name='tb_staff';
 	$db = $this->getAdapter();
 	$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
 	$acc_no = $db->fetchOne($sql);
 	$new_acc_no= (int)$acc_no+1;
 	$acc_no= strlen((int)$acc_no+1);
 	$pre ="S";
 	for($i = $acc_no;$i<4;$i++){
 		$pre.='0';
 	}
 	return $pre.$new_acc_no;
 }
  
 public function getTransferStockNo(){
 	$this->_name='rms_transferstock';
 	$db = $this->getAdapter();
 	$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
 	$acc_no = $db->fetchOne($sql);
 	$new_acc_no= (int)$acc_no+1;
 	$acc_no= strlen((int)$acc_no+1);
 	$pre ='T';
 	for($i = $acc_no;$i<4;$i++){
 		$pre.='0';
 	}
 	return $pre.$new_acc_no;
 }
  
 public function getTransferReceiveNo(){
 	$this->_name='rms_transfer_receive';
 	$db = $this->getAdapter();
 	$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
 	$acc_no = $db->fetchOne($sql);
 	$new_acc_no= (int)$acc_no+1;
 	$acc_no= strlen((int)$acc_no+1);
 	$pre ='TR';
 	for($i = $acc_no;$i<4;$i++){
 		$pre.='0';
 	}
 	return $pre.$new_acc_no;
 }
  
 public function getStaffIdNo(){
 	$this->_name='tb_staff';
 	$db = $this->getAdapter();
 	$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
 	$acc_no = $db->fetchOne($sql);
 	$new_acc_no= (int)$acc_no+1;
 	$acc_no= strlen((int)$acc_no+1);
 	$pre ='S';
 	for($i = $acc_no;$i<4;$i++){
 		$pre.='0';
 	}
 	return $pre.$new_acc_no;
 }
 
 public function getVendorPaidNumber($branch_id = null){
 	$this->_name='tb_vendor_payment';
 	$db = $this->getAdapter();
 	$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$branch_id." LIMIT 1 ";
 	$pre = $this->getPrefixCode($branch_id)."R";
 	$acc_no = $db->fetchOne($sql);
 
 	$new_acc_no= (int)$acc_no+1;
 	$acc_no= strlen((int)$acc_no+1);
 	for($i = $acc_no;$i<5;$i++){
 		$pre.='0';
 	}
 	return $pre.$new_acc_no;
 }
  
}
?>