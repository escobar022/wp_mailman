function trim(s) {
	while ((s.substring(0, 1) == ' ') || (s.substring(0, 1) == '\n') || (s.substring(0, 1) == '\r')) {
		s = s.substring(1, s.length);
	}
	while ((s.substring(s.length - 1, s.length) == ' ') || (s.substring(s.length - 1, s.length) == '\n') || (s.substring(s.length - 1, s.length) == '\r')) {
		s = s.substring(0, s.length - 1);
	}
	return s;
}

function checkemail(a) {
	if (a) {
		e = a;
		ok = "1234567890qwertyuiop[]asdfghjklzxcvbnm.@-_QWERTYUIOPASDFGHJKLZXCVBNM";
		for (i = 0; i < e.length; i++) {
			if (ok.indexOf(e.charAt(i)) < 0) {
				//alert('Please enter a valid email.');
				return false;
			}
		}
		re = /(@.*@)|(\.\.)|(^\.)|(^@)|(@$)|(\.$)|(@\.)/;
		re_two = /^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
		if (!e.match(re) && e.match(re_two)) {
			return true;
		}
		//alert('Please enter a valid email.');
		return false;
	}
	else
		return false;
}

function isCheckedById(id) {
	var checked = jQuery("input[id=" + id + "]:checked").length;
	return checked != 0;
}

/*General Settings */
//Intro
jQuery(function ($) {
	$("#mgintropage").submit(function () {
		console.log('hi');
		if (isCheckedById("selector")) {
			if (trim($("#subscription_email").val()) == "") {
				alert("Please enter subscription alert email address.");
				$("#subscription_email").focus();
				return false;
			} else {
				if (!checkemail($("#subscription_email").val())) {
					alert("Please enter valid email address.");
					$("#subscription_email").focus();
					return false;
				}
			}
		}
		if (isCheckedById("selector2")) {
			if (trim($("#bounce_alert_email").val()) == "") {
				alert("Please enter bounce alert email address.");
				$("#bounce_alert_email").focus();
				return false;
			} else {
				if (!checkemail($("#bounce_alert_email").val())) {
					alert("Please enter valid email address.");
					$("#bounce_alert_email").focus();
					return false;
				}
			}
		}
		return true;
	});
});

//Custom/Admin Message Add Section
jQuery(function ($) {
	//Admin Message Add
	$('#addmessage').submit(function () {
		if ($("#title").val() == "") {
			alert("Please enter message title.");
			$(this).focus();
			return false;
		}
		if ($("textarea#message").val() == "") {
			alert("Please enter a message.");
			$(this).focus();
			return false;
		}
		if ($("#visible").val() == "") {
			alert("Please select its visibility to use in response.");
			$(this).focus();
			return false;
		}
		return true;
	});

	$(".reset_admin_email").click(function () {

		var r = confirm("Are you sure you want to reset this email?");

		if (r === true) {
			var email_id = $(this).data('email_id');

			var data = {
				action   : 'wpmg_reset_admin_email',
				email_id : email_id,
				nextNonce: PT_Ajax.nextNonce
			};

			$.post(PT_Ajax.ajaxurl, data, function () {
				location.reload(true);
				return true;
			});
		}
	});

	$('#messagelist').dataTable({
		"aoColumnDefs"  : [
			{"bSortable": false, "aTargets": [1, 2, 3]}
		],
		"fnDrawCallback": function () {
			if ($("#messagelist").find("tr:not(.ui-widget-header)").length <= 5) {
				document.getElementById('messagelist_paginate').style.display = "none";
			} else {
				document.getElementById('messagelist_paginate').style.display = "block";
			}
		}
	});

	$(".custom_msg_visibility").click(function () {

		var email_id = $(this).data('email_id');
		var visibility = $(this).data('visibility');

		var data = {
			action    : 'wpmg_custom_msg_visibility',
			email_id  : email_id,
			visibility: visibility,
			nextNonce : PT_Ajax.nextNonce
		};

		$.post(PT_Ajax.ajaxurl, data, function () {
			location.reload(true);
			return true;
		});
	});

	$(".remove_custom_email").click(function () {
		var r = confirm("Are you sure you want to delete this email?");

		if (r === true) {
			var email_id = $(this).data('email_id');

			var data = {
				action   : 'wpmg_remove_custom_email',
				email_id : email_id,
				nextNonce: PT_Ajax.nextNonce
			};

			$.post(PT_Ajax.ajaxurl, data, function () {
				//location.reload(true);
				return true;
			});
		}
	});


});

//Custom Style Page
jQuery(function ($) {
	$("#styleform").submit(function () {
		if (trim($('textarea' + this).val()) == "") {
			alert("Please enter css styles to submit.");
			$("#user_style").focus();
			return false;
		}
		return true;
	});
});


/*Mailing Group Manager*/
//Mailing Group Add Functions
jQuery(function ($) {

	$('#mg_group_mail_usernameContain,#mg_group_mail_passwordContain,#mg_group_pop_sslContain').hide();

	function server_type() {
		if ($("input[name=mg_group_server_type]:checked").val() == 'pop3') {
			if ($('#mg_group_up_required').is(':checked')) {
				$("#mg_group_pop_sslContain").show();
			} else {
				$("input[name=mg_group_pop_ssl]").prop('checked', false);
				$("#mg_group_pop_sslContain").hide();
			}
		} else {
			$("input[name=mg_group_pop_ssl]").prop('checked', false);
			$("#mg_group_pop_sslContain").hide();
		}
	}

	server_type();
	$("input[name=mg_group_server_type]").change(function () {
		server_type();
	});

	function mail_box_up() {
		if ($('#mg_group_up_required').is(':checked')) {
			$('#mg_group_mail_usernameContain,#mg_group_mail_passwordContain').show();
		} else {
			$('#mg_group_mail_usernameContain,#mg_group_mail_passwordContain').hide();
			$("#mg_group_mail_username,#mg_group_mail_password").val("");
		}
	}

	mail_box_up();
	$("#mg_group_up_required").click(function () {
		mail_box_up();
		server_type();
	});

	var mail_user_pass = '#mg_group_smtp_serverContain,#mg_group_smtp_portContain,#mg_group_smtp_sslContain,#mg_group_smtp_usernameContain,#mg_group_smtp_passwordContain';
	var mail_user_pass_fields = '#mg_group_smtp_server,#mg_group_smtp_port,#mg_group_smtp_ssl,#mg_group_smtp_username,#mg_group_smtp_password';
	$(mail_user_pass).hide();


	$("input[name=mg_group_mail_type]").click(function () {
		var mail_type = $(this).val();
		if (mail_type == 'smtp') {
			$(mail_user_pass).show();
			smtp_ssl();
		} else if (mail_type == 'wp') {
			$(mail_user_pass).hide();
			$(mail_user_pass_fields).val("");
			$('#mg_group_smtp_ssl').prop('checked', false);
		} else if (mail_type == 'php') {
			$(mail_user_pass).hide();
			$(mail_user_pass_fields).val("");
			$('#mg_group_smtp_ssl').prop('checked', false);
		}
	});

	if ($("input[name=mg_group_mail_type]:checked").val() == 'smtp') {
		$(mail_user_pass).show();
	}

	function smtp_ssl() {
		if ($('#mg_group_smtp_ssl').is(':checked')) {
			$('#mg_group_smtp_usernameContain,#mg_group_smtp_passwordContain').show();
		} else {
			$('#mg_group_smtp_usernameContain,#mg_group_smtp_passwordContain').hide()
			$('#mg_group_smtp_username,#mg_group_smtp_password').val("");
		}
	}

	smtp_ssl();
	$("#mg_group_smtp_ssl").click(function () {
		smtp_ssl();
	});


	function auto_delete() {
		if ($("input[name=mg_group_auto_delete]:checked").val() == 'no') {
			$("input[name=mg_group_auto_delete_limit]").val('');
			$("#mg_group_auto_delete_limitContain").hide();
		}
		if ($("input[name=mg_group_auto_delete]:checked").val() == 'yes') {
			$("#mg_group_auto_delete_limitContain").show();
		}
	}

	auto_delete();
	$("input[name=mg_group_auto_delete]").change(auto_delete);


	function archive() {
		if ($('#mg_group_archive').is(':checked')) {
			$('#mg_group_auto_deleteContain').show();
			if ($("input[name=mg_group_auto_delete]:checked").val() == 'yes') {
				$("#mg_group_auto_delete_limitContain").show();
			}
		} else {
			$('#mg_group_auto_deleteContain').hide();
			$('#mg_group_auto_delete_limitContain').hide();
			$('input[name=mg_group_auto_delete_limit]').val('');
		}
	}

	archive();
	$('#mg_group_archive').click(function () {
		archive()
	});

});

/*Subscription Request Manager*/
jQuery(function ($) {

	$(".approve_request").click(function () {

		var request_id = $(this).data("request_id");
		var user_id = $(this).data("user_id");

		var data = {
			action    : 'wpmg_approve_group_request',
			request_id: request_id,
			user_id   : user_id,
			nextNonce : PT_Ajax.nextNonce
		};

		$.post(PT_Ajax.ajaxurl, data, function () {
			location.reload();
			return true;
		});

	});

	$(".deny_request").click(function () {

		var request_id = $(this).data("request_id");
		var user_id = $(this).data("user_id");

		var data = {
			action    : 'wpmg_deny_group_request',
			request_id: request_id,
			user_id   : user_id,
			nextNonce : PT_Ajax.nextNonce
		};

		$.post(PT_Ajax.ajaxurl, data, function () {
			location.reload();
			return true;
		});

	});

	$('#sendmessage').submit(function () {
		if ($("#selectmessage option:selected").val() == "") {
			alert("Please select message to load or select new message.");
			$("#selectmessage").focus();
			return false;
		}
		if (trim($("#title").val()) == "") {
			alert("Please enter title.");
			$("#title").focus();
			return false;
		}
		if (trim($("#description").val()) == "") {
			alert("Please enter description.");
			$("#description").focus();
			return false;
		}
		return true;
	});
});


/*Add Subscribers to Group Manager*/
jQuery(function ($) {

	$(".remove_user").click(function () {

		var r = confirm("Are you sure you want to remove user from group?");
		if (r === true) {
			var group_id = $(this).data("group_id");
			var user_id = $(this).data("user_id");

			var data = {
				action   : 'wpmg_remove_user',
				group_id : group_id,
				user_id  : user_id,
				nextNonce: PT_Ajax.nextNonce
			};

			$.post(PT_Ajax.ajaxurl, data, function (response) {

				if (response['action'] === 'error') {
					$('#message').html(response['description']);
				} else {
					location.reload(true);
				}

				return true;
			});
		}
	});

	$(".add_user").click(function () {

		var r = confirm("Are you sure you want to add user to group?");

		if (r === true) {
			var group_id = $(this).data("group_id");
			var user_id = $(this).data("user_id");

			var data = {
				action   : 'wpmg_add_user_to_current_group',
				group_id : group_id,
				user_id  : user_id,
				nextNonce: PT_Ajax.nextNonce
			};

			$.post(PT_Ajax.ajaxurl, data, function (response) {

				if (response['action'] === 'error') {
					$('#message').html(response['description']);
				} else {
					location.reload(true);
				}

				return true;
			});
		}

	});

	$.fn.dataTableExt.oApi.fnFilterAll = function (oSettings, sInput, iColumn, bRegex, bSmart) {
		var settings = $.fn.dataTableSettings;

		for (var i = 0; i < settings.length; i++) {
			settings[i].oInstance.fnFilter(sInput, iColumn, bRegex, bSmart);
		}
	};


	var table = $('.memberlist').dataTable({
		"aoColumnDefs"  : [
			{"bSortable": true, "aTargets": [0, 1]}
		],
		"oLanguage"     : {
			"sZeroRecords": "There are no more members available."
		},
		"fnDrawCallback": function () {
			$('.dataTables_filter').hide();
		}
	});

	$("#search_all").keyup(function () {
		// Filter on the column (the index) of this element
		table.fnFilterAll(this.value);
	});

	$('.mailinggrouplist').DataTable({
		"aoColumnDefs": [
			{"bSortable": true, "aTargets": [0, 1]}
		],
		"oLanguage"   : {
			"sZeroRecords": "There are no mailing groups available."
		}
	});

});

/*Edit Member*/
jQuery(function ($) {
	$(".add_user_to_group").click(function () {

		var r = confirm("Are you sure you want to add user to group?");

		if (r === true) {
			var user_id = $("#user_id").val();
			var group_id = $(this).data("group_id");
			var group_format = $('input[name=email_format_edit_' + group_id + ']:checked').val();

			var data = {
				action      : 'wpmg_add_user_to_current_group',
				group_id    : group_id,
				user_id     : user_id,
				group_format: group_format,
				nextNonce   : PT_Ajax.nextNonce
			};

			$.post(PT_Ajax.ajaxurl, data, function (response) {

				if (response['action'] === 'error') {
					$('#message').html(response['description']);
				} else {
					location.reload(true);
				}

				return true;
			});
		}
	});

	$(".remove_from_group").click(function () {

		var group_id = $(this).data("group_id");
		var user_id = $("#user_id").val();
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

			var data = {
				action   : 'wpmg_leave_group',
				user_id  : user_id,
				group_id : group_id,
				nextNonce: PT_Ajax.nextNonce
			};

			$.post(PT_Ajax.ajaxurl, data, function () {
				location.reload();
				return true;
			});
		});

		$(cancel_button).click(function () {
			$(".remove_from_group").toggle(true);
			$(current_status).show();
			$(confirm_message).hide();
			$(confirm_button).toggle(false);
			$(cancel_button).toggle(false);
		});

	});

	$('.email_format_edit').change(function () {

		var user_id = $("#user_id").val();
		var group_id = $(this).data("group_id");
		var upd_button = $(".update_group_format[data-group_id=" + group_id + "]");
		var current_format = $(".current_format[data-group_id=" + group_id + "]").val();
		var new_group_format = $('input[name=email_format_edit_' + group_id + ']:checked').val();


		if (current_format != new_group_format) {
			$(upd_button).toggle(true);

			$(".update_group_format").click(function () {

				var data = {
					action          : 'wpmg_update_group_format',
					user_id         : user_id,
					group_id        : group_id,
					new_group_format: new_group_format,
					nextNonce       : PT_Ajax.nextNonce
				};

				$.post(PT_Ajax.ajaxurl, data, function () {
					location.reload();
					return true;
				});
			});
		} else {
			$(upd_button).toggle(false);
		}
	});
});

//Add user section through admin
jQuery(function ($) {

	$("#addmember .confirmation_email").change(function () {
		if (this.value == '0') {
			$("#status_1").removeAttr("disabled");
		} else {
			$("#status_1").attr("disabled", "disabled");
			$("#status_0").attr("checked", true);
		}
	});


	$("#check_username").click(function () {
		if (trim($("#username").val()) == "") {
			alert("Please enter username to check.");
			$("#username").focus();
			return false;
		}
		var thisUsername = trim($("#username").val());

		var data = {
			action  : 'wpmg_checkusername',
			page    : 'wpmg_mailinggroup_memberadd',
			username: thisUsername
		};
		$.post(ajaxurl, data, function (response) {

			if (trim(response) == 'yes') {
				alert("Username is available.");
				$("#username").val(thisUsername)
				console.log(ajaxurl);
				return true;
			} else {
				alert("Username is not available, please try again.");
				$("#username").val(thisUsername);
				return true;
			}
		});
	});

});