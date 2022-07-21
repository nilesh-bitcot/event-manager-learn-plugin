<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header(); 
$date_now = new DateTime();
?>
<header id="neo_events-single_header">
	<h1><?php the_title(); ?></h1>
	<p><a href="<?php echo get_post_type_archive_link('neo_events'); ?>">See all events</a></p>
</header>
<section class="">
	<div class="neo_events_list">
		<?php
		if( have_posts() ){
			while ( have_posts() ) {
				the_post();

				$date = get_post_meta(get_the_ID(), 'event_date', true);
				$location = get_post_meta(get_the_ID(), 'event_location', true);
				$price = get_post_meta(get_the_ID(), 'event_price', true);
				$seats = get_post_meta(get_the_ID(), 'event_seats', true);

				$event_date = new DateTime($date);

				$bookings = neo_evnts_get_booking_data(get_the_ID());
				$booked_seats = count($bookings);
				?>
				<div id="post-<?php the_ID();?>" class="single-event-grid" style="display: flex;width: 100%;">
					<div style="width:40%">
					<?php if ( has_post_thumbnail() ) : ?>
					    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
					        <?php the_post_thumbnail('thumbnail',array( 'sizes' => '(max-width:320px) 145px, (max-width:425px) 220px, 500px', 'style' => 'max-width:150px;max-height:150px' )); ?>
					    </a>
					<?php endif; ?>
					</div>

					<div style="width: 60%;">
						<h3><?php the_title(); ?></h3>
						<div class="entry-content-meta">
							<p>On: <?php echo get_post_meta(get_the_ID(),'event_date',true); ?></p>
							<p>Location: <?php echo get_post_meta(get_the_ID(),'event_location',true); ?></p>
							<p>Ticket Price: $<?php echo get_post_meta(get_the_ID(),'event_price',true); ?></p>
							<p>Seats Capacity: <?php echo get_post_meta(get_the_ID(),'event_seats',true); ?></p>
							<p>Booked Seats: <?php echo $booked_seats; ?></p>
							<p>Seats remaining: <?php echo intval($seats)- $booked_seats; ?></p>

						</div>
						<div class="entry-content"><?php the_content(); ?></div>
					</div>

				</div>
				<div style="display: flex;width: 100%;">
					<?php if ( is_singular() ) : ?>
						<div class="entry-content">
							<h3>fill following form to confirm your seat.</h3>
							<div class="form">
								<?php if( (intval($seats) - intval($booked_seats)) > 0 && $event_date >= $date_now ): ?>
								<form action="" method="post">
									<div class="mb-3">
										<input type="email" class="form-control" id="your_email" placeholder="name@example.com" name="your_email">
									</div>
									<div class="mb-3">
										<input type="text" class="form-control" id="your_name" placeholder="your name" name="your_name">
									</div>
									<div class="mb-3">
										<select class="form-control" id="seat_count" placeholder="Enter seats" name="seat_count">
											<option value="1">1</option>
											<option value="2">2</option>
											<option value="3">3</option>
										</select>
									</div>
									<div class="mb-3">
										<input type="hidden" value="<?php the_ID(); ?>" name="event_id">
										<input type="hidden" value="add_event_booking" name="action">
										<input type="submit" value="submit" name="submit">
										<?php 
										if( isset($_GET['err']) && $_GET['err'] == 2 ){
											echo '<p>not enough seats available,</p>';
										}else if(isset($_GET['err']) && $_GET['err'] == 1){
											echo '<p>try again later</p>';
										}else if(isset($_GET['err']) && $_GET['err'] == 3){
											echo '<p>Enter right details</p>';
										}
										if( isset($_GET['suc']) ){
											echo '<p>Your ticket booked successfully.</p>';
										}
										?>
									</div>
								</form>
								<?php else: ?>
									<p>Sorry all seats are booked. try in other events</p>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<?php
			}
		}
		?>
	</div>
</section>

<?php

get_footer(); 