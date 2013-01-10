<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * OmniGallery Class
 *
 * @package WordPress
 * @subpackage OmniGallery
 * @author ColorLabs & Company
 * @since 1.0.0
 *
 */

Class OmniGallery
{
    private $file;
	public $version;

	function __construct($file){

		$this->version = '';
		$this->file = $file;
        
		/* Setup plugin path and URL. */
		$this->plugin_path = trailingslashit( str_replace( '/includes', '', dirname( __FILE__ ) ) );
		$this->plugin_url = trailingslashit( str_replace( '/includes', '', plugins_url( plugin_basename( dirname( __FILE__ ) ) ) ) );

		/* Cater for Windows systems where / is not present. */
		$this->plugin_path = trailingslashit( str_replace( 'includes', '', $this->plugin_path ) );
		$this->plugin_url = trailingslashit( str_replace( 'includes', '', $this->plugin_url ) );

		/* Setup assets path and URL. */
		$this->assets_path = trailingslashit( $this->plugin_path . 'assets' );
		$this->assets_url = trailingslashit( $this->plugin_url . 'assets' );
		
		/* Setup default name and token. */
		$this->name = __( 'OmniGallery', 'omnigallery' );
		$this->token = 'omnigallery';

		$this->load_plugin_textdomain();
        add_action( 'init', array( &$this, 'load_localisation' ), 0 );
        
		//add pages
		add_action( 'admin_menu', array( &$this, 'admin_screen_register') );   // Register menu
        add_action( 'admin_init', array( &$this, 'admin_init') );              // Register options
        add_action( 'admin_init', array( &$this, 'sc_auth_read') );
                
        add_filter( 'post_gallery', array(&$this, 'modify_gallery'), 20, 2 );
        add_filter( 'media_upload_tabs', array(&$this, 'media_upload_tabs') );
		add_action( 'media_upload_omnigallery', array(&$this, 'media_upload_omnigallery') );
        
	}

    function sc_auth_read() {
        if ( isset($_GET['frob']) ) {
            global $pf;
            $auth = $pf->auth_getToken($_GET['frob']);
            update_option('sc_flickr_token', $auth['token']['_content']);
            $pf->setToken($auth['token']['_content']);
            header('Location: ' . $_SESSION['phpFlickr_auth_redirect']);
            exit;
        }
    }

	function getInstance()
	{
		global $omnigallery;
		if(!isset($omnigallery))
		{
			$omnigallery = new OmniGallery();
		}
		
		return $omnigallery;
	}
        
	/**
	 * admin_styles function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	function admin_styles () {
		wp_register_style( $this->token . '-admin', $this->assets_url . 'css/admin.css', '', '1.0.0', 'screen' );
		
		wp_enqueue_style( $this->token . '-admin' );
	} // End admin_styles()
        
	/**
	 * load_localisation function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_localisation () {
		$lang_dir = trailingslashit( str_replace( 'classes', 'lang', basename( dirname(__FILE__) ) ) );
		load_plugin_textdomain( 'omnigallery', false, $lang_dir );
	} // End load_localisation()
    
	/**
	 * Load the plugin textdomain from the main WordPress "languages" folder.
	 * @since  1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'omnigallery';
	    // The "plugin_locale" filter is also used in load_plugin_textdomain()
	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	 
	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain()
    
	/**
	 * Run on activation of the plugin.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function activation() {
	    $this->install();
		$this->register_plugin_version();
	} // End activation()

	/**
	 * Log the current version of the plugin within the database.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function register_plugin_version () {
		if ( $this->version != '' ) {
			update_option( $this->token . '-version', $this->version );
		}
	} // End register_plugin_version()

    function admin_screen_register(){
        $hook = add_menu_page( $this->name, $this->name, 'manage_options', $this->token, array( $this, 'admin_page' ), $this->assets_url . 'images/menu-icon.png' );
        add_action( 'admin_print_styles-' . $hook, array( $this, 'admin_styles' ) );
        
    }

	/**
	 * Adds a "OmniGallery" tab to the "Add Media" panel.
	 *
	 * @param $tabs
	 * @return array
	 */
	function media_upload_tabs($tabs) {
		$tabs['omnigallery'] = 'OmniGallery';
		return $tabs;
	}
    
	/**
	 * Invokes the form to display the omnigallery insertion screen in the "Add Media" panel. The call to wp_iframe ensures that the right CSS and JS are called.
	 *
	 * @return void
	 */
	function media_upload_omnigallery() {
		wp_iframe(array(&$this, 'media_upload_omnigallery_form'));
	}
    
	/**
	 * First prints the standard buttons for media upload, then shows the UI for OmniGallery.
	 *
	 * @return void
	 */
	function media_upload_omnigallery_form() {
		media_upload_header();
		require_once( $this->plugin_path ."/omnigallery-form.php");
	}

	static function get_image_sizes_selection($element_name, $show_full = false) {
		global $_wp_additional_image_sizes;
		$image_sizes = array();
		$standard_sizes = array('thumbnail', 'medium', 'large');
		if ($show_full) {
			$standard_sizes[] = 'full';
		}
		foreach ($standard_sizes as $standard_size) {
			if ($standard_size != 'full') {
				$image_sizes[$standard_size] = array('width' => get_option($standard_size.'_size_w'), 'height' => get_option($standard_size.'_size_h'));
			}
			else {
				$image_sizes[$standard_size] = array('width' => __('Original width', 'omnigallery'), 'height' => __('Original height', 'omnigallery'));
			}
		}
		if (is_array($_wp_additional_image_sizes)) {
			$image_sizes = array_merge($image_sizes, $_wp_additional_image_sizes);
		}
		$ret = "<select name='$element_name'>";
		foreach ($image_sizes as $size_name => $size_attrs) {
			$ret .= "<option value='$size_name'>$size_name ({$size_attrs['width']} &times; {$size_attrs['height']})</option>";
		}
		$ret .= '</select>';
		return $ret;
	}
    
	/**
	 * Overrides the native gallery short code, and does a lot more.
	 *
	 * @param $content
	 * @param array $attr
	 * @return string
	 */
	function modify_gallery($content, $attr = array()) {
		global $post, $omnigallery, $scinstagram, $scfacebook, $scflickr, $omnigallery_default_gallery_type, $omnigallery_nested_shortcodes;
		if ($attr == null) {
			$attr = array();
		}

		$attr = array_merge(array(
			// Specially for OmniGallery
			'type' => $omnigallery_default_gallery_type,  //default, flickr, picasa
			'style' => 'default',   //default
			'id'         => $post->ID,
		), $attr);

		if ($omnigallery_nested_shortcodes) {
			$attr = array_map('do_shortcode', $attr);
		}

		extract($attr);

		$type = strtolower($type);

		switch ($type) {
			case 'instagram':
				if (!isset($scinstagram)) {
					$scinstagram = new SCInstagram();
				}
				$images = $scinstagram->get_gallery_images($attr);
				break;
			case 'flickr':
				if (!isset($scflickr)) {
					$scflickr = new SCFlickr();
				}
				$images = $scflickr->get_gallery_images($attr);
				break;
			case 'picasa':
				if (!isset($scpicasa)) {
					$scpicasa = new SCPicasa();
				}
				$images = $scpicasa->get_gallery_images($attr);
				break;
			case 'pinterest':
				if (!isset($scpinterest)) {
					$scpinterest = new SCPinterest();
				}
				$images = $scpinterest->get_gallery_images($attr);
				break;
			case 'facebook':
				if (!isset($scfacebook)) {
					$scfacebook = new SCFacebook();
				}
				$images = $scfacebook->get_gallery_images($attr);
				break;
			case 'dribble':
				if (!isset($scdribble)) {
					$scdribble = new SCDribble();
				}
				$images = $scdribble->get_gallery_images($attr);
				break;
					
		}

		if (isset($images) && is_array($images)) {
			if (isset($style)) {
				$gallery_html = $this->build_gallery($images, $style, $attr);
				return $gallery_html;
			}
		}
		else if (isset($images)) {
			return $images;
		}

		return $content;
	}
    
	/**
	 * Display Admin Page
	 */
	function admin_page(){
	
		global $current_user;
		$current_user = wp_get_current_user();
		?>
        
        <div id="omnigallery" class="wrap">
        
        	<div id="icon-options-general" class="icon32"><br/></div>
            <!--div id="icon-omnigallery" class="icon32"><br/></div-->
        	<h2><?php echo esc_html( $this->name ); ?> <span class="version"><?php echo esc_html( $this->version ); ?></span></h2>
            <p class="by-colorlabs"><?php _e( 'Powered by', 'omnigallery' ); ?><a href="http://colorlabsproject.com" title="ColorLabs"><img src="<?php echo $this->assets_url; ?>images/colorlabs.png" alt="ColorLabs" /></a></p>
            
        	<form method="post" action="options.php" id="options">
                
                <?php
                // Get array with all the options
                $sc_settings = $this->get_settings();
                // Make selects data
                $sc_type_instaArr = array( 'popular' => __('Popular','omnigallery'), 'self' => __('Self','omnigallery'), 'feed' => __('Feed','omnigallery'));
                
                ?>
                
        		<?php wp_nonce_field('update-options'); settings_fields('sc-options'); ?>            
            
                <?php require_once('omnigallery.admin.php'); ?>
                
                <input type="hidden" name="sc_action" value="update" />
                
                <p>
                <input type="hidden" name="sc_active_version" class="button-primary" value="<?php echo $sc_settings['sc_active_version']  ?>" />
                <input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save', 'instagrabber') ?>"/></p>
            
            </form><!--/#options-->
            
        </div><!--/#omnigallery-->
            
		<?php
	}
    
    // When plugin is activated, update version, and set any new settings to default
    function install() {
    		update_option('sc_active_version', '1.0.0');

            add_option('sc_type_instagram', 'popular' );
            add_option('sc_piccount_instagram', '10' );
            add_option('sc_random_instagram', '' );
            add_option('sc_tag_instagram', '' );
            add_option('sc_address_instagram', '' );
            
            add_option('sc_api_flickr', 'ac87048a9c9f196051db45de49f3830a' );
    		add_option('sc_secret_flickr', '79e03f86fd898330');
            add_option('sc_username_flickr', '' );
    		add_option('sc_piccount_flickr', '10');
            
            add_option('sc_username_pinterest', '' );
    		add_option('sc_piccount_pinterest', '10');
    		add_option('sc_board_pinterest', '');
            
    		add_option('sc_username_picasa', '113539730014413629030' );
    		add_option('sc_piccount_picasa', '10' );
            
            add_option('sc_flickr_token', '' );
    }
    
    function get_settings() {
    
    	$sc_settings=array(
            'sc_type_instagram'           => get_option('sc_type_instagram'),
            'sc_piccount_instagram'       => get_option('sc_piccount_instagram'),
            'sc_random_instagram'         => get_option('sc_random_instagram' ),
            'sc_tag_instagram'            => get_option('sc_tag_instagram'),
            'sc_address_instagram'        => get_option('sc_address_instagram'),
            
            'sc_api_flickr'               => get_option('sc_api_flickr' ),
    		'sc_secret_flickr'            => get_option('sc_secret_flickr'),
            'sc_username_flickr'          => get_option('sc_username_flickr'),
    		'sc_piccount_flickr'          => get_option('sc_piccount_flickr'),
            
    		'sc_username_pinterest'       => get_option('sc_username_pinterest'),
            'sc_piccount_pinterest'       => get_option('sc_piccount_pinterest'),
    		'sc_board_pinterest'          => get_option('sc_board_pinterest'),
            
    		'sc_username_picasa'          => get_option('sc_username_picasa'),
    		'sc_piccount_picasa'          => get_option('sc_piccount_picasa'),
			
			'sc_username_dribble'          => get_option('sc_username_dribble'),
    		'sc_piccount_dribble'          => get_option('sc_piccount_dribble'),
            
            'sc_flickr_token'             => get_option('sc_flickr_token'),
    	);
    
    	return $sc_settings;
    }

    function admin_init() {
            register_setting('sc-options', 'sc_type_instagram');
            register_setting('sc-options', 'sc_piccount_instagram');
            register_setting('sc-options', 'sc_random_instagram');
            register_setting('sc-options', 'sc_tag_instagram');
    		register_setting('sc-options', 'sc_address_instagram');
            
    		register_setting('sc-options', 'sc_api_flickr');
    		register_setting('sc-options', 'sc_secret_flickr');
    		register_setting('sc-options', 'sc_username_flickr');
    		register_setting('sc-options', 'sc_piccount_flickr');
            
            register_setting('sc-options', 'sc_username_pinterest');
    		register_setting('sc-options', 'sc_piccount_pinterest');
    		register_setting('sc-options', 'sc_board_pinterest');
            
    		register_setting('sc-options', 'sc_username_picasa');
    		register_setting('sc-options', 'sc_piccount_picasa');
            register_setting('sc-options', 'sc_flickr_token');
			register_setting('sc-options', 'sc_username_dribble');
    		register_setting('sc-options', 'sc_piccount_dribble');
    }

}
?>