<?php

/* get all variables */
$actreq  = wpmg_trimVal($_REQUEST["act"]);
$info    = wpmg_trimVal($_REQUEST["info"]);
$delid   = wpmg_trimVal($_GET["did"]);
$id      = get_current_user_id();
$gid     = wpmg_trimVal($_GET["gid"]);
/* get all variables */
if(isset($_POST) && $_POST['deletemessagebtn']!='') {
	if(count($_POST['deletemsg'])>0) {
		foreach($_POST['deletemsg'] as $key => $delid) {
			$wpdb->query("delete from ".$table_name_sent_emails." where user_id = '".$id."' and id=".$delid);
		}
	}
	wpmg_redirectTo("wpmg_mailinggroup_archivemessage&uid=".$id."&info=upd");
	exit;
} else if($actreq=='del' && $delid!='') {
	$wpdb->query("delete from ".$table_name_sent_emails." where user_id = '".$id."' and id=".$delid);
	wpmg_redirectTo("wpmg_mailinggroup_archivemessage&uid=".$id."&info=upd");
	exit;
}
$group_str = "";
if($gid!='' && $id=='') {
	$group_str .= " where group_id='".$gid."' and status!='2'";
} else if($id!='' && $gid=='') {
	$group_str .= " where user_id='".$id."' and status!='2'";
} else if($id!='' && $gid!='') {
	$group_str .= " where group_id='".$gid."' and user_id='".$id."' and status!='2'";
} else {
	$group_str .= " where status!='2'";
}
if($info=="upd") {
	wpmg_showmessages("updated", __("Message(s) has been deleted successfully.", 'mailing-group-module'));
}
$result = $objMem->selectRows($table_name_sent_emails, "",  $group_str);
$totcount = count($result);
if ($totcount>0) {
?>
<script type="text/javascript">
	/* <![CDATA[ */
	jQuery(document).ready(function() {
		/* Build the DataTable with third column using our custom sort functions */
		jQuery('#archivelist').dataTable( {
			"aoColumns": [ 
				null,
				null,
				null,
				null,
				{ "bVisible":    false },
				null,
				null
			],
			"aoColumnDefs": [ 
			  { "bSortable": false,
			  	"aTargets": [ 0,1,2,3,4,5,6 ]
			  }
			],
			"fnDrawCallback":function(){
				if('<?php echo $totcount; ?>'<=5){
					document.getElementById('archivelist_paginate').style.display = "none";
				} else {
					document.getElementById('archivelist_paginate').style.display = "block";
				}
			}
		} );
		jQuery('body').on('click', '.quick_view', function() { var thisId = this.name; var data = { action: 'wpmg_viewmessage', page: 'wpmg_mailinggroup_viewmessage',id:thisId}; jQuery.post(ajaxurl, data, function(response) {jQuery("#ajaxContent").html(response); jQuery("#ajaxstart").focus(); });})
	} );
	/* ]]> */
</script>
<?php
}
$resultuserSubscribedGroup = $objMem->selectRowsCompleteQuery("Select ut.*, g.title from $table_name_user_taxonomy ut inner join $table_name_group g on g.id = ut.group_id where user_id = '".$id."' order by g.id");
?>
    <div class="wrap">
    	<form name="archivemessageformgroup" id="archivemessageformgroup" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="get">
            <h2><?php _e("Message Archive", 'mailing-group-module'); ?></h2>
            <p class="submit clear">
                <?php _e("Select Group to sort", 'mailing-group-module'); ?> : 
                <select name="selectgrp" id="selectgrp">
                    <option value=""><?php _e("All Group(s)", 'mailing-group-module'); ?></option>
                    <?php
                    foreach($resultuserSubscribedGroup as $gkey => $gvalue) {
                    ?>
                        <option value="<?php echo $gvalue->group_id ?>" <?php echo ($gid==$gvalue->group_id?"selected":""); ?>><?php echo $gvalue->title ?></option>
                    <?php
                    }
                    ?>
                </select>
            </p>
        </form>
    <form name="archivemessageform" id="archivemessageform" action="" method="post">
         <table class="wp-list-table widefat fixed" id="archivelist">
            <thead>
                <tr role="row" class="topRow">
                    <th width="5%"></th>
                    <th><?php _e("Sender", 'mailing-group-module'); ?></th>
                    <th><?php _e("Name", 'mailing-group-module'); ?></th>
                    <th><?php _e("Subject", 'mailing-group-module'); ?></th>
                    <th><?php _e("Content", 'mailing-group-module'); ?></th>
                    <th><?php _e("Date", 'mailing-group-module'); ?></th>
                    <th><?php _e("Actions", 'mailing-group-module'); ?></th>
                </tr>
            </thead>
            <tbody>
    		<?php
			
			if ($totcount>0) {
				foreach ($result as $row) {
					$id = $row->id;
					$emailId = $row->email_id;
					$date = $row->sent_date;
					
					$resultMails = $objMem->selectRows($table_name_parsed_emails, "",  " where id = '".$emailId."' and type='email'");
					$emailId = $resultMails[0]->id;
					$sender = $resultMails[0]->email_from;
					$name = $resultMails[0]->email_from_name;
					$subject = $resultMails[0]->email_subject;
					$content = $resultMails[0]->email_content;
				?>
				<tr>
					<td><input type="checkbox" name="deletemsg[]" id="selector" value="<?php echo $id;?>" /></td>
					<td><?php echo $sender; ?></td>
					<td><?php echo $name; ?></td>
					<td><a href="#ajaxstart" class="quick_view" name="<?php echo $emailId;?>" title="<?php _e("View", 'mailing-group-module'); ?>"><?php echo $subject; ?></a></td>
                    <td><?php echo $content; ?></td>
					<td><?php echo $date; ?></td>
					<td width="8%" class="last"><a class="delete_record" title="<?php _e("Delete", 'mailing-group-module'); ?>" href="admin.php?page=wpmg_mailinggroup_adminarchive&act=del&did=<?php echo $id;?>" onclick="return confirm('<?php _e("Are you sure you want to delete this message?", 'mailing-group-module'); ?>');"></a>|<a href="#ajaxstart" name="<?php echo $emailId;?>" class="view_record quick_view" title="<?php _e("View", 'mailing-group-module'); ?>"></a></td>
				</tr>
				<?php
				}
			} else { ?>
				<tr>
					<td colspan="6" align="center"><?php _e("No Message Found!", 'mailing-group-module'); ?></td>
				<tr>
			<?php } ?>
        	</tbody>
        </table>
        <p class="submit clear">
            <input type="submit" value="Delete Selected" class="button" id="deletemessagebtn" name="deletemessagebtn"/>
        </p>
        <a href="#" id="ajaxstart" name="ajaxstart"></a>
        <div id="ajaxContent" class="ajaxContent"></div>
    </div>
</form>