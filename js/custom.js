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
	$('#addmessage').submit(function () {
		if (trim($("#title").val()) == "") {
			alert("Please enter message title.");
			$("#title").focus();
			return false;
		}
		if (trim($("#description").val()) == "") {
			alert("Please enter message description.");
			$("#description").focus();
			return false;
		}
		if ($("#status option:selected").val() == "") {
			alert("Please select message status.");
			$("#status").focus();
			return false;
		}
		return true;
	});
});

//Custom Style Page
jQuery(function ($) {
	$("#styleform").submit(function () {
		if (trim($("#user_style").val()) == "") {
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

//Add user section through admin TODO
jQuery(function ($) {

	$('#auto_generate').click(function () {

		if ($('#auto_generate').is(':checked')) {
			$("#gen_username").hide();
		} else {
			$("#gen_username").show();
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

/*Add Subscribers*/
jQuery(function ($) {
	$('#addmember').submit(function () {
		if (trim($("#name").val()) == "") {
			alert("Please enter your name.");
			$("#name").focus();
			return false;
		}
		if ($('#auto_generate').is(':checked')) {
		} else {
			if (trim($("#username").val()) == "") {
				alert("Please enter username.");
				$("#username").focus();
				return false;
			}
		}
		if (trim($("#email").val()) == "") {
			alert("Please enter email.");
			$("#email").focus();
			return false;
		}
		if (!checkemail($("#email").val())) {
			alert("Please enter valid email address.");
			$("#email").focus();
			return false;
		}
		if (!isCheckedById("selector")) {
			alert("Please select at least one group.");
			return false;
		}
		return true;
	});
});

