<!-- INSTAGRAM SETTINGS -->
<div class="group c1" id="sc-option-instagramsettings">

    <h3 class="title"><?php _e('Instagram Settings', 'omnigallery'); ?></h3>

    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row"><label for="sc_type_instagram"><?php _e('Type', 'omnigallery'); ?></label></th>

                <td><select class="sc-input" name="sc_type_instagram" id="sc_type_instagram">
					<?php
					foreach($sc_type_instaArr as $key => $type) {
                        if( !is_numeric($key)){ $value = $key; }else{ $value = $type; }
                       
						if($sc_settings['sc_type_instagram'] != $value) $selected = '';
						else $selected = ' selected';
						echo "<option value='$value'$selected>$type</option>\n";
					} ?>
                </select>
                <br />
                <span class="description"><em><?php _e('Select type to show the instagramPopular', 'omnigallery'); ?></em></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="sc_piccount_instagram"><?php _e('Count Instagram', 'omnigallery'); ?></label></th>
                <td>
                    <input class="regular-text" name="sc_piccount_instagram" id="sc_piccount_instagram" type="small-text" value="<?php if ( isset( $sc_settings['sc_piccount_instagram'] ) ) echo $sc_settings['sc_piccount_instagram'];?>"/>
                    <br/>
                    <span class="description"><label for="sc_piccount_instagram"><?php _e('Count of your Instagram image', 'omnigallery'); ?></label></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e('Random Instagram', 'omnigallery'); ?></th>

                <td>
                <label for="sc_random_instagram"><input type="checkbox" class="checkbox sc-input" name="sc_random_instagram" id="sc_random_instagram" <?php if ( $sc_settings['sc_random_instagram'] ) echo ' checked="yes"';?> />&nbsp;<?php _e('Random Instagram Image', 'omnigallery'); ?></label>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="sc_tag_instagram"><?php _e('Tag Instagram', 'omnigallery'); ?> (<?php _e('optional', 'omnigallery'); ?>)</label></th>

                <td><input class="regular-text" name="sc_tag_instagram" id="sc_tag_instagram" type="text" value="<?php if ($sc_settings['sc_tag_instagram']!="") echo $sc_settings['sc_tag_instagram'];?>"/><br /><span class="description"><label for="sc_tag_instagram"><?php _e('Tag', 'omnigallery'); ?> (<?php _e('Currently only one tag. Username is ignored.', 'omnigallery'); ?>)</label></span></td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="sc_address_instagram"><?php _e('Address', 'omnigallery'); ?>/<?php _e('Coordinates Instagram', 'omnigallery'); ?> (<?php _e('optional', 'omnigallery'); ?>)</label></th>

                <td><input class="regular-text" name="sc_address_instagram" id="sc_address_instagram" type="text" value="<?php if ($sc_settings['sc_address_instagram']!="") echo $sc_settings['sc_address_instagram'];?>"/><br /><span class="description"><label for="sc_address_instagram"><?php _e('Entry Address', 'omnigallery'); ?>/<?php _e('Coordinates instagram in here', 'omnigallery'); ?>.</label></span></td>
            </tr>
        </tbody>
    </table>

</div><!--/#instagramsettings-->
<!-- END OF INSTAGRAM SETTINGS -->


<!-- FLICKR SETTINGS -->
<div class="group c1" id="sc-option-flickrsettings">
    <h3 class="title"><?php _e('Flickr Settings', 'omnigallery'); ?></h3>

    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row"><label for="sc_api_flickr"><?php _e('Flickr API', 'omnigallery'); ?></label></th>

                <td><input class="regular-text" name="sc_api_flickr" id="sc_api_flickr" type="text" value="<?php if ($sc_settings['sc_api_flickr']!="") echo $sc_settings['sc_api_flickr'];?>"/>
                <br />
                <span class="description"><label for="sc_api_flickr"><?php _e('Entry Flickr API', 'omnigallery'); ?>.</label></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="sc_secret_flickr"><?php _e('Flickr Secret', 'omnigallery'); ?></label></th>
                <td>
                    <input class="regular-text" name="sc_secret_flickr" id="sc_secret_flickr" type="text" value="<?php if ($sc_settings['sc_secret_flickr']!="") echo $sc_settings['sc_secret_flickr'];?>"/>
                    <br/>
                    <span class="description"><label for="sc_secret_flickr"><?php _e('Entry Flickr Secret', 'omnigallery'); ?>.</label></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="sc_username_flickr"><?php _e('Flickr ID', 'omnigallery'); ?></label></th>

                <td>
                    <input class="regular-text" name="sc_username_flickr" id="sc_username_flickr" type="text" value="<?php if ($sc_settings['sc_username_flickr']!="") echo $sc_settings['sc_username_flickr'];?>"/>
                    <br />
                    <span class="description"><label for="sc_username_flickr"><?php _e('Enter your Flickr ID', 'omnigallery'); ?> (<a href="http://www.idgettr.com" target="_blank">idGettr</a>) <?php _e('in here', 'omnigallery'); ?>.</label></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="sc_piccount_flickr"><?php _e('Count Flickr', 'omnigallery'); ?></label></th>

                <td>
                <input class="regular-text" name="sc_piccount_flickr" id="sc_piccount_flickr" type="text" value="<?php if ($sc_settings['sc_piccount_flickr']!="") echo $sc_settings['sc_piccount_flickr'];?>"/>
                <br />
                <span class="description"><label for="sc_piccount_flickr"><?php _e('Enter the Flickr image limit', 'omnigallery'); ?>.</label></span>
                </td>
            </tr>

        </tbody>
    </table>

</div><!--/#flickrsettings-->
<!-- END OF FLICKR SETTINGS -->


<!-- PINTEREST SETTINGS -->
<div class="group c1" id="sc-option-pinterestsettings">
    <h3 class="title"><?php _e('Pinterest Settings', 'omnigallery'); ?></h3>

    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row"><label for="sc_username_pinterest"><?php _e('Pinterest Username', 'omnigallery'); ?></label></th>

                <td><input class="regular-text" name="sc_username_pinterest" id="sc_username_pinterest" type="text" value="<?php if ($sc_settings['sc_username_pinterest']!="") echo $sc_settings['sc_username_pinterest'];?>"/>
                <br />
                <span class="description"><label for="sc_username_pinterest"><?php _e('Enter your Pinterest username here', 'omnigallery'); ?>.</label></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="sc_piccount_pinterest"><?php _e('Count Pinterest', 'omnigallery'); ?></label></th>
                <td>
                    <input class="regular-text" name="sc_piccount_pinterest" id="sc_piccount_pinterest" type="text" value="<?php if ($sc_settings['sc_piccount_pinterest']!="") echo $sc_settings['sc_piccount_pinterest'];?>"/>
                    <br/>
                    <span class="description"><label for="sc_piccount_pinterest"><?php _e('Enter the image limit for pinterest', 'omnigallery'); ?>.</label></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="sc_board_pinterest"><?php _e('Specific Board', 'omnigallery'); ?> (<?php _e('optional', 'omnigallery'); ?>):</label></th>

                <td>
                    <input class="regular-text" name="sc_board_pinterest" id="sc_board_pinterest" type="text" value="<?php if ($sc_settings['sc_board_pinterest']!="") echo $sc_settings['sc_board_pinterest'];?>"/>
                    <br />
                    <span class="description"><label for="sc_board_pinterest"><?php _e('Enter the specific board for y pinterest', 'omnigallery'); ?></label></span>
                </td>
            </tr>

        </tbody>
    </table>

</div><!--/#pinterestsetting-->
<!-- END OF PINTEREST SETTINGS -->


<!-- PICASA SETTINGS -->
<div class="group c1" id="sc-option-picasasettings">
    <h3 class="title"><?php _e('Picasa Settings', 'omnigallery'); ?></h3>

    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row"><label for="sc_username_picasa"><?php _e('Picasa Username', 'omnigallery'); ?></label></th>

                <td><input class="regular-text" name="sc_username_picasa" id="sc_username_picasa" type="text" value="<?php if ($sc_settings['sc_username_picasa']!="") echo $sc_settings['sc_username_picasa'];?>"/>
                <br />
                <span class="description"><label for="sc_username_picasa"><?php _e('Enter your Picasa Username in here', 'omnigallery'); ?>.</label></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="sc_piccount_picasa"><?php _e('Count Picasa', 'omnigallery'); ?></label></th>
                <td>
                    <input class="regular-text" name="sc_piccount_picasa" id="sc_piccount_picasa" type="text" value="<?php if ($sc_settings['sc_piccount_picasa']!="") echo $sc_settings['sc_piccount_picasa'];?>"/>
                    <br/>
                    <span class="description"><label for="sc_piccount_picasa"><?php _e('Enter the image limit for Picasa', 'omnigallery'); ?>.</label></span>
                </td>
            </tr>

        </tbody>
    </table>
    
</div><!-- /#picasasettings -->
<!-- END OF PICASA SETTINGS -->

<!-- Dribble SETTINGS -->
<div class="group c1" id="sc-option-dribblesettings">
    <h3 class="title"><?php _e('Dribble Settings', 'omnigallery'); ?></h3>

    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row"><label for="sc_username_dribble"><?php _e('Dribble Username', 'omnigallery'); ?></label></th>

                <td><input class="regular-text" name="sc_username_dribble" id="sc_username_picasa" type="text" value="<?php if ($sc_settings['sc_username_dribble']!="") echo $sc_settings['sc_username_dribble'];?>"/>
                <br />
                <span class="description"><label for="sc_username_dribble"><?php _e('Enter your Dribble Username in here', 'omnigallery'); ?>.</label></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="sc_piccount_dribble"><?php _e('Count Dribble', 'omnigallery'); ?></label></th>
                <td>
                    <input class="regular-text" name="sc_piccount_dribble" id="sc_piccount_picasa" type="text" value="<?php if ($sc_settings['sc_piccount_dribble']!="") echo $sc_settings['sc_piccount_dribble'];?>"/>
                    <br/>
                    <span class="description"><label for="sc_piccount_dribble"><?php _e('Enter the image limit for Dribble', 'omnigallery'); ?>.</label></span>
                </td>
            </tr>

        </tbody>
    </table>
    
</div><!-- /#picasasettings -->
<!-- END OF Dribble SETTINGS -->