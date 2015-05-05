<?php
/* get all variables */
$actreq  = wpmg_trimVal($_REQUEST["act"]);
$info    = wpmg_trimVal($_REQUEST["info"]);
$delid   = wpmg_trimVal($_GET["did"]);
$id      = wpmg_trimVal($_GET["uid"]);
$gid     = wpmg_trimVal($_GET["gid"]);
/* get all variables */
if(isset($_POST) && $_POST['deletemessagebtn']!='') {
	if(count($_POST['deletemsg'])>0) {
		foreach($_POST['deletemsg'] as $key => $delid) {
			$wpdb->query("delete from ".$table_name_sent_emails." where id=".$delid);
		}
	}
	wpmg_redirectTo("wpmg_mailinggroup_adminarchive&gid=".$gid."&info=upd");
	exit;
} else if($actreq=='del' && $delid!='') {
	$wpdb->query("delete from ".$table_name_sent_emails." where id=".$delid);
	wpmg_redirectTo("wpmg_mailinggroup_adminarchive&gid=".$gid."&info=upd");
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
	wpmg_redirectTo("wpmg_mailinggroup_list");
	exit;
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
	jQuery(document).ready(function(){
		/* Build the DataTable with third column using our custom sort functions */
		jQuery('#archivelist').DataTable( {
		    "searchHighlight": true,
			"aoColumns": [ 
				null,
				null,
				null,
				{ "bVisible": false },				
				{ "bVisible": false },
				null,
				null
			],
			"aoColumnDefs": [ 
			  { "bSortable": true,
			  	"aTargets": [ 0,1,2,3,4,5,6,7 ],
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
		
		//jQuery('body').on('click', '.quick_view', function() {
		jQuery('body').on('click', '.quick_view', function() { jQuery('#archivelist > tbody  > tr').each(function() { jQuery(this).closest('tr').css("background-color","#F9F9F9"); }); jQuery(this).closest('tr').css("background-color","#FEA03D"); var thisId = this.name; var data = { action: 'wpmg_viewmessage', page: 'wpmg_mailinggroup_viewmessage',id:thisId}; jQuery.post(ajaxurl, data, function(response) {/* alert(response); */jQuery("#ajaxContent").html(response); jQuery("#ajaxstart").focus(); });});

		//jQuery('.quick_view').click(function(){ jQuery('#archivelist > tbody  > tr').each(function() { jQuery(this).closest('tr').css("background-color","#F9F9F9"); }); jQuery(this).closest('tr').css("background-color","#FEA03D"); var thisId = this.name; var data = { action: 'wpmg_viewmessage', page: 'mailinggroup_viewmessage',id:thisId}; jQuery.post(ajaxurl, data, function(response) {/* alert(response); */jQuery("#ajaxContent").html(response); jQuery("#ajaxstart").focus(); });});
		
		jQuery("#toplevel_page_mailinggroup_intro").removeClass('wp-not-current-submenu');
		jQuery("#toplevel_page_mailinggroup_intro").addClass('wp-has-current-submenu');
		jQuery("#toplevel_page_mailinggroup_intro ul :nth-child(3)").addClass("current");
	});
	/* ]]> */
</script>
<?php } ?>
<?php
$resultgp = $objMem->selectRows($table_name_group, "",  " where id='".$gid."'");
if (count($resultgp)>0) {
	foreach ($resultgp as $rowgp) {
		$groupName = $rowgp->title;
	}
}
?>
<form name="archivemessageform" id="archivemessageform" action="" method="post">
    <div class="wrap">
        <h2><?php _e("Message Archive", 'mailing-group-module'); ?> <?php echo ($groupName!=''?"($groupName) <a class='backlink' href='admin.php?page=wpmg_mailinggroup_list'>". _e("Back", 'mailing-group-module')."</a>":"") ?></h2>
        <?php
		if (count($result)>0) {
		?>
            <p class="submit clear">
                <input type="submit" value="<?php _e("Delete Selected", 'mailing-group-module'); ?>" class="button" id="deletemessagebtn" name="deletemessagebtn"/>
            </p>
        <?php
		}
		?>
		<table class="wp-list-table widefat fixed" id="archivelist">
            <thead>
                <tr role="row" class="topRow">
                    <th width="5%"></th>
                    <th><?php _e("Sender", 'mailing-group-module'); ?></th>
                    <th><?php _e("Name", 'mailing-group-module'); ?></th>
                    <th><?php _e("Subject", 'mailing-group-module'); ?></th>
					<th><?php _e("Body", 'mailing-group-module'); ?></th>
                    <th><?php _e("Content", 'mailing-group-module'); ?></th>
                    <th><?php _e("Date", 'mailing-group-module'); ?></th>
                    <th width="8%"><?php _e("Actions", 'mailing-group-module'); ?></th>
                </tr>
            </thead>
            <tbody>
    		<?php
			if ($totcount>0) {
				foreach ($result as $row) {
				
					$id      = $row->id;
					$emailId = $row->email_id;
					$date    = $row->sent_date;
					
					$resultMails = $objMem->selectRows($table_name_parsed_emails, "",  " where id = '".$emailId."'");
					$sender      = $resultMails[0]->email_from;
					$name        = $resultMails[0]->email_from_name;
					$subject     = $resultMails[0]->email_subject;
					$content     = $resultMails[0]->email_content;
				?>
				<tr>
					<td><input type="checkbox" name="deletemsg[]" id="selector" value="<?php echo $id;?>" /></td>
					<td><?php echo $sender; ?></td>
					<td><?php echo $name; ?></td>
					<td><a href="#ajaxstart" title="<?php _e("View", 'mailing-group-module'); ?>" class="quick_view" name="<?php echo $emailId;?>"><?php echo $subject; ?></a></td>
                    <td><?php echo $content; ?></td>
					<td><?php echo wp_trim_words( $content, 20, '<a href="#ajaxstart" title="Read More" class="quick_view" name="'.$emailId.'">...Read More</a>' ); ?></td>
					<td><?php echo $date; ?></td>
					<td width="8%" class="last"><a class="delete_record" title="<?php _e("Delete", 'mailing-group-module'); ?>" href="admin.php?page=wpmg_mailinggroup_adminarchive&act=del&did=<?php echo $id;?>&gid=<?php echo $gid;?>" onclick="return confirm('<?php _e("Are you sure you want to delete this message?", 'mailing-group-module'); ?>');"></a>|<a href="#ajaxstart" class="view_record quick_view" title="<?php _e("View", 'mailing-group-module'); ?>" name="<?php echo $emailId;?>"></a></td>
				</tr>
				<?php
				}
			} else { ?>
				<tr>
					<td colspan="6" align="center"><?php _e("There are currently no messages in the archive.", 'mailing-group-module'); ?></td>
				<tr>
			<?php } ?>
        	</tbody>
        </table>
        <?php
		if (count($result)>0) {
		?>
            <p class="submit clear">
                <input type="submit" value="<?php _e("Delete Selected", 'mailing-group-module'); ?>" class="button" id="deletemessagebtn" name="deletemessagebtn"/>
            </p>
        <?php
		}
		?>
        <a href="#" id="ajaxstart" name="ajaxstart"></a>
        <div id="ajaxContent" class="ajaxContent"></div>
    </div>
</form>