/**
 * Created by AEscobar on 5/12/2015.
 */
jQuery(function ($) {

	$(".request_group").click(function () {
		var user_id = $("#user_id").val();
		var email = $("#email").val();
		var group_id = $(this).data("group_id");
		var group_format = $('input[name=email_format_' + group_id + ']:checked').val();


		var data = {
			action      : 'wpmg_request_group',
			user_id     : user_id,
			email       : email,
			group_id    : group_id,
			group_format: group_format,
			nextNonce   : PT_Ajax.nextNonce
		};

		$.post(PT_Ajax.ajaxurl, data, function (response) {
			console.log(response);
			return true;
		});
	});

	$(".cancel_request").click(function () {
		var group_id = $(this).data("group_id");


		console.log(group_id);

	});

	$(".remove_group").click(function () {
		var group_id = $(this).data("role");
		console.log('hehe' + group_id);

	});


});
