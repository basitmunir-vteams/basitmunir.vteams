
var count=1;
	$(function(){
		
		$("#tabs a").tabs();
		$(".datepicker").datepicker({'maxDate': "+0", 'dateFormat':'yy-mm-dd'});
		$(".datepicker_expense").datepicker({'maxDate': "+0", 'dateFormat':'yy-mm-dd'});	
		/*
			
		*/
		$(".remove_this").live('click',function(){
			$(this).parent().parent().parent().parent().remove();
		});
		/*
			function to calculate total hotel loding cost and overall total expense
		*/
		$('.room-cost, .room-tax').live('blur',function() {
			
			var RoomCost= 0;
			$(".room-cost").each(function() {
                RoomCost = RoomCost + Number($(this).val())
            });
			$(".room-tax").each(function() {
                RoomCost = RoomCost + Number($(this).val())
            });
			
			$("#lodging_total").text(RoomCost);
			calculateTotalAmount('offStation');
		});
		// function that calculate tootal expense amount and sumup with commulative amount of travel.
		$(".expense-amount").live('blur',function(){
			var amount= 0;
			$(".expense-amount").each(function() {
                amount = amount + Number($(this).val())
            });
			
			$("#other_expense_total").text(amount);
			calculateTotalAmount('offStation');
		});
		
	});

	//After vaidation we are going to call this form action to navigate to corresponding tab instead of by default tab
	$(document).ready(function(){
		<?php if (isset($this->request->post['tab'])) {  ?>

				var tab = "<?php echo $this->request->post['tab'] ?>";
				if ( tab == "1" ) {
					$("#overhead_tab").click();
				} else if( tab == "2" ) {
					$("#local_travel_tab").click();
				} else if( tab == "3" ) {
					$("#travel_tab").click()
				}
		<?php } ?>
		
		$("#other_expenses_section, #hotel_lodging_section")
		
	});
	
	/*
		 calculate total amount expenses it takes one parameter either is local travel or a long travel. 
		 then according to this criteria it calculate total amount of expense and store value in expense amount field
		 */
	
	function calculateTotalAmount(travelType){
		
		var travelCost = tolls = otherExpense = roomCost = roomTax = parking = 0;
		
		if (travelType == 'local') {
			
			travelCost	= Number ( $("#local_travel_miles").val() * $("#local_travel_mileage_rate").val() );
			tolls		= Number ($("#local_travel_toll").val() );
			otherExpense= Number ( $("#local_other_expense").val() );
			
			$("#local_travel_amount").val( (travelCost + tolls + otherExpense + parking + roomCost + roomTax).toFixed(2) );
				
		} else if (travelType == 'offStation') {
			
			travelCost	= Number ($("#travel_miles").val() * $("#mileage_rate").val() );
			tolls		= Number ($("#toll_expense").val() );
			parking 	= Number ( $("#travel_parking").val() );
			roomCost	= Number ($("#lodging_total").text());
			otherExpense= Number($("#other_expense_total").text());
			
			$("#travel_amount").val( (travelCost + tolls + otherExpense + parking + roomCost + roomTax).toFixed(2) );
		}
		
	}
		
	/*
	* function to calculate distance this funciton goes to expense controller and call google api via curl request and manupulate the result.
	* this ajax request returns distance in miles which populates the miles field.
	*/
	function CalculateDistance(travelType) {
		var origins=destinations='';
		if (travelType == 'local') {
			origins= $("#local_travel_from").val();
			destinations=$("#local_travel_to").val();
		} else if (travelType == 'offStation') {
			origins= $("#travel_from").val();
			destinations=$("#travel_to").val();
		}
		//var url= "https://maps.googleapis.com/maps/api/distancematrix/json?origins="+encodeURI(origins)+"&destinations="+encodeURI(destinations)+"&mode=driving &language=fr-FR&key=AIzaSyAd_bTAJbqP-ILgzM7V5MJVkNnPZsomlJw";
		if(origins != '' && destinations != '') {
			var url = "<?php echo $findDistanceUrl; ?>&origin="+origins+"&destination="+destinations;
			$.ajax({
				url: url ,
				type: 'get',
				beforeSend: function() {
				},
				complete: function() {
				},
				success: function(data) {
                                        if( data.indexOf('@') ) {
                                            alert(data.substring(3, data.length));
                                            data = 0;
                                            
                                        }                                          
                                        if (travelType == 'local') {
                                            $("#local_travel_miles").val(data);
                                        } else {
                                            $("#travel_miles").val(data);
                                        }
                                        
				}
			});		
		}
	}
	
	//function appends loding fields for different date at off station travel tab.
	function appendLodgingFields(){
			var strHTML ="<table class=\"list\"><tr><td class='right' colspan='2'><span class='remove_this'>remove</span></td></tr><tr>";
                strHTML +="  <td class=\"left\"><?php echo $this->language->get('text_date') ?><span class=\"required\">*</span></td>";
                strHTML +="  <td class=\"left\" width=\"80%\">";
                strHTML +="     <input type=\"text\" name=\"room_occupy_date[]\" id=\"room_cost\" id=\"myid"+count+"\" class=\"datepicker_expense\"  onblur=\"\" />";
                strHTML +="       </td>";
                strHTML +="   </tr>";
                strHTML +="   <tr>";
                strHTML +="       <td class=\"left\"><?php echo $this->language->get('text_room_cost') ?><span class=\"required\">*</span></td>";
                strHTML +="       <td class=\"left\" width=\"80%\">";
                strHTML +="           <input type=\"text\" name=\"room_cost[]\" class=\"room-cost\" />";
                strHTML +="       </td>";
                strHTML +="   </tr>";
                strHTML +="   <tr>";
                strHTML +="       <td class=\"left\"><?php echo $this->language->get('text_room_tax') ?><span class=\"required\">*</span></td>";
                strHTML +="       <td class=\"left\" width=\"80%\">";
                strHTML +="           <input type=\"text\" name=\"room_tax[]\" class=\"room-tax\"  />";
                strHTML +="       </td>";
                strHTML +="   </tr>";
                strHTML +="   <tr> </table>";
				
				$("#hotel_lodging_section").children('table:last').after(strHTML);				
	}
	
	//function appends other expense fields for different date at off station travel tab.	
	function appendOtherExpenseFields(){
		var strHTML ="<table class=\"list\"><tr><td class='right' colspan='2'><span class='remove_this'>remove</span></td></tr>";
            strHTML +="    	<tr>";
            strHTML +="            <td class=\"left\"><?php echo $this->language->get('text_date') ?><span class=\"required\">*</span></td>";
            strHTML +="            <td class=\"left\" width=\"80%\">";
            strHTML +="               <input type=\"text\" name=\"other_expense_date[]\" class=\"datepicker-expense\"  onblur=\"\" />";
            strHTML +="            </td>";
            strHTML +="        </tr>";
			strHTML +="		<tr>";
			strHTML +="				<td class=\"left\"><?php echo $this->language->get('text_expense_name') ?></td>";
			strHTML +="				<td class=\"left\" width=\"80%\">";
			strHTML +="					<input type=\"text\" name=\"other_expense_name[]\" />";
			strHTML +="				</td>";
			strHTML +="			</tr>";
            strHTML +="         <tr>";
            strHTML +="            <td class=\"left\"><?php echo $this->language->get('text_expense_amount') ?><span class=\"required\">*</span></td>";
            strHTML +="            <td class=\"left\" width=\"80%\">";
            strHTML +="                <input type=\"text\" name=\"other_expense_amount[]\" class=\"expense-amount\" />";
            strHTML +="           </td>";
            strHTML +="        </tr>";
            strHTML +="        <tr>";
            strHTML +="            <td class=\"left\"><?php echo $this->language->get('text_reason') ?><span class=\"required\">*</span></td>";
            strHTML +="            <td class=\"left\" width=\"80%\">";
            strHTML +="                <input type=\"text\" name=\"other_expense_comment[]\"  />";
            strHTML +="            </td>";
            strHTML +="        </tr>";  
            strHTML +="    </table>";
			
			$("#other_expenses_section").children('table:last').after(strHTML);
			
	}

