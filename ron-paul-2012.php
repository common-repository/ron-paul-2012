<?php
/*
	Plugin Name: Ron Paul 2012
	Plugin URI: http://perishablepress.com/ron-paul-2012/
	Description: Adds a "Ron Paul 2012" banner with a variety of display options
	Author: Jeff Starr
	Version: 2012-06-01a
	Usage: Visit the "Ron Paul 2012" options page, choose your settings, save changes, done.
*/

// require minimum version of WordPress
add_action('admin_init', 'require_wp_version');
function require_wp_version() {
	global $wp_version;
	$plugin = plugin_basename(__FILE__);
	$plugin_data = get_plugin_data(__FILE__, false);

	if (version_compare($wp_version, "3.3", "<")) {
		if (is_plugin_active($plugin)) {
			deactivate_plugins($plugin);
			$msg =  '<p><strong>' . $plugin_data['Name'] . '</strong> requires WordPress 3.3 or higher, and has been deactivated!</p>';
			$msg .= '<p>Please upgrade WordPress and try again.</p><p>Return to the <a href="' .admin_url() . '">WordPress Admin area</a>.</p>';
			wp_die($msg);
		}
	}
}

// include the required HTML markup
function ron_paul_2012(){ 
	$options = get_option('rp2012_options');
	if ($options['rp2012_enable'] == '1') { 
		$style = $options['rp2012_style'];

		if ($style == 'rp_circle') {
			$css_div  = 'position:fixed;right:5px;bottom:5px';
			$css_link = 'display:block;width:88px;height:88px;background:rgba(102,153,204,0.7);border:2px solid #efefef;box-shadow:1px 1px 3px 0 rgba(0,0,0,0.3);color:#fff;border-radius:99px;font-size:11px;text-align:center;line-height:88px;text-decoration:none;font-weight:bold';

		} elseif ($style == 'rp_banner') {
			$css_div  = 'position:fixed;right:0;bottom:5px';
			$css_link = 'display:block;width:120px;height:40px;background:rgba(255,255,255,0.7);border:1px solid rgba(102,153,204,0.7);border-right:none;border-top-left-radius:3px;border-bottom-left-radius:3px;box-shadow:1px 1px 3px 0 rgba(0,0,0,0.3);color:rgba(51,102,153,0.9);font-size:12px;text-align:center;line-height:40px;text-decoration:none;font-weight:bold';

		} elseif ($style == 'rp_ribbon') {
			$css_div = 'position:fixed;right:-60px;top:20px;z-index:9999';
			$css_link = 'display:block;width:200px;height:30px;line-height:30px;font-size:12px;text-align:center;border:1px solid rgba(255,255,255,0.7);text-decoration:none; color:#fff;background:rgba(102,153,204,0.9);-webkit-transform:rotate(40deg);-moz-transform:rotate(40deg);-ms-transform:rotate(40deg);-o-transform:rotate(40deg);z-index:9999';

		} elseif ($style == 'rp_custom') {
			$css_div  = $options['rp2012_custom_div'];
			$css_link = $options['rp2012_custom_link'];
		}
		echo "\n".'<div id="ron-paul-2012" style="'. $css_div .'"><a href="http://www.ronpaul2012.com/" target="_blank" style="'. $css_link .'">Ron Paul 2012</a><div>'."\n\n";
	}
}
// include GA tracking code via wp_footer
add_action('wp_footer', 'ron_paul_2012');

// display settings link on plugin page
function rp2012_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename(__FILE__) ) {
		$ga_links = '<a href="'. get_admin_url() .'options-general.php?page=ron-paul-2012/ron-paul-2012.php">'. __('Settings') .'</a>';
		array_unshift($links, $ga_links);
	}
	return $links;
}
add_filter( 'plugin_action_links', 'rp2012_plugin_action_links', 10, 2 );

// remove plugin settings after deletion
function rp2012_delete_plugin_options() {
	delete_option('rp2012_options');
}
register_uninstall_hook (__FILE__, 'rp2012_delete_plugin_options');

// define default settings
function rp2012_add_defaults() {
	$tmp = get_option('rp2012_options');

	if(($tmp['default_options'] == '1') || (!is_array($tmp))) {
		$arr = array (
			'rp2012_enable' => '0',
			'rp2012_style'  => 'rp_circle',
			'rp2012_custom_div' => 'position:fixed; right:5px; bottom:5px;',
			'rp2012_custom_link' => 'background:#fff; color:blue;'
		);
		update_option('rp2012_options', $arr);
	}
}
register_activation_hook (__FILE__, 'rp2012_add_defaults');

// define style options
$display_style = array(
	'rp_circle' => array(
		'value' => 'rp_circle',
		'label' => 'Circle'
	),
	'rp_banner' => array(
		'value' => 'rp_banner',
		'label' => 'Banner'
	),
	'rp_ribbon' => array(
		'value' => 'rp_ribbon',
		'label' => 'Ribbon'
	),
	'rp_custom' => array(
		'value' => 'rp_custom',
		'label' => 'Custom'
	)
);

// whitelist settings
add_action ('admin_init', 'rp2012_init');
function rp2012_init() {
	register_setting('rp2012_plugin_options', 'rp2012_options', 'rp2012_validate_options');
}

// sanitize and validate input
function rp2012_validate_options($input) {
	global $select_options, $display_style;

	if (!isset($input['rp2012_enable'])) $input['rp2012_enable'] = null;
	$input['rp2012_enable'] = ($input['rp2012_enable'] == '1' ? '1' : '0');

	if (!isset($input['rp2012_style'])) $input['rp2012_style'] = null;
	if (!array_key_exists($input['rp2012_style'], $display_style)) $input['rp2012_style'] = null;

	$input['rp2012_custom_div'] = wp_filter_post_kses($input['rp2012_custom_div']);
	$input['rp2012_custom_link'] = wp_filter_post_kses($input['rp2012_custom_link']);
	return $input;
}

// add the options page
add_action ('admin_menu', 'rp2012_add_options_page');
function rp2012_add_options_page() {
	add_options_page('Ron Paul 2012', 'Ron Paul 2012', 'manage_options', __FILE__, 'rp2012_render_form');
}

// create the options page
function rp2012_render_form() { 
	global $select_options, $display_style;
	if (!isset( $_REQUEST['settings-updated'])) $_REQUEST['settings-updated'] = false; ?>

	<script>
		jQuery(document).ready(function(){ 
			if(jQuery('form input[type=radio]:checked').val() == 'rp_custom'){
				jQuery('#custom_style').show();
			} else {
				jQuery('#custom_style').hide();
			}
			jQuery('form input:radio').change(function(){
				if (jQuery(this).val() == 'rp_custom') {
					jQuery('#custom_style').slideDown('fast');
				} else {
					jQuery('#custom_style').slideUp('fast');
				}
			});
		});
	</script>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php _e('Ron Paul 2012') ?></h2>
		<p><strong><?php _e('Instructions') ?>:</strong> <?php _e('Choose your options and click "Save Settings".') ?></p>

		<form method="post" action="options.php">
			<?php settings_fields('rp2012_plugin_options'); ?>
			<?php $options = get_option('rp2012_options'); ?>

			<table class="form-table">
				<tr>
					<th scope="row"><label class="description" for="rp2012_options[rp2012_enable]"><?php _e('Enable the plugin') ?></label></th>
					<td>
						<input name="rp2012_options[rp2012_enable]" type="checkbox" value="1" <?php if (isset($options['rp2012_enable'])) { checked('1', $options['rp2012_enable']); } ?> /> 
						<?php _e('Display "Ron Paul 2012" on your site?') ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Pick your style') ?></th>
					<td>
						<?php if (!isset($checked)) $checked = '';
							foreach ($display_style as $option) {
								$radio_setting = $options['rp2012_style'];
								if ('' != $radio_setting) {
									if ($options['rp2012_style'] == $option['value']) {
										$checked = "checked=\"checked\"";
									} else {
										$checked = '';
									}
								} ?>

								<label class="description">
									<input type="radio" name="rp2012_options[rp2012_style]" value="<?php esc_attr_e($option['value']); ?>" <?php echo $checked; ?> /> <?php echo $option['label']; ?>
								</label>
						<?php } ?>

					</td>
				</tr>
				<tr id="custom_style">
					<th scope="row"><?php _e('Define your own style') ?><br /><small>(<?php _e('Requires "Custom" option') ?>)</small></th>
					<td>
						<div>
							<label class="description" for="rp2012_options[rp2012_custom_div]"><?php _e('CSS for container') ?> <code>&lt;div&gt;</code></label><br />
							<textarea class="textarea" cols="50" rows="3" name="rp2012_options[rp2012_custom_div]"><?php echo esc_textarea($options['rp2012_custom_div']); ?></textarea>
						</div>
						<div>
							<label class="description" for="rp2012_options[rp2012_custom_link]"><?php _e('CSS for link') ?> <code>&lt;a&gt;</code></label><br />
							<textarea class="textarea" cols="50" rows="3" name="rp2012_options[rp2012_custom_link]"><?php echo esc_textarea($options['rp2012_custom_link']); ?></textarea>
						</div>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label class="description" for="rp2012_options[default_options]"><?php _e('Restore Default Settings') ?></label></th>
					<td>
						<input name="rp2012_options[default_options]" type="checkbox" value="1" <?php if (isset($options['default_options'])) { checked('1', $options['default_options']); } ?> /> 
						<?php _e('Restore defaults upon plugin deactivation/reactivation') ?><br /><em><?php _e('Hint: leave option unchecked to remember your settings'); ?></em>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Settings') ?>" />
			</p>
		</form>
		<ul style="width:177px; padding:20px; list-style-type:disc; border-top:1px solid #ccc;">
			<li><a href="http://www.ronpaul2012.com/" title="Visit Ron Paul's Official Website" target="_blank"><?php _e('Ron Paul Revolution') ?></a></li>
			<li><a href="http://monzilla.biz/" title="Monzilla Media" target="_blank">Get WordPress Help</a></li>
			<li><a href="http://perishablepress.com/ron-paul-2012/" target="_blank">Visit Plugin Page</a></li>
		</ul>
	</div>

<?php } ?>