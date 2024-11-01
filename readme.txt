=== WP-GeoMap ===
Contributors: Iain Cambridge
Tags: GeoLocation, Google Maps, GeoIP
Requires at least: 3.0.1
Tested up to: 3.0.1
Stable tag: trunk

A geolocation plugin to show where posters were when making a new post.

== Description ==

A plugin that leverages maxmind's geolocation ability to display a google map of the the location of the author of a post when creating a post. Uses 3 methods to get the geo location of a author, one is to use the php extenstion, the second is to use maxmind's city web service and the third is to use a web service on api.codeninja.me.uk which uses maxmind's GeoCityLite database.

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Goto the Wp-GeoMap settings page and click enable and select the option you wish to use to retrive the data.

== Changelog == 

= 0.3 =

* Fixed design flaw from the design flaw fix. (I forgot to change something, that'll teach me for not testing. :p)
* Added google map's api customization with zoom level and map type.
* Added customization of the text above the google map.
* Fixed SQL design flaws. (For some reason I was running a different schema on my install.)
* Fixed uncaught exception error.

= 0.2 =
* Fixed design flaw in class.geoapi.php that resulted in users of the free webservice having exceptions flung for no license key.

== Upgrade Notice ==

= 0.3 =
* Deactivate and reactivate. Should make plugin actually work!
