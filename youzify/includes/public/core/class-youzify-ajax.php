<?php

class Youzify_Ajax {

	function __construct() {

		// Posts - Ajax Pagination
		add_action( 'wp_ajax_nopriv_youzify_pages_pagination', array( $this, 'posts_pagination' ) );
		add_action( 'wp_ajax_youzify_pages_pagination', array( $this, 'posts_pagination' ) );

		// Courses - Ajax Pagination
		add_action( 'wp_ajax_nopriv_youzify_courses_pagination', array( $this, 'courses_pagination' ) );
		add_action( 'wp_ajax_youzify_courses_pagination', array( $this, 'courses_pagination' ) );

		// Tutor Course - Ajax Pagination
		// add_action( 'wp_ajax_nopriv_youzify_tutor_courses_pagination', array( $this, 'tutor_courses_pagination' ) );
		// add_action( 'wp_ajax_youzify_tutor_courses_pagination', array( $this, 'tutor_courses_pagination' ) );

		// Comments - Ajax Pagination
		add_action( 'wp_ajax_nopriv_youzify_comments_pagination', array( $this, 'comments_pagination' ) );
		add_action( 'wp_ajax_youzify_comments_pagination', array( $this, 'comments_pagination' ) );

		// Handle Account Verification.
		add_action( 'wp_ajax_youzify_handle_account_verification',  array( $this, 'handle_verification' ) );

		// Add Activity.
		add_action( 'wp_ajax_youzify_get_activity_tools',  array( $this, 'get_activity_tools' ) );

		add_action( 'wp_ajax_youzify_unlink_provider_account',  array( $this, 'unlink_instagram_provider_account' ) );

	}

	/**
	 * Unlink Provider Account.
	 */
	function unlink_instagram_provider_account() {

	    // Hook.
	    do_action( 'youzify_before_account_unlink_provider' );

	    // Check Ajax Referer.
	    check_ajax_referer( 'youzify-unlink-provider-account', 'security' );

	    // Get Data.
	    $data = array();

	    // Get User ID.
	    $user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : bp_displayed_user_id();

	    // Get Data.
	    $provider = isset( $_POST['provider'] ) ? sanitize_text_field( $_POST['provider'] ) : null;

	    // Get Access Token ID.
	    $option_id = 'youzify_wg_' . $provider . '_account_token';

	    // Delete Token.
	    $delete_token = delete_user_meta( $user_id, $option_id );

	    // if ( $delete_token ) {

	        // Delete Account infos.
	        delete_user_meta( $user_id, 'youzify_wg_' . $provider . '_account_user_data' );

	        $data['action'] = 'done';
	        $data['msg'] = __( 'User account is unlinked successfully', 'youzify' );

	        do_action( 'youzify_after_unlinking_provider_account', $user_id, $provider );

	    // } else {

	        // $data['error'] = __( "We couldn't unlink the account, please try again!", 'youzify' );

	    // }

	    die( json_encode( $data ) );

	}

	/**
	 * Posts Tools Function
	 */
	function get_activity_tools() {

		do_action( 'youzify_before_get_activity_tools' );

		// Get Activity ID.
		$activity_id = absint( $_POST['activity_id'] );

		// Filter.
		$tools = apply_filters( 'youzify_activity_tools', array(), $activity_id );

		if ( empty( $tools ) ) {
			wp_send_json_error();
		}

		ob_start();

		?>

		<div class="youzify-item-tools youzify-activity-tools" data-activity-id="<?php echo $activity_id; ?>">
			<?php foreach ( $tools as $tool ) : ?>
				<?php $attributes = isset( $tool['attributes'] ) ? $tool['attributes'] : null; ?>
				<div class="youzify-item-tool <?php echo youzify_generate_class( $tool['class'] ); ?>" <?php youzify_get_item_attributes( $attributes ); ?> <?php if ( isset( $tool['action'] ) ) { echo 'data-action="' . $tool['action'] .'"'; } ?>>
					<div class="youzify-tool-icon"><i class="<?php echo $tool['icon'] ?>"></i></div>
					<div class="youzify-tool-name"><?php echo $tool['title']; ?></div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php

		$content = ob_get_clean();

		wp_send_json_success( $content );

		die();

	}

	/**
	 * Posts Tab Pagination.
	 */
	function posts_pagination() {

		// Include Posts File.
        require_once YOUZIFY_CORE . 'tabs/class-youzify-tab-posts.php';

		// Get Profile User ID
	    $query_vars = json_decode( stripslashes( $_POST['query_vars'] ), true );

	    // Pagination Args
		$args = array(
			'order' 		 => 'DESC',
			'post_status'	 => 'publish',
			'paged' 		 => absint( $_POST['youzify_page'] ),
			'base' 		 	 => sanitize_text_field( $_POST['youzify_base'] ),
			'author' 		 => sanitize_text_field( $query_vars['youzify_user'] ),
			'posts_per_page' => youzify_option( 'youzify_profile_posts_per_page', 5 )
		);

		$posts_tab = new Youzify_Posts_Tab();

		// Get Posts Core
		$posts_tab->posts_core( $args );

	    die();

	}
	
	/**
	 * Courses Tab Pagination.
	 */
	function courses_pagination() {

        // Get Course PLugin
        $course_plugin = isset( $_POST['course_plugin'] ) ? $_POST['course_plugin'] : '';

        if ( empty( $course_plugin ) ) {
        	wp_send_json_error();
        }

		// Include Posts File.
        require_once YOUZIFY_CORE . 'tabs/class-youzify-tab-courses.php';

		// Get Profile User ID
	    $query_vars = json_decode( stripslashes( $_POST['query_vars'] ), true );

	    // Get User ID
		$user_id = sanitize_text_field( $query_vars['youzify_user'] );

	    // Pagination Args
		$args = array(
			'order' 		 => 'DESC',
			'post_status'	 => 'publish',
			'paged' 		 => absint( $_POST['youzify_page'] ),
			'base' 		 	 => sanitize_text_field( $_POST['youzify_base'] ),
			'posts_per_page' => youzify_option( 'youzify_profile_courses_per_page', 6 ),
		);

        switch ( $course_plugin ) {
        	
        	case 'lifterlms':
			
				// Get Student.
				$student = llms_get_student( $user_id );

				// Get Courses
		        $courses = $student->get_courses(
					array(
						'limit'  => 1000,
						'status' => 'enrolled',
					)
				);

        		// Get Data.
        		$args['post_type'] = 'course';
        		$args['post__in'] = $courses['found'] > 0 ? $courses['results'] : array( 0 ) ;

        		// Include Courses Class.
		        require_once YOUZIFY_CORE . 'tabs/class-youzify-tab-lifter-courses.php';

				// Get Courses Class
				$courses_tab = new Youzify_Lifter_Courses_Tab();

        		break;

        	case 'tutor':

        		// Get Data.
        		$args['author'] = $user_id;
        		$args['post_status'] = 'completed';
        		$args['post_type'] = 'tutor_enrolled';

				// Include Courses Class.
		        require_once YOUZIFY_CORE . 'tabs/class-youzify-tab-tutor-courses.php';

				// Get Courses Class
				$courses_tab = new Youzify_Tutor_Courses_Tab();

        		break;

        	case 'learndash':

        		// Get Data.
        		$args['post_type'] = 'sfwd-courses';
        		$args['post__in'] = learndash_user_get_enrolled_courses( $user_id, array(), true );
        		
				// Include Courses Class.
        		require_once YOUZIFY_CORE . 'tabs/class-youzify-tab-learndash-courses.php';

				// Get Courses Class
		        $courses_tab = new Youzify_LearnDash_Courses_Tab();

        		break;

        }


		// Get Posts Core
		$courses_tab->courses_core( $args, $user_id );

	    die();

	}
	
	// function tutor_courses_pagination() {

	// 	// Get Profile User ID
	//     $query_vars = json_decode( stripslashes( $_POST['query_vars'] ), true );

    //     $user_id = sanitize_text_field( $query_vars['youzify_user'] );

	// 	// Get Profile User ID
	//     $query_vars = json_decode( stripslashes( $_POST['query_vars'] ), true );

	// 	$args = array(
	// 		'post_type'		 => '',
	// 		'order' 		 => 'DESC',
	// 		'post_status'	 => 'completed',
	// 		'posts_per_page' => youzify_option( 'youzify_profile_courses_per_page', 6 ),
	// 		'paged' 		 => absint( $_POST['youzify_page'] ),
	// 		'base' 		 	 => sanitize_text_field( $_POST['youzify_base'] ),
	// 		'author' 		 => $user_id,
	// 		'fields' 		 => "id=>parent",
	// 	);

	// 	// Get Courses Tab
	// 	$courses_tab = new Youzify_Tutor_Courses_Tab();

	//     die();

	// }

	/**
	 * Comments Tab Pagination.
	 */
	function comments_pagination() {

		// Include Comments Files
        require_once YOUZIFY_CORE . 'tabs/class-youzify-tab-comments.php';

		// Get Page Number.
		$cpage = absint( $_POST['youzify_page'] );

		// Get Profile User ID
	    $query_vars = json_decode( stripslashes( $_POST['query_vars'] ), true );

		// Get Data.
		$commentsNbr = youzify_option( 'youzify_profile_comments_nbr', 5 );
		$offset 	 = ( $cpage - 1 ) * $commentsNbr;

		// Pagination Args
		$args = array(
			'paged'   => $cpage,
			'offset'  => $offset,
			'number'  => $commentsNbr,
			'base' 	  => sanitize_text_field( $_POST['youzify_base'] ),
			'user_id' => sanitize_text_field( $query_vars['youzify_user'] ),
		);

		$comments = new Youzify_Comments_Tab();

		// Get Comments Core
		$comments->comments_core( $args );

	    die();
	}

	/**
	 * Handle Account Verification.
	 */
	function handle_verification( $user_id ) {

		// Hook.
		do_action( 'youzify_before_handle_account_verification' );

		if ( ! youzify_is_user_can_verify_accounts() || ! is_user_logged_in() ) {
			$data['error'] = $this->msg( 'invalid_role' );
			die( json_encode( $data ) );
		}

		// Get Data.
		$data = array();

		// Allowed Actions
		$allowed_actions = array( 'verify', 'unverify' );

		// Get User ID.
		$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : null;

		if ( empty( $user_id ) ) {
			$data['error'] = $this->msg( 'invalid_user_id' );
			die( json_encode( $data ) );
		}

		check_ajax_referer( 'youzify-account-verification-' . $user_id, 'security' );

		// Get Action
		$action = isset( $_POST['verification_action'] ) ? sanitize_text_field( $_POST['verification_action'] ) : null;

		if ( ! in_array( $action, $allowed_actions ) ) {
			$data['error'] = $this->msg( 'invalid_action' );
			die( json_encode( $data ) );
		}

		if ( 'verify' == $action ) {
			// Mark Account As Verified.
			update_user_meta( $user_id, 'youzify_account_verified', 'on' );
			$data['action'] = 'unverify';
			$data['msg'] = __( 'Account marked as verified successfully', 'youzify' );
			do_action( 'youzify_after_verifying_account', $user_id );
		} elseif ( 'unverify' == $action ) {
			// Mark Account As Unverified.
			update_user_meta( $user_id, 'youzify_account_verified', 'off' );
			$data['action'] = 'verify';
			$data['msg'] = __( 'Account marked as unverified successfully', 'youzify' );
			do_action( 'youzify_after_unverifying_account', $user_id );
		}

		$data['verify_account'] = __( 'Verify Account', 'youzify' );
        $data['unverify_account'] = __( 'Unverify Account', 'youzify' );

		die( json_encode( $data ) );

	}

    /**
     * Get Error Message.
     */
    function msg( $code ) {

        // Messages
        switch ( $code ) {

            case 'invalid_role':
                return __( 'The action you have requested is not allowed.', 'youzify' );

            case 'invalid_action':
                return __( 'The action you have requested is not exit.', 'youzify' );

            case 'invalid_user_id':
                return __( 'User id was not found, please try again later.', 'youzify' );
        }

        return __( 'An unknown error occurred. Please try again later.', 'youzify' );
    }

}

// Init Class.
new Youzify_Ajax();