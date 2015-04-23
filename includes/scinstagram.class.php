<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

global $omnigallery;

// Instagram base URLs
define('SCINSTAGRAM_DEVELOPER_URL', 'http://instagram.com/developer/');

define('scinstagram_version', '1.0');

$scIncludePath = get_include_path().PATH_SEPARATOR.
						$omnigallery->plugin_path.'/includes/instagram-php-api/'.PATH_SEPARATOR.
						$omnigallery->plugin_path.'/includes/PowerHour_Geocoder/';
	
if(!set_include_path($scIncludePath)) 
	ini_set('include_path',	$scIncludePath);

require_once 'Instagram_XAuth.php';

function getLatLng($address){
	global $omnigallery;		
	require_once( $omnigallery->plugin_path . '/includes/PowerHour_Geocoder/Geocoder.php');			
			
	if(preg_match('/\d+\.d+,\d+\.\d+/', $address) > 0)
		{
				$result = explode(',', $address);
		}
	else if(strlen($address) > 0){
		$result = array();
		
		try
		{
					
		$geocoder = new PowerHour_Geocoder();
					
		$geocoder->mapFromAddress($address);
					
		$result[0] = $geocoder->getLatitude();
		$result[1] = $geocoder->getLongitude();
		}
		catch(Exception $ex){}
	}
			
			return $result;
}

class SCInstagram
{
			
	var $dbOptionKey = 'SCInstagram_Options';
	
	var $cachePath = '';
	var $token;
	
	/**
	 * Constructor
	 */
	function SCInstagram()
	{
		$this->token = 'scinstagram-settings';

		add_action( 'admin_menu', array( &$this, 'register_instagram_menu' ), 20 );
		
		add_shortcode('omnigallery_instagram', array(&$this, 'shortcode'));
		
		$this->cachePath = ABSPATH.'wp-content/cache/';
		
		add_action('wp_ajax_sc_paging', array(&$this, 'ajax_sc_paging'));
		add_action('wp_ajax_nopriv_sc_paging', array(&$this, 'ajax_sc_paging'));
		
		
	}
	
	function register_instagram_admin_head(){
    
        //global $omnigallery;
    
		echo '<style type="text/css">'
			.'#panel-content .section .description { float:none; width:35% }'
			.'</style>';
		
	}

	function getInstance()
	{
		global $SCInstagram;
		if(!isset($SCInstagram))
		{
			$SCInstagram = new SCInstagram();
		}
		
		return $SCInstagram;
	}
	
	function getAPIInstance()
	{
		$config = SCInstagram::getConfiguration();
		        
		$instagram = new Instagram_XAuth($config);
		
		$instagram->setAccessToken(SCInstagram::getAccessToken());
		
		return $instagram;
	}
	
	function install()
	{
		$this->getOptions();
	}
	
	function shortcode($params)
	{
		$values = shortcode_atts(array
								(
									'userid' => '',
									'size' => 85,
									'piccount' => 9,
									'effect' => false,
									'url' => false,
									'title' => 0,
									'paging' => 0,
									'max_id' => '',
									'like' => 0,
									'tag' => ''
								), 
								$params);

		
		// Default-size 150x150
		$picSize = (intval($values['size']) > 0) ? intval($values['size']) : 150;

		$page = intval($values['paging']);
		
		$beforeImage = '<div class="sc-shortcode-image %1$s" id="sc-shortcode-image-%2$d">';
		$imageHtml = '<img src="%1$s" ';

		if(!$this->imageAttributesDisabled())
		{
			$imageHtml .= 'width="%2$d" height="%2$d" ';
		}
		$imageHtml .= 'border="0" /></a></div>';

		$paginatorHtml = '<div class="sc-shortcode-pager">%s</div>';
		
		$buttonNextHtml = '<a href="'.get_bloginfo( 'wpurl' ).'" class="next-page-sc" rel="%d">'.__('Next', 'omnigallery').' &gt;&gt;</a>';
		
		$buttonPrevHtml = '<a href="'.get_bloginfo( 'wpurl' ).'" class="prev-page-sc" rel="%d">&lt;&lt; '.__('Previous', 'omnigallery').'</a>';
		
		$result = '<div class="sc-shortcode version-'.SCInstagram::getVersion().($page ? ' sc-shortcode-page' : '').'" id="sc-shortcode-page-'.$page.'">';
		
		if(!$values['url']) 
		{
			$result .= $this->getFeed($values, $imageHtml, $beforeImage, $picSize, $values['max_id']);
							
			
			if($page && strlen($values['max_id']) == 0)
			{
				$buttons = '';
				if($page > 1)$buttons .= sprintf($buttonPrevHtml, $page-1);
				
				$buttons .= sprintf($buttonNextHtml, $page+1);
				
				$paginator = sprintf($paginatorHtml, $buttons);
				
				
				$result = 	'<script type="text/javascript">var scConfig = '.json_encode($values).';</script>'.
							$paginator.
							'<div class="sc-gallery">'.
							$result;
			}
		}
		else 
		{
			$oEmbed = $this->getOEmbedImage($values['url']);
			
			$result .= sprintf($beforeImage, 'oembed', 0);
			
			if($values['effect']==true)
			{
				$result .= '<a href="'.$oEmbed->url.'" rel="fancybox" title="'.htmlentities($oEmbed->title).'">';
			}else{
				$result .= '<a href="'.$values['url'].'" target="_blank">';
			}				
			$result .= sprintf($imageHtml, $oEmbed->url, $picSize);
		}
		
		$result .= '</div>';
					
		if($page)
		{
			$result .= '</div>'.$paginator;
		}
		
		return $result;
	}
	
	function ajax_sc_paging()
	{
		$values = $_POST['config'];
		foreach($values as $key=>$value)
		{
			if(is_numeric($value))
			{
				$values[$key] = intval($value);
			}
		}
		$values['url'] = false;
		$values['max_id'] = $_POST['nextMaxId'];
		
		echo $this->shortcode($values);
		die(); 
	}
	
	function getFeed($values, $imageHtml, $beforeImage, $picSize, $nextMaxId = '')
	{
		$tagFeed = (!empty($values['tag']));
		$result = "";
		if(!$tagFeed)
		{
			$userid = $values['userid'];
			if(!is_numeric($values['userid']) && $values['userid'] != 'self' && $values['userid'] != 'feed' && strlen($values['userid']))
				$userid = SCInstagram::getUserIdByName($values['userid']);
		}
			
		$piccounter = 1;
	
		$odd = true;
		
		$lastShownId = $nextMaxId;
		
		do
		{
			$max_id = $nextMaxId;
			
			if(!$tagFeed)
				$data = SCInstagram::getFeedByUserId($userid, $max_id, $nextMaxId, intval($values['piccount']));
			else 
				$data = SCInstagram::getFeedByTag($values['tag'], $max_id, $nextMaxId, intval($values['piccount']));

			
			if(count($data) > 0)
			{
				foreach($data as $obj)
				{
					
					if(intval($values['piccount']) > 0 && $piccounter > $values['piccount'])
						break;
						
					
					$title = (intval($values['title']) == 1) ? $obj->caption->text : "";
					
				
					$result .= sprintf($beforeImage, (($odd) ? 'odd' : 'even'), $piccounter++);
					
					$odd = !$odd;
					
					
					$imageKey = SCInstagram::getImageKey($picSize);
					
					$result .= '<a href="'.$obj->link.'" target="_blank">';
					
					$result .= sprintf($imageHtml, $obj->images->$imageKey->url, $picSize);
											
					if($nextMaxId)
						$lastShownId = $obj->id;
					else
						$lastShownId = '';
				}
			}
			else
			{
				break;
			}
		}
		while($nextMaxId && ($piccounter <= $values['piccount'] || intval($values['piccount']) == 0));
		
		$result .= '<input type="hidden" id="sc-next-max-id-'.(intval($values['paging'])+1).'" value="'.$nextMaxId.'" />';
		
		return $result;
	}
	
	function getOEmbedImage($url)
	{
		$json = @file_get_contents('http://api.instagram.com/oembed?url='.$url);
		return json_decode($json);
	}
	
	
	function getFeedByUserId($userid, $max_id = '', $nextMaxId = 0, $count = 0)
	{	
		$writeToCache = true;
								
		$cacheid = $userid.($max_id ? "_".$max_id : "");
		
		if(SCInstagram::getInstance()->getFeedFromCache($cacheid))
		{
			$json = SCInstagram::getInstance()->getFeedFromCache($cacheid);
			$writeToCache = false;
		}
		
		else if(intval($userid) != 0 || $userid == 'self')
		{
			$json = SCInstagram::getAPIInstance()->getUserRecent($userid, $max_id, $count);
		}
		
		else if($userid == 'feed')
		{
			$json = SCInstagram::getAPIInstance()->getUserFeed($max_id);
		}
		
		else
		{
			$json = SCInstagram::getAPIInstance()->getPopularMedia();
		}
					
		$response = json_decode($json);
		
		if($writeToCache && $response->data)
			SCInstagram::getInstance()->writeFeedToCache($cacheid, $json);
			
		
		if($response->pagination)
			$nextMaxId = $response->pagination->next_max_id; 
		else 
			$nextMaxId = null;
			
			
		return $response->data;
	}
	
	function getFeedByTag($tag, $max_id = '', $nextMaxId = 0, $count = 0)
	{	
		$writeToCache = true;
								
		$cacheid = $tag.($max_id ? "_".$max_id : "");
		
		if(SCInstagram::getInstance()->getFeedFromCache($cacheid))
		{
			$json = SCInstagram::getInstance()->getFeedFromCache($cacheid);
			$writeToCache = false;
		}
		else
		{
			$json = SCInstagram::getAPIInstance()->getRecentTags($tag, $max_id);
		}
					
		$response = json_decode($json);
		
		if($writeToCache && $response->data)
			SCInstagram::getInstance()->writeFeedToCache($cacheid, $json);
			
		
		if($response->pagination)
			$nextMaxId = $response->pagination->next_max_id; // max_id für nächsten Request setzen
		else 
			$nextMaxId = null;
			
		return $response->data;
	}
	
	function getCacheFilename($cachename)
	{
		if(!$cachename)
			$cachename = 'popular-media';
		return $this->cachePath.'cache-'.$cachename.'.json';
	}
	
	function getDataFromCache($cachename)
	{
		
		$cacheFile = $this->getCacheFilename($cachename);
		
		
		if(is_readable($cacheFile) && filemtime($cacheFile) > strtotime('- '.$this->getOption('scinstagram_cache_time').' Minutes', time()))
		{
			return @file_get_contents($cacheFile);	
		}
		
		return false;	
	}
	
	function writeDataToCache($cachename, $json)
	{
		
		$cacheFile = $this->getCacheFilename($cachename);
		
		
		if($this->cacheIsEnabled())
		{
			@file_put_contents($cacheFile, $json);
			return true;
		}
		
		return false;
	}
	
	function cacheIsEnabled()
	{
		
		if(!is_dir($this->cachePath) && is_writable(ABSPATH.'wp-content/'))
		{
			
			return @mkdir($this->cachePath, 0755);
		}
		
	
		return is_writable($this->cachePath);
	}
	
	function getFeedFromCache($cachename)
	{
		return $this->getDataFromCache($cachename);
	}
	
	function writeFeedToCache($cachename, $json)
	{
		return $this->writeDataToCache($cachename, $json);
	}
	
	function getMediaFromCache($mediaId)
	{
		return $this->getDataFromCache('media-'.$mediaId);
	}
	
	function writeMediaToCache($mediaId, $json)
	{
		return $this->writeDataToCache('media-'.$mediaId, $json);
	}
	
	function getLocationBasedFeed($coordinates)
	{
		
		if(!empty($coordinates))
		{
			
			$cachename = implode('-', $coordinates);
			$cachename = str_replace('.', '_', $cachename);
			
			
			if(SCInstagram::getInstance()->getFeedFromCache($cachename))
			{
				$json = SCInstagram::getInstance()->getFeedFromCache($cachename);
			}
			else 
			{
				$json = SCInstagram::getAPIInstance()->mediaSearch($coordinates[0], $coordinates[1], null, null, 250);
				SCInstagram::getInstance()->writeFeedToCache($cachename, $json);
			}
			
			$response = json_decode($json);
			
			return $response->data;
		}
		
		return array();
	}
	
	function getImageTitle($imageId)
	{

		$json = $this->getMediaFromCache($imageId);
		
		
		if(!$json)
		{
			$json = $this->getAPIInstance()->getMedia($imageId);
			$writeToCache = true;
		}
		
		$media = json_decode($json);
		
		if($writeToCache && $media->data)
			SCInstagram::getInstance()->writeMediaToCache($imageId, $json);
		
		return $media->data->caption->text;
	}
	
	function getUserIdByName($name)
	{			
		if($name && $name != 'self')
		{
			$json = SCInstagram::getAPIInstance()->searchUser($name);
			
			$response = json_decode($json);
							
			$data = $response->data;
			
			if(count($data) > 0)
			{
				return $data[0]->id;
			}
		}
		else if($name == 'self')
		{
			return $name;
		}
		return 0;
	}
	
	function getOptions()
	{
	
		$options = array
		(
			'scinstagram_access_token' => '',
			'scinstagram_cache_time' => 30,
            'scinstagram_disable_effects' => '',
            'scinstagram_disable_image_attributes' => ''                
		);
		
		
		$saved = get_option($this->dbOptionKey);
		
		
		if(!empty($saved))
		{
			
			foreach($saved as  $key => $option)
			{
				$options[$key] = $option;
			}
		}
		
		
		if($saved != $options)
			update_option($this->dbOptionKey, $options);
			
		return $options;
	}
	
	function getPluginUrl()
	{
		return get_admin_url(null, 'admin.php?page=scinstagram-settings');
	}
	
	function getPluginDirUrl()
	{
		return trailingslashit(plugins_url('', __FILE__));
	}
	
	function getPluginDirPath()
	{
		return trailingslashit(plugin_dir_path(__FILE__));
	}
	
	function getOption($key)
	{
		$options = $this->getOptions();
		
		return $options[$key];
	}
	
	function handleOptions()
	{
		$options = $this->getOptions();
		
		
		if(isset($_POST['scinstagram-update-auth-settings']))
		{			
			$options = array();
			$options['scinstagram_user_username'] = trim($_POST['scinstagram-app-user-username']);
			$options['scinstagram_user_password'] = trim($_POST['scinstagram-app-user-password']);
			
			
			update_option($this->dbOptionKey, $options);
			
			$instagram = SCInstagram::getAPIInstance();
			
			if(!$options['scinstagram_access_token'])
			{
				
				$errorMessage = "";
				
				$token = $instagram->getAccessToken($errorMessage);
			
				
				if($token)
				{
					
					$options['scinstagram_access_token'] = $token;
					
					update_option($this->dbOptionKey, $options);
					
					echo '<div class="updated"><p>'.__('Settings saved.', 'omnigallery').'</p></div>';
				}
				else if($errorMessage) 
				{
					echo '<div class="error"><p>'.__('Instagram API reported the following error', 'omnigallery').': <b>';
					echo $errorMessage;
					echo '</b></p></div>';
				}
			}
		}
		
		else if(isset($_POST['scinstagram-reset-settings']))
		{
			
			delete_option($this->dbOptionKey);
		}
		
	
		if(isset($_POST['scinstagram-update-settings']))
		{
			
			$cacheTime = intval($_POST['scinstagram-cache-time']);
			if($cacheTime > 0)
			{
				$options['scinstagram_cache_time'] = $cacheTime;
			}
			$options['scinstagram_disable_effects'] = isset($_POST['scinstagram-disable-fancybox']);
			$options['scinstagram_disable_image_attributes'] = isset($_POST['scinstagram-disable-image-attr']);
			
			update_option($this->dbOptionKey, $options);
		}
		
		
		$authorizeUrl = $this->getOAuthRedirectUrl();
		
		include('scinstagram.options.php');
	}
	
	
	function getConfiguration()
	{
		$options = SCInstagram::getInstance()->getOptions();
		return array(
						'site_url' 		=> 'https://api.instagram.com/oauth/access_token',
			            'client_id' 	=> '0a344b64448b43e5bb8e1c22acffc0ef',
			            'client_secret' => 'ff62e43965be4a48b83a32261cd540bc',
						'username' 		=> $options['scinstagram_user_username'],
						'password' 		=> $options['scinstagram_user_password'],
			            'grant_type' 	=> 'password',
			            'redirect_uri'	=> SCInstagram::getOAuthRedirectUrl()
			        );
	}
	
	
	function register_instagram_menu()
	{
		
		$this->admin_page = add_submenu_page( 'omnigallery', __('Instagram settings', 'omnigallery'), __('Instagram settings', 'omnigallery'), 'manage_options', $this->token, array($this, 'handleOptions') );
		add_action( 'admin_print_styles-'.$this->admin_page, array( &$this, 'register_instagram_admin_head' ) );
	}

	function getPluginName()
	{
		return plugin_basename(__FILE__);
	}
	
	
	function imageAttributesDisabled()
	{
		return $this->getOption('scinstagram_disable_image_attributes');	
	}
	

	function getOAuthRedirectUrl()
	{
		return get_admin_url().'admin.php?page=scinstagram-settings';//.'instagram/oauth.php';
	}
	
	function getAccessToken()
	{
		$options = SCInstagram::getInstance()->getOptions();
		
		return $options['scinstagram_access_token'];
	}
	
	function getVersion()
	{
		return scinstagram_version;
	}
	
	function getImageKey($size)
	{
		if($size <= 150)
			return 'thumbnail';
		if($size <= 306)
			return 'low_resolution';
		
		return 'standard_resolution';
	}
			
	function isCurlInstalled() 
	{
		return in_array('curl', get_loaded_extensions());
	}
	
	function getErrors()
	{
		$errors = array();
		if(!SCInstagram::getInstance()->cacheIsEnabled())
			$errors[] = sprintf(__('To improve performance of this plugin, it is highly recommended to make the directory wp-content or wp-content/cache writable. For further information click <a target="_blank" href="%s">here</a>' , 'omnigallery'), 'http://codex.wordpress.org/Changing_File_Permissions');
		if(!SCInstagram::getInstance()->isCurlInstalled())
			$errors[] = __('Instagram requires <a href="http://php.net/manual/en/book.curl.php" target="_blank">PHP cURL</a> extension to work properly', 'omnigallery');
		if(!function_exists('mb_detect_encoding'))
			$errors[] = __('Geocoding won\'t work unless <a href="http://www.php.net/manual/en/mbstring.installation.php" target="_blank">mbstring</a> is activated', 'omnigallery');
			
		return (count($errors) > 0 ? $errors : false);
	}

    function get_gallery_images( $attr=array() ){
        global $omnigallery, $scinstagram, $post;

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
        
    	$type=get_option('sc_type_instagram');
    	$limit=get_option('sc_piccount_instagram');
    	$tag=get_option('sc_tag_instagram');
    	$address=get_option('sc_address_instagram');
    	$getlatlang = getLatLng($address);
    	if(empty($limit))$limit=10;
    	$nextMaxId = '';
    	$max_id = $nextMaxId;
    	$piccounter = 1;
    	$token = SCInstagram::getAccessToken();
        
        $instagramOptions = SCInstagram::getInstance()->getOptions();
		$accessToken = SCInstagram::getInstance()->getAccessToken();
        $user = $instagramOptions['scinstagram_user_username'];
        
        $values = array(
            'userid' => $user,
            'tag' => $tag,
            'piccount' => $limit,
        );
        
        if(empty($tag))
		{
			$user = $values['userid'];
			if(!is_numeric($values['userid']) && $type != 'popular' && $type != 'feed' && strlen($values['userid']))
				$user = SCInstagram::getUserIdByName($values['userid']);
		}
        
    	if(!empty($getlatlang))
    		$data = SCInstagram::getLocationBasedFeed($getlatlang);
    	else{
    		if(empty($tag)){ 
                $data = SCInstagram::getFeedByUserId($user, $max_id, $nextMaxId);
    		}else{ 
                $data = SCInstagram::getFeedByTag($tag, $max_id, $nextMaxId); }					
    	}
        
    	$columns = intval($columns);
    	$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
    	$float = is_rtl() ? 'right' : 'left';        
        $selector = "sc_gallery_instagram-{$instance}";
        
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

    	if(count($data) > 0){
    		if(get_option('sc_random_instagram')=='true') shuffle($data);	
    		foreach($data as $obj){
    		  
                if(intval($limit) > 0 && $piccounter > $limit) break;		
    						
    			$return .= '
                <dl class="gallery-item">
                    <dt class="gallery-icon">
						<a href="'.$obj->images->standard_resolution->url.'" title="'.$obj->caption->text.'" rel="lightbox-" target="_blank">
                            <img src="'.$omnigallery->plugin_url.'/includes/timthumb.php?src='.$obj->images->low_resolution->url.'&amp;w='.$width.'&amp;h='.$height.'&amp;zc=1&amp;q=90" alt="Instagram Gallery" class="sc-image" width="'.$width.'">
							<!--img src="'.$obj->images->low_resolution->url.'" alt="Instagram Gallery" class="sc-image '.$size.'" width="'.$width.'" height="'.$height.'"-->
						</a>
                    </dt>
                </dl>';
                
    			$piccounter++;
    		}
    	}
        
        $return .= '</div><!--/#gallery-->';
        

        //$return .= esc_attr( $instagramOptions['scinstagram_user_username'] ).' --- '.$accessToken.' --- '.$user;
        
    	return $return;        
    }

} // END of SCInstagram Class
	

