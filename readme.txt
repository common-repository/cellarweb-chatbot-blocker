=== ChatBot Blocker by CellarWeb    ===
Contributors: rhellewellgmailcom
Tags: robots.txt, robots, chatbot, chatgpt, AI
Requires at least: 5.4
Tested up to: 6.6
Requires PHP: 7.2
Version: 2.02
Donate link: https://cellarweb.com/donate.php
Plugin URI: https://www.cellarweb.com/wordpress-plugins/
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

ChatBot Blocker by CellarWeb    adds commands to the WordPress virtual robots.txt file to block various chatbots from using your site content.

== Description ==

You can block ChatGPT and Bard and other compatible AI scanners from using your site content by adding specific commands to your site's virtual robots.txt file. Note that an actual robots.txt file in your site root will override the virtual commands.

The plugin also adds your sitemap.xml file to the virtual robots.txt file.


== Installation ==

1. Download the plugin
2. Unzip it
3. Upload the unzipped folder to `wp-content/plugins` directory
4. Activate and enjoy!

Or you can simply install it through the admin area plugin installer.

== Screenshots ==

1. A view of the admin option

== Frequently Asked Questions ==

= Where do I find the settings for this plugin? =

It's all automatic, but you can see the current virtual robots.txt settings via the Settings page. This is because the virtual file is automatically created by WordPress when the robots.txt file is requested.

= What are the default settings created with this plugin? =

Default settings are:

~~~
>		User-agent: *
>		Disallow: /wp-admin/
>		Allow: /wp-admin/admin-ajax.php
>
>		Sitemap: https://yourdomain.com/wp-sitemap.xml
>
>		# Added by CellarWeb Chatbot Blocker plugin Version 2.00 (3 Mar 2024) (begin)		  #  Blocks ChatGPT bot scanning
>		        User-agent: GPTBot
>		        Disallow: /
>		  #  Blocks Bard bot scanning
>		        User-agent: Bard
>		        Disallow: /
>		  #  Blocks Bing bot scanning
>		        User-agent: bingbot-chat/2.0
>		        Disallow: /
>		  #  Blocks Common Crawl bot scanning
>		        User-agent: CCBot
>		        Disallow: /
>		  #  Blocks omgili bot scanning
>		        User-agent: Omgili
>		        Disallow: /
>		  #  Blocks omgilibot bot scanning
>		        User-agent: Omgili Bot
>		        Disallow: /
>		  #  Blocks Diffbot bot scanning
>		        User-agent: Diffbot
>		        Disallow: /
>		  #  Blocks MJ12bot bot scanning
>		        User-agent: MJ12bot
>		        Disallow: /
>		  #  Blocks anthropic-ai bot scanning
>		        User-agent: anthropic-ai
>		        Disallow: /
>		  #  Blocks ClaudeBot bot scanning
>		        User-agent: ClaudeBot
>		        Disallow: /
>		  #  Blocks FacebookBot bot scanning
>		        User-agent: FacebookBot
>		        Disallow: /
>		  #  Blocks Google-Extended bot scanning
>		        User-agent: Google-Extended
>		        Disallow: /
>		  #  Blocks SentiBot bot scanning
>		        User-agent: SentiBot
>		        Disallow: /
>		  #  Blocks sentibot bot scanning
>		        User-agent: sentibot
>		        Disallow: /
>		# Added by CellarWeb Chatbot Blocker plugin Version 2.00 (3 Mar 2024) (end)
>
~~~

= What about other chatbots scanners? =

We'll add them to the plugin when we find them. And the latest list will automatically be enabled by the plugin. Use the support pages for the plugin for requests.

= Whare do I add more settings? =

We recommend using WP standards to add to your virtual robots.txt file.

= How do I see the actual virtual robots.txt file? =

Use the URL of www.yoursite.com?robots=1  , replacing 'yoursite.com' with your actual site domain name. There is a link on the Settings, Reading screen under the virtual robots.txt box created by the plugin.

= What if I have an actual (not virtual) robots.txt file in the root of my site? =

The actual file will override any virtual settings. The screen will show a warning message below the display of the virtual settings if an actual file is found in the site's root folder.

= Why do I want a robots.txt file? =

The robots.txt file is used by responsbile bots to scan only the files that you want to be scanned. The plugin adds commands to block the AI bots from scanning your site and using your content.

There might be some bots that ignore the robots.txt directives. This is not common, but there is no easy way to block those irresponsible bots.

= Should I put SEO Optimization commands in the robots.txt file? =

You could, but they won't be effective. There are better ways to do SEO optimization.

= Where can I learn more about how site crawlers work and how they use the robots.txt file? =

[Here](https://developers.google.com/webmasters/control-crawl-index/docs/robots_txt) is a general guide by Google and [here](https://wordpress.org/support/article/search-engine-optimization/) is the WordPress SEO documentation.

== Changelog ==

= 2.02 (30 Aug 2024) =
	- Added additional chatbot agents to include Applebot:
				"Applebot"		=> "Applebot",

= 2.01 (9 Mar 2024) =
* Added additional user agents based on research.
* Changed the Settings title to "Chabot Blocker by CellarWeb"

= 2.00 (3 Mar 2024) =
* Plugin settings page moved to Settings menu. There are no user-supplied settings, so the settings page is just informational.
* Code refactored for more efficiency.
* Added compatibility with the CellarWeb Privacy and Security plugin, which also has this feature, in addition to other recommended security and privacy settings.
* Changes to readme file sections.
* Updated screenshot.

= 1.03 (29 Feb 2024) =
* Additional bot agents added .
* Added explanatory text below the settings box on the "Settings", "Read" page.

= 1.02 (27 Feb 2024) =
* Additional bot agents added.

= 1.01 (1 Nov 2023) =
* Minor changes to plugin header area for links to plugin.

= 1.00 (28 Oct 2023) =
* Initial version release.


