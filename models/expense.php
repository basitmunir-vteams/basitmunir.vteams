<?php
/*
	Author: Muhammad Basit Munir
	Desciption: this file contain functions to manage expenses. save, fetch expense and expense details this class 
	contain no constructor bt different methods regarding expense management module.
*/
class ModelExpenseExpense extends Model{
	
	/*
		addExpense function used to save overhead expenses. take an array of posted data
	*/
	public function addExpense($data) {
		$data['expense_type'] = isset($data['expense_type']) ? $data['expense_type'] : 1;
		$travelID= isset($data['travel_id']) ? $data['travel_id']  : NULL;
		$sql = "INSERT INTO `".DB_PREFIX."expenses` (`travel_id`, `user_id`, `expense_name`, `expense_type`, `amount`, `date`, `comment`) VALUES ( '".$this->db->escape($travelID)."', '".$this->db->escape($this->user->getId())."', '".$this->db->escape($data['expense_name'])."', '".$this->db->escape($data['expense_type'])."', '".$this->db->escape($data['expense_amount'])."', '".date( "Y-m-d", strtotime($this->db->escape($data['expense_date']) ))."', '".$this->db->escape($data['expense_reason'])."');";
		$this->db->query($sql);
		return true;
	}	
	
	/*
		addTravelExpense function used to save local travel expenses. take an array of posted data
	*/
	public function addTravelExpense($data)	{
		$sql = "INSERT INTO `".DB_PREFIX."user_travel` (`user_id`,`travel_date`, `travel_from`, `travel_to`, `travel_by`, `miles`, `mileage_rate`, `toll`, `parking`, `amount`, `project_no`, `comment`) VALUES ('".$this->db->escape($this->user->getId())."', '".date( "Y-m-d", strtotime($this->db->escape($data['travel_date']) ))."', '".$this->db->escape($data['travel_from'])."', '".$this->db->escape($data['travel_to'])."', '".$this->db->escape($data['travel_by'])."', '".$this->db->escape($data['travel_miles'])."', '".$this->db->escape($data['mileage_rate'])."', '".$this->db->escape($data['toll_expense'])."', NULL, '".$this->db->escape($data['expense_amount'])."', '".$this->db->escape($data['project_no'])."','".$this->db->escape($data['expense_reason'])."')";
		
		$this->db->query($sql);
		
		$data['travel_id']		= $this->db->getLastId();
		$data['expense_name'] 	= 'Local Travel Expense';
		$data['expense_type']	= 2;
		$data['expense_amount']	= $data['other_expense'];
		$data['expense_date']	= $data['travel_date'];
		$data['expense_reason']	= $data['expense_reason'];
		$this->addExpense($data);
		return true;
	}
	
	/*
		addOffStationTravelExpense function used to save off station travel expenses. take an array of posted data
	*/
	public function addOffStationTravelExpense($data) {
			
		// insert travel record
		
		$sql = "INSERT INTO `".DB_PREFIX."user_travel` (`user_id`,`travel_date`, `travel_from`, `travel_to`, `travel_by`, `miles`, `mileage_rate`,`per_diem_lodging_rate`, `per_diem_rate`, `toll`, `parking`, `amount`, `project_no`, `comment`) VALUES ('".$this->db->escape($this->user->getId())."', '".date( "Y-m-d", strtotime($this->db->escape($data['travel_date']) ))."', '".$this->db->escape($data['travel_from'])."', '".$this->db->escape($data['travel_to'])."', '".$this->db->escape($data['travel_by'])."', '".$this->db->escape($data['travel_miles'])."', '".$this->db->escape($data['mileage_rate'])."', '".$this->db->escape($data['per_diem_rate'])."', '".$this->db->escape($data['per_diem_loding_rate'])."', '".$this->db->escape($data['toll_expense'])."','".$this->db->escape($data['travel_parking'])."' , '".$this->db->escape($data['expense_amount'])."', '".$this->db->escape($data['project_no'])."','".$this->db->escape($data['expense_reason'])."')";
		
		$this->db->query($sql);
		$data['travel_id']		= $this->db->getLastId();
		// insert other expenses for travel trip
		for($iterator=0; $iterator< count($data['other_expense_date']); $iterator++){
			$data['expense_name'] 	= $data['other_expense_name'][$iterator];
			$data['expense_type']	= 3;
			$data['expense_amount']	= $data['other_expense_amount'][$iterator];
			$data['expense_date']	= $data['other_expense_date'][$iterator];
			$data['expense_reason']	= $data['other_expense_comment'][$iterator];
			$this->addExpense($data);
		}
		
		//save hotel/ lodging expenses
		$hotelData=array();
		for($iterator=0; $iterator < count($data['room_occupy_date']); $iterator++){
			$hotelData['travel_id']= $data['travel_id'];
			$hotelData['room_tax'] = $data['room_tax'][$iterator];
			$hotelData['room_cost'] = $data['room_cost'][$iterator];
			$hotelData['room_occupy_date'] = $data['room_occupy_date'][$iterator];
			$this->HotelLodgingExpenses($hotelData);
		}
		
		return true;

	}
	
	/*
		getExpenses function fetch expenses added in the table for admin to approve or disapprove the expense and for employee to view its status or details.
	*/
	public function HotelLodgingExpenses($data){
		$sql = "INSERT INTO `bb_hotel_expense` (`travel_id`, `room_occupy_date`, `room_cost`, `room_tax`) VALUES ( '".$this->db->escape($data['travel_id'])."', '".$this->db->escape($data['room_occupy_date'])."', '".$this->db->escape($data['room_cost'])."', '".$data['room_tax']."')";
		$this->db->query($sql);
		return true;
	}
	
	/*
		getExpenses function fetch expenses added in the table for admin to approve or disapprove the expense and for employee to view its status or details.
	*/
	public function getExpenses($data, $user=false) {
		$chunk = '';
		if ($user != false) {
			$chunk .= "where exp.user_id='".$this->db->escape($user)."'";
		}
		else{
			$chunk .= "where exp.expense_status='0'";
		}
		
		$sql = "SELECT exp.expense_id,exp.travel_id, exp.date, exp.expense_name, expense_type, exp.expense_status, hotel_expenses.lodging_cost as LodgingCost, trvl.amount as travelAmount,exp.amount
		FROM ".DB_PREFIX."expenses exp
		LEFT JOIN ".DB_PREFIX."user_travel trvl ON exp.travel_id = trvl.travel_id
		LEFT JOIN (
			SELECT ( sum( room_cost ) + sum( room_tax )) AS lodging_cost, travel_id FROM ".DB_PREFIX."hotel_expense GROUP BY travel_id
		) AS hotel_expenses ON hotel_expenses.travel_id = trvl.travel_id

 ".$chunk." limit ".$data['start'].", ".$data['limit']."";

		$query = $this->db->query($sql);
		return $query->rows;
		
	}
	
	/*
		countExpenses function used to count number of rows added. this function run two quiries as we have scenario in out db that expense table can have multiple rows for travel field as travel expenses and also single row record as over head expense.
	*/
	public function countExpenses($user=false) {
		$chunk = '';
		if ($user != false) {
			$chunk .= " and exp.user_id='".$this->db->escape($user)."'";
		}else{
			$chunk .= " and exp.expense_status='0'";
		}
		
		$sqlOverHeadCount = "select count( exp.expense_id ) as count from ".DB_PREFIX."expenses exp where expense_type = 1".$chunk;
		$sqlTravelCount = "select count( DISTINCT(exp.travel_id) ) as count from ".DB_PREFIX."expenses exp where expense_type > 1".$chunk;
		
		$query = $this->db->query($sqlOverHeadCount);
		$queryTravels= $this->db->query($sqlTravelCount);
		return $query->row['count'] + $queryTravels->row['count'];
	}
	
	/*
		getExpenseDetails function used to get expenses details to review the expense, travel etc.
	*/
	public function getExpenseDetails($expenseID) {
		$sql = "select exp.*,trvl.*, CONCAT(u.firstname,' ',u.lastname) as name, exp.comment as expense_comment, exp.amount as expense_amount from ".DB_PREFIX."expenses exp inner join ".DB_PREFIX."user u on exp.user_id=u.user_id 
		left join ".DB_PREFIX."user_travel trvl on exp.travel_id=trvl.travel_id 
		where exp.expense_id='".$this->db->escape($expenseID).".'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getHotelLodgingDetails($travel_id) {
		$sql = "select he.* from ".DB_PREFIX."hotel_expense he where he.travel_id='".$this->db->escape($travel_id).".'";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getOtherExpenseDetails($travel_id){
		$sql = "select exp.* from ".DB_PREFIX."expenses exp where exp.travel_id='".$this->db->escape($travel_id).".'";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	/*
		approveExpense function self explantory used to approve the expense . it took two parameters first is expense id which is going to be approve/disapprove and other is status is it approved or not. 
	*/
	public function approveExpense ($expense, $approve) {
		$approve = $approve == 1 ? 1 : -1;
		$sql = "update ".DB_PREFIX."expenses set expense_status = '".$this->db->escape($approve)."' where expense_id='".$this->db->escape($expense)."'";
		$query = $this->db->query($sql);
		return true;
	}
	
	/*
		following function "GetUserProjects" function used to get current projected to which employee have attached. either he created it, lead of it or a part of it in some kind of task
	*/
	public function getUserProjects(){
		$sql = "select project.id as project_id,project.name as project_name from ".DB_PREFIX."project project left join ".DB_PREFIX."project_contributer contributers on contributers.project_id=project.id where contributers.user='".$this->db->escape($this->user->getId())."'";
		$query = $this->db->query($sql);
		return $query->rows;
	}

	/*
	*Function used to calculate distance between two addresses
	*/
	public function calculatedistance(){
            
		if(!empty($this->request->get['origin']) && ($this->request->get['destination'])){
			$url = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins='.urlencode($this->request->get['origin']).'&destinations='.urlencode($this->request->get['destination']).'&mode=driving&language=en-EN&key=AIzaSyAd_bTAJbqP-ILgzM7V5MJVkNnPZsomlJw';
	
			$ch = curl_init();  
			curl_setopt	($ch,CURLOPT_URL,$url);
			curl_setopt	($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt	($ch,CURLOPT_HEADER, false); 
			curl_setopt	($ch,CURLOPT_SSL_VERIFYPEER,true);
			curl_setopt ($ch, CURLOPT_CAINFO, DIR_APPLICATION."cacert.pem");
			$output=curl_exec($ch);
			// close curl resource to free up system resources
			curl_close($ch); 
			$returnData = json_decode ($output);
                       
                        if(isset( $returnData->rows[0]->elements[0]->status) && $returnData->rows[0]->elements[0]->status == 'ZERO_RESULTS'  ) {
                            $this->load->language('expense/expense');
                            echo -1 .'@'. $this->language->get('text_zero_result');
                        } else {
                            echo round((int) $returnData->rows[0]->elements[0]->distance->value / (1000 * 1.61) , 2) ;
                        }
                        
			
		}
	}
}
?>