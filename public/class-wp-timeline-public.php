<?php

class Wp_Timeline_Public {

	private $plugin_name;
	private $version;

	private $post_types;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->post_types = ['post', 'project', 'album'];

		$this->add_shortcodes();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-timeline-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-timeline.js', array( 'jquery' ), $this->version, false );

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
		//add_shortcode('wp_timeline_posts', array( &$this, 'get_timeline_posts'));
	}

	private function prepare_timeline_posts_array()
	{
		$posts = $this->get_timeline_posts();

		$array_out = [];

		foreach($posts as $post)
		{
			$directory = wp_get_post_terms( $post->ID, 'directory', true);
			log_it($directory);
			$start_date = $post->post_type === 'project' ? get_post_meta($post->ID, 'wp-post-projects_start_date', true) : $post->post_date;
			$end_date = $post->post_type === 'project' ? get_post_meta($post->ID, 'wp-post-projects_end_date', true) : $post->post_date;
			$totalMonths = get_post_meta($post->ID, 'wp-post-projects_duration_in_months', true);
			$post_arr = array(
				'ID' => $post->ID,
				'title' => $post->post_title,
				'url' => get_the_permalink($post->ID),
				'post_date' => $post->post_date,
				'post_type' => $post->post_type,
				'directory' => $directory[0]->slug,
				'startDate' => $start_date,
				'endDate' => $end_date,
				'monthsFromNow' => count($this->months_to_now($end_date)),
				'totalMonths' => $totalMonths ? $totalMonths : 1
				);
			array_push($array_out, $post_arr);
		}

		return $array_out;
	}

	public function show_timeline()
	{

		$posts = $this->prepare_timeline_posts_array();
		$latest_date = $posts[0]['post_date'];
		$earliest_date = end($posts)['post_date'];

		//$posts = $this->sort_timeline_posts( $posts );
		
		$period = $this->timeline_dates($earliest_date);
		$totalMonths = count($period);
		
		echo '<div id="wp-timeline">';
			echo '<ul class="wp-timeline-events">';
			$currentYear = null;



			foreach ($period as $dt) {

					$year = $dt->format("Y");
					echo '<li>';
					if($year !== $currentYear)
					{
						echo '<strong>' . $dt->format("Y") . '</strong>';
						$currentYear = $year;
					}
			    //echo '<li>';
			   	
			   	$month = $dt->format("M");

			   	/*foreach($posts as $event){
		    		echo '<a href="' . get_the_permalink($event->ID)  . '" class="event-marker" data-post-type="' . $event->post_type . '" title="' . $m->post_title . '"></a>';
		    	}*/

			    echo '<span class="month">' . $month . '</span></li>';
			}
			echo '</ul>';
			echo '<div id="wp-timeline-tooltip"></div>';
		echo '</div>';

		echo '<script>';
			echo 'window.timeline = new Timeline({ posts: ' . json_encode($posts) . ', totalMonths: ' . $totalMonths . ' });';
		echo '</script>';

	}


	public function get_timeline_posts($json = false)
	{
		$args = array(
			'post_type'  => $this->post_types,
			'numberposts' => -1,
			'meta_query' => array(
        array(
          'key' => $this->plugin_name . '_hide-from-timeline',
          'compare' => 'NOT EXISTS'
          )
        )
			//'order_by' => 'post_date'
			);

		$posts =  get_posts( $args );
		//log_it(count($posts));

		if($json){
			return json_encode($posts);
		}
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



	public function months_to_now($start_date)
	{
		$start    = (new DateTime($start_date))->modify('first day of this month'); 
		$end      = (new DateTime())->modify('first day of next month');
		$interval = new DateInterval('P1M');

		$period = new DatePeriod($start, $interval, $end);

		$dates = array();
		foreach ($period as $dt) {
		    $dates[] = $dt;
		}
		
		return $dates;
	}



	public function timeline_dates($start_date)
	{
		$period = $this->months_to_now($start_date);

		// Reverse dates so that latest posts come first
		return array_reverse($period);
	}

}
