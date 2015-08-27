<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
  die ( 'Please do not load this screen directly. Thanks!' );
}

define('scflickr_version', '1.0');
require_once( 'flickr/wordpress-flickr.php' );

global $SCFlickr;
error_reporting(0);

class SCFlickr
{
      
  var $dbOptionKey = 'SCFlickr_Options';
  
  var $cachePath = '';
  var $token;
  
  /**
   * Constructor
   */
  function SCFlickr()
  {
    $this->token = 'scflickr-settings';
    add_action( 'admin_menu', array( &$this, 'register_flickr_menu' ), 20 );
  }
  
  function register_flickr_menu()
  {
    $this->admin_page = add_submenu_page('omnigallery', __( 'Flickr Settings', 'omnigallery' ), __( 'Flickr Settings', 'omnigallery' ), 'manage_options', $this->token, array( &$this, 'handleOptions' ) );
    add_action( 'admin_print_styles-'.$this->admin_page, array( &$this, 'register_flickr_admin_head' ) );
  }
  
  function register_flickr_admin_head()
  {        
    echo '<link rel="stylesheet" type="text/css" href="' . get_template_directory_uri() . '/functions/admin-style.css" media="screen" />';
    echo '<style type="text/css">'
      .'#panel-content .section .description { float:none; width:35% }'
      .'</style>';      
  }

  function get_gallery_images($attr = array()) {
    global $omnigallery, $SCFlickr, $post;
    static $instance = 0;
    $instance++;

    $attr = array_merge(array(
      'order'      => 'ASC',
      'orderby'    => 'menu_order ID',
      'id'         => $post->ID,
      'itemtag'    => 'dl',
      'icontag'    => 'dt',
      'captiontag' => 'dd',
      'columns'    => 3,
      'size'       => 'thumbnail',
      'include'    => '',
      'exclude'    => '',
      'width'      => '150',
      'height'     => '150',
    ), $attr);

    extract($attr);
        
    $id = intval($id);
      
    $api_key = get_option( 'sc_api_flickr' );
    $api_secret = get_option( 'sc_secret_flickr' );
    $user_id = get_option('sc_username_flickr');
    
    $flickr = new Colabs_WP_Flickr( $api_key, $api_secret );

    $user_photo = $flickr->get_user_photos( $user_id, get_option( 'sc_piccount_flickr' ) );

    $columns = intval($columns);
    $itemwidth = $columns > 0 ? floor(100/$columns) : 100;
    $float = is_rtl() ? 'right' : 'left';        
    $selector = "sc_gallery_flickr-{$instance}";
        
    $return = '';
    $size_class = sanitize_html_class( $size );
    $return .= "
      <style type='text/css'>
        #{$selector} {
          margin: auto;
          display: block
        }
        #{$selector}:after {
          width: 0;
          height: 0;
          clear: both;
          content: ' ';
          display: block;
          overflow: hidden;
          visibility: hidden;
        }
        #{$selector} .gallery-item {
          float: {$float};
          margin-top: 10px;
          text-align: center;
          width: {$itemwidth}%;
        }
        #{$selector} img {
          border: 2px solid #cfcfcf;
        }
        #{$selector} .gallery-caption {
          margin-left: 0;
        }
      </style>";
    
    $return .= "<div id='$selector' class='gallery galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class}'>";

    foreach( $user_photo['photos'] as $photo ) {

      $urlimg= $photo['image']['m'];
      $urlimgori= $photo['image']['o'];

      $return .= '
        <dl class="gallery-item">
          <dt class="gallery-icon">
            <a href="'.$urlimgori.'" title="'.$photo['title'].'" rel="lightbox-" target="_blank">
              <img src="'.$omnigallery->plugin_url.'/includes/timthumb.php?src='.$urlimg.'&amp;w='.$width.'&amp;h='.$height.'&amp;zc=1&amp;q=90" alt="Flickr Gallery" class="sc-image" width="'.$width.'" height="'.$height.'"/>
            </a>
          </dt>
        </dl>';
    }
        
    $return .= '</div><!--/#gallery-->';
        
    return $return;
  }
}