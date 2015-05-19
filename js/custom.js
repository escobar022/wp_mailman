function trim(s) {
	while ((s.substring(0, 1) == ' ') || (s.substring(0, 1) == '\n') || (s.substring(0, 1) == '\r')) {
		s = s.substring(1, s.length);
	}
	while ((s.substring(s.length - 1, s.length) == ' ') || (s.substring(s.length - 1, s.length) == '\n') || (s.substring(s.length - 1, s.length) == '\r')) {
		s = s.substring(0, s.length - 1);
	}
	return s;
}

function checkblank(a, b) {
	if (a) {
		if (trim(a.value) == "") {
			alert("Please enter value for " + b);
			a.focus();
			return false;
		}
		return true;
	}
	else
		return false;
}

function checknumber(a) {
	if (a) {
		e = a;
		ok = "1234567890";
		for (i = 0; i < e.length; i++) {
			if (ok.indexOf(e.charAt(i)) < 0) {
				//alert('Wrong value for '+b+'. Only number ,-+ or () allowed.');
				return (false);
			}
		}
		return true;
	}
	else
		return false;
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
	if (checked == 0) {
		return false;
	}
	else {
		return true;
	}
}


jQuery(document).ready(function () {
	jQuery('#addgroup').submit(function () {
		if (trim(jQuery("#title").val()) == "") {
			alert("Please enter group name.");
			return false;
		}
		return true;
	});
	jQuery('#addmessage').submit(function () {
		if (trim(jQuery("#title").val()) == "") {
			alert("Please enter message title.");
			jQuery("#title").focus();
			return false;
		}
		if (trim(jQuery("#description").val()) == "") {
			alert("Please enter message description.");
			jQuery("#description").focus();
			return false;
		}
		if (jQuery("#status option:selected").val() == "") {
			alert("Please select message status.");
			jQuery("#status").focus();
			return false;
		}
		return true;
	});
	jQuery("#mail_group").change(function () {
		var url = "admin.php?page=mg_emailgroupsetting&id=" + this.value;
		window.location = url;
	});
	jQuery('#addmailingrequest').submit(function () {
		if (trim(jQuery("#name").val()) == "") {
			alert("Please enter name.");
			jQuery("#name").focus();
			return false;
		}
		if (trim(jQuery("#email").val()) == "") {
			alert("Please enter email address.");
			jQuery("#email").focus();
			return false;
		}
		if (!checkemail(jQuery("#email").val())) {
			alert("Please enter valid email address.");
			jQuery("#email").focus();
			return false;
		}
		if (jQuery("#status option:selected").val() == "") {
			alert("Please select subscription status.");
			jQuery("#status").focus();
			return false;
		}
		if (!isCheckedById("selector")) {
			alert("Please select at least one group.");
			return false;
		}
		return true;
	});
	jQuery('#sendmessage').submit(function () {
		if (jQuery("#selectmessage option:selected").val() == "") {
			alert("Please select message to load or select new message.");
			jQuery("#selectmessage").focus();
			return false;
		}
		if (trim(jQuery("#title").val()) == "") {
			alert("Please enter title.");
			jQuery("#title").focus();
			return false;
		}
		if (trim(jQuery("#description").val()) == "") {
			alert("Please enter description.");
			jQuery("#description").focus();
			return false;
		}
		return true;
	});
	jQuery('#addmember').submit(function () {
		if (trim(jQuery("#name").val()) == "") {
			alert("Please enter your name.");
			jQuery("#name").focus();
			return false;
		}
		if (jQuery('#auto_generate').is(':checked')) {
		} else {
			if (trim(jQuery("#username").val()) == "") {
				alert("Please enter username.");
				jQuery("#username").focus();
				return false;
			}
		}
		if (trim(jQuery("#email").val()) == "") {
			alert("Please enter email.");
			jQuery("#email").focus();
			return false;
		}
		if (!checkemail(jQuery("#email").val())) {
			alert("Please enter valid email address.");
			jQuery("#email").focus();
			return false;
		}
		if (!isCheckedById("selector")) {
			alert("Please select at least one group.");
			return false;
			/*
			 if(confirm("This member will now not be subscribed to ANY Mailing Groups. Do you wish to delete their WordPress user account and settings?")) {
			 jQuery("#delete_wp").val('1');
			 return true;
			 } else {
			 jQuery("#delete_wp").val('0');
			 return true;
			 }
			 */
		}
		return true;
	});
	jQuery("#importuserform1").submit(function () {
		if (!isCheckedById("selector")) {
			alert("Please select at least one user.");
			return false;
		}
		if (!isCheckedById("selectorgroup")) {
			alert("Please select at least group.");
			return false;
		}
		return true;
	});
	jQuery("#importuserform2").submit(function () {
		var ext = jQuery('#fileupload').val().split('.').pop().toLowerCase();
		if (jQuery.inArray(ext, ['csv']) == -1) {
			alert("Invalid file format, please browse a csv file.");
			return false;
		}
	});
	jQuery("#mailingrequestform").submit(function () {
		if (trim(jQuery("#fname").val()) == "") {
			alert("Please enter name.");
			jQuery("#fname").focus();
			return false;
		}
		if (trim(jQuery("#email").val()) == "") {
			alert("Please enter email address.");
			jQuery("#email").focus();
			return false;
		}
		if (!checkemail(jQuery("#email").val())) {
			alert("Please enter valid email address.");
			jQuery("#email").focus();
			return false;
		}
		if (!isCheckedById("selector")) {
			alert("Please select at least one group.");
			return false;
		}
		if (trim(jQuery("#c_captcha").val()) == "") {
			alert("Please enter captcha code.");
			jQuery("#c_captcha").focus();
			return false;
		}
		return true;
	});
	jQuery("#archivemessageform").submit(function () {
		if (!isCheckedById("selector")) {
			alert("Please select at least one message(s) to delete.");
			return false;
		} else {
			return confirm('Are you sure you want to delete these message(s)?');
		}
		return true;
	});
	jQuery("#selectgrp").change(function () {
		//if(jQuery("#selectgrp option:selected").val()!="") {
		var url = "admin.php?page=wpmg_mailinggroup_memberarchive&gid=" + this.value;
		window.location = url;
		//}
	});
	jQuery("#mgintropage").submit(function () {
		if (isCheckedById("selector")) {
			if (trim(jQuery("#subscription_email").val()) == "") {
				alert("Please enter subscription alert email address.");
				jQuery("#subscription_email").focus();
				return false;
			} else {
				if (!checkemail(jQuery("#subscription_email").val())) {
					alert("Please enter valid email address.");
					jQuery("#subscription_email").focus();
					return false;
				}
			}
		}
		if (isCheckedById("selector2")) {
			if (trim(jQuery("#bounce_alert_email").val()) == "") {
				alert("Please enter bounce alert email address.");
				jQuery("#bounce_alert_email").focus();
				return false;
			} else {
				if (!checkemail(jQuery("#bounce_alert_email").val())) {
					alert("Please enter valid email address.");
					jQuery("#bounce_alert_email").focus();
					return false;
				}
			}
		}
		return true;
	});
	jQuery("#styleform").submit(function () {
		if (trim(jQuery("#user_style").val()) == "") {
			alert("Please enter css styles to submit.");
			jQuery("#user_style").focus();
			return false;
		}
		return true;
	});
	jQuery("#approvedeleterequest").submit(function () {
		if (!isCheckedById("selector")) {
			alert("Please select atleast one request to continue.");
			return false;
		} else {
			if (jQuery("#massaction").val() == '' && jQuery("#massaction2").val() == '') {
				alert("Please select atleast one action from the dropdown.");
				return false;
			} else if (jQuery("#massaction").val() == '2' && jQuery("#massaction2").val() == '2') {
				return confirm('Are you sure you want to reject these request(s)?');
			}
		}
		return true;
	});

	jQuery("#selectorall").change(function () {
		if (jQuery(this).is(":checked")) {
			jQuery(".selectorsubscription").each(function () {
				jQuery(this).attr("checked", true);
			});
		} else {
			jQuery(".selectorsubscription").each(function () {
				jQuery(this).attr("checked", false);
			});
		}
	});
	jQuery("#addmember .confirmation_email").change(function () {
		if (this.value == '0') {
			jQuery("#status_1").removeAttr("disabled");
		} else {
			jQuery("#status_1").attr("disabled", "disabled");
			jQuery("#status_0").attr("checked", true);
		}
	});
});

//Mailing Group Admin Section
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

//Mailing Add User Section
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
	
	
	//Request Manager

	$(".approve_request").click(function () {

		var request_id = $(this).data("request_id");
		var user_id = $(this).data("user_id");

		var data = {
			action      : 'wpmg_approve_group_request',
			request_id     : request_id,
			user_id       : user_id,
			nextNonce   : PT_Ajax.nextNonce
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
			action      : 'wpmg_deny_group_request',
			request_id     : request_id,
			user_id       : user_id,
			nextNonce   : PT_Ajax.nextNonce
		};

		$.post(PT_Ajax.ajaxurl, data, function () {
            location.reload();
			return true;
		});

	});

	
	
	
});

