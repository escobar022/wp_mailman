<div class="wrap" id="mail_listing">

	<h2><?php _e( "Mailing Group Manager", 'mailing-group-module' ); ?>
		<a class="button add-new-h2" href="/wp-admin/edit.php?post_type=mg_groups"><?php _e( "Add New Mailing Group", 'mailing-group-module' ); ?></a>
	</h2>

	<p><?php _e( "Mailing Groups can be added and configured below. You can set up and configure as many as you wish in this plugin version. Just click 'Add New Mailing Group' to get started.", 'mailing-group-module' ); ?></p>

	<table class="wp-list-table widefat fixed mailinggrouplist">
		<thead>
		<tr role="row" class="topRow">
			<th class="sort topRow_messagelist"><a href="#"><?php _e( "Group Name", 'mailing-group-module' ); ?></a></th>
			<th><a href="#"><?php _e( "Email Address", 'mailing-group-module' ); ?></a></th>
			<th><?php _e( "Status", 'mailing-group-module' ); ?></th>
			<th width="22%"><?php _e( "Actions", 'mailing-group-module' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php

		$args  = array(
			'post_type'      => 'mg_groups',
			'post_status'    => array( 'publish', 'private' ),
			'perm'           => 'readable',
			'posts_per_page' => - 1
		);
		$query = new WP_Query( $args );

		$groups = $query->get_posts();

		$groups_count = count( $groups );

		if ( $groups_count > 0 ) {

			foreach ( $groups as $row ) {
				$id              = $row->ID;
				$title           = get_the_title( $id );
				$email           = get_post_meta( $id, 'mg_group_email', true );
				$status          = get_post_meta( $id, 'mg_group_status', true );
				$archive_message = get_post_meta( $id, 'mg_group_archive', true );
				?>
				<tr>
					<td><?php echo $title; ?></td>
					<td><?php echo( $email != '' ? $email : 'Please Enter Email' ); ?></td>
					<td><?php echo( $status == '1' ? 'Inactive' : 'Active' ); ?></td>
					<td class="last">
						<a class="add_subscriber" title="<?php _e( "View/Add Subscriber", 'mailing-group-module' ); ?>" href="admin.php?page=wpmg_mailinggroup_memberlist&gid=<?php echo $id; ?>"></a>
						<a class="archive_messages" href="<?php echo get_the_permalink( $id ); ?>" title="<?php _e( "View Archived Messages", 'mailing-group-module' ); ?>"></a>
						<a class="edit_record" title="<?php _e( "Edit", 'mailing-group-module' ); ?>" name="<?php echo $id; ?>" href="/wp-admin/post.php?post=<?php echo $id; ?>&action=edit"></a>
					</td>
				</tr>
			<?php }

		} else { ?>
		<tr>
			<td colspan="3" align="center"><?php _e( "Click 'Add New Mailing Group' to get started", 'mailing-group-module' ); ?></td>
		<tr>
			<?php } ?>
		</tbody>
	</table>
</div>