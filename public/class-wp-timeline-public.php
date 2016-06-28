<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://dropshop.io
 * @since      1.0.0
 *
 * @package    Wp_Timeline
 * @subpackage Wp_Timeline/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Timeline
 * @subpackage Wp_Timeline/public
 * @author     James Towers <james@songdrop.com>
 */
class Wp_Timeline_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->add_shortcodes();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Timeline_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Timeline_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-timeline-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Timeline_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Timeline_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-timeline-public.js', array( 'jquery' ), $this->version, false );

	}


	/**
	 * Enable shortcode
	 *
	 * Adds the [gfd_form] shortcode for displaying the submission form on a page
	 *
	 * @since    1.0.0
	 */
	public function add_shortcodes()
	{
		add_shortcode('wp_timeline', array( &$this, 'show_timeline'));
	}


	public function show_timeline()
	{

		$posts_array = $this->get_timeline_posts();

		$earliest_date = $posts_array[0]->post_date;
		$latest_date = end($posts_array)->post_date;

		$posts = $this->sort_timeline_posts( $posts_array );
		
		$period = $this->timeline_dates($earliest_date, $latest_date);

		echo '<ul class="wp-timeline-events">';
		$currentYear = null;

		foreach ($period as $dt) {

				$year = $dt->format("Y");
				if($year !== $currentYear)
				{
					echo '<li><strong>' . $dt->format("Y") . '</strong></li>';
					$currentYear = $year;
				}
		    echo '<li>';
		   	
		   	$month = $dt->format("M");
		    
		    if(isset($posts[$year][$month]))
		    {
		    	foreach($posts[$year][$month] as $m){
		    		echo '<a href="' . get_the_permalink($m->ID) . '" class="event-marker" title="' . $m->post_date	 . ' - ' . $m->post_title . '"></a>';
		    	}
		    }

		    echo '<span class="month">' . $month . '</span></li>';
		}
		echo '</ul>';

	}


	public function get_timeline_posts()
	{
		global $wpdb;
		$posts = $wpdb->get_results( 
			"
			SELECT ID, post_title, post_date
			FROM $wpdb->posts
			WHERE post_status = 'publish'
			AND post_type = 'post'
			ORDER BY 'post_date' DESC
			", OBJECT
		);
		//print_r($posts);
		return $posts;

	}

	public function sort_timeline_posts($posts)
	{
		$array_out = [];
		$current_year = null;
		$current_month = null;

		foreach($posts as $post)
		{

			$year = get_the_date('Y', $post->ID);
			$month = get_the_date('M', $post->ID);

			if(!isset($array_out[$year][$month])){
				$array_out[$year][$month] = [];
			}
			array_push($array_out[$year][$month], $post);

			$current_month = $month;
			$current_year = $year;

		}

		return $array_out;
	}



	public function timeline_dates($start_date, $end_date)
	{
		$start    = (new DateTime($start_date))->modify('first day of this month');
		$end      = (new DateTime($end_date))->modify('first day of next month');
		$interval = new DateInterval('P1M');
		
		$period = new DatePeriod($start, $interval, $end);

		// Reverse dates so that latest posts come first
		$dates = array();
		foreach ($period as $dt) {
		    $dates[] = $dt;
		}
		return array_reverse($dates);
	}

}
