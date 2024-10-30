<?php
	/**
	 * Plugin Name: ChatBot Blocker by CellarWeb
	Plugin URI: https://www.cellarweb.com/wordpress-plugins/
	 * Description: ChatBot Blocker by CellarWeb adds commands to the WP virtual robots.txt file to block various chatbots and AI Scanners from using your site content. .
	 * Version: 2.02
	 * Requires at least: 5.5
	 * Tested up to: 6.4
	 * Requires PHP: 7.2
	 * Author: Rick Hellewell - CellarWeb.com
	 * Author URI: https://profiles.wordpress.org/rhellewellgmailcom/
	 * License: GPL-2.0+
	 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
	 * Text Domain: cellarweb-chatbot-blocker
	 */

	/**
	 * Copyright 2023-2024  CellarWeb.com (Rick Hellewell)
	 *
	 * "ChatBot Blocker by CellarWeb" is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation, either version 2 of the License, or
	 * any later version.
	 *
	 * "ChatBot Blocker by CellarWeb" is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * "along with ChatBot Blocker by CellarWebr". If not, see http://www.gnu.org/licenses/gpl-2.0.txt.
	 */

	// Exit if accessed directly.
	if (!defined('ABSPATH')) {
		exit;
	}

	// Current version for display
	define("CWCB_VERSION", "2.02 (30 Aug 2024)"); // show version on settings screen

	// put options in a constant to ensure not multiple calls to the options table
	$options = get_option('CWCB_settings');
	define("CWCB_SETTINGS", $options);

	// ----------------------------------------------------------------
	// version checking: checking, notices, register/deregister, etc       BEGIN
	// ----------------------------------------------------------------

	// version checking
	$min_wp  = '4.9.6';
	$min_php = '7.3';
	if (!CWCB_is_requirements_met($min_wp, $min_php)) {
		add_action('admin_init', 'CWCB_disable_plugin');
		add_action('admin_notices', 'CWCB_show_notice_disabled_plugin', 10, 10);
		add_action('network_admin_init', 'CWCB_disable_plugin');
		add_action('network_admin_notices', 'CWCB_show_notice_disabled_plugin', 10, 10);
		CWCB_deregister();
		return;
		die("Plugin disabled due to PHP or WP version incompatibility.");
	}

	// --------------------------------------------------------------
	// register/deregister/uninstall hooks
	register_activation_hook(__FILE__, 'CWCB_register');
	register_deactivation_hook(__FILE__, 'CWCB_deregister');
	register_uninstall_hook(__FILE__, 'CWCB_uninstall');

	/**
	 * Dynamically create the robots.txt file with our saved content.
	 *
	 * @since   1.00
	 * @uses    get_option
	 * @uses    esc_attr
	 * @param string $output The contents of robots.txt filtered.
	 * @param string $public The visibility option.
	 * @return  string
	 */
	function CWCB_robots_option_content($output, $public) {
		$content = get_option('CWCB_chatbot_content');
		if ($content) {
			$output = esc_attr(wp_strip_all_tags($content));
		}
		return $output;
	}

	/**
	 * Deactivation hook. Deletes our option containing the robots.txt content.
	 *
	 * @since   1.00
	 * @uses    delete_option
	 * @return  void
	 */
	function CWCB_chatbot_deactivation() {
		delete_option('CWCB_chatbot_content');
	}

	/**
	 * Activation hook.  Adds the option we'll be using.
	 *
	 * @since   1.00
	 * @uses    add_option
	 * @return  void
	 */
	function CWCB_chatbot_activation() {
		add_option('CWCB_chatbot_content', false);

		// Backwards compatibility.
		$old = get_option('cw_chatbot_block_content');
		if (false !== $old) {
			update_option('CWCB_chatbot_content', $old);
			delete_option('cw_chatbot_block_content');
		}
	}

	// register/deregister/uninstall options (even though there aren't options)
	function CWCB_register() {
		add_option('CWCB_chatbot_content');
		return;
	}

	function CWCB_deregister() {
		return;
	}

	function CWCB_uninstall() {
		delete_option('CWCB_chatbot_content');
		return;
	}

	// --------------------------------------------------------------
	// check if at least WP 4.6 and PHP version at least 5.3
	// based on https://www.sitepoint.com/preventing-wordpress-plugin-incompatibilities/
	function CWCB_is_requirements_met($min_wp = '4.6', $min_php = '7.3') {
		// Check for WordPress version
		if (version_compare(get_bloginfo('version'), $min_wp, '<')) {
			return false;
		}
		// Check the PHP version
		if (version_compare(PHP_VERSION, $min_php, '<')) {
			return false;
		}
		return true;
	}

	// --------------------------------------------------------------
	// disable plugin if WP/PHP versions are not enough
	function CWCB_disable_plugin() {
		if (is_plugin_active(plugin_basename(__FILE__))) {
			deactivate_plugins(plugin_basename(__FILE__));
			// Hide the default "Plugin activated" notice
			if (isset($_GET['activate'])) {
				unset($_GET['activate']);
			}
		}
	}

	// --------------------------------------------------------------
	// show notice that plugin was deactivated because WP/PHP versions not enough
	function CWCB_show_notice_disabled_plugin() {
		echo '<div class="notice notice-error is-dismissible"><h3><strong>CellarWeb Chatbot Blocker </strong></h3><p> cannot be activated - requires at least WordPress 4.6 and PHP 5.4.&nbsp;&nbsp;&nbsp;Plugin automatically deactivated.</p></div>';
		return;
	}

	// --------------------------------------------------------------
	// admin notice if something failed
	function CWCB_admin_notice_generic_error($theerrors = array('Something did not work correctly!')) {
		foreach ($the_errors as $the_error) {
			echo "<div class='notice notice-error is-dismissible'><h3><strong>CellarWeb Chatbot Blocker  </strong></h3><p> - Error: $the_error .</p></div>";
		}
		return;
	}

	// ----------------------------------------------------------------
	// version checking: checking, notices, register/deregsiter, etc       END
	// ----------------------------------------------------------------

	// ----------------------------------------------------------------
	//  set up all the plugin stuff           BEGIN
	// ----------------------------------------------------------------

	add_action('admin_menu', 'CWCB_add_admin_menu'); // adds to the admin menu
	add_action('admin_init', 'CWCB_settings_init'); // does register_setting, add_settings_section add_settings_field, etc

	function CWCB_add_admin_menu() {

		add_options_page('Chatbot Blocker by CellarWeb', 'Chatbot Blocker by CellarWeb', 'manage_options', 'cellarweb_chatbot_blocker', 'CWCB_options_page');
	}

	function CWCB_remove_footer_admin() {
		echo '';
		return;
	}

	// ============================================================================
	// Add settings link on plugin page
	// ----------------------------------------------------------------------------
	function CWCB_settings_link($links) {
		$settings_link = '<a href="options-general.php?page=cellarweb_chatbot_blocker" title="CellarWeb Chatbot Blocker Settings Page">Settings Page</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	// ============================================================================
	// link to the settings page
	// ----------------------------------------------------------------------------
	$plugin = plugin_basename(__FILE__);
	add_filter("plugin_action_links_$plugin", 'CWCB_settings_link');

	function CWCB_settings_init() {
		// Force remove of the thank you on plugin screen
		add_filter('admin_footer_text', 'CWCB_remove_footer_admin');

		// get some CSS loaded for the settings page
		wp_register_style('CWCB_namespace', plugins_url('/css/settings.css', __FILE__), array(), CWCB_VERSION);
		wp_enqueue_style('CWCB_namespace'); // gets the above css file in the proper spot

		register_setting('CWCB_option_group', // option group name
			'CWCB_settings', // option name (used to store into wp-options
			array(
				'sanitize_callback' => 'CWCB_sanitize_data' // sanitize the data function'
			)
		);

		add_settings_section(
			'CWCB_CWCB_option_group_section', // id slug of the section
			__('CellarWeb Chatbot Blocker  to enable', // string for the title of the section
				'CWCB_namespace'),
			'CWCB_settings_section_callback', // function for text below the settings title
			'CWCB_option_group' // slug name of the settings page
		);

		// ----------------------------------------------------------------
		//  all the settings fields - BEGIN

		/* syntax for add_settings_field
		- $id = string to put in the ID of the setting tag
		- $title = the 'title' of the field (will be put to the left of the  input)
		- $callback = function to render the field
		- $page - page name of the field; match the 4th parameter of add_settings_section
		- $args = array for additional settings
		'label_for' => attributes to put in the label tag
		'class' => class name to put in the class attribute the TR used to display the inut field

		// 27 = stop comment posting direct access = CWCB_stop_comment_direct_access
		add_settings_field(
		'CWCB_chatbot_enable', // field name
		__('', 'CWCB_namespace'), // message before field (not used)
		'CWCB_render_chatbot', //
		'CWCB_option_group', // plugin page name
		'CWCB_CWCB_option_group_section' // plugin section name
		);

		/*      template for new fields
		// xx = desc = fieldname

		add_settings_field(
		'CWCB_',                   // field name
		__( '', 'CWCB_namespace' ),    // message before field (not used)
		'CWCB_render_happy',      // render function
		'CWCB_option_group',                   // plugin page name
		'CWCB_CWCB_option_group_section'      // plugin section name
		);

		 */

		// ----------------------------------------------------------------
		//  all the settings fields setup       END
		// ----------------------------------------------------------------

		// ----------------------------------------------------------------
		// add actions that will display admin-type messages
	}

	// ----------------------------------------------------------------
	// end of the admin area / settings setup
	// ----------------------------------------------------------------

	// ----------------------------------------------------------------
	// render the fields on the page via do_settings          BEGIN
	/* NOTE (since version 3.00)
	- all rendering of settings area is done via one call to CWCB_render_fields()
	- the 'render' element in the add_settings for each field is set to CWCB_render_happy(), which doesn't do anything - and is not used.
	 */

	// ----------------------------------------------------------------
	// end of rendering fields area via do_settings         END
	// ----------------------------------------------------------------

	// ----------------------------------------------------------------
	// render all fields bypassing the do_settings(0 function, which puts everything in a table - BEGIN
	// ----------------------------------------------------------------
	function CWCB_render_fields() {
// temp
	?>
<div class="CWCB_settings_fields CWCB_box_cyan">


<h3 class='CWCB_h3' ><br><br>ChatBot AI Scanner Blocking</h3>

    <p><b>Blocks AI ChatBot Scanners from scanning your site (and using your site content in AI programs)</b> by adding blocking directives to the WordPress-generated virtual robots.txt file.  Does not affect scanning content by search engines, and does not affect any SEO on your site. The virtual robots.txt file directives used by your WordPress site are shown below. Note that if you have an actual robots.txt file in your site root, the virtual robots.txt directives will not be used.</p>

<?php
			// enable chatbot blocking
			add_filter('robots_txt', 'CWCB_robots_option_content', 109, 2); // filter to add robots
			$content = CWCB_robots_build_content(); // displays virtual robots.txt file on the settings screen
			CWCB_robots_settings_info($content);
	//	}
	?>

</div>

<?php
	return;
	}

	// ----------------------------------------------------------------
	// callback to sanitize the form posting
	//	- here only for plugin compatibility, as there are no user-supplied settings to save
	//      $input should contain all of the form post data
	//         @param array $input Contains all settings fields as array keys
	//      $new_input returned to let the API store the data in wp-options table


	function CWCB_sanitize_data($input) {
		// need at least one array value,  just in case nothing checked, or errors caused on render functions trying to read array items from a null string stored in the wp-option
		$new_input = array('CellarWeb' => 'Private Functions');

		if (isset($_POST['CWCB_chatbot_enable'])) {
			$new_input['CWCB_chatbot_enable'] = "1";} else { $new_input['CWCB_chatbot_enable'] = "0";}

		return $new_input;
	}

	// ----------------------------------------------------------------
	//  end of validation area
	// ----------------------------------------------------------------

	// ----------------------------------------------------------------
	// display into text at the top of the section
	//	- here only for plugin compatibility, as there are no user-supplied settings to save
	// ----------------------------------------------------------------
	function CWCB_settings_section_callback() {
		return;
	}

	// ----------------------------------------------------------------
	// Outputs settings page with information. No user-supplied settings to save
	// ----------------------------------------------------------------

	function CWCB_options_page() {
	?>
<div align='center' class = 'CWCB_header'>
     <img src="<?php echo plugin_dir_url(__FILE__); ?>assets/banner-1000x200.jpg" width="95%"  alt="" class='CWCB_shadow'>
<p align='center'>Version
<?php echo CWCB_VERSION; ?></p>
</div>
<div>
<div class='CWCB_settings'>
    <div class='CWCB_options'>
        <form action='options.php' method='post'>
        <?php
        		settings_fields('CWCB_option_group'); // initializes all of the settings fields
        		CWCB_render_fields(); // render fields without do_settings so no table codes
        	?>
<?php
;?>
        </form>
    </div>
</div>
</div>
    <div class='CWCB_sidebar'>
        <?php CWCB_sidebar();?>
    </div>
<div class='CWCB_footer'>
    <?php CWCB_footer();?>
</div>
<?php
	}

	// ----------------------------------------------------------------

	// CSS for settings page
	// Add stylesheet
	function CWCB_load_css() {
		wp_register_style('CWCB_regular_css', plugins_url('/css/settings.css', __FILE__));
		wp_enqueue_style('CWCB_regular_css'); // gets the above css file in the proper spot
	}
	add_action('wp_enqueue_scripts', 'CWCB_load_css');

	// ----------------------------------------------------------------
	// additional place for intro text under the header on the settings page

	function CWCB_intro_text() {
	return;
	}

	// ----------------------------------------------------------------
	//  this does the work of enabling the selected options  BEGIN
	// ----------------------------------------------------------------

	// add robots directives
   //	if (!empty(CWCB_SETTINGS['CWCB_chatbot_enable'])) {
		// enable chatbot blocking
		// priority set to 109 if CellarWeb Privacy and Security plugin also enables chatbot blocking, so that this plugin will show this plugin branding
		add_filter('robots_txt', 'CWCB_robots_build_content', 109, 2); // filter to add robots
   //	}

	// -------------------------------------------------------------
	//  end of all features activation code
	// --------------------------------------------------------------

	// --------------------------------------------------------------
	// end of add_actions setting
	// --------------------------------------------------------------

	// --------------------------------------------------------------
	// display the bottom info part of the page
	// --------------------------------------------------------------
	function CWCB_add_copyright_footer() {
		// print copyright with current year, never needs updating
		$xstartyear    = "2013";
		$xname         = "Rick Hellewell";
		$xcompanylink1 = ' <a href="http://cellarweb.com" title="CellarWeb" style="text-decoration:underline !important;color:blue !important;" >CellarWeb.com</a>';
		// leave this empty if no company 2
		$xcompanylink2 = '';
		// output
		echo '<p id="site-info" align="center"   >Design and implementation Copyright &copy; ' . $xstartyear . '  - ' . gmdate("Y") . ' by ' . $xname . ' and ' . $xcompanylink1;
		if ($xcompanylink2) {
			echo ' and ';
			echo $xcompanylink2;
		}
		echo ' , All Rights Reserved.</p> ';
		return;
	}

	// --------------------------------------------------------------
	// set up a current_year shortcode to display the current year
	// --------------------------------------------------------------
	function CWCB_current_year() {
		return gmdate("Y");
	}

	// ============================================================================
	//  settings page sidebar content
	// --------------------------------------------------------------
	function CWCB_sidebar() {
	?>
<h3 class='CWCB_h3'  align="center">But wait, there's more!!</h3>
<p><b>Secure your site</b> with the '<a href="https://wordpress.org/plugins/cellarweb-privacy-and-security-options/" target="_blank" title="Privacy and Security Options">Privacy and Security Options by CellarWeb</a>' plugin. Over 20 different settings to help secure your site and keep information private. Includes the Chatbot Blocker. Analyzes potential security risks. Block Comment spam. Shows hidden plugins and lists admin-level users that might be a risk. And more!</p>
<p><b>Totally eliminate comment spam</b> with our <a href="https://wordpress.org/plugins/block-comment-spam-bots/" target="_blank">Block Comment Spam Bots</a> plugin. No more automated comment spam - it's very effective.</p>
<p>There's our plugin that will automatically add your <strong>Amazon Affiliate code</strong> to any Amazon links - even links entered in comments by others!&nbsp;&nbsp;&nbsp;Check out our nifty <a href="https://wordpress.org/plugins/amazolinkenator/" target="_blank">AmazoLinkenator</a>! It will probably increase your Amazon Affiliate revenue!</p>
<p>We've got a <a href="https://wordpress.org/plugins/simple-gdpr/" target="_blank"><strong>Simple GDPR</strong></a> plugin that displays a GDPR banner for the user to acknowledge. And it creates a generic Privacy page, and will put that Privacy Page link at the bottom of all pages.</p>
<p>How about our <strong><a href="https://wordpress.org/plugins/url-smasher/" target="_blank">URL Smasher</a></strong> which automatically shortens URLs in pages/posts/comments?</p>
<p><a href="https://wordpress.org/plugins/blog-to-html/" target="_blank"><strong>Blog To HTML</strong></a> : a simple way to export all blog posts (or specific categories) to an HTML file. No formatting, and will include any pictures or galleries. A great way to convert your blog site to an ebook.</p>
<hr />
<p><strong>To reduce and prevent spam</strong>, check out:</p>
<p> <a href="https://wordpress.org/plugins/block-comment-spam-bots/" target="_blank"><b>Block Comment Spam Bots</b></a> plugin totally blocks all automated comment spam! No more automated comment spam - it's very effective. A total rewrite of our FormSpammerTrap for Comments plugin.</p>
<p><a href="https://wordpress.org/plugins/formspammertrap-for-comments/" target="_blank"><strong>FormSpammerTrap for Comments</strong></a>: reduces spam without captchas, silly questions, or hidden fields - which don't always work. </p>
<p><a href="https://wordpress.org/plugins/formspammertrap-for-contact-form-7/" target="_blank"><strong>FormSpammerTrap for Contact Form 7</strong></a>: reduces spam when you use Contact Form 7 forms. All you do is add a little shortcode to the contact form.</p>
<p>And check out our <a href="https://www.FormSpammerTrap.com" target="_blank"><b>FormSpammerTrap</b></a> code for Word Press and non-Word Press sites. A contact form that spam bots can't get past!</p>
<hr />
<p>For <strong>multisites</strong>, we've got:
    <ul>
    <li><strong><a href="https://wordpress.org/plugins/multisite-comment-display/" target="_blank">Multisite Comment Display</a></strong> to show all comments from all subsites.</li>
    <li><strong><a href="https://wordpress.org/plugins/multisite-post-reader/" target="_blank">Multisite Post Reader</a></strong> to show all posts from all subsites.</li>
    <li><strong><a href="https://wordpress.org/plugins/multisite-media-display/" target="_blank">Multisite Media Display</a></strong> shows all media from all subsites with a simple shortcode. You can click on an item to edit that item.
    </li>
    </ul>
    </p>
    <hr />
    <p><strong>They are all free and fully featured!</strong></p>
    <hr />
        <p>I don't drink coffee, but if you are inclined to donate because you like my Word Press plugins, go right ahead! I'll grab a nice hot chocolate, and maybe a blueberry muffin. Thanks!</p>
<div align='center'><?php CWCB_donate_button();?></div>
<hr />
<p><strong>Privacy Notice</strong>: This plugin does not store or use any personal information or cookies.</p>
<hr>
<?php
	CWCB__cellarweb_logo();

		return;
	}

	// ----------------------------------------------------------------------------
	// footer for settings page
	// ----------------------------------------------------------------------------
	function CWCB_footer() {
	?>
<p align="center"><strong>Copyright &copy; 2016-<?php echo gmdate('Y'); ?> by Rick Hellewell and  <a href="http://CellarWeb.com" title="CellarWeb" >CellarWeb.com</a> , All Rights Reserved. Released under GPL2 license. <a href="http://cellarweb.com/contact-us/" target="_blank" title="Contact Us">Contact us page</a>.</strong></p>
<?php
	return;
	}

	// ============================================================================
	// PayPal donation button for settings sidebar (as of 25 Jan 2022)
	// ----------------------------------------------------------------------------
	function CWCB_donate_button() {
	?>
<form action="https://www.paypal.com/donate" method="post" target="_top">
<input type="hidden" name="hosted_button_id" value="TT8CUV7DJ2SRN" />
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>

<?php
	return;
	}

	// ----------------------------------------------------------------------------
	function CWCB__cellarweb_logo() {
	?>
 <p align="center"><a href="https://www.cellarweb.com" target="_blank" title="CellarWeb.com site"><img src="<?php echo plugin_dir_url(__FILE__); ?>assets/cellarweb-logo-2022.jpg"  width="90%" class="AZLNK_shadow" ></a></p>
 <?php
 	return;
 	}

 	// ----------------------------------------------------------------------------
 	// show robots.txt output if option enables
 	//      - also will add_filter to enable changes
 	// ----------------------------------------------------------------------------

 	function CWCB_robots_build_content() {
 		$botlist = array(
 			"ChatGPT" => "GPTBot",
 			"Bard" => "Bard",
 			"Bing" => "bingbot-chat/2.0",
 			"Common Crawl" => "CCBot",
 			"omgili" => "Omgili",
 			"omgilibot" => "Omgili Bot",
 			"Diffbot" => "Diffbot",
 			"MJ12bot" => "MJ12bot",
 			"anthropic-ai" => "anthropic-ai",
 			"ClaudeBot" => "ClaudeBot",
 			"FacebookBot" => "FacebookBot",
 			"Google-Extended" => "Google-Extended",
 			"SentiBot" => "SentiBot",
 			"sentibot" => "sentibot",
			// updated 9 Mar 2024
			"Twitterbot"	=> "Twitterbot",
			"AhrefsBot"		=> "AhrefsBot",
			"CCBot"			=> "CCBot",
			"AwarioRssBot"	=> "AwarioRssBot",
			"AwarioSmartBot"=> "AwarioSmartBot",
			"Claude-Web"	=> "Claude-Web",
			"FacebookBot"	=> "FacebookBot",
			"magpie-crawler"	=> "magpie-crawler",
			"peer39_crawler"	=> "peer39_crawler",
			"PerplexityBot"		=> "PerplexityBot",
			"CrystalSemanticsBot"	=> "CrystalSemanticsBot",
			"Applebot"		=> "Applebot",
 		);

 		$directive = "\n# Added by CellarWeb Chatbot Blocker plugin Version " . CWCB_VERSION . " (begin)\n";
 		$directive .= "# These directives block access by AI scanners. \n\n";
 		foreach ($botlist as $name => $agent) {
 			$directive .= "  #  Blocks " . $name . " bot scanning \n";
 			$directive .= str_repeat(" ", 8) . "User-agent: " . $agent . "\n" . str_repeat(" ", 8) . "Disallow: / \n";
 		}

 		$content  = "User-agent: *\n";
 		$site_url = wp_parse_url(site_url());
 		$path     = (!empty($site_url['path'])) ? $site_url['path'] : '';
 		$content .= "Disallow: $path/wp-admin/\n";
 		$content .= "Allow: $path/wp-admin/admin-ajax.php\n";
 		$content .= "Sitemap: {$site_url['scheme']}://{$site_url['host']}/sitemap_index.xml\n";
 		$content .= $directive;

 		$content .= "\n# Added by CellarWeb Chatbot Blocker plugin Version " . CWCB_VERSION . " (end)\n";
 		return $content;
 	}

 	function CWCB_robots_settings_info($content) {
 		$site_url   = home_url();
 		$robotslink = $site_url . "?robots=1";
		echo "<p>You can test your virtual robots.txt file by using this link: <a href='$robotslink' target='_blank'>$robotslink</a> (opens in a new tab/window). These directives are what a site scanner will see when they request the robots.txt file . Note that bots do not have to follow the directives when scanning your site.</p> ";
 		echo "<p><b>This is the virtual robots.txt file (created by WordPress) for your site.</b> These directives will not affect search engine scanning or SEO.</p>";
 		echo "<textarea readonly rows='15' cols='70' class='CWCB_textarea' >" . $content . "</textarea>";
 		$robots_file = get_home_path() . "robots.txt";
 		$robots_url  = site_url() . "/robots.txt";
 		if (file_exists($robots_file)) {
 			$message = "<p><b style='background-color:yellow;'>WARNING! You have an actual robots.txt file at $robots_file.</b> That will override the above virtual robots.txt file used by Word Press. We recommend adding directives via WP standards, and not use an actual robots.txt file. </p>";
 		} else {
 			$message = "<p><b>You do not have an actual robots.txt file in your site root, so the above Word Press virtual robots.txt directives will be used when the robots.txt file is requested.</b></p>";
 		}
		$message .= '<hr><p><b>Learn more about how site crawlers work and how they use the robots.txt file.</b> (Links open in new window/tab.)</p> <ul style="list-style-type: disc;
  padding-left: 20px;"><li>A general guide by Google: <a href="https://developers.google.com/webmasters/control-crawl-index/docs/robots_txt" target="_blank">https://developers.google.com/webmasters/control-crawl-index/docs/robots_txt</a> . </li><li>WordPress SEO documentation: <a href="https://wordpress.org/support/article/search-engine-optimization/" target="_blank">https://wordpress.org/support/article/search-engine-optimization/</a> . </li></ul>';
 		echo $message;
 		return;
 	}

/*
	// only enabled for debugging
	function CWCB_print_array($the_array = array()) {
 		echo "<pre>";
 		print_r($the_array);
 		echo "</pre>";
 		return;
 	}
*/
 	// --------------------------------------------------------------
 	// end of everything
 // ============================================================================
