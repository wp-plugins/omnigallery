<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

global $scfacebook_name, $scfacebook_version;
$scfacebook_name       = "Facebook Photo Album";
$scfacebook_version    = "1.0.0";

//The Facebook application API key
global $appapikey, $appsecret;
$appapikey     = '547156575299608';
$appsecret     = '8d6457f099c8d705bde14fc8aafe9092';

//Wordpress Database Options (get_option())
global $option_fb_sess_key, $option_fb_sess_sec, $option_fb_sess_uid, $option_fb_sess_uname;
$option_fb_sess_key     = 'fb-session-key';    //The user's session key
$option_fb_sess_sec     = 'fb-session-secret'; //The user's session secret
$option_fb_sess_uid     = 'fb-session-uid';    //The user's UID
$option_fb_sess_uname   = 'fb-session-uname';  //The user's username

/**
  * Output the plugin's Admin Page 
  */
class SCFacebook
{
	var $token;
	
	function SCFacebook()
		{
			$this->token = 'scfacebook-settings';

			add_action( 'admin_menu', array( &$this, 'register_facebook_menu' ), 20 );
			
			
		}
	function register_facebook_admin_head(){
        
			echo '<link rel="stylesheet" type="text/css" href="' . get_template_directory_uri() . '/functions/admin-style.css" media="screen" />';
			echo '<style type="text/css">'
				.'#panel-content .section .description { float:none; width:35% }'
				.'</style>';
			
		}	
	function register_facebook_menu()
		{
			
			$this->admin_page = add_submenu_page('omnigallery', __( 'FB Album Settings', 'omnigallery' ), __( 'FB Album Settings', 'omnigallery' ), 'manage_options', $this->token, array( &$this, 'scfacebook_admin_page' ) );
			add_action( 'admin_print_styles-'.$this->admin_page, array( &$this, 'register_facebook_admin_head' ) );
		}
	function scfacebook_admin_page()
	{
		global $scfacebook_name;
			global $appapikey, $appsecret;
			global $option_fb_sess_key, $option_fb_sess_sec, $option_fb_sess_uid, $option_fb_sess_uname;
			
			?>
			<div id="sc_options" class="wrap <?php if (get_bloginfo('text_direction') == 'rtl') { echo 'rtl'; } ?> sc_facebook">
			<div class="one_col wrap sc_container">
			<div class="clear"></div>
			<?php
			//Show a warning if they're using a naughty other plugin
			if( class_exists('Facebook') )
			{
					?><div class="error"><p><strong><?php _e('Warning', 'omnigallery'); ?> :</strong> <?php _e('Another plugin has included the Facebook API throughout all of Wordpress.  I suggest you contact that plugin\'s author and ask them to include it only in pages where it\'s actually needed.', 'omnigallery'); ?><br /><br /><?php _e('Things may work fine as-is, but only if the API version included by the other plugin is at least as recent as the one required by Facebook Photo Fetcher.', 'omnigallery'); ?></p></div><?php
			}
			else
			{
                if(version_compare('5', PHP_VERSION, "<=")) require_once('facebook-platform/facebook.php');
                    else die("Sorry, but as of version 1.0.0, Facebook Photo Album requires PHP5.");
			}
					
			//Connect to Facebook and create an auth token.
			//Note: We only care about $token when the user is creating/saving a session; otherwise it's irrelevant and we just ignore it.
			$facebook = new Facebook($appapikey, $appsecret, null, true);
			
			$facebook->api_client->secret = $appsecret;
			$token = $facebook->api_client->auth_createToken();
			
			if(!$token) echo 'Failed to create Facebook authentication token!'; 

			//Check $_POST for what we're doing, and update any necessary options
			if( isset($_POST[ 'save-facebook-session']) )  //User connected a facebook session (login+save)
			{
					//We're connecting the useraccount to facebook, and the user just did STEP 2
					//We need to use the connection token to create a new session and save it,
					//which we'll use from now on to reconnect as the authenticated user.
					$token = $_POST[ 'save-facebook-session' ];
					try
					{
							$new_session = $facebook->api_client->auth_getSession($token);
					}
					catch(Exception $e)
					{
							$new_session = 0;
					}
					$errorMsg = 0;
					if( !$new_session )             $errorMsg = "Failed to get an authenticated session.";
					if( !$new_session['secret'])    $errorMsg = "Failed to get a session secret.";
				
					
					//Success!  Save the key, secret, userID, and username
					if( !$errorMsg )
					{
							$user = $facebook->api_client->users_getInfo($new_session['uid'], array('name'));
							update_option( $option_fb_sess_key, $new_session['session_key'] );
							update_option( $option_fb_sess_sec, $new_session['secret'] );
							update_option( $option_fb_sess_uid, $new_session['uid'] );
							update_option( $option_fb_sess_uname, $user[0]['name'] );
							?><div class="updated"><p><strong><?php echo 'Facebook Session Saved. (UID: ' . $new_session['uid'] . ')' ?></strong></p></div><?php
					}
					else
					{
							update_option( $option_fb_sess_key, '' );
							update_option( $option_fb_sess_sec, '' );
							update_option( $option_fb_sess_uid, '' );
							update_option( $option_fb_sess_uname, '' );
							?><div class="updated"><p><strong><?php echo 'An error occurred while linking with Facebook: ' . $errorMsg ?></strong></p></div><?php
					}
			}
			
			if( isset($_POST[ 'reset-facebook-session']) ) 
			{
							update_option( $option_fb_sess_key, '' );
							update_option( $option_fb_sess_sec, '' );
							update_option( $option_fb_sess_uid, '' );
							update_option( $option_fb_sess_uname, '' );
							?><div class="updated"><p><strong><?php echo 'Reset Facebook account successfully'; ?></strong></p></div><?php
			}
			//Get all the options from the database

			$session_key= get_option($option_fb_sess_key);
			$session_sec= get_option($option_fb_sess_sec);
			$my_uid     = get_option($option_fb_sess_uid);
			$my_name    = get_option($option_fb_sess_uname);
			
			//Finally, OUTPUT THE ADMIN PAGE.
			?>
		
			<?php //SECTION - Connect to Facebook.  See note at top of file.?>
			<div style="width:100%;padding-top:15px;"></div>
      <div class="clear"></div>
			<div id="main">
        
				<div id="panel-content">
				<div class="section">
					<h3 class="heading"><?php _e( 'Connect with Facebook', 'omnigallery' ); ?></h3>
					<div class="option">
					<?php
					if( $my_uid ) echo "<p><i>This plugin is successfully connected with <b>$my_name</b>'s Facebook account and is ready to create galleries.</i></p>";
					else          echo "<p>Before this plugin can be used, you must connect it with your Facebook account.</p><p>Please click the following button and complete the pop-up login form. <b><i>When finished, close the pop-up and click the save button to save your session</i></b>. You will only have to do this once.</p><p>If you failed save the session please check in your <a href='http://www.facebook.com/settings?tab=applications' target='-Blank'>Facebook App Settings</a> and remove it, then try to connect again.</p>";
					if( $my_uid ){
					?>
					<div id="step1wrap">
					<form method="post" id="step1Frm" action="">
						<input type="submit" class="button-secondary" id="step1Btn" value="<?php echo _e('Reset Account','omnigallery'); ?>" />
						<input type="hidden" name="reset-facebook-session" value="true" />
					</form>
					</div>
					
					<?php }else{?>
					<div id="step1wrap">
					<form method="get" id="step1Frm" action="http://www.facebook.com/login.php" target="_blank">
						<input type="hidden" name="api_key" value="<?php echo $appapikey ?>" />
						<input type="hidden" name="auth_token" value="<?php echo $token ?>" />
						<input type="hidden" name="popup" value="1" />      <?php //Style the window as a popup?>
						<input type="hidden" name="skipcookie" value="1" /> <?php //User must enter login info even if already logged in?>
						<input type="hidden" name="req_perms" value="offline_access,user_photos,friends_photos" /> <?php  //Require an infinite session?>
						<input type="hidden" name="v" value="1.0" />
						<input type="submit" class="button-secondary" id="step1Btn" value="<?php echo $my_uid?"Change Account":"Connect to Facebook"; ?>" />
					</form>
					</div>
					
					<div id="step2wrap" style="display:none;">
					<form method="post" action="">
						<input type="hidden" name="save-facebook-session" value="<?php echo $token ?>" />
						<input type="submit" class="button-primary" value="Save Facebook Session" />
					</form>
					</div>
								
					<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery('#step1Frm').submit(function(e) {
								e.preventDefault();
								jQuery('#step1wrap').toggle();
								jQuery('#step2wrap').toggle();
								
								var $form = jQuery(this),
										url = $form.attr('action'),
										param = $form.serialize();
										url = url + '?' + param + '&TB_iframe=true';
								
								window.open (url,"Facebook Login","menubar=1,resizable=0,width=500,height=350");
										
							});
					});
					</script>
					<?php }?>
					</div>
				</div>
				</div>	
				</div>
			</div>
			<?php
	}
    
    function get_gallery_images( $attr = array() ){
        
        global $omnigallery, $scfacebook, $post;
        
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
        
        if( is_null($gallery) ){ return; }
        
    	$columns = intval($columns);
    	$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
    	$float = is_rtl() ? 'right' : 'left';        
        $selector = "sc_gallery_facebook-{$instance}";
        
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
        
		$session_key= get_option('fb-session-key');
		$session_sec= get_option('fb-session-secret');
		if(($session_key!='')&&($session_sec!='')){
    		$defaults = array('hideHead'=> true);
    		$album_content = sc_facebook_fetch_album_content($gallery,$defaults);
    		$return .= $album_content['content'];
		}
        
        $return .= '</div><!--/#gallery-->';
        
        return $return;

    }
    
}


/* =======================================================
Album Output
==========================================================*/

function sc_facebook_fetch_album_content($aid, $params)
{
    //Combine optional parameters with default values
    $defaults = array('cols'    => 4,               //Number of columns of images (aka Number of images per row)
                      'start'   => 0,               //The first photo index to show (aka skip some initially)
                      'max'     => 99999999999,     //The max number of items to show
                      'swapHead'=> false,           //Swap the order of the 2 lines in the album header?
                      'hideHead'=> true,           //Hide the album header entirely?
                      'hideCaps'=> false,           //Hide the per-photo captions on the main listing?
                      'noLB'    => false,           //Suppress outputting the lightbox javascript?
                      'rand'    => false,           //Randomly select n photos from the album (or from photos between "start" and "max")
                      'isGroup' => false,           //The ID number specifies a GROUP ID instead of an albumID
                      'isPage'  => false,           //The ID number specifies a FAN PAGE ID instad of an albumID.  It'll return all photos in all albums on that page (for now).
                      'isEvent' => false,           //NOT YET SUPPORTED - the fql query doesn't return what it should...
                      'orderby' => 'normal');       //Can be "normal" or "reverse" (for now)
    $params = array_merge( $defaults, $params );
    $itemwidth = $params['cols'] > 0 ? floor(100/$params['cols']) : 100;
    $itemwidth -= (0.5/$params['cols']); //For stupid IE7, which rounds fractional percentages UP (shave off 0.5%, or the last item will wrap to the next row)
    $retVal = Array();
    
    //Connect to Facebook and restore our user's session
    global $appapikey, $appsecret;
    global $option_fb_sess_key, $option_fb_sess_sec;
    if( !class_exists('Facebook') )
    {
        if(version_compare('5', PHP_VERSION, "<=")) require_once('facebook-platform/facebook.php');
            else die("Sorry, but as of version 1.0.0, Facebook Photo Album requires PHP5.");
    }
    $facebook = new Facebook($appapikey, $appsecret, null, true);  
    $facebook->api_client->session_key  = get_option($option_fb_sess_key);
    $facebook->api_client->secret       = get_option($option_fb_sess_sec);

    //Get the specified album, its photos, and its author
    //(Different methods of fetching the photos albums, groups, pages, etc)
    if( $params['isGroup'] )
    {
        //NOTE: According to http://wiki.developers.facebook.com/index.php/Photos.get,
        //you should be able to do this for events too - but it photos_get always returns null.
        $group = $facebook->api_client->groups_get('', $aid, '');
        if( !$group )
        {
            $retVal['content'] = "Invalid Group ID ($aid)";
            return $retVal;
        }
        $group = $group[0];
        $photos = $facebook->api_client->photos_get($aid, '', '');
        $album['link'] = "http://www.facebook.com/group.php?gid=$aid";
        $album['name'] = $group['name'];
        $retVal['thumb'] = $group['pic_big'];
    }
    else if( $params['isPage'] )
    {
        $page = $facebook->api_client->pages_getInfo($aid, array('name', 'pic_big'), null, null);
        if( !$page )
        {
            $retVal['content'] = "Invalid Page ID ($aid)";
            return $retVal;
        }
        $page = $page[0];
        $photos = $facebook->api_client->fql_query("SELECT pid, aid, owner, src, src_big, src_small, link, caption, created FROM photo WHERE aid IN (SELECT aid FROM album WHERE owner = $aid)");
        $album['link'] = "http://www.facebook.com/profile.php?id=$aid";
        $album['name'] = $page['name'];
        $retVal['thumb'] = $page['pic_big']; 
    }
    else if( $params['isEvent'] )
    {
        $retVal['content'] = "Events not yet supported.";
        return $retVal;                        
    }
    else
    {
        $album = $facebook->api_client->photos_getAlbums(null, $aid);
        if( !$album )
        {
            $retVal['content'] = "Invalid Album ID ($aid)";
            return $retVal;
        }
        $album = $album[0];
        if( !$album )
        {
            $retVal['content'] = "Facebook Photo Album was unable to connect to Facebook.  Please check its options and verify that it's been associated with your account, then re-fetch this album.";
            return $retVal;
        }
        $photos = $facebook->api_client->photos_get(null, $aid, null);
        $author = $facebook->api_client->users_getInfo($album['owner'], array('name', 'profile_url'));
        $author = $author[0];
    }
    if( !is_array( $photos) ) $photos = array();
    $album['size'] = count($photos);
    
    //Store the filename of the album thumbnail when found
    //Note: we want the fullsize, because when WP uploads it it'll auto-resize it to an appropriate thumbnail for us.
    //We must do this here, prior to slicing down the array of photos.
    if( isset($album['cover_pid']) && !isset($retVal['thumb']) && isset($GLOBALS['add-from-server']) )
    {
        foreach($photos as $photo)
        {
            if( strcmp($photo['pid'],$album['cover_pid']) == 0 )
                $retVal['thumb'] = $photo['src_big'];
        }
    }
    
    //Reorder the photos if necessary
    if( $params['orderby'] == 'reverse' )
    {
        $photos = array_reverse($photos);
    }
    
    //Slice the photo array as necessary
    if( count($photos) > 0 )
    {
        //Slice the photos between "start" and "max"
        if( $params['start'] > $album['size'] )
        {
            $retVal['content'] .= "<b>Error: Start index ". $params['start']." is greater than the total number of photos in this album; Defaulting to 0.</b><br /><br />";
            $params['start'] = 0;
        }
        if( $params['max'] > $album['size'] - $params['start'] )
            $params['max'] = $album['size'] - $params['start'];
        $photos = array_slice($photos, $params['start'], $params['max']); 
        
        //If "rand" is specified, randomize the order and slice again
        if( $params['rand'] )
        {
            shuffle($photos);
            $photos = array_slice($photos, 0, $params['rand']);
        }
    } 
    $retVal['count'] = count($photos);
    
    //Create a header with some info about the album
    if(!$params['hideHead'])
    {
        $headerTitle  = 'From <a href="' . htmlspecialchars($album['link']) . '">' . $album['name'] . '</a>';
        if( isset($author) && isset($album['created']) )
        {
            $headerTitle .= ', posted by <a href="' . htmlspecialchars($author['profile_url']) . '">' . $author['name'] . '</a>';
            $headerTitle .= ' on ' . date('n/d/Y', $album['created']);
        }
        if( $retVal['count'] < $album['size'])$headerTitle .= ' (Showing ' . $retVal['count'] . ' of ' . $album['size'] . " items)\n";
        else                                  $headerTitle .= ' (' . $retVal['count'] . " items)\n";
        $headerTitle .= '<br /><br />';            
        if( $album['description'] ) $headerDesc = '"'.$album['description'].'"<br /><br />'."\n";
        else                        $headerDesc = "";
    } 

    //Output the album!  Starting with a (hidden) timestamp, then the header, then each photo.
    global $scfacebook_version, $omnigallery;
    $retVal['content'] .= "<!-- ID ". $aid ." Last fetched on " . date('m/d/Y H:i:s') . " v$scfacebook_version-->\n";
    if( $params['swapHead'] )   $retVal['content'] .= $headerTitle . $headerDesc;
    else                        $retVal['content'] .= $headerDesc . $headerTitle; 
    foreach($photos as $photo)
    {
        //Output this photo (must get rid of [], or WP will try to run it as shortcode)
        $caption = preg_replace("/\[/", "(", $photo['caption']);
        $caption = preg_replace("/\]/", ")", $caption);
        $caption = preg_replace("/\r/", "", $caption);
        
        $width = '150'; $height = '150'; $alt = __('Facebook Gallery', 'omnigallery');
        if( !empty($width) ){ $set_width = 'width = "' . $width . '"'; }else{ $set_width = ''; }
        if( !empty($height) ){ $set_height = 'height = "' . $height . '"'; }else{ $set_height = ''; }
        
        if(!$params['hideCaps'])$caption_no_br = htmlspecialchars(preg_replace("/\n/", " ", $caption));
        $link = '<a rel="lightbox-' . $aid . '" href="'.$photo['src_big'] . '" title="'.$caption_no_br.' " >';
        
        $link .= '<img src="'. $photo['src_big'] .'" alt="'.$alt.'" class="scfacebook-image" '. $set_width . $set_height . ' />';
        
        $link .= '</a>';
        
        $retVal['content'] .= '<dl class="gallery-item"><dt class="gallery-icon">';
		$retVal['content'] .= "$link";
		$retVal['content'] .= '</dt></dl>';
    }

    $retVal['content'] .= "<!-- End Album ". $aid ." -->\n";
    return $retVal;
}

?>