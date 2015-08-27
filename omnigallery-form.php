<?php
/**
 * Creates a form in the "Add Media" screen under the new "OmniGallery" tab. This form lets you insert the gallery shortcode with
 * the right arguments for native WP galleries, Flickr, Picasa, SmugMug and 500px.
 *
 * @package OmniGallery
 * @subpackage UI
 */

$selected_tab = isset($_GET['omnigallery-tab']) ? esc_attr($_GET['omnigallery-tab']) : 'instagram';
if (!in_array($selected_tab, array('instagram', 'flickr', 'picasa', 'pinterest', 'facebook', 'dribble'))) {
	$selected_tab = 'instagram';
}

if (isset($_POST['omnigallery-submit'])) {
	$shortcode =  stripslashes($_POST['omnigallery-shortcode']);
	return media_send_to_editor($shortcode);
}
else if (isset($_POST['omnigallery-cancel'])) {
	return media_send_to_editor('');
}
?>
<script type="text/javascript">
	$j = jQuery.noConflict();

	function omnigalleryAdminHtmlEncode(value){
		return $j('<div/>').text(value).html();
	}

	$j(document).ready(function() {
        
		$j('#omnigallery-shortcode-form input[type="text"], #omnigallery-shortcode-form select').change(function(event) {
			var comboValues = $j('#omnigallery-shortcode-form').serializeArray();
			var newValues = new Array();
			var len = comboValues.length;
            
            if(len > 0){
    			for (var i=0; i<len; i++) {
    				var individual = comboValues[i];
    				if (individual['name'].trim() != 'omnigallery-shortcode' && individual['name'].trim() != 'omnigallery-submit' &&
    						individual['name'].trim() != 'omnigallery-cancel' && individual['value'].trim() != '') {
    					newValues.push(individual['name'] + "='" + omnigalleryAdminHtmlEncode(decodeURIComponent(individual['value'].trim())) + "'");
    				}
    			}
    
    			var shortcode = "[gallery type='<?php echo $selected_tab; ?>' ";
    			len = newValues.length;
    			for (var i=0; i<len; i++) {
    				shortcode += newValues[i] + ' ';
    			}
    			shortcode += ']';
            
			$j('#omnigallery-preview').text(shortcode);
			$j('#omnigallery-shortcode').val(shortcode);
            
            }
		});
		$j('#omnigallery-shortcode-form select').change();
        
		if ( !$j('#omnigallery-shortcode-form select').length ){
		  var shortcode = "[gallery type='<?php echo $selected_tab; ?>']";
		  $j('#omnigallery-preview').text(shortcode);
		  $j('#omnigallery-shortcode').val(shortcode);
        }
        
	});
</script>
<style type="text/css">
#omnigallery-shortcode-form p.prelude, #omnigallery-shortcode-form .button-panel{ clear: both; }
#omnigallery-shortcode-form .subsubsub{ float: none; }
#omnigallery-shortcode-form div.preview{ float: left; clear: both; }
</style>
<?php

$fields = array(
	'instagram' => array(
		'name' => __('Instagram', 'omnigallery'),
		'prelude' => __('You have to define your Instagram API Key under OmniGallery &rarr; Instagram Settings', 'omnigallery'),
		'fields' => array(),
	),
	'flickr' => array(
		'name' => __('Flickr', 'omnigallery'),
		'prelude' => __('You can define your Flickr API Key under OmniGallery Settings', 'omnigallery'),
		'fields' => array(),
	),
	'picasa' => array(
		'name' => __('Picasa', 'omnigallery'),
        'prelude' => __('You can define your Picasa Username under OmniGallery Settings', 'omnigallery'),
		'fields' => array(),
	),
	'pinterest' => array(
		'name' => __('Pinterest', 'omnigallery'),
		'prelude' => __('You can define your Pinterest Username under OmniGallery Settings', 'omnigallery'),
		'fields' => array(),
	),
	'dribble' => array(
		'name' => __('Dribble', 'omnigallery'),
        'prelude' => __('You can define your Dribble Username under OmniGallery Settings', 'omnigallery'),
		'fields' => array(),
	),
);

$tab_list = '';
$tab_fields = '';
$field_list = array();
$prelude = '';
foreach ($fields as $tab => $field_group) {
	$tab_list .= "<li><a href='".esc_url(add_query_arg(array('omnigallery-tab' => $tab)))."' class='".($tab == $selected_tab ? 'current' : '')."'>".esc_attr($field_group['name'])."</a> | </li>";
	if ($tab == $selected_tab) {
		$field_list = $field_group['fields'];
		$prelude = isset($field_group['prelude']) ? $field_group['prelude'] : '';
	}
}

echo "<form id='omnigallery-shortcode-form' method='post' action=''>";
echo "<ul class='subsubsub'>";
if (strlen($tab_list) > 8) {
	$tab_list = substr($tab_list, 0, -8);
}
echo $tab_list;
echo "</ul>";

if (!empty($prelude)) {
	echo "<p class='prelude'>"; print_r($prelude); echo "</p>";
}

echo "<table class='omnigallery-form'>";
echo "<tr>";
foreach ($field_list as $field) {
	echo "<tr>";
	echo "<th scope='row'>{$field['name']} ".(isset($field['req']) && $field['req'] ? '(*)' : '')." </th>";
	switch ($field['type']) {
		case 'text':
			echo "<td><input type='text' name='{$field['id']}' value='".(isset($field['std']) ? $field['std'] : '')."'/></td>";
			continue;
		case 'select':
			echo "<td><select name='{$field['id']}'>";
			foreach ($field['options'] as $option_name => $option_value) {
				echo "<option value='$option_name'>$option_value</option>";
			}
			echo "</select></td>";
			continue;
		case 'raw':
			echo "<td>".$field['std']."</td>";
			continue;
	}
	echo "<td class='hint'>".(isset($field['hint']) ? $field['hint'] : '')."</td>";
	echo "</tr>";
}
echo "</table>";

echo "<div class='preview'>";
echo "<h4>".__('Shortcode preview', 'omnigallery')."</h4>";
echo "<pre class='html' id='omnigallery-preview' name='omnigallery-preview'></pre>";
echo "<input type='hidden' id='omnigallery-shortcode' name='omnigallery-shortcode' />";
echo "</div>";

echo "<div class='button-panel'>";
echo get_submit_button(__('Insert into post', 'omnigallery'), 'primary', 'omnigallery-submit', false).' ';
echo get_submit_button(__('Cancel', 'omnigallery'), 'delete', 'omnigallery-cancel', false);
echo "</div>";
echo "</form>";
?>