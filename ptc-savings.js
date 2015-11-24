jQuery(document).ready( function($) {
	
	$("input[name='ptc_land_savings_setting']").blur(function(){
		$.ajax({
			type: "POST",
			data: "ptc_land_savings_setting="+$(this).attr("value") + "&action=ptc_savings_numeric_check",
			url: ajaxurl,
			beforeSend: function(){
			$("#landInfo").html("Checking Email...");
		},
		success: function(data){
			if(data == "valid")
			{
				$("#landInfo").html("Number OK");
			}
			else
			{
				$("#landInfo").html("You have not entered a valid number!");
			}
		}
		});
	});
	
	$("input[name='ptc_water_savings_setting']").blur(function(){
		$.ajax({
			type: "POST",
			data: "ptc_water_savings_setting="+$(this).attr("value") + "&action=ptc_savings_numeric_check",
			url: ajaxurl,
			beforeSend: function(){
			$("#waterInfo").html("Checking Email...");
		},
		success: function(data){
			if(data == "valid")
			{
				$("#waterInfo").html("Number OK");
			}
			else
			{
				$("#waterInfo").html("You have not entered a valid number!");
			}
		}
		});
	});
	
	$("input[name='ptc_carbon_savings_setting']").blur(function(){
		$.ajax({
			type: "POST",
			data: "ptc_carbon_savings_setting="+$(this).attr("value") + "&action=ptc_savings_numeric_check",
			url: ajaxurl,
			beforeSend: function(){
			$("#carbonInfo").html("Checking Email...");
		},
		success: function(data){
			if(data == "valid")
			{
				$("#carbonInfo").html("Number OK");
			}
			else
			{
				$("#carbonInfo").html("You have not entered a valid number!");
			}
		}
		});
	});
	
});