<?php

class UserManagement {
	//********* PROPERTIES
	// Names of Custom Post Type
	public $group_id = 'Question';
	public $postTypeNamePlural = 'Questions';

	// Meta Box Stuff
	public $metaBoxTitle = 'Answers';
	public $metaBoxTempl = 'template/grpusers.templ.php';

	// Question Id's
	public $answerIds = array( 'quiz-a-1', 'quiz-a-2', 'quiz-a-3', 'quiz-a-4' );


	//********* CONSTRUCTOR
	public function __construct( $type ) {

		switch ( $type ) {
			case 'admin' :
				// Add the Meta Box
				add_action( 'wp_loaded', array( $this, 'displayUserTable' ) );

			// Accept an Ajax Request
//				add_action( 'wp_ajax_save_answer', array( $this, 'saveAnswers' ) );

			// Watch for Post being saved
//				add_action( 'save_post', array( $this, 'savePost' ) );
		}
	}

	//display table
	public function displayUserTable() {


		$gid    = sanitize_text_field( $_REQUEST["gid"] );

		$this->userMngtScripts();


		$args = array(
			'meta_query' => array(
				array(
					'key'     => 'mg_user_group_sub_arr',
					'value'   => '"' . $gid . '"',
					'compare' => 'LIKE'
				)
			)
		);

		$user_query = new WP_User_Query( $args );

		$users_results = $user_query->get_results();

		$all_group_users = array();

		// Check for results
		if ( ! empty( $users_results ) ) {
			// loop trough each author
			foreach ( $users_results as $user ) {
				// get all the user's data
				$all_group_users[] = array('userID'=>$user->ID,'userEmail'=>$user->user_email);
			}
		}

		$args2 = array(
			'meta_query' => array(
				array(
					'key'     => 'mg_user_group_sub_arr',
					'value'   => '"' . $gid . '"',
					'compare' => 'LIKE'
				)
			)
		);

		$user_query2 = new WP_User_Query( $args2 );

		$all_users_results = $user_query2->get_results();

		$all_available_users = array();

		// Check for results
		if ( ! empty( $all_users_results ) ) {
			// loop trough each author
			foreach ( $all_users_results as $user ) {
				// get all the user's data
				$all_available_users[] = array('userID'=>$user->ID,'userEmail'=>$user->user_email);
			}
		}


		// Set data needed in the template
		$viewData = array(
			'group_id'    => $gid,
			'users_in_group_info' => json_encode( $all_group_users ),
			'users_available_info' => json_encode( $all_available_users)
		);

		echo $this->getTemplatePart( $this->metaBoxTempl, $viewData );

	}

	public function getTemplatePart( $filePath, $viewData = null ) {

		( $viewData ) ? extract( $viewData ) : null;

		ob_start();
		include( "$filePath" );
		$template = ob_get_contents();
		ob_end_clean();

		return $template;
	}


	//Scripts for user management
	public function userMngtScripts() {
		wp_register_script( 'backbone_grp_users_js', plugin_dir_url( __FILE__ ) . '../js/backbone_grp_users.js', array( 'backbone' ), null, true );
		wp_enqueue_script( 'backbone_grp_users_js' );
	}


	// Save Answers
	public function saveAnswers() {
		// Get PUT data and decode it
		$model = json_decode( file_get_contents( "php://input" ) );

		// Ensure that this user has the correct permissions
		if ( ! $this->canSaveData( $model->post_id ) ) {
			return;
		}

		// Attempt an insert/update
		$update = add_post_meta( $model->post_id, $model->answer_id, $model->answer, true );
		// or
		$update = update_post_meta( $model->post_id, $model->answer_id, $model->answer );

		// If a save or update was successful, return the model in JSON format
		if ( $update ) {
			echo json_encode( $this->getOneAnswer( $model->post_id, $model->answer_id ) );
		} else {
			echo 0;
		}

		die();
	}


	// Get meta box
	public function getMetaBox( $post ) {
		// Get the current values for the questions
		$json = array();
		foreach ( $this->answerIds as $id ) {
			$json[] = $this->getOneAnswer( $post->ID, $id );
		}

		// Set data needed in the template
		$viewData = array(
			'post'    => $post,
			'answers' => json_encode( $json ),
			'correct' => json_encode( get_post_meta( $post->ID, 'correct_answer' ) )
		);

		echo $this->getTemplatePart( $this->metaBoxTempl, $viewData );
	}


	// Save post
	public function savePost( $post_id ) {

		// Check that the user has correct permissions
		if ( ! $this->canSaveData( $post_id ) ) {
			return;
		}

		// Access the data from the $_POST global and create a new array containing
		// the info needed to make the save
		$fields = array();
		foreach ( $this->answerIds as $id ) {
			$fields[ $id ] = $_POST[ $id ];
		}

		// Loop through the new array and save/update each one
		foreach ( $fields as $id => $field ) {
			add_post_meta( $post_id, $id, $field, true );
			// or
			update_post_meta( $post_id, $id, $field );
		}

		// Save/update the correct answer
		add_post_meta( $post_id, 'correct_answer', $_POST['correct_answer'], true );
		// or
		update_post_meta( $post_id, 'correct_answer', $_POST['correct_answer'] );
	}



	//********* METHOD-HELPERS
	/**
	 * Determine if the current user has the relevant permissions
	 *
	 * @param $post_id
	 *
	 * @return bool
	 */
	private function canSaveData( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return false;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return false;
			}
		}

		return true;
	}



}