<?php
/**
 * BuddyPress - Bookmark Loop
 *
 */

/**
 * Fires before the start of the activity loop.
 *
 * @since 1.2.0
 */
do_action( 'bp_before_bookmarks_loop' ); ?>

<div class="activity">

<?php if ( bp_has_activities( bp_ajax_querystring( 'bookmarks' ) ) ) : ?>

	<?php if ( empty( $_POST['page'] ) ) : ?>

		<ul id="activity-stream" class="activity-list item-list">

	<?php endif; ?>

	<?php while ( bp_activities() ) : bp_the_activity(); ?>

		<?php bp_get_template_part( 'activity/entry' ); ?>

	<?php endwhile; ?>

	<?php if ( bp_activity_has_more_items() ) : ?>

		<li class="load-more">
			<a href="<?php bp_activity_load_more_link() ?>"><i class="fas fa-level-down-alt"></i><?php _e( 'Load More Posts', 'youzify' ); ?></a>
		</li>

	<?php endif; ?>

	<?php if ( empty( $_POST['page'] ) ) : ?>

		</ul>

	<?php endif; ?>

<?php else : ?>

	<div id="message" class="info">
		<p><?php _e( 'Sorry, there was no bookmarks found.', 'youzify' ); ?></p>
	</div>

<?php endif; ?>

<?php

/**
 * Fires after the finish of the activity loop.
 *
 * @since 1.2.0
 */
do_action( 'bp_after_bookmarks_loop' ); ?>

<?php if ( empty( $_POST['page'] ) ) : ?>

	<form action="" name="activity-loop-form" id="activity-loop-form" method="post">

		<?php wp_nonce_field( 'activity_filter', '_wpnonce_activity_filter' ); ?>

	</form>

<?php endif; ?>

</div>