<?php

/**
 * Archive Topic Content Part
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<div id="bbpress-forums">

	<div class="youzify-bbp-topic-head">
		<?php bbp_breadcrumb(); ?>
	</div>

	<?php if ( bbp_allow_search() ) : ?>

	<?php bbp_get_template_part( 'form', 'search' ); ?>

	<?php endif; ?>

	<?php if ( bbp_is_topic_tag() ) bbp_topic_tag_description(); ?>

	<?php do_action( 'bbp_template_before_topics_index' ); ?>

	<?php if ( bbp_has_topics() ) : ?>

		<?php bbp_get_template_part( 'loop',       'topics'    ); ?>

		<?php bbp_get_template_part( 'pagination', 'topics'    ); ?>

	<?php else : ?>

		<?php bbp_get_template_part( 'feedback',   'no-topics' ); ?>

	<?php endif; ?>

	<?php do_action( 'bbp_template_after_topics_index' ); ?>

</div>
