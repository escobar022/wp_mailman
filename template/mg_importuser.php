<?php
$WPMG_SETTINGS = get_option("WPMG_SETTINGS");
/* get all variables */
$info = sanitize_text_field($_REQUEST["info"]);
$gid = sanitize_text_field($_REQUEST["gid"]);
$actreq = sanitize_text_field($_REQUEST["act"]);
$id = sanitize_text_field($_REQUEST["id"]);
$delid = sanitize_text_field($_GET["did"]);

/* get all variables */
if(isset($_POST) && isset($_POST['importuserbtn'])) {
	if(count($_POST['selectusers'])>0) {
		foreach($_POST['group_name'] as $key) {
			$arrInsert[$key] = '1';
		}
		foreach($_POST['selectusers'] as $key => $val) {
			$userId = $val;
			update_user_meta( $userId, "Plugin", "groupmailing" );
			update_user_meta( $userId, "mg_user_status", "1" );
			update_user_meta( $userId, "mg_user_group_subscribed", serialize($arrInsert) );
//			$objMem->addUserGroupTaxonomy($table_name_user_taxonomy, $userId, $arrInsert);
		}
		wpmg_redirectTo("wpmg_mailinggroup_importuser&info=suc");
		exit;
	}
} else if(isset($_POST) && isset($_POST['uploaduser'])) {
	/* get the csv file */
    $file = $_FILES['fileupload']['tmp_name'];
	$filetype = wp_check_filetype($_FILES['fileupload']['name']);
    if($filetype['ext'] == 'csv'){
    $handle = fopen($file,"r");    
    /* loop through the csv file and insert into database */
	$originalCount = 0;
	$insertedCount = 0;
    while ($data = fgetcsv($handle,1000,",","'")) {
        if ($data[0]) {
			$name  = wpmg_trimVal($data[0]);
			$email = wpmg_trimVal($data[1]);
			if($name != '' && $email != '') {
				if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
					$username = $email;
					$random_password = wp_generate_password( 12, false );
					$username_e = username_exists( $username );
					$email_e = email_exists($email);
					if (email_exists($email) == false ) {
						$userdata = array( 
							'user_login' => $username,
							'first_name' => $name,
							'user_pass' => $random_password,
							'user_email' => $email,
							'role' => 'subscriber' );
						$user_id = wp_insert_user( $userdata );
						wp_new_user_notification($user_id, $random_password);
						$insertedCount++;
					}
				}
			}
			$originalCount++;
        }
    }
	wpmg_showmessages("updated", sprintf( __( "%1s out of %2s users have been imported successfully.", 'mailing-group-module' ), $insertedCount, $originalCount ));
    }else{
	wpmg_showmessages("error", __( "Please upload correct file type.", 'mailing-group-module'));
	}
} else if(isset($_POST) && isset($_POST['uploaduservcf'])) {
	/*upload the vcf file*/
	if(!empty($_FILES["fileuploadvcf"]["name"])){
	$saveFilepath = dirname(__FILE__) . '/temp/';
	$completeFilepath = $saveFilepath . $_FILES["fileuploadvcf"]["name"];
    $tempfile = $_FILES['fileuploadvcf']['tmp_name'];
	
	if (file_exists($completeFilepath)) {
		unlink($completeFilepath);
	}
	move_uploaded_file($tempfile, $completeFilepath);
	
	/*loop through the vcf file and insert into database*/
	if(file_exists($completeFilepath)) {
		$lines = file($completeFilepath);
		if (!$lines) {
			wpmg_showmessages("error", __( "File cannot be read properly, Please try again..", 'mailing-group-module') );
		} else {
			 $cards = wpmg_parse_vcards($lines);
			 if(count($cards)>0) {
			 	$originalCount = 0;
				$insertedCount = 0;
				foreach($cards as $compactName => $vcardArray) {
					foreach($vcardArray as $dataType => $dataArray) {
						/*echo "<pre>".print_r($dataArray);*/
						if($dataArray['FN'][0]->value!='') {
							$name = $dataArray['FN'][0]->value;
						} else {
							$name = $dataArray['N'][0]->value;
						}
						
						foreach($dataArray['EMAIL'] as $loopemail) {
							if($loopemail->value!='' && filter_var($loopemail->value, FILTER_VALIDATE_EMAIL)) {
								$email = $loopemail->value;
							}
						}
						$name  = wpmg_trimVal($data[0]);
						$email = wpmg_trimVal($data[1]);
						if($name != '' && $email != '') {
							if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
								$username = $email;
								$random_password = wp_generate_password( 12, false );
								$username_e = username_exists( $username );
								$email_e = email_exists($email);
								if (email_exists($email) == false ) {
									$userdata = array( 
										'user_login' => $username,
										'first_name' => $name,
										'user_pass' => $random_password,
										'user_email' => $email,
										'role' => 'subscriber' );
									$user_id = wp_insert_user( $userdata );
									wp_new_user_notification($user_id, $random_password);
									$insertedCount++;
								}
							}
						}
						$originalCount++;
					}
				}
			 } else {
			 	wpmg_showmessages("error", __( "No records found in the file, Please try again..", 'mailing-group-module') );
			 }
		}
	} else {
		wpmg_showmessages("error", __( "File cannot be uploaded correctly, Please try again..", 'mailing-group-module') );
	}	
	wpmg_showmessages("updated", sprintf( __( "%1s out of %2s users have been imported successfully.", 'mailing-group-module' ), $insertedCount, $originalCount ));
    }else{
	wpmg_showmessages("error", sprintf( __( "No file selected, Please try again..", 'mailing-group-module' ), $insertedCount, $originalCount ));
	}
}
if($info=="suc") {
	wpmg_showmessages("updated", __( "Member(s) have been successfully added to selected groups.", 'mailing-group-module' ));
}
$websiteurl = $WPMG_SETTINGS["MG_WEBSITE_URL"];


$args  = array(
    'post_type'   => 'mg_groups',
    'post_status' => 'publish',
    'perm'        => 'readable',
);
$query = new WP_Query( $args );

$result_groups = $query->get_posts();

$result = get_users(array("mg_user_group_subscribed",""));
$totcount = count($result);
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#toplevel_page_mailinggroup_intro").removeClass('wp-not-current-submenu');
		jQuery("#toplevel_page_mailinggroup_intro").addClass('wp-has-current-submenu');
		jQuery(".toplevel_page_mailinggroup_intro").removeClass('wp-not-current-submenu');
		jQuery(".toplevel_page_mailinggroup_intro").addClass('wp-has-current-submenu');
		jQuery("#toplevel_page_mailinggroup_intro ul :nth-child(4)").addClass("current");
	});
</script>
<form name="importuserform1" id="importuserform1" action="" method="post">
<div class="wrap">
	<h2 class="nav-tab-wrapper">
        <a href="admin.php?page=wpmg_mailinggroup_requestmanagerlist" title="<?php _e("Subscription Request Manager", 'mailing-group-module'); ?>" class="nav-tab"><?php _e("Subscription Request Manager", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_requestmanageradd&act=add" class="nav-tab" title="<?php _e("Add New Subscriber", 'mailing-group-module'); ?>"><?php _e("Add New Subscriber", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_importuser" class="nav-tab nav-tab-active" title="<?php _e("Import Users", 'mailing-group-module'); ?>"><?php _e("Import Users", 'mailing-group-module'); ?></a>
    </h2>
    <div>&nbsp;</div>
    <div class="outer_group_div">
        <div class="check_div_fir">
            <h3><?php _e("Import Users from WordPress", 'mailing-group-module'); ?></h3>
        </div>
    </div>
    <p class="pimportcsv"><?php echo _e( 'Any users you import to a mailing group below will have their subscription activated immediately, without any opt-in confirmation sent to their email address. Please only import users as subscribers here if you have their permission already.', 'mailing-group-module'); ?></p>
	 <table class="wp-list-table widefat fixed" id="importuser">
		<thead>
			<tr role="row" class="topRow">
				<th width="8%" class="sort topRow_messagelist">&nbsp;</th>
                <th><?php _e("Name", 'mailing-group-module'); ?></th>
                <th><?php _e("Email Address", 'mailing-group-module'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
		if ($totcount>0) {
			$cntr = 0;
			foreach ($result as $row) {
				$id = $row->ID;
				$group_subscribed = get_user_meta($id, "mg_user_group_subscribed", true);
				$unSeriGroup = unserialize($group_subscribed);

				if(is_array($unSeriGroup)>0) {
					$groupCount = count($unSeriGroup);
				} else {
					$groupCount = 0;
				}
				
				$user_login = $row->user_login;
				$user_email = $row->user_email;
				$display_name = $row->first_name;
				if(!$groupCount) {
	?>
				<tr>
					<td>
                    <input type="checkbox" id="selector" name="selectusers[]" value="<?php echo $id; ?>" id="" /></td>
					<td><?php echo $display_name; ?></td>
					<td><?php echo $user_email; ?></td>
				</tr>
	<?php
				$cntr++;
				}
    		}
		} 
		if($cntr=='0') { ?>
			<tr>
				<td colspan="3" align="center"><?php _e("There are currently no WordPress users available for import.", 'mailing-group-module'); ?></td>
			<tr>
		<?php } ?>
	</tbody>
	</table>
    <?php
	if ($cntr>0) {
	?>
        	<div class="outer_group_div">
            	<div class="check_div_fir">
                    <h3><?php _e("Import Selected Users into:", 'mailing-group-module'); ?></h3>
                </div>
            </div>
            <div class="outer_group_div">
                <div class="check_div_fir"></div>
                <div class="check_div_imp">
                <?php foreach($result_groups as $group) { ?>
                    <p class="inner_check_imp"><input type="checkbox" name="group_name[]" id="selectorgroup" value="<?php echo $group->ID; ?>" />&nbsp;<?php echo $group->post_title; ?></p>
                <?php } ?>
                </div>
            </div>
            <p class="submit clear">
                <input type="submit" value="<?php _e("Import", 'mailing-group-module'); ?>" class="button" id="importuserbtn" name="importuserbtn"/>
            </p>
       </form>
       <form name="importuserform2" id="importuserform2" action="" method="post" enctype="multipart/form-data">
            <div class="outer_group_div">
            	<div class="check_div_fir">
                    <h3> <?php _e("Import from CSV file", 'mailing-group-module'); ?></h3>
                </div>
            </div>
            <div class="clear"></div>
            <div class="form-wrap">
                <div class="form-field">
                    <label for="tag-name"><?php _e("Browse CSV file", 'mailing-group-module'); ?> : </label>
                    <input type="file" name="fileupload" id="fileupload" />
                </div>
                <p class="submit clear">
                    <input type="submit" value="<?php _e("Submit", 'mailing-group-module'); ?>" class="button" id="uploaduser" name="uploaduser"/>
                </p>
                <p class="clear"><?php _e("NB: The CSV file should be formatted as follows with no extra data, and each entry on a separate line:<br />Full Name, email@address.com", 'mailing-group-module'); ?></p>
            </div>
        </form>
        <hr class="hrabove" />
       <form name="importuserform3" id="importuserform3" action="" method="post" enctype="multipart/form-data">
            <div class="outer_group_div">
            	<div class="check_div_fir">
                   <h3> <?php _e("Import from VCF file (v3.0, v4.0)", 'mailing-group-module'); ?></h3>
                </div>
            </div>
            <div class="clear"></div>
            <div class="form-wrap">
                <div class="form-field">
                    <label for="tag-name"><?php _e("Browse VCF file", 'mailing-group-module'); ?> : </label>
                    <input type="file" name="fileuploadvcf" id="fileuploadvcf" />
                </div>
                <p class="submit clear">
                    <input type="submit" value="<?php _e("Submit", 'mailing-group-module'); ?>" class="button" id="uploaduservcf" name="uploaduservcf"/>
                </p>
                <p class="clear"><?php _e("NB: Only the name, full name, and email will be imported from the VCF file - please ensure your file contains them to avoid errors. Here is their typical format:", 'mailing-group-module'); ?><br /><code class="codemail">BEGIN:VCARD<br />
N:Smith;Jim;Alvin;Mr.<br />
FN:Jim A. Smith<br />
EMAIL:test@virage.com<br />
END:VCARD<br /><br />
BEGIN:VCARD<br />
N:Barnes;Julie<br />
FN:Julie Barnes<br />
EMAIL:jbarnes@virage.com<br />
END:VCARD</code></p>
            </div>
        </form>		
	<?php
	} else {
	?>
    	</form>
    <?php
	}
	?>
</div>
<?php
if ($cntr) {
?>
<script type="text/javascript">
	/* <![CDATA[ */
	jQuery(document).ready(function() {
		/* Build the DataTable with third column using our custom sort functions */
		jQuery('#importuser').dataTable( {
			"aoColumnDefs": [ 
			  { "bSortable": false, "aTargets": [ 0,1,2 ] },
			],
			"oLanguage": {
			  "sZeroRecords": "<?php _e("There are no more members available to import.", 'mailing-group-module'); ?>"
			},
			"fnDrawCallback":function(){
				if('<?php echo $cntr ?>' <= 5){
					document.getElementById('importuser_paginate').style.display = "none";
				} else {
					document.getElementById('importuser_paginate').style.display = "block";
				}
			}
		} );
	} );
	/* ]]> */
</script>
<?php } ?>