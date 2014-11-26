
	var cache = {};
	$('.date').datepicker({dateFormat: 'yy-mm-dd'});
	$('.datetime').datetimepicker({
		dateFormat: 'yy-mm-dd',
		timeFormat: 'h:m'
	});
	$('.time').timepicker({timeFormat: 'h:m'});
        
	$(".remove_row").live('click',function(){
                var total = $("#total_amount").val();
                var serviceCost = $(this).parent().parent().children("td:last-child").text();
                $(this).parent().parent().remove();
                total = Number(total) - Number(serviceCost);
		$("#total_amount").val(total);
	});
	
	
	// to display added row of services
	$("#button-product").click(function(){
		
		var account_type	= $("#account_type_field").val();
		var description		= $("#description_field").val();
		var amount		= $("#amount_field").val();
		if(account_type != '' && description != '' && amount != '') {
			
			//added row to table
			addNewRow(account_type, description, amount);
			//reseting fields to add new one
			$("#account_type_field").val('');
			$("#description_field").val('');
			$("#amount_field").val('');
                        total = Number(total) + Number(amount);
                        $("#total_amount").val(total);
		}
		
		
	});
	
	$( "#payee_name" ).autocomplete({
		minLength: 2,
		source: function( request, response ) {
			var term = request.term;
			if ( term in cache ) {
				response( cache[ term ] );
				return;
			}
			$.getJSON( "<?php echo $user_link; ?>", request, function( data, status, xhr ) {
				cache[ term ] = data;
				response( data );
			});
		},
		 select: function( event, ui ) {
			$("#payee").val(  ui.item ? ui.item.id : '' );
			
		}
	});
	

	$('.vtabs a').tabs();
	
	function changeSections(type){
		if(type == 1 ) {
			$("#invoice_sec, #payment_sect").hide();
			$("#expiration_date").parent().prev().text("<?php echo $this->language->get('text_expiry_date') ?>");
		} else if (type == 2 ) {
			$("#invoice_sec").show();
			$("#payment_sect").hide();
			$("#expiration_date").parent().prev().text("<?php echo $this->language->get('text_due_date') ?>");
		} else if (type == 3 ) {
			$("#invoice_sec").hide();
			$("#payment_sect").show();
			$("#expiration_date").parent().prev().text("<?php echo $this->language->get('text_payment_date') ?>");
		}
	}

	/*
		function to used to get information about a profile for display purpose 
		either we are making right invoice for right customer or not.
	*/
	function getCustomerDetail(id) {
		$.ajax({
			url: "<?php echo $this->url->link('profiles/manage/profiledetail&token='.$this->session->data['token']) ?>&user="+id,
			type: 'get',
			//dataType: 'json',
			beforeSend: function() {
			},
			complete: function() {
			},
			success: function(data) {
				var userDetail= eval ( "("+ data + ")" );
				$("#email").val(userDetail['email']);
				$("#phone").val(userDetail['phone']);
				$("#mobile").val(userDetail['mobile']);
				$("#billing_address").val(userDetail['billing_address']);
				$("#billing_city").val(userDetail['billing_city']);
				$("#billing_state").val(userDetail['billing_state']);
				$("#shipping_address").val(userDetail['shipping_address']);
				$("#shipping_city").val(userDetail['shipping_city']);
				$("#shipping_state").val(userDetail['shipping_state']);
			}
		});
	}
	
	/*used to append new row to add multiple record.*/
	function addNewRow(accountType, description, amount){
		var strHtml = "<tr><td class=\"left\"><img src=\"view/image/delete.png\" title=\"<?php echo $this->language->get('button_remove'); ?>\" alt=\"<?php echo $this->language->get('button_remove'); ?>\" style=\"cursor: pointer;\" class=\"remove_row\" /></td>";
			strHtml += "<td class=\"left\">"+accountType;
			strHtml += "<input type=\"hidden\" name=\"account_type[]\" class=\"account_type\" value =\""+accountType+"\" />";
			strHtml += "<input type=\"hidden\" name=\"account_description[]\" class=\"description\" value =\""+description+"\" />";
			strHtml += "<input type=\"hidden\" name=\"amount[]\" class=\"amount\" value =\""+amount+"\" />";
			strHtml += "</td>";
			strHtml += "<td class=\"left\">"+description+"</td><td class=\"right\">"+amount+"</td></tr>";
                        alert(strHtml);
                        
		$("#services_list").append(strHtml);
	}
