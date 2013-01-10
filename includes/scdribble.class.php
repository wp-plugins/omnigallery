<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

class DribbleAPI {
	// url to dribble api
	public $apiUrl = 'http://api.dribbble.com/';

	// dribble username or user id
	public $user;

	public function __construct($user)
	{
		$this->user = $user;
	}

	public function getPlayerShots($perPage = 15)
	{
		$user = $this->user;

		$json = wp_remote_get($this->apiUrl . 'players/' . $user . '/shots?per_page=' . $perPage);

		$array = json_decode($json['body']);

		$shots = $array->shots;

		return $shots;
	}
}

class SCDribble
{

	var $dbOptionKey = 'SCDribble_Options';
	
	var $cachePath = '';
	var $token;
	

	/**
	 * Constructor
	 */
	function SCDribble()
	{
		$this->token = 'scdribble-settings';
	}

	function getInstance()
	{
		global $scdribble;
		
        if(!isset($scdribble)){
		  
		}
		
		return $scdribble;
	}
	
    function get_gallery_images( $attr = array() ){
        
        global $omnigallery, $scdribble, $post;
        
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
        $selector = "sc_gallery_dribble-{$instance}";
        
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

		$dribbleAPI = new DribbleAPI(get_option('sc_username_dribble'));
		$shots = $dribbleAPI->getPlayerShots(get_option('sc_piccount_dribble'));

		if($shots) {
			foreach($shots as $shot) {
			$title = $shot->title;
			$urlimg= $shot->image_url;
            $return .= '
            <dl class="gallery-item">
                <dt class="gallery-icon">
					<a href="'.$urlimg.'" title="'.$title.'" rel="lightbox-" target="_blank">
						<img src="'.$omnigallery->plugin_url.'/includes/timthumb.php?src='.$urlimg.'&amp;w='.$width.'&amp;h='.$height.'&amp;zc=1&amp;q=90" alt="Picasa Gallery" class="sc-image" width="'.$width.'" height="'.$height.'"/>
                        <!--img src="'.$urlimg.'" alt="Picasa Gallery" class="sc-image" width="'.$width.'" height="'.$height.'"/-->
					</a>
                </dt>
            </dl>';
            }
		}
        
        $return .= '</div><!--/#gallery-->';
        
        return $return;
        
    }
    
}

?>