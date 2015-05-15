/**
 * Created by AEscobar on 5/12/2015.
 */
jQuery(function ($) {

	$("#request_group").click(function () {

		var saveData = [];
		saveData["group_id"] = $("#group_name").val();
		saveData["group_format"] = $('input[name=email_format_'+saveData["group_id"]+']:checked').val();

		//creating a json object
		var user_requested_groups={};

		for(var i in saveData)
		{
			user_requested_groups[i] = saveData[i];
		}

		var user_id = $("#user_id").val();
		var email = $("#email").val();

		var data = {
			action   : 'wpmg_request_group',
			user_id  : user_id,
			email  : email,
			user_requested_groups:user_requested_groups,
			nextNonce: PT_Ajax.nextNonce
		};

		$.post(PT_Ajax.ajaxurl, data, function (response) {
			console.log(response);
			return true;
		});
	});

});
