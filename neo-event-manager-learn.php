<?php
/**
 * Plugin Name:Neo Event Manager Learning plugin
 * */


add_action( 'plugins_loaded', 'shubham_theme_create_extra_table' );

function shubham_theme_create_extra_table(){
    global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $table_name = $wpdb->prefix . "event_bookings"; 

    $sql = "CREATE TABLE $table_name (
      id bigint(11) unsigned NOT NULL AUTO_INCREMENT,
      your_name varchar(255) NOT NULL,
      your_email varchar(255) NOT NULL,
      event_id bigint(11) NOT NULL,
      PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    dbDelta( $sql );
}

add_action('wp_enqueue_scripts', 'neo_s_enqueue_scripts');
function neo_s_enqueue_scripts(){
    // wp_enqueue_style('bootstrap', plugin_dir_url().'/css/bootstrap.min.css');
    // wp_enqueue_style('datatables', '//cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css');
    
    // wp_enqueue_script('bootstrap', plugin_dir_url().'/js/bootstrap.bundle.min.js', array('jquery'));
    // wp_enqueue_script('datatables', '//cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js', array('jquery'));
}

add_action('admin_enqueue_scripts', 'neo_s_admin_enqueue_scripts');
function neo_s_admin_enqueue_scripts(){
    wp_enqueue_style('datatables', '//cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css');
    
    wp_enqueue_script('datatables', '//cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js', array('jquery'));
}


function neo_events_post_type() {
    $args = array(
        'public'    => true,
        'label'     => __( 'Events', 'shubham-theme' ),
        'menu_icon' => 'dashicons-calendar-alt',
        'show_ui' => true,
        'has_archive' => true,
        'show_in_nav_menus' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'supports'     => array( 'title', 'editor','thumbnail' )
    );
    register_post_type( 'neo_events', $args );
}
add_action( 'init', 'neo_events_post_type' );


add_action('add_meta_boxes', 'neo_events_meta_boxes');
function neo_events_meta_boxes(){
    add_meta_box(
        'neo_event_id',
        'Events Details',
        'neo_events_metabox_callback',
        'neo_events'
    );

    add_meta_box(
        'neo_event_booking_id',
        'Events Booking Details',
        'neo_events_booking_metabox_callback',
        'neo_events'
    );
}

add_filter( 'manage_neo_events_posts_columns', 'neo_events_filter_posts_columns' );
function neo_events_filter_posts_columns( $columns ) {
    $columns['seats'] = __( 'Seats' );
    $columns['event_date'] = __( 'Event Date' );
    return $columns;
}


add_action( 'manage_neo_events_posts_custom_column', 'neo_events_seats_column_response', 10, 2);
function neo_events_seats_column_response( $column, $post_id ) {
    if ( 'seats' === $column ) {
        $bookings = neo_evnts_get_booking_data($post_id);
        $total_seats = get_post_meta($post_id,'event_seats', true);
        $booked_seats = count($bookings);
        echo '<span style="padding:5px;background-color:'.($booked_seats < $total_seats ? 'green':'red').';color:#fff;">'. $booked_seats .'/'. $total_seats .'</span>';
    }
    if( 'event_date' === $column ){
    	echo get_post_meta($post_id,'event_date', true);
    }
}
  

function neo_events_metabox_callback($post){
    $date = get_post_meta($post->ID, 'event_date', true);
    $location = get_post_meta($post->ID, 'event_location', true);
    $price = get_post_meta($post->ID, 'event_price', true);
    $seats = get_post_meta($post->ID, 'event_seats', true);
    ?>
    <table width="100%">
        <tr>
            <td>Date</td><td><input type="date" name="event_date" value="<?php echo $date; ?>"></td>
        </tr>
        <tr>
            <td>Location</td><td><input type="text" name="event_location" value="<?php echo $location; ?>"></td>
        </tr>
        <tr>
            <td>price</td><td><input type="text" name="event_price" value="<?php echo $price; ?>"></td>
        </tr>
        <tr>
            <td>Seats</td><td><input type="text" name="event_seats" value="<?php echo $seats; ?>"></td>
        </tr>
    </table>
    <?php
}

function neo_events_booking_metabox_callback($post){
    $bookings = neo_evnts_get_booking_data($post->ID);
    $total_seats = get_post_meta($post->ID,'event_seats', true);
    $booked_seats = count($bookings);
    ?>
    <div style="display: flex; width:100%">
        Booking stats : <span style="padding:5px;background-color:<?= ($booked_seats < $total_seats) ? 'green':'red' ?>;color:#fff;"><?php echo $booked_seats;  ?>/<?php echo $total_seats; ?></span>
    </div>
    <div style="display: flex; width:100%">
    <table class="table" width="100%">
        <thead><tr><th>Name</th><th>eamil</th></tr></thead>
        <tbody>
    <?php
    if( $bookings ){
        foreach( $bookings as $single ){
            ?>
            <tr>
                <td><?php echo $single['your_name']; ?></td>
                <td><?php echo $single['your_email']; ?></td>
                <td>----</td>
            </tr>
            <?php
        }
    }
    ?>
    </tbody>
    </table>
    </div>
    <?php
}

add_action('save_post', 'update_neo_events_post_meta');

function update_neo_events_post_meta($post_id){
    if( isset($_POST['event_date']) ) update_post_meta($post_id, 'event_date', $_POST['event_date']);

    if( isset($_POST['event_location']) ) update_post_meta($post_id, 'event_location', $_POST['event_location']);

    if( isset($_POST['event_price']) ) update_post_meta($post_id, 'event_price', $_POST['event_price']);

    if( isset($_POST['event_seats']) ) update_post_meta($post_id, 'event_seats', $_POST['event_seats']);
}


add_action('init', function(){
    if( isset($_POST['action']) && $_POST['action'] == 'add_event_booking'  ){
        if( empty($_POST['your_name']) || empty($_POST['your_email']) ){
        	wp_safe_redirect($_SERVER['HTTP_REFERER'].'?err=3');        	
        	exit();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . "event_bookings";

        $name = $_POST['your_name'];
        $email = $_POST['your_email'];
        $event_id = $_POST['event_id'];
        $count = intval($_POST['seat_count']);

        $booked_seats = neo_evnts_get_booking_data($event_id);

        $total_seats = intval(get_post_meta($event_id,'event_seats', true));

        if( ($total_seats - count($booked_seats)) < $count ){
        	// echo $_SERVER['HTTP_REFERER'];
        	wp_safe_redirect($_SERVER['HTTP_REFERER'].'?err=2');        	
        	exit();
        }

        $data = array(
            'your_name' => $name,
            'your_email' => $email,
            'event_id' => $event_id
        );
        // $row_id;
        for ($i=1; $i <= $count ; $i++) { 
        	$row_id = $wpdb->insert($table_name, $data);
        }        

        if( is_wp_error($row_id) ){
            wp_safe_redirect($_SERVER['HTTP_REFERER'].'?err=1');        	
        	exit();
        }
    }
});

function neo_evnts_get_booking_data($event_id = 0){
    global $wpdb;
    $table_name = $wpdb->prefix.'event_bookings';
    $sql = "SELECT * FROM $table_name";

    if( $event_id > 0){
        $sql .= " WHERE event_id='$event_id'";
    }

    return $wpdb->get_results( $sql, ARRAY_A);
}


add_action('admin_menu', function(){
    add_menu_page(
        'event bookings',
        'bookings',
        'manage_options',
        'event-bookings',
        'event_entry_admin_page',
        'dashicons-cloud-saved'
    );
});


function event_entry_admin_page(){
    $selected_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
    $bookings = neo_evnts_get_booking_data($selected_id);

    $events = get_posts(array(
        "post_type"=>"neo_events",
        "posts_per_page" => -1
    ));
    
    ?>
    <div class="wrap">
        <h1>Event entries</h1>
        <div>
            <form name="filter_booking" method="get">
            <label>
                Select event
                <select name="event_id">
                <option value="">select event</option>
                    <?php 
                    if( $events ){
                        foreach($events as $e ){
                            ?><option value="<?php echo $e->ID; ?>"  <?php echo ($e->ID == $selected_id) ? 'selected':''; ?>><?php echo $e->post_title; ?></option><?php
                        }
                    }
                    ?>
                    
                </select>
            </label>
            <input type="hidden" value="event-bookings" name="page">
            <button type="submit" class="button button-primary">Filter</button>
            </form>
        </div>
        <div class="entries_tables">
            <table width="100%" class="table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Name</th>
                        <th>email</th>
                        <th>action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if( $bookings ){
                        foreach( $bookings as $single ){
                            ?>
                            <tr>
                                <td><a href="<?php echo get_edit_post_link($single['event_id']); ?>"><?php echo get_the_title($single['event_id']); ?></a></td>
                                <td><?php echo get_post_meta($single['event_id'], 'event_date', true); ?></td>
                                <td><?php echo $single['your_name']; ?></td>
                                <td><?php echo $single['your_email']; ?></td>
                                <td>----</td>
                            </tr>
                            <?php
                        }
                    }
                    ?>

                </tbody>
            </table>
        </div>
    </div>
    <script>
        jQuery(document).ready(function($){
            $('.table').DataTable();
        });
    </script>
    <?php
}


add_filter( 'single_template', 'wpsites_custom_post_type_template',11 );
function wpsites_custom_post_type_template($single_template) {
     global $post;

     if ($post->post_type == 'neo_events' ) {
          // $single_template = plugin_dir_path( '/templates/single-neo_events.php' );
     	$single_template = dirname( __FILE__ ) . '/templates/single-neo_events.php';
     }
     return $single_template;
  
}


add_filter( 'archive_template', 'get_custom_post_type_template' );
 
function get_custom_post_type_template( $archive_template ) {
     global $post;
 
     if ( is_post_type_archive ( 'neo_events' ) ) {
          $archive_template = dirname( __FILE__ ) . '/templates/archive-neo_events.php';
          // $archive_template = plugin_dir_path('/templates/archive-neo_events.php');
     }
     return $archive_template;
}



add_action( 'pre_get_posts', 'neo_events_list_post_filter' );
function neo_events_list_post_filter($query) {
	if( is_admin() ) {
		return $query;
	}

    if ( ! $query->is_main_query() ) return;

    // if( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'neo_events' ) {
    if( is_post_type_archive( 'neo_events' ) ){

		$query->set('meta_key', 'event_date');
		$query->set('orderby', 'meta_value');	
		// $query->set('order', 'DESC');
		$query->set('order', 'ASC');


		$meta_query = $query->get('meta_query');
		if( empty($meta_query) ){
			$meta_query = [];
		}
	    $meta_query[] = array(
	      'key' => 'event_date',
	      'value' => date('Y-m-d',time()),
	      'compare' => '>=',
	    );
	    $query->set('meta_query', $meta_query);

	}

	return $query;
}