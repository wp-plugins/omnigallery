<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

class SCPicasa
{

	var $dbOptionKey = 'SCPicasa_Options';
	
	var $cachePath = '';
	var $token;
	
	/**
	 * Constructor
	 */
	function SCPicasa()
	{
		$this->token = 'scpicasa-settings';
	}
	
	function register_flickr_admin_head()
	{        
		echo '<link rel="stylesheet" type="text/css" href="' . get_template_directory_uri() . '/functions/admin-style.css" media="screen" />';
		echo '<style type="text/css">'
			.'#panel-content .section .description { float:none; width:35% }'
			.'</style>';			
	}

	function getInstance()
	{
		global $scpicasa;
		
        if(!isset($scpicasa)){
		  
		}
		
		return $scpicasa;
	}

  function get_gallery_images( $attr = array() ){
        
    global $omnigallery, $scpicasa, $post;

    $sc_username_picasa = get_option('sc_username_picasa');
    if( empty($sc_username_picasa) ) return;
           
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

    $columns = intval($columns);
    $itemwidth = $columns > 0 ? floor(100/$columns) : 100;
    $float = is_rtl() ? 'right' : 'left';        
    $selector = "sc_gallery_picasa-{$instance}";
        
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
    
    $picasa_api = 'https://picasaweb.google.com/data/feed/api/user/'.get_option('sc_username_picasa').'?alt=json';
    
    $content = wp_remote_get($picasa_api);
    $result = json_decode($content['body'],true);
        
		foreach($result['feed']['entry'] as $value){
      
      $title = $value['title']['$t'];
      $image = $value['media$group']['media$content'][0]['url'];

      $return .= '
        <dl class="gallery-item">
          <dt class="gallery-icon">
            <a href="'.$image.'" title="'.$title.'" rel="lightbox-" target="_blank">
              <img src="'.$omnigallery->plugin_url.'/includes/timthumb.php?src='.$image.'&amp;w='.$width.'&amp;h='.$height.'&amp;zc=1&amp;q=90" alt="Picasa Gallery" class="sc-image" width="'.$width.'" height="'.$height.'"/>
            </a>
          </dt>
        </dl>';
            
		}
        
    $return .= '</div><!--/#gallery-->';
    
    return $return;
        
  }
    
}

?>