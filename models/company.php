<?php
/*
	Class Name	: ModelExpenseCompany
	Date Created: 17 july 2014
	description	: this model class used to deal with company expenses.
*/
class ModelExpenseCompany extends Model {
	
	/*count total number of bills or expenses company have received so far. either it is paid or not.*/
	public function getTotalExpenses() {
		$sql = "select count(bill_id) as count from ".DB_PREFIX."company_bill";
		$query = $this->db->query($sql);
		return $query->row['count'];
	}
	
	/*return company expense details*/
	public function getExpenses($data) {
		$sql = "select company_bill.bill_id, company_bill.bill_number, company_bill.bill_date, company_bill.bill_due_date, expense_amount.amount, company_bill.expense_type, personal_profiles.display_name from ".DB_PREFIX."company_bill company_bill left join ".DB_PREFIX."personal_profiles personal_profiles on company_bill.vendor_id=personal_profiles.profile_id left join ( select bill_id, sum(amount) as amount from ".DB_PREFIX."company_bill_details group by bill_id ) as expense_amount on expense_amount.bill_id = company_bill.bill_id limit ".$data['start'].", ".$data['limit']."";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	/*getVendors is function to get vendor lists available*/
	public function getVendors() {
		$sql = "SELECT profile_id as vendor_id, display_name from ".DB_PREFIX."personal_profiles where profile_type=2";
		$query= $this->db->query($sql);
		return $query->rows;
	}
	
	/**/
	public function insertBillRecord($data) {
		if(empty($data['bill_id'])) {
			
		$sql = "INSERT INTO `".DB_PREFIX."company_bill` (`vendor_id`, `bill_number`, `bill_date`, `bill_due_date`, `memo`, `attachment`, `terms`, `expense_type`, `payee`, `expense_date`, `payment_method`, `check_number`, `status`) VALUES ('".$this->db->escape($data['vendor_id'])."', '".$this->db->escape($data['bill_number'])."', '".$this->db->escape($data['bill_date'])."', '".$this->db->escape($data['bill_due_date'])."', '".$this->db->escape($data['memo'])."', '".$this->db->escape($data['attachment'])."', '".$this->db->escape($data['terms'])."', '".$this->db->escape($data['expense_type'])."', '".$this->db->escape($data['payee'])."', '".$this->db->escape($data['expense_date'])."', '".$this->db->escape($data['payment_method'])."', '".$this->db->escape($data['check_number'])."', '".$this->db->escape($data['status'])."')";
		
		} else {
			
			$sql = "update `".DB_PREFIX."company_bill` set 
					`vendor_id` = '".$this->db->escape($data['vendor_id'])."', 
					`bill_number`= '".$this->db->escape($data['bill_number'])."', 
					`bill_date` = '".$this->db->escape($data['bill_date'])."', 
					`bill_due_date` = '".$this->db->escape($data['bill_due_date'])."', 
					`memo` = '".$this->db->escape($data['memo'])."', 
					`attachment` = '".$this->db->escape($data['attachment'])."', 
					`terms` = '".$this->db->escape($data['terms'])."' , 
					`expense_type` = '".$this->db->escape($data['expense_type'])."', 
					`payee` = '".$this->db->escape($data['payee'])."', 
					`expense_date` = '".$this->db->escape($data['expense_date'])."', 
					`payment_method` = '".$this->db->escape($data['payment_method'])."', 
					`check_number` = '".$this->db->escape($data['check_number'])."' , 
					`status` = '".$this->db->escape($data['status'])."'
				where bill_id= '".$this->db->escape($data['bill_id'])."'
			";	
			
		}
//		echo $sql.'<br />';
		$query = $this->db->query($sql);
		
		//check if it is insert mod then we need to retrieve last insert id to store data in detail table
		if(empty($data['bill_id'])) {

			$data['bill_id'] = $this->db->getLastId();
			
		} else { // ekse we need to remove record for this sale id and insert the record
			$this->deleteDetail($data['bill_id']);
		}
				
		$this->insertDetail($data);
		return true;
	}
	
	//function delete records from details table.
	public function deleteDetail($saleId){
		$sql = "delete from ".DB_PREFIX."company_bill_details where bill_id= '".$this->db->escape($saleId)."'";
		$this->db->query($sql);
	}
	
	//function insert sale detail like what services being provided and when it is provided at which cost will be displayed over here.
	public function insertDetail($data) {

		$sql = "INSERT INTO `".DB_PREFIX."company_bill_details` (`bill_id`, `account_type`, `account_description`, `amount`) VALUES ";
		for($iterator = 0; $iterator < count($data['account_type']) ; $iterator++ ) {
			
			$sql .= "('".$this->db->escape($data['bill_id'])."', '".$this->db->escape($data['account_type'][$iterator])."', '".$this->db->escape($data['account_description'][$iterator])."', '".$this->db->escape($data['amount'][$iterator])."'),";
			
		}
		$sql = substr($sql,0, strlen($sql)-1);
	//	echo $sql;
		$this->db->query($sql);
		return true;
	}
	
	//function to get sale detail for view purpose and edit purpose.
	public function getExpenseDetail($id) {
		$sql = "select company_bill.*, personal_profiles.*, company_bill_details.*, company_bill.bill_id, company_bill.terms as bill_terms, concat(user.firstname,' ',user.lastname) as payee_name From ".DB_PREFIX."company_bill company_bill 
			left join ".DB_PREFIX."personal_profiles personal_profiles on company_bill.vendor_id=personal_profiles.profile_id 
			left join ".DB_PREFIX."company_bill_details company_bill_details on company_bill.bill_id=company_bill_details.bill_id 
			left join ".DB_PREFIX."user user on user.user_id = company_bill.payee 
			where company_bill.bill_id='".$this->db->escape($id)."'";
		$query= $this->db->query($sql);
		return $query->rows;
		
	}
	
	
	//functoin used to delete sale record. if it is no more necessary. then delete it on deletion of this record a trigger will be called that will delete corresponding records from detail table.
	public function deleteBillRecord($id){
		$sql = "DELETE from `".DB_PREFIX."company_bill` where bill_id='".$this->db->escape($id)."'";
		$this->db->query($sql);
		return true;
	}
	
	// return users list for payee field.
	public function getUsers($terms) {
		$sql = "select user_id as id,concat(firstname,' ',lastname) as label from ".DB_PREFIX."user where firstname like '%".$this->db->escape($terms)."%' OR lastname like '%".$this->db->escape($terms)."%'";
		$query = $this->db->query($sql);
		echo json_encode($query->rows);
	}
}
?>