<?php
class Wp_Timeline_Admin {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-timeline-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-timeline-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function post_meta_boxes_setup()
	{
		add_action( 'add_meta_boxes', array( &$this, 'add_timeline_meta_boxes') );
		add_action( 'save_post', array( &$this, 'save_timeline_meta'), 10, 2 );
	}

	public function add_timeline_meta_boxes($postType)
	{
		if(in_array($postType, array('post', 'project'))){
		  add_meta_box(
		    $this->plugin_name . '_hide_from_timeline_meta_box',      // Unique ID
		    esc_html__( 'Timeline', $this->plugin_name ),    // Title
		    array( &$this, 'render_hide_from_timeline_meta_box'),   // Callback function
		    $postType, 
		    'side',
		    'default'  
		  );
		}
	}

	public function render_hide_from_timeline_meta_box( $object, $box )
	{
		$meta_key = $this->plugin_name . '_hide-from-timeline';
		$current = get_post_meta($bject->ID, $meta_key, true);

		// Add nonce field - use meta key name with '_nonce' appended
    wp_nonce_field( basename( __FILE__ ), $meta_key . '_nonce' );

    echo '<p class="description">' .  _e( "Check this box to hide this from the timeline", $this->plugin_name ) . '</p>';

    $checked = isset($current) ? 'checked' : '';
    echo '<input type="checkbox" value="1" name="' . $meta_key . '" ' . $checked . ' /><br>';
	}

	public function save_timeline_meta( $post_id, $post )
  {
    $this->save_meta($post_id, $post, $this->plugin_name . '_hide-from-timeline'); 
  }

  public function save_meta($post_id, $post, $meta_key)
  {
    $this->verify_nonce($meta_key . '_nonce', $post_id);

    /* Get the post type object. */
    $post_type = get_post_type_object( $post->post_type );
    /* Check if the current user has permission to edit the post. */
    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
      return $post_id;

    /* Get the posted data and sanitize it for use as an HTML class. */
    $new_meta_value = ( isset( $_POST[$meta_key] ) ? sanitize_html_class( $_POST[$meta_key] ) : '' );

    $this->save_or_edit_meta($post_id, $meta_key, $new_meta_value);
  }

  public function verify_nonce($nonce_key, $post_id)
  {
    if ( !isset( $_POST[$nonce_key] ) || !wp_verify_nonce( $_POST[$nonce_key], basename( __FILE__ ) ) )
      return $post_id;
  }



  public function save_or_edit_meta($post_id, $meta_key, $new_meta_value)
  {
  	  log_it($new_meta_value);
    /* Get the meta value of the custom field key. */
    $meta_value = get_post_meta( $post_id, $meta_key, true );

    /* If a new meta value was added and there was no previous value, add it. */
    if ( $new_meta_value && '' == $meta_value )
      add_post_meta( $post_id, $meta_key, $new_meta_value, true );

    /* If the new meta value does not match the old value, update it. */
    elseif ( $new_meta_value && $new_meta_value != $meta_value )
      update_post_meta( $post_id, $meta_key, $new_meta_value );

    /* If there is no new meta value but an old value exists, delete it. */
    elseif ( '' == $new_meta_value && $meta_value )
      delete_post_meta( $post_id, $meta_key, $meta_value );
  }

}
