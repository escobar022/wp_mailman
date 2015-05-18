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

		var user_id = $("#user_id").val();
		var email = $("#email").val();
		var group_id = $(this).data("group_id");
		var request_id = $(this).data("request_id");

		var data = {
			action    : 'wpmg_cancel_request',
			user_id   : user_id,
			email     : email,
			group_id  : group_id,
			request_id: request_id,
			nextNonce : PT_Ajax.nextNonce
		};

		$.post(PT_Ajax.ajaxurl, data, function (response) {
			console.log(response);
			return true;
		});
	});

	$(".remove_group").click(function () {
		var group_id = $(this).data("role");
		console.log('hehe' + group_id);

	});

	$(".req_leave_group").click(function () {

		var group_id = $(this).data("group_id");
		var current_status = $(".current_status[data-group_id=" + group_id + "]");
		var confirm_message = $(".confirm_message[data-group_id=" + group_id + "]");
		var confirm_button = $(".confirm_leave_group[data-group_id=" + group_id + "]");
		var cancel_button = $(".cancel_leave_group[data-group_id=" + group_id + "]");

		$(this).toggle(false);
		$(current_status).hide();
		$(confirm_message).show();
		$(confirm_button).toggle(true);
		$(cancel_button).toggle(true);

		$(confirm_button).click(function () {

			var user_id = $("#user_id").val();
			var email = $("#email").val();
			var group_id = $(this).data("group_id");

			var data = {
				action    : 'wpmg_leave_group',
				user_id   : user_id,
				email     : email,
				group_id  : group_id,
				nextNonce : PT_Ajax.nextNonce
			};

			$.post(PT_Ajax.ajaxurl, data, function (response) {
				console.log(response);
				return true;
			});
		});

		$(cancel_button).click(function () {
			$(".req_leave_group").toggle(true);
			$(current_status).show();
			$(confirm_message).hide();
			$(confirm_button).toggle(false);
			$(cancel_button).toggle(false);
		});

	});

});
