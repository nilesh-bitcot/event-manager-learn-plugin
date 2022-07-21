<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header(); 
?>
<header id="neo_events-archive_header">
	<h1>Events</h1>
</header>
<section class="">
	<div class="neo_events_list" style="display:flex;width: 100%;">
		<?php
		if( have_posts() ){
			while ( have_posts() ) {
				the_post();
				$seats = get_post_meta(get_the_ID(), 'event_seats', true);

				$bookings = neo_evnts_get_booking_data(get_the_ID());
				$booked_seats = count($bookings);

				?>
				<div id="post-<?php the_ID();?>" class="single-event-grid" style="display: block;width: 32%;float: left;">
					<?php if ( has_post_thumbnail() ) : ?>
					    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
					        <?php the_post_thumbnail('thumbnail',array( 'sizes' => '(max-width:320px) 145px, (max-width:425px) 220px, 500px', 'style' => 'max-width:150px;max-height:150px' )); ?>
					    </a>
					<?php endif; ?>
					<h3><?php if ( is_archive() ) : ?><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php endif; ?><?php the_title(); ?><?php if ( is_archive() ) : ?></a><?php endif; ?></h3>
					<div class="entry-content-meta">
						<p>On: <?php echo get_post_meta(get_the_ID(),'event_date',true); ?></p>
						<p>Location: <?php echo get_post_meta(get_the_ID(),'event_location',true); ?></p>
						<p><span style="color:#fff;background-color:<?php echo ($booked_seats < $seats)?'green':'red'; ?>;"><?php echo $booked_seats.'/'.$seats ?></span></p>
					</div>
					<div class="entry-content"><?php the_content(); ?></div>

				</div>
				<?php
			}
		}
		?>
	</div>
</section>

<?php

get_footer(); 