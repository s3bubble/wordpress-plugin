<?php
/*
Plugin Name: S3Bubble Amazon Web Services Media Streaming
Plugin URI: https://s3bubble.com
Description: S3Bubble Amazon Web Services Media Streaming Plugin 
Version: 4.8
Author: S3Bubble
Author URI: https://s3bubble.com
Text Domain: s3bubble-amazon-web-services-oembed-media-streaming-support
Domain Path: /languages
License: GPL2
*/
 
/*  Copyright YEAR  Samuel East  (email : mail@samueleast.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/ 


if (!class_exists("s3bubble_oembed")) {
	class s3bubble_oembed {
		
		// Set the version
		public  $version = 110;  

		/*
		 * Constructor method to intiat the class
		 * @author sameast
		 * @params none
		 */
		public function  __construct(){ 

			/*
			 * Run the add admin menu class
			 * @author sameast
			 * @params none
			 */ 
			add_action( 'admin_menu', array( $this, 's3bubble_oembed_admin_menu' ));

			/*
			 * Add css to the header of the document
			 * @author sameast
			 * @params none
			 */ 
			add_action( 'wp_enqueue_scripts', array( $this, 's3bubble_oembed_scripts' ), 12 );

			/*
			 * Add javascript to the frontend footer connects to wp_footer
			 * @author sameast
			 * @params none
			 */ 
			add_action( 'admin_enqueue_scripts', array( $this, 's3bubble_oembed_admin_scripts' ) );
			
			/*
			 * Setup website connection
			 */
			add_action( 'init', array( $this, 's3bubble_website_connection' ));

			/*
			 * Oembed support fix
			 */
			add_action( 'init', array( $this, 's3bubble_oembed_iframes' ));

			/*
			 * Tiny mce button for the plugin
			 * @author sameast
			 * @params none
			 */
			add_action( 'init', array( $this, 's3bubble_oembed_buttons' ) );

			/*
			 * Setup shortcodes for the plugin
			 * @author sameast
			 * @params none
			 */ 
			add_shortcode( 's3bubble', array( $this, 's3bubble_aws_self_hosted' ) );

			/*
			 * Load the languages file
			 * @author sameast
			 * @params none
			 */ 
			add_action( 'plugins_loaded', array( $this, 's3bubble_amazon_web_services_oembed_media_streaming_support_textdomain' ) );

		}

		/*
		 * Loads the path to languages folder
		 * @author sameast
		 * @params none
		 */ 
		function s3bubble_amazon_web_services_oembed_media_streaming_support_textdomain() {
		    load_plugin_textdomain( 's3bubble-amazon-web-services-oembed-media-streaming-support', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
		}


		/*
		 * Create a connected website option
		 * @author sameast
		 * @params none
		 */ 
		function s3bubble_website_connection(){

			if(isset($_SERVER['HTTP_HOST'])){

				$host = (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == "127.0.0.1") ? "localhost" : $_SERVER['HTTP_HOST'];
				$host = preg_replace('#^www\.(.+\.)#i', '$1', $host); // remove the www
				update_option("s3bubble_oembed_connected_website", $host);

			}

		}

		/*
		 * Self hosted code
		 * @author sameast
		 * @params none
		 */ 
		function s3bubble_aws_self_hosted($atts){

			// Extract the vars from the shortcode
			extract( shortcode_atts( array(
				'code'   => '', // Playlists
				'codes'   => '', // Media
				'source'   => '', // source data
				'options'   => '', // Options
				'meta'   => '', // meta data
				'brand'   => '', // brand data
				'modal'   => '', // modal data
				'popit'  => '', // popit data
				'stream'   => '', // Stream
				'type' => 'video',
				'media' => 'video'
			), $atts, 's3bubble' ) );

			// Make sure its unique
			$id = uniqid();

			if(!empty($stream)){
				$type = "stream";
			}

			if(!empty($code)){
				return '<div id="s3bubble-' . $id . '" class="s3bubble-playlist" data-setup=\'{"code": "' . $code . '","type": "' . $type . '"}\'></div>';
			}

			// Check for multiple codes
			if(strpos($codes, ',') !== false) {
				$codes = explode(",", $codes);
				$codes = json_encode($codes);
			}else{
				$codes = json_encode(array($codes));
			}

			// Check for the popit plugin
			if (array_key_exists("popit",$atts)){
				$popit = ',"popit": {' . $popit . '}';
			}

			switch ($type) {
				case 'video':
					// Video single
					return '<div id="s3bubble-' . $id . '" class="s3bubble" data-setup=\'{"codes": ' . $codes . ',"source": {' . $source . '},"options": {' . $options . '},"meta": {' . $meta . '},"brand": {' . $brand . '}' . $popit . '}\'></div>';
					break;
				case 'audio':
					// Audio single
					return '<div id="s3bubble-' . $id . '" class="s3bubble-audio" data-setup=\'{"codes": ' . $codes . ',"source": {' . $source . '},"options": {' . $options . '},"meta": {' . $meta . '},"brand": {' . $brand . '}' . $popit . '}\'></div>';
					break;
				case 'service':
				    // Youtube links
					return '<div id="s3bubble-' . $id . '" class="s3bubble-service" data-setup=\'{"codes": ' . $codes . ',"source": {' . $source . '},"options": {' . $options . '},"meta": {' . $meta . '},"brand": {' . $brand . '}' . $popit . '}\'></div>';
					break;
				case 'stream':
					// This needs to be different 
					return '<div id="s3bubble-' . $id . '" class="s3bubble-live" data-setup=\'{"stream": "' . $stream . '","source": {' . $source . '},"options": {' . $options . '},"meta": {' . $meta . '},"brand": {' . $brand . '}' . $popit . '}\'></div>';
					break;
				case 'modal':
					// Modal popup
					return '<div id="s3bubble-' . $id . '" class="s3bubble-modal" data-setup=\'{"codes": ' . $codes . ',"source": {' . $source . '},"options": {' . $options . '},"meta": {' . $meta . '},"brand": {' . $brand . '},"modal": {' . $modal . '}}\'></div>';
					break;
				default:
					
					break;
			}

		}

		/*
		 * Add default option to database
		 * @author sameast
		 * @params none
		 */ 
		function s3bubble_oembed_iframes(){

			// Audio progressive
			wp_embed_register_handler( 
		        's3bubble-audio-progressive', 
		        '#https://media.s3bubble\.com/embed/aprogressive/id/([a-zA-Z0-9_-]+)$#i',   // <-- Adjust this to your needs!
		        array( $this, 's3bubble_audio_oembed_progressive_embed_handler' ) 
		    );
		    
		    // Audio hls
			wp_embed_register_handler( 
		        's3bubble-audio-hls', 
		        '#https://media.s3bubble\.com/embed/ahls/id/([a-zA-Z0-9_-]+)$#i',   // <-- Adjust this to your needs!
		        array( $this, 's3bubble_audio_oembed_hls_embed_handler' ) 
		    );

			// Video progressive
		    wp_embed_register_handler( 
		        's3bubble-video-progressive', 
		        '#https://media.s3bubble\.com/embed/progressive/id/([a-zA-Z0-9_-]+)$#i',   // <-- Adjust this to your needs!
		        array( $this, 's3bubble_video_oembed_progressive_embed_handler' )
		    );

		    // 360 Degree Video pano
		    wp_embed_register_handler( 
		        's3bubble-video-pano', 
		        '#https://media.s3bubble\.com/embed/pano/id/([a-zA-Z0-9_-]+)$#i',   // <-- Adjust this to your needs!
		        array( $this, 's3bubble_video_oembed_pano_embed_handler' )
		    );

			// Video hls
		    wp_embed_register_handler( 
		        's3bubble-video-hls', 
		        '#https://media.s3bubble\.com/embed/hls/id/([a-zA-Z0-9_-]+)$#i',   // <-- Adjust this to your needs!
		        array( $this, 's3bubble_video_oembed_hls_embed_handler' )
		    );

		    // Video hls playlist
		    wp_embed_register_handler( 
		        's3bubble-video-playlist', 
		        '#https://media.s3bubble\.com/embed/playlist/id/([a-zA-Z0-9_-]+)$#i',   // <-- Adjust this to your needs!
		        array( $this, 's3bubble_video_oembed_playlist_embed_handler' )
		    );

		    // Audio hls playlist
		    wp_embed_register_handler( 
		        's3bubble-audio-playlist', 
		        '#https://media.s3bubble\.com/embed/aplaylist/id/([a-zA-Z0-9_-]+)$#i',   // <-- Adjust this to your needs!
		        array( $this, 's3bubble_audio_oembed_playlist_embed_handler' )
		    );

		    // Live streaming
		    wp_embed_register_handler( 
		        's3bubble-video-live-streaming', 
		        '#https://media.s3bubble\.com/embed/live/username/([a-zA-Z0-9_-]+)$#i',   // <-- Adjust this to your needs!
		        array( $this, 's3bubble_video_oembed_live_embed_handler' )
		    );

		}

		/*
		 * Adds progressive oembed audio iframe support
		 * @author sameast
		 * @params none
		 */ 
		function s3bubble_audio_oembed_progressive_embed_handler( $matches, $attr, $url, $rawattr )
		{
		    $embed = sprintf(
		        '<iframe class="s3bubble-audio-oembed-iframes" src="https://media.s3bubble.com/embed/aprogressive/id/%1$s" height="160" frameborder="0" allowfullscreen></iframe>',
		        esc_attr( $matches[1] )
		    );
		    return apply_filters( 's3bubble_audio_oembed_progressive_embed_handler', $embed, $matches, $attr, $url, $rawattr );
		}

		/*
		 * Adds adaptive bitrate oembed audio iframe support
		 * @author sameast
		 * @params none
		 */ 
		function s3bubble_audio_oembed_hls_embed_handler( $matches, $attr, $url, $rawattr )
		{
		    $embed = sprintf(
		        '<iframe class="s3bubble-audio-oembed-iframes" src="https://media.s3bubble.com/embed/ahls/id/%1$s" height="160" frameborder="0" allowfullscreen></iframe>',
		        esc_attr( $matches[1] )
		    );
		    return apply_filters( 's3bubble_audio_oembed_hls_embed_handler', $embed, $matches, $attr, $url, $rawattr );
		}
        
        /*
		 * Adds progressive oembed video iframe support
		 * @author sameast
		 * @params none
		 */ 
		function s3bubble_video_oembed_progressive_embed_handler( $matches, $attr, $url, $rawattr )
		{

			// Make sure its unique
			$id = uniqid();

			if(get_option( 's3bubble_selfhosted_switch' )){

				return '<div id="s3bubble-' . $id . '" class="s3bubble" data-setup=\'{"codes": ["' . esc_attr( $matches[1] ) . '"]}\'></div>';

			}else{

				$embed = sprintf(
			        '<iframe height="360" class="s3bubble-video-oembed-iframes" src="https://media.s3bubble.com/embed/progressive/id/%1$s" frameborder="0" webkitAllowFullScreen="true" mozallowfullscreen="true" allowFullScreen="true"></iframe>',
			        esc_attr( $matches[1] )
			    );
			    return apply_filters( 's3bubble_video_oembed_progressive_embed_handler', $embed, $matches, $attr, $url, $rawattr );

			}

		}

		/*
		 * Adds pano 360 degress oembed video iframe support
		 * @author sameast
		 * @params none
		 */ 
		function s3bubble_video_oembed_pano_embed_handler( $matches, $attr, $url, $rawattr )
		{
		    $embed = sprintf(
		        '<iframe height="360" class="s3bubble-video-oembed-iframes" src="https://media.s3bubble.com/embed/pano/id/%1$s" frameborder="0" webkitAllowFullScreen="true" mozallowfullscreen="true" allowFullScreen="true"></iframe>',
		        esc_attr( $matches[1] )
		    );
		    return apply_filters( 's3bubble_video_oembed_pano_embed_handler', $embed, $matches, $attr, $url, $rawattr );
		}
        
        /*
		 * Adds adaptive bitrate oembed video iframe support
		 * @author sameast
		 * @params none
		 */ 
		function s3bubble_video_oembed_hls_embed_handler( $matches, $attr, $url, $rawattr )
		{

			// Make sure its unique
			$id = uniqid();

			if(get_option( 's3bubble_selfhosted_switch' )){

				return '<div id="s3bubble-' . $id . '" class="s3bubble" data-setup=\'{"codes": ["' . esc_attr( $matches[1] ) . '"]}\'></div>';

			}else{

				$embed = sprintf(
			        '<iframe height="360" class="s3bubble-video-oembed-iframes" src="https://media.s3bubble.com/embed/hls/id/%1$s" frameborder="0" webkitAllowFullScreen="true" mozallowfullscreen="true" allowFullScreen="true"></iframe>',
			        esc_attr( $matches[1] )
			    );
			    return apply_filters( 's3bubble_video_oembed_hls_embed_handler', $embed, $matches, $attr, $url, $rawattr );

			}
		    
		}

		/*
		 * Adds adaptive bitrate oembed video playlist iframe support
		 * @author sameast
		 * @params none
		 */ 
		function s3bubble_video_oembed_playlist_embed_handler( $matches, $attr, $url, $rawattr )
		{
		    $embed = sprintf(
		        '<iframe height="360" class="s3bubble-video-oembed-iframes" src="https://media.s3bubble.com/embed/playlist/id/%1$s" frameborder="0" webkitAllowFullScreen="true" mozallowfullscreen="true" allowFullScreen="true"></iframe>',
		        esc_attr( $matches[1] )
		    );
		    return apply_filters( 's3bubble_video_oembed_playlist_embed_handler', $embed, $matches, $attr, $url, $rawattr );
		}

		/*
		 * Adds adaptive bitrate oembed audio playlist iframe support
		 * @author sameast
		 * @params none
		 */ 
		function s3bubble_audio_oembed_playlist_embed_handler( $matches, $attr, $url, $rawattr )
		{
		    $embed = sprintf(
		        '<iframe height="290" class="s3bubble-audio-oembed-iframes" src="https://media.s3bubble.com/embed/aplaylist/id/%1$s" frameborder="0" webkitAllowFullScreen="true" mozallowfullscreen="true" allowFullScreen="true"></iframe>',
		        esc_attr( $matches[1] )
		    );
		    return apply_filters( 's3bubble_audio_oembed_playlist_embed_handler', $embed, $matches, $attr, $url, $rawattr );
		}

		/*
		 * Adds live streaming iframe support
		 * @author sameast
		 * @params none
		 */ 
		function s3bubble_video_oembed_live_embed_handler( $matches, $attr, $url, $rawattr )
		{
		    $embed = sprintf(
		        '<iframe height="360" class="s3bubble-video-oembed-iframes" src="https://media.s3bubble.com/embed/live/username/%1$s" frameborder="0" webkitAllowFullScreen="true" mozallowfullscreen="true" allowFullScreen="true"></iframe>',
		        esc_attr( $matches[1] )
		    );
		    return apply_filters( 's3bubble_video_oembed_live_embed_handler', $embed, $matches, $attr, $url, $rawattr );
		}

		function register_s3bubble_aws_self_hosted_settings() {
			//register our settings
			register_setting( 's3bubble-aws-self-hosted-plugin-settings-group', 's3bubble_selfhosted_switch' );
		}

		/*
		* Adds the menu item to the wordpress admin
		* @author sameast
		* @none
		*/ 
        function s3bubble_oembed_admin_menu(){

			add_menu_page( 's3bubble_oembed', 'AWS S3Bubble', 'administrator', 's3bubble_oembed', array($this, 's3bubble_oembed_admin'), 'https://s3.amazonaws.com/s3bubble-cdn/theme-images/s3bubblelogo.png', 10);

			//call register settings function
			add_action( 'admin_init', array($this, 'register_s3bubble_aws_self_hosted_settings') );

    	}

    	/*
		* Add css to wordpress admin to run colourpicker
		* @author sameast
		* @none
		*/ 
		function s3bubble_oembed_admin_scripts(){
			
			$s3bubble_oembed_connected_website = get_option("s3bubble_oembed_connected_website");
			wp_enqueue_style( 's3bubble-oembed-admin-css', plugins_url('dist/css/admin.min.css', __FILE__), array(), $this->version );
			wp_enqueue_style( 's3bubble-oembed-chosen-css', plugins_url('dist/css/chosen.min.css', __FILE__), array(), $this->version );
			wp_enqueue_style( 's3bubble-oembed-sweet-css', plugins_url('dist/css/sweetalert.min.css', __FILE__), array(), $this->version );

			wp_enqueue_script( 'buttons-github-js', 'https://buttons.github.io/buttons.js', array(),  $this->version, false );
			wp_enqueue_script( 's3bubble-oembed-chosen-js', plugins_url('dist/js/chosen.jquery.min.js',__FILE__ ), array( 'jquery' ),  $this->version, true );
			wp_enqueue_script( 's3bubble-oembed-sweetalert-js', plugins_url('dist/js/sweetalert.min.js',__FILE__ ), array( 'jquery' ),  $this->version, true );
			wp_localize_script('s3bubble-oembed-sweetalert-js', 's3bubble_oembed_uid', array(
				's3website' => (!empty($s3bubble_oembed_connected_website) ? $s3bubble_oembed_connected_website : ""),
				's3bubbleSelfHosted' => get_option( 's3bubble_selfhosted_switch' ) ? "true" : "false"
			));
		}
		
		/*
		* Add css ties into wp_head() function
		* @author sameast
		* @params none
        */ 
		function s3bubble_oembed_scripts(){
			
			wp_enqueue_style('s3bubble-oembed-css', plugins_url('dist/css/styles.min.css',__FILE__ ), array(),  $this->version );
			wp_enqueue_script( 's3bubble-oembed-js', plugins_url('dist/js/scripts.min.js',__FILE__ ), array( 'jquery' ),  $this->version, true );

			// Locally hosted
			wp_enqueue_style('s3bubble-hosted-cdn', plugins_url('dist/css/s3bubble.min.css',__FILE__ ), array(), $this->version);
        	wp_enqueue_script('s3bubble-hosted-cdn', plugins_url('dist/js/s3bubble.min.js',__FILE__ ), array(), $this->version, true);

			// Include the self hosted scripts
			//wp_enqueue_style( 's3bubble-hosted-cdn', '//s3.amazonaws.com/aws-hosted/s3bubble.min.css' );
			//wp_enqueue_script( 's3bubble-hosted-cdn', '//s3.amazonaws.com/aws-hosted/s3bubble.min.js', 'jquery', $this->version, true );

		}

		/*
		* Sets up tiny mce plugins
		* @author sameast
		* @none
		*/ 
		function s3bubble_oembed_buttons() {
			if ( current_user_can( 'manage_options' ) )  {
				add_filter( 'mce_external_plugins', array( $this, 's3bubble_oembed_add_buttons' ) ); 
				add_filter( 'mce_buttons', array( $this, 's3bubble_oembed_register_buttons' ) );
			} 
		}
		
		/*
		* Adds the menu item to the tiny mce
		* @author sameast
		* @none
		*/ 
		function s3bubble_oembed_add_buttons( $plugin_array ) {
		    $plugin_array['S3bubbleOembed'] = plugins_url('/dist/js/tinymce.min.js',__FILE__);
		    return $plugin_array;
		}
		
		/*
		* Registers the amount of buttons
		* @author sameast
		* @none
		*/ 
		function s3bubble_oembed_register_buttons( $buttons ) {
		    array_push( $buttons, 's3bubble_oembed_global_shortcode' ); 
		    return $buttons;
		}
		 
    	/*
		* Add javascript to the footer connect to wp_footer()
		* @author sameast
		* @none
		*/ 
		function s3bubble_oembed_admin(){ 

			$alert = "";

			if(isset($_SERVER['HTTP_HOST'])){

				$host = (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == "127.0.0.1") ? "localhost" : $_SERVER['HTTP_HOST'];
				$host = preg_replace('#^www\.(.+\.)#i', '$1', $host); // remove the www
				update_option("s3bubble_oembed_connected_website", $host);

			}else{

				$alert = '<div class="error"><p>We could not get your website address please contact support@s3bubble.com</p></div>';
			
			}

		?>

		<div class="wrap">
			<h2><?php echo __( 'Fast, Reliable, Secure Media Streaming Powered by Amazon Web Services', 's3bubble-amazon-web-services-oembed-media-streaming-support' ); ?></h2>
			<?php echo $alert; ?>
			<div class="metabox-holder has-right-sidebar">
				<div class="inner-sidebar" style="width:40%">
					<div class="postbox">
						<h3 class="hndle"><?php echo __( 'S3Bubble Links', 's3bubble-amazon-web-services-oembed-media-streaming-support' ); ?></h3>
						<div class="inside">
							<ul>
								<li><?php echo __( 'Sign up for an S3Bubble account at', 's3bubble-amazon-web-services-oembed-media-streaming-support' ); ?> <a href="https://s3bubble.com" target="_blank">https://s3bubble.com</a>
								</li>
							</ul>
							<a class="s3bubble-wp-btn" href="https://s3bubble.com/documentation/" target="_blank"><?php echo __( 'VIEW ALL EXAMPLES', 's3bubble-amazon-web-services-oembed-media-streaming-support' ); ?></a>
						</div> 
					</div>
					<div class="postbox">
						<h3 class="hndle"><?php echo __( 'Now Hosted On GitHub', 's3bubble-amazon-web-services-oembed-media-streaming-support' ); ?></h3>
						<div class="inside">
							<h4>Download update or roll back to previous versions on GitHub get the latest release here <a href="https://github.com/s3bubble/wordpress-plugin/releases" target="_blank">https://github.com/s3bubble/wordpress-plugin/releases</a></h4>
							<a class="github-button" href="https://github.com/s3bubble/wordpress-plugin" data-size="large" data-show-count="true" aria-label="Star s3bubble/wordpress-plugin on GitHub">Star</a>
							<a class="github-button" href="https://github.com/s3bubble/wordpress-plugin/issues" data-size="large" data-show-count="true" aria-label="Issue s3bubble/wordpress-plugin on GitHub">Issue</a>
							<a class="github-button" href="https://github.com/s3bubble/wordpress-plugin/subscription" data-size="large" data-show-count="true" aria-label="Watch s3bubble/wordpress-plugin on GitHub">Watch</a>
						</div> 
					</div>
				</div>
				<div id="post-body">
					<div id="post-body-content" style="margin-right: 41%;">
						<div class="postbox"> 
							<h3 class="hndle"><?php echo __( 'Must Watch Setup Video', 's3bubble-amazon-web-services-oembed-media-streaming-support' ); ?></h3>
							<div class="inside">
								<div style="position: relative;padding-bottom: 56.25%;"><iframe style="position: absolute;top: 0;left: 0;width: 100%;height: 100%;" src="https://www.youtube.com/embed/7J8j7u293Fw" frameborder="0" allowfullscreen></iframe></div> 	
							</div><!-- .inside -->
						</div>

						<form method="post" action="options.php">
						    <?php settings_fields( 's3bubble-aws-self-hosted-plugin-settings-group' ); ?>
						    <?php do_settings_sections( 's3bubble-aws-self-hosted-plugin-settings-group' ); ?>
						    <table class="form-table">
						    	<tr valign="top">
						        <th scope="row"><?php echo __( 'Switch over to a Self Hosted Setup', 's3bubble-amazon-web-services-oembed-media-streaming-support' ); ?><br><small>Please ignore this if you are a new user.</small></th>
						        <td><input name="s3bubble_selfhosted_switch" type="checkbox" value="1" <?php checked( '1', get_option( 's3bubble_selfhosted_switch' ) ); ?> /></td>
						        </tr>
						    </table>
						    
						    <?php submit_button(); ?>

						</form>
					</div> <!-- #post-body-content -->
				</div> <!-- #post-body -->
			</div> <!-- .metabox-holder --> 
		</div> <!-- .wrap -->
		<?php	
       }

    }

	/*
	* Initiate the class
	* @author sameast
	* @none
	*/ 
	$s3bubble_oembed = new s3bubble_oembed();
	
}