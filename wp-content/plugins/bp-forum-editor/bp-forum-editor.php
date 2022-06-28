<?php
/**
 * Plugin Name: BuddyPress Forum Editor
 * Plugin URI: http://urlless.com/?p=2785
 * Description: This plugin provides your members with an easy to use Rich text editor for BuddyPress Group Forums.
 * Version: 1.0
 * Author: Taehan Lee
 * Author URI: http://urlless.com
 * License: GPLv2 or later
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'BuddyPressForumEditor' ) ) :

class BuddyPressForumEditor {

function __construct(){
	$this->plugin_id = 'bpfed';
	$this->plugin_ver = '1.0';
	$this->required_wp_ver = '3.9';
	$this->required_bp_ver = '2.0';
	
	$this->plugin_url = plugin_dir_url(__FILE__);
	$this->plugin_dir = plugin_dir_path(__FILE__);
	$this->image_subdir = $this->plugin_id;
	
	register_activation_hook( __FILE__, array($this, 'activation') );
	register_deactivation_hook( __FILE__, array($this, 'deactivation') );
	
	load_plugin_textdomain($this->plugin_id, false, dirname(plugin_basename(__FILE__)).'/');
	
	add_action( 'bp_init', array($this, 'init') );
}

function init(){
	// display Editor for BBPress forum
	add_filter( 'bbp_use_wp_editor', '__return_false');
	add_filter( 'bbp_get_the_content', array($this, 'get_the_bbp_editor'), 99, 2);
	
	// display Editor for BP built-in forum
	// topic post, site-wide
	add_action( 'groups_forum_new_topic_after', array($this, 'get_the_bp_topic_new_editor'));
	// topic post
	add_action( 'bp_after_group_forum_post_new', array($this, 'get_the_bp_topic_new_editor'));
	// reply post
	add_action( 'groups_forum_new_reply_after', array($this, 'get_the_bp_reply_new_editor'));
	// topic edit
	add_action( 'bp_group_after_edit_forum_topic', array($this, 'get_the_bp_topic_edit_editor'));
	// reply edit
	add_action( 'bp_group_after_edit_forum_post', array($this, 'get_the_bp_reply_edit_editor'));
	
	// allowed tags
	add_filter( 'bp_forums_allowed_tags', array($this, 'allowed_tags') );
	
	// scripts
	add_action( 'bp_enqueue_scripts', array($this, 'enqueue_scripts'), 99 );
	
	// image insert(mce plugin)
	add_action( 'wp_ajax_bpfed_image_js', array($this, 'image_insert_js') );
	add_action( 'wp_ajax_nopriv_bpfed_image_js', array($this, 'image_insert_js') );
	add_action( 'wp_ajax_bpfed_image_page', array($this, 'image_insert_page') );
	add_action( 'wp_ajax_nopriv_bpfed_image_page', array($this, 'image_insert_page') );
	
	// admin, install
	add_action( 'admin_init', array($this, 'admin_init') );
	if( bp_core_do_network_admin() ){
		add_action( 'network_admin_menu', array($this, 'admin_menu') );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
		$this->settings_page = 'settings.php';
		$this->settings_page_url = network_admin_url($this->settings_page);
		$this->settings_capability = 'manage_network_options';
	}else{
		add_action( 'admin_menu', array($this, 'admin_menu') );
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
		$this->settings_page = 'options-general.php';
		$this->settings_page_url = admin_url($this->settings_page);
		$this->settings_capability = 'manage_options';
	}
	$this->settings_page_slug = 'bpfed-settings';
}

function enqueue_scripts(){
	if( is_buddypress() ){
		wp_enqueue_style( $this->plugin_id.'-post', $this->plugin_url.'style.css', '', $this->plugin_ver);
	}
}
	
function get_the_bbp_editor($r, $args){
	$context = $args['context'];
	if( $context=='topic' || $context=='reply' ){
		remove_filter( 'bbp_get_form_' . $context . '_content', 'esc_textarea' );
		
		$post_content = call_user_func( 'bbp_get_form_' . $context . '_content' );
		
		$r = $this->get_the_editor('bbp_' . $context . '_content', $post_content);
	}
	return $r;
}

function get_the_bp_topic_new_editor(){
	echo $this->get_the_editor('topic_text');
}

function get_the_bp_reply_new_editor(){
	echo $this->get_the_editor('reply_text');
}

function get_the_bp_topic_edit_editor(){
	echo $this->get_the_editor('topic_text', bp_get_the_topic_text());
}

function get_the_bp_reply_edit_editor(){
	echo $this->get_the_editor('post_text', bp_get_the_topic_post_edit_text());
}

function get_the_editor($textarea_id, $post_content=''){
	ob_start();
	
	$settings = array(
		'wpautop'           => true,
		'media_buttons'     => false,
		'textarea_rows'     => 10,
		'teeny'             => false,
		'dfw'               => false,
		'tinymce'           => true,
		'quicktags'         => true,
	);
	
	$settings = apply_filters($this->plugin_id.'_mce_settings', $settings);
	$settings = apply_filters($this->plugin_id.'_'.$textarea_id.'_mce_settings', $settings);
	
	$this->add_mce_filters();
	
	wp_editor( $post_content, $textarea_id, $settings );
	
	$this->remove_mce_filters();
	?>
	<script>
	(function($){ 
		var ta = $('#<?php echo $textarea_id?>:not(.wp-editor-area)');
		var ed = $('#wp-<?php echo $textarea_id?>-wrap').addClass('bpfed-editor');
		ed.parents('form:eq(0)').addClass('bpfed-editor-form');
		if( ta.length ) ta.after(ed).remove(); 
	})(jQuery);
	</script>
	<?php
	return ob_get_clean();
}

function add_mce_filters(){
	add_filter( 'mce_buttons', array($this, 'mec_buttons'));
	add_filter( 'mce_buttons_2', '__return_empty_array');
	add_filter( 'quicktags_settings', array($this, 'mce_quicktags_settings'));
	add_filter( 'tiny_mce_before_init', array($this, 'mce_before_init'));
	add_filter( 'mce_external_plugins', array($this, 'mce_external_plugin'));
	add_filter( 'mce_css', array($this, 'mce_editor_style'));
}

function remove_mce_filters(){
	remove_filter( 'mce_buttons', array($this, 'get_buttons'));
	remove_filter( 'mce_buttons_2', '__return_empty_array');
	remove_filter( 'quicktags_settings', array($this, 'mce_quicktags_settings'));
	remove_filter( 'tiny_mce_before_init', array($this, 'mce_before_init'));
	remove_filter( 'mce_external_plugins', array($this, 'mce_external_plugin'));
	remove_filter( 'mce_css', array($this, 'mce_editor_style'));
}

function mec_buttons(){
	$opts = $this->get_option();
	return array_map('trim', explode(',', $opts['buttons']));
}

function mce_quicktags_settings( $settings = array() ) {
	$settings['buttons'] = 'strong,em,link,block,img,ul,ol,li,code,close';
	return $settings;
}

function mce_before_init($r){
	$r['fontsize_formats'] = '80% 90% 100% 120% 150% 200%';
	$r['block_formats'] = 'Paragraph=p;Pre=pre;Code=code;Heading 4=h4;Heading 5=h5;Heading 6=h6';
	return $r;
}

function mce_external_plugin($plugins) {
	$r['bpfed_image'] = admin_url('admin-ajax.php?action=bpfed_image_js', 'relative');
	return $r;
}

function mce_editor_style($ret){
	$ret .= ',' . $this->plugin_url.'editor-content.css?ver=' . $this->plugin_ver;
	return $ret;
}

function allowed_tags($_tags){
	$tags = array(
		'a' => array(
			'href' => true,
			'rel' => true,
			'target' => true,
			'title' => true,
		),
		'b' => array(),
		'big' => array(),
		'blockquote' => array(),
		'br' => array(),
		'code' => array(),
		'div' => array(),
		'em' => array(),
		'h4' => array(),
		'h5' => array(),
		'h6' => array(),
		'hr' => array(),
		'i' => array(),
		'img' => array(
			'src' => true,
			'alt' => true,
			'width' => true,
			'height' => true,
			'title' => true,
		),
		'li' => array(),
		'p' => array(),
		'pre' => array(),
		'span' => array(),
		'small' => array(),
		'strike' => array(),
		'del' => array(),
		'strong' => array(),
		'sub' => array(),
		'sup' => array(),
		'table' => array(
			'width' => true,
		),
		'thead' => array(),
		'tbody' => array(),
		'tfoot' => array(),
		'tr' => array(),
		'th' => array(
			'colspan' => true,
			'rowspan' => true,
			'width' => true,
			'height' => true,
		),
		'td' => array(
			'colspan' => true,
			'rowspan' => true,
			'width' => true,
			'height' => true,
		),
		'u' => array(),
		'ul' => array(),
		'ol' => array(),
	);
	
	$global_attr = array(
		'class' => true,
		'id' => true,
		'style' => true,
	);
	
	foreach($tags as $tag=>&$attr)
		$attr = array_merge($attr, $global_attr);
	
	$tags = apply_filters($this->plugin_id.'_allowed_tags', $tags);
	
	return $tags;
}

function image_insert_js(){
	header( 'Content-Type: application/javascript' );
	$url = admin_url('admin-ajax.php?action=bpfed_image_page', 'relative');
	?>
	tinymce.PluginManager.add('bpfed_image', function(editor) {
		editor.addButton('bpfed_image', {
			icon: 'image',
			tooltip: '<?php _e('Insert image', $this->plugin_id)?>',
			onclick: function(){
				editor.windowManager.open({
					title: '<?php _e('Insert image', $this->plugin_id)?>',
					padding: 0,
					height: 200,
					url:  '<?php echo $url;?>',
				});
			}
		});
	});
	<?php
	exit;
}

function image_insert_page(){
	$options = $this->get_option();
	$max_size = $options['max_image_upload'];
	$file_types = 'jpg,png,gif';
	$error = '';
	$img = '';
	if( isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], $this->plugin_id) ){
		if( !empty($_POST['url']) ){
			$img = '<img src="' . esc_url($_POST['url']) . '">';
		
		}elseif( !empty($_FILES['file']['size']) ){
			$file = $_FILES['file'];
			if( $file['size'] > $max_size*1024*1024){
				$error = sprintf(__('File size must be less than %s Mbytes.', $this->plugin_id), $max_size);
			}else{	
				add_filter( 'upload_dir', array($this, 'change_image_subdir') );
				$upload = wp_upload_bits($file['name'], null, @file_get_contents($file['tmp_name']));
				if( empty($upload['error']) ){
					$s = @getimagesize($upload['file']);
					if( !isset($s[0]) || 0 >= (int) $s[0] ){
						$error = sprintf(__('You can upload only image file(%s).', $this->plugin_id), $file_types);
						@unlink($upload['file']);
					}else{
						$img = '<img src="'.$upload['url'].'">';
					}
				}else{
					$error = $upload['error'];
				}
			}
		}
		
		if( $img ){
			echo "<script>var ed = parent.tinymce.activeEditor; ed.execCommand('mceInsertContent', false, '{$img}'); ed.windowManager.close();</script>";
			exit;
		}
	}
	?>
	<!DOCTYPE html><html><head><meta charset="<?=get_option('blog_charset')?>">
	<?php wp_print_scripts('jquery');?>
	<style>
	body { margin: 15px; max-width: 400px; font-family: sans-serif; font-size: 12px; line-height: 1.5; }
	label { font-weight: bold; margin-right: 10px; }
	.input { width: 100%; box-sizing: border-box; }
	.or-row { line-height: 2.5; color: #999; }
	.description { font-size: 11px; color: #999; margin-top: 3px; }
	.error { color: #c00; }
	.loader {background: #fff url(<?php echo includes_url('/images/wpspin-2x.gif')?>) no-repeat center center;background-color: rgba(255,255,255,.7);position: absolute;left: 0;top: 0;width: 100%;height: 100%;display: none;}
	.loading .loader { display: block;}
	</style>
	</head>
	<body>
	<form method="post" enctype="multipart/form-data">
		<?php wp_nonce_field($this->plugin_id); ?>
		<div class="url-row">
			<label><?php _e('URL', $this->plugin_id)?></label>
			<input type="text" name="url" class="input">
		</div>
		<div class="or-row">- <?php _e('or', $this->plugin_id)?> -</div>
		<div class="file-row">
			<label><?php _e('Upload', $this->plugin_id)?></label>
			
			<input type="file" name="file">
			<div class="description"><?php _e('File types', $this->plugin_id)?>: <?php echo $file_types?>,
			<?php _e('Max size', $this->plugin_id)?>: <?php echo $max_size?>Mbytes</div>
			
			<?php if( $error ){ ?>
			<div class="error"><?php echo $error?></div>
			<?php } ?>
		</div>
		<?php submit_button(__('Insert image', $this->plugin_id));?>
	</form>
	<span class="loader"></span>
	<script>
	jQuery('form').submit(function(){
		jQuery('body').addClass('loading');
	});
	</script>
	
	</body>
	</html>
	<?php
	exit;
}

function change_image_subdir($r){
	$subdir = '/' . trim($this->image_subdir, '/');
	$r['path'] = str_replace($r['subdir'], $subdir, $r['path']);
	$r['url'] = str_replace($r['subdir'], $subdir, $r['url']);
	$r['subdir'] = $subdir;
	return $r;
}

function plugin_action_links( $links, $file ) {
	if ( plugin_basename( __FILE__ ) == $file ){
		$links['settings'] = '<a href="' . add_query_arg( array( 'page' => $this->settings_page_slug ), $this->settings_page_url) . '">' . __( 'Settings', $this->plugin_id ) . '</a>';
	}
	return $links;
}

function get_option(){
	$r = get_option($this->plugin_id);
	
	if( empty($r['buttons']) )
		$r['buttons'] = $this->get_default_buttons();
	
	if( empty($r['max_image_upload']) )
		$r['max_image_upload'] = 2;
		
	return $r;
}

function get_default_buttons(){
	return 'fontsizeselect, bold, italic, underline, strikethrough, alignleft, aligncenter, alignright, blockquote, bullist, numlist, removeformat, link, unlink, bpfed_image';
}

function get_all_buttons(){
	return 'formatselect, fontsizeselect, forecolor, bold, italic, underline, strikethrough, alignleft, aligncenter, alignright, removeformat, undo, redo, link, unlink, pastetext, pasteword, bullist, numlist, blockquote, outdent, indent, charmap, hr, bpfed_image';
}


function admin_menu(){
	add_submenu_page( 
		$this->settings_page, 
		__('BuddyPress Forum Editor', $this->plugin_id), 
		__('BuddyPress Forum Editor', $this->plugin_id),
		$this->settings_capability, 
		$this->settings_page_slug, 
		array($this, 'admin_settings_page') 
	);
}

function admin_init(){
	register_setting($this->plugin_id.'_options', $this->plugin_id);
}

function admin_settings_page(){
	$options = $this->get_option();
	extract($options);
	?>
	<div class="wrap">
		<h2><?php _e('BuddyPress Forum Editor', $this->plugin_id); ?></h2>
		
		<?php settings_errors( $this->plugin_id ) ?>
		
		<form action="<?php echo admin_url('options.php')?>" method="post">
			
		<?php settings_fields($this->plugin_id.'_options'); ?>
		
		<table class="form-table">
			<tr>
				<th><?php _e('Buttons', $this->plugin_id)?></th>
				<td>
					<input type="text" name="<?php echo $this->plugin_id?>[buttons]" value="<?php echo $buttons;?>" class="large-text">
					
					<p class="description">
						<strong><?php _e('Available buttons', $this->plugin_id)?> :</strong>
						<br><?php echo $this->get_all_buttons();?>
					</p>
				</td>
			</tr>
			<tr>
				<th><?php _e('Max image upload', $this->plugin_id)?></th>
				<td>
					<input type="number" name="<?php echo $this->plugin_id?>[max_image_upload]" value="<?php echo $max_image_upload;?>" min=1 max=10>MB
				</td>
			</tr>
		</table>
		
		<?php submit_button(); ?>
		
		</form>
	</div>
	<?php
}

function activation(){
	$error = array();
	
	if ( version_compare( $GLOBALS['wp_version'], $this->required_wp_ver, '<' ) )
		$error[] = sprintf( __( '%1$s %2$s requires WordPress version %3$s or higher.', $this->plugin_id ), __('BuddyPress Forum Editor', $this->plugin_id), $this->plugin_ver, $this->required_wp_ver );
	
	if ( ! defined('BP_VERSION') || version_compare( BP_VERSION, $this->required_bp_ver, '<' ) )
		$error[] = sprintf( __( '%1$s %2$s requires BuddyPress version %3$s or higher.', $this->plugin_id ), __('BuddyPress Forum Editor', $this->plugin_id), $this->plugin_ver, $this->required_bp_ver );
	
	if( $error )
		exit( implode('<br>', $error) );
}

function deactivation(){
}

}

new BuddyPressForumEditor;

endif;