<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

class SCPinterest
{

	var $dbOptionKey = 'SCPinterest_Options';
	
	var $cachePath = '';
	var $token;
	
	/**
	 * Constructor
	 */
	function SCPinterest()
	{
		$this->token = 'scpinterest-settings';
	}
	
	function register_pinterest_admin_head()
	{        
		echo '<link rel="stylesheet" type="text/css" href="' . get_template_directory_uri() . '/functions/admin-style.css" media="screen" />';
		echo '<style type="text/css">'
			.'#panel-content .section .description { float:none; width:35% }'
			.'</style>';			
	}

	function getInstance()
	{
		global $scpinterest;
		
        if(!isset($scpinterest)){
		  
		}
		
		return $scpinterest;
	}
    
	function handleOptions()
	{			
		//include('theme-pinterest-options.php');
	}

    function get_gallery_images( $attr = array() ){
        
        global $omnigallery, $scpinterest, $post;
        
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
        $selector = "sc_gallery_pinterest-{$instance}";
        
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
        
		$user = get_option('sc_username_pinterest');
		$limit = get_option('sc_piccount_pinterest');
		$board = get_option('sc_board_pinterest');
		if(empty($limit))$limit=20;

		if(!empty($board))$feed_url = 'http://pinterest.com/'.$user.'/'.$board.'/rss'; 
		else $feed_url = 'http://pinterest.com/'.$user.'/feed.rss';	
		
		$latest_pins = sc_pinterest_get_rss_feed( $user, $limit, $feed_url );
		if(!empty( $latest_pins ) ){
            $ii=0;
			foreach ( $latest_pins as $item ):
						
				$rss_pin_description = $item->get_description();
				preg_match('/href="([^"]*)"/', $rss_pin_description, $link); $href = $link[1]; unset($link);	
				preg_match('/src="([^"]*)"/', $rss_pin_description, $image); $src = $image[1]; unset($image);				
				$pin_caption = strip_tags( $rss_pin_description );
				$date = $item->get_date('j F Y | g:i a');

                $return .= '
                <dl class="gallery-item">
                    <dt class="gallery-icon">
    					<a href="'.str_ireplace('_b.jpg','_c.jpg',$src).'" title="'.$pin_caption.'" rel="lightbox-" target="_blank">
    						<img src="'.$omnigallery->plugin_url.'/includes/timthumb.php?src='.$src.'&amp;w='.$width.'&amp;h='.$height.'&amp;zc=1&amp;q=90" alt="Pinterest Gallery" class="sc-image" width="'.$width.'" height="'.$height.'"/>
    					</a>
                    </dt>
                </dl>';
				
			endforeach;
		}
        
        $return .= '</div><!--/#gallery-->';
        
        return $return;

    }

}

/*-----------------------------------------------------------------------------------*/
/*  sc_pinterest_get_rss_feed */
/*-----------------------------------------------------------------------------------*/
if ( !function_exists('sc_pinterest_get_rss_feed') ) {
	function sc_pinterest_get_rss_feed( $pinterest_username, $number_of_pins_to_show, $feed_url ){				
		// Get a SimplePie feed object from the specified feed source.		
		$rss = fetch_feed( $feed_url );
		if (!is_wp_error( $rss ) ) : 
			// Figure out how many total items there are, but limit it to number specified
			$maxitems = $rss->get_item_quantity( $number_of_pins_to_show ); 
			$rss_items = $rss->get_items( 0, $maxitems ); 
		endif;		
		return $rss_items;
	}
}

?>