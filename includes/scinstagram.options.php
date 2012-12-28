
<style>
#main #panel-content .regular-text {
    margin: 5px 0 15px;
		width: 30%;
}
.form-wrap > div {
    float: left;
    width: 100%;
}
</style>
<div id="sc_options" class="wrap <?php if (get_bloginfo('text_direction') == 'rtl') { echo 'rtl'; } ?> sc_instagram">

	<div class="one_col wrap sc_container">
    
	<div id="main">
        
    <div id="panel-content">
	
	<?php
		$isPHP5 = (version_compare(phpversion(), '5.0.0', '>='));
		
		if(!$isPHP5)
		{
			$scErrors = array(sprintf(__('OmniGallery requires at least PHP 5.0 to work properly, your version is: %s', 'omnigallery'), phpversion()));
		}
		else
		{
			// Fehlermeldungen ausgeben 
			$scErrors = SCInstagram::getInstance()->getErrors();
		}
		
		if($scErrors): 
	?>
	<div class="error">
		<?php foreach($scErrors as $scError): ?>
		<p>
		 	<?php echo $scError; ?>
		</p>
		<?php endforeach; ?>
	</div>
	<?php 
		endif; // $scErrors 
	?>
	
	<?php if($isPHP5): ?>
    
	<form method="post">

    <!-- Instagram Account Settings -->
    <div class="group c1" id="sc-option-intaaccsettings">
        <h3 class="title"><?php _e( 'Instagram Account Settings', 'omnigallery' ); ?></h3>
    
        <table class="form-table">
            <tbody>
            
			<?php 
			$instagramOptions = SCInstagram::getInstance()->getOptions();
				
			if(SCInstagram::getInstance()->getAccessToken()):
				echo '<p class="success">';
				_e('Your application is authorized, have fun!', 'omnigallery');
				echo '</p>';
			?>
				<p>
					<input type="submit" class="button" name="scinstagram-reset-settings" value="<?php _e('Reset settings', 'omnigallery'); ?>" />
				</p>
                        
			<?php else: ?>
            
				<p><?php _e('To activate, just enter your Instagram username and password', 'omnigallery') ?>:</p>
                <tr valign="top">
                    <th scope="row"><label for="scinstagram-app-user-username"><?php _e('Username', 'omnigallery') ?></label></th>
                    <td><input class="regular-text" type="text" id="scinstagram-app-user-username" name="scinstagram-app-user-username" value="<?php echo esc_attr( $instagramOptions['scinstagram_user_username'] ) ?>" />
                    </td>
                </tr>
    
                <tr valign="top">
                    <th scope="row"><label for="scinstagram-app-user-password"><?php _e('Password', 'omnigallery') ?></label></th>
                    <td>
                        <input class="regular-text" type="password" id="scinstagram-app-user-password" name="scinstagram-app-user-password" value="<?php echo esc_attr( $instagramOptions['scinstagram_user_password'] ) ?>" />
                    </td>
                </tr>
                
                <tr valign="top"><td><p><input type="submit" class="button" name="scinstagram-update-auth-settings" value="<?php _e('Save settings', 'omnigallery'); ?>" /></p></td></tr>
        
			<?php endif;?>

            </tbody>
        </table>
        
    </div><!-- /#sc-option-intaaccsettings -->
    <!-- END OF Instagram Account Settings -->

    <!-- Instagram General settings -->
    <div class="group c1" id="sc-option-instageneralsettings">
        <h3 class="title"><?php _e( 'Instagram General settings', 'omnigallery' ); ?></h3>
    
        <table class="form-table">
            <tbody>
                <tr valign="top">
					<?php if(SCInstagram::getInstance()->cacheIsEnabled()): // Cache aktiv?	?>
                    <th scope="row"><label for="scinstagram-cache-time"><?php _e('Refresh cache after', 'omnigallery') ?></label></th>
                    <td>
						<?php $possibleCacheTimes = array(5, 10, 15, 30, 45, 60); ?>
						<select id="scinstagram-cache-time" name="scinstagram-cache-time">
							<?php foreach($possibleCacheTimes as $value): ?>
								<option <?php echo esc_attr( $instagramOptions['scinstagram_cache_time'] ) == $value ? ' selected="selected"' : '' ?>><?php echo $value ?></option>
							<?php endforeach; ?>
						</select><?php _e('minutes', 'omnigallery') ?>
                    </td>

					<?php else: // Cache inactive ?>
						<p><?php _e('Cache is not active', 'omnigallery'); ?></p>
					<?php endif; ?>
                </tr>
    
                <tr valign="top">
                    <th scope="row"><label for="scinstagram-disable-fancybox"><?php _e('FancyBox Effect', 'omnigallery'); ?></label></th>
                    <td>
                        <label for="scinstagram-disable-fancybox">
						<input type="checkbox" name="scinstagram-disable-fancybox" id="scinstagram-disable-fancybox" <?php echo esc_attr( $instagramOptions['scinstagram_disable_effects'] ) ? ' checked="checked"' : '' ?> />
						<?php _e('Disable fancybox effect', 'omnigallery'); ?></label>
                        <br />
						<span class="description">
							<?php _e('Note: Do only check this if you are having conflicts with other effects or if you do not want to use any effect', 'instagram'); ?>)
						</span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="scinstagram-disable-image-attr"><?php _e('Image Attribute', 'omnigallery'); ?></label></th>
                    <td>
						<label for="scinstagram-disable-image-attr">
						<input type="checkbox" name="scinstagram-disable-image-attr" id="scinstagram-disable-image-attr" <?php echo esc_attr( $instagramOptions['scinstagram_disable_image_attributes'] ) ? ' checked="checked"' : '' ?> />
						<?php _e('Disable width and height attribute for images (e.g. for responsive layouts)', 'omnigallery'); ?>
						</label>
                    </td>
                </tr>
                             
            </tbody>
        </table>
        
        <p><input type="submit" class="button" name="scinstagram-update-settings" value="<?php _e('Save settings', 'omnigallery'); ?>" /></p>
        
    </div><!-- /#sc-option-instageneralsettings -->
    <!-- END OF Instagram General settings -->
   
	</form>
    <?php endif; // ($isPHP5): ?>
	
    </div><!-- #panel-content -->

	</div><!-- #main -->

	</div><!-- .sc_container -->
    
</div><!-- #sc_options -->
