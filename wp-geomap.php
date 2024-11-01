<?php
 /**
  * 
  * @package WP-GeoMap
  * @author Iain Cambridge
  * @copyright All rights reserved 2010 (c)
  * @license http://backie.org/copyright/freebsd-license FreeBSD License
  * @todo Create function for displaying data in theme.
  */

ini_set('display_errors',1);
/*
Author: Iain Cambridge
Author Uri: http://codeninja.me.uk/?ref=wp-geomap
Description: Uses maxmind geoip php module and google maps api to give a geological location of where the author was when creating the post. 
Plugin Name: WP-GeoMap
Version: 0.3  
 */

global $wpdb;
require_once 'class.geoapi.php';
$GLOBALS['wp_geomap'] = array();
$GLOBALS['wp_geomap']['errors'] = array();
$GLOBALS['wp_geomap']['enabled'] = get_option("wpgeo_enabled");	
$GLOBALS['wp_geomap']['method'] = get_option("wpgeo_method");
$GLOBALS['wp_geomap']['api_key'] = get_option("wpgeo_api_key");
$GLOBALS['wp_geomap']['map_type'] = get_option("wpgeo_map_type");	
$GLOBALS['wp_geomap']['zoom'] = get_option("wpgeo_zoom");
$GLOBALS['wp_geomap']['title'] = get_option("wpgeo_title");
$GLOBALS['wp_geomap']['map_types'] = array("ROADMAP","SATELLITE","HYBRID","TERRAIN") ;
$wpgeo_version = 0.3;
$main_table_name = $wpdb->prefix."geodata";
$relation_table_name = $wpdb->prefix."geo_relationships";
// Register activation and deactivation functions.
register_activation_hook( __FILE__, 'wpgeo_install' );
register_deactivation_hook( __FILE__, 'wpgeo_uninstall' );


// Function to create the database and options.
function wpgeo_install(){

	global $wpdb;
	global $main_table_name;
	global $relation_table_name;
	global $installed_ver;
	
	$installed_ver = get_option( "wpgeo_version" );
	
	// Get data
	$GeoIPMod = extension_loaded('geoip');
	if ( $GeoIPMod ){
		$CountryDatabase = geoip_db_avail(GEOIP_COUNTRY_EDITION);
		$CityDatabase = geoip_db_avail(GEOIP_CITY_EDITION_REV0);
	}
	else{
		$CountryDatabase = false;
		$CityDatabase = false;
	}	
	
	// Insert Data
	add_option("wpgeo_mod_installed",$GeoIPMod);
	add_option("wpgeo_country_database",$CountryDatabase);
	add_option("wpgeo_city_database",$CityDatabase);
	add_option("wpgeo_api_key","");
	add_option("wpgeo_enabled","false");
	add_option("wpgeo_method","true");
	add_option("wpgeo_title","Posted from");
	add_option("wpgeo_zoom",4);
	add_option("wpgeo_map_type","ROADMAP");
	
	$wpdb->query("CREATE TABLE  IF NOT EXISTS  `{$main_table_name}` (
	`geodata_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`ip_address` VARCHAR( 255 ) NOT NULL ,
	`city` VARCHAR( 255 ) NOT NULL ,
	`longitude` VARCHAR( 255 ) NOT NULL ,
	`latitude` VARCHAR( 255 ) NOT NULL ,
	`country_code` VARCHAR( 2 ) NOT NULL
	) ENGINE = MYISAM ;");
	
	$wpdb->query("CREATE TABLE  IF NOT EXISTS  `{$relation_table_name}` (
	`relation_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`geodata_id` INT NOT NULL ,
	`post_id` INT NOT NULL ,
	`post_type` INT NOT NULL
	) ENGINE = MYISAM ;");
	

	if( $installed_ver != $wpgeo_version ) {
	
	      $sql = "ALTER TABLE `wp_geodata` CHANGE `ID` `geodata_id` INT( 11 ) NOT NULL AUTO_INCREMENT ";
	
	      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	      dbDelta($sql);
	
	      update_option( "wpgeo_version", $wpgeo_version );
	      return;
	}
}

// Function to remove options and various other items.
function wpgeo_uninstall(){
	
	delete_option("wpgeo_mod_installed");
	delete_option("wpgeo_country_database");
	delete_option("wpgeo_city_database");
	delete_option("wpgeo_api_key");
	delete_option("wpgeo_enabled");
	delete_option("wpgeo_method");
	delete_option("wpgeo_title");
	delete_option("wpgeo_zoom");
	delete_option("wpgeo_map_type");
	
}

function wpgeo_init(){
	
    wp_register_script( 'google-maps', 'http://maps.google.com/maps/api/js?sensor=false');	
    wp_enqueue_script( 'google-maps' );  
}

function wpgeo_show_admin_page(){
	
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	
	if ( !empty($_POST) ){
		
		if ( !isset($_POST['frm_enabled']) && $_POST['frm_enabled'] != "true" && $_POST['frm_enabled'] != "false"){
			$GLOBALS['wp_geopmap']['errors'][] = "Invalid enabled mode";
		}		
		
		if ( !isset($_POST['frm_method']) && $_POST['frm_method'] != "module" && $_POST['frm_method'] != "paid_api" && $_POST['frm_method'] != "free_api"){
			$GLOBALS['wp_geomap']['errors'][] = "Invalid method selected";
		}
		
		if ( $_POST['frm_method'] == "module" && !get_option("wpgeo_mod_installed") ){
			$GLOBALS['wp_geomap']['errors'][] = "Can't use module as it isn't installed";
		}
		
		if ( ( $_POST['frm_method'] == "paid_api" ) && empty($_POST['frm_api_key']) ){
			$GLOBALS['wp_geomap']['errors'][] = "Invalid API key.";
		}
		
		if ( 
			( !ctype_digit($_POST['frm_zoom']) ) &&
		 	!( intval($_POST['frm_zoom']) >= 1 && intval($_POST['frm_zoom']) >= 23 ) 
		   ){			
			$GLOBALS['wp_geomap']['errors'][] = "Invalid zoom level.";
		}
		
		if ( !in_array($_POST['frm_map_type'],$GLOBALS['wp_geomap']['map_types'],true) ){			
			$GLOBALS['wp_geomap']['errors'][] = "Invalid map type.";
		}
		
		if ( empty($_POST['frm_title']) ){			
			$GLOBALS['wp_geomap']['errors'][] = "Display map title can't be empty.";
		}
		
		if ( empty($GLOBALS['wp_geomap']['errors']) ){
			
			$GLOBALS['wp_geomap']['enabled'] = $_POST['frm_enabled'];		
			$GLOBALS['wp_geomap']['method'] = $_POST['frm_method'];
			$GLOBALS['wp_geomap']['api_key'] = $_POST['frm_api_key'];
			$GLOBALS['wp_geomap']['title'] = $_POST['frm_title'];		
			$GLOBALS['wp_geomap']['map_type'] = $_POST['frm_map_type'];
			$GLOBALS['wp_geomap']['zoom'] = $_POST['frm_zoom'];
			$GLOBALS['wp_geomap']['updated'] = true;			
			
			update_option("wpgeo_enabled",$GLOBALS['wp_geomap']['enabled']);
			update_option("wpgeo_method",$GLOBALS['wp_geomap']['method']);
			update_option("wpgeo_api_key",$GLOBALS['wp_geomap']['api_key']);	
			update_option("wpgeo_title",$GLOBALS['wp_geomap']['title']);
			update_option("wpgeo_map_type",$GLOBALS['wp_geomap']['map_type']);
			update_option("wpgeo_zoom",$GLOBALS['wp_geomap']['zoom']);
			update_option("wpgeo_error_message","");
		
		}
		
	}
	
	
	require_once 'admin.page.php';
}

// Add WP-GeoMap option to menu
add_action('admin_menu', 'wpgeo_menu');

function wpgeo_collect_data($IpAddress){
	
	global $main_table_name,$wpdb; 	
	
	if ( !$GeoDataID = $wpdb->get_var("SELECT geodata_id FROM {$main_table_name} WHERE ip_address = '{$IpAddress}'") ){
	
		try {
			if ($GLOBALS['wp_geomap']['method'] == "module"){
				
				$Data = geoip_record_by_name($IpAddress);
				
			}
			elseif ($GLOBALS['wp_geomap']['method'] == "paid_api"){
				
				$objGeoIP = new GeoApi($IpAddress, GeoApi::REQUEST_CITY,$GLOBALS['wp_geomap']['api_key']);
				$objGeoIP->execute();
				$Data = $objGeoIP->getRecord();
				
			}
			elseif ($GLOBALS['wp_geomap']['method'] == "free_api"){
				
				$objGeoIP = new GeoApi($IpAddress, GeoApi::REQUEST_OPEN_CITY);
				$objGeoIP->execute();
				$Data = $objGeoIP->getRecord();		
					
			}
		}
		catch (Exception $objException){
			
			update_option("wpgeo_enabled","false");
			update_option("wpgeo_error_message", $objException->getMessage());
			return 0;
		}		
		$wpdb->insert($main_table_name, array("ip_address" => $IpAddress, "city" => $Data['city'], "longitude" => $Data['longitude'], "latitude" => $Data['latitude'], "country_code" => $Data['country_code'] ) );
	
		$GeoDataID = $wpdb->insert_id;
	}	
	
	return $GeoDataID;
	
}

function wpgeo_publish_post($PostID){
	
	global $wpdb,$relation_table_name;
	
	if ($GLOBALS['wp_geomap']['enabled'] == "false"){
		return;
	}
	
	if ( !$wpdb->get_var("SELECT relation_id FROM {$relation_table_name} WHERE post_id = {$PostID} AND post_type = 1")){
		
		if ( $GeoDataID = wpgeo_collect_data($_SERVER['REMOTE_ADDR']) ){
		
			$wpdb->insert($relation_table_name, array("geodata_id" => $GeoDataID, "post_id" => $PostID, "post_type" => 1) ); 
		
		}

	}
}


function wpgeo_comment_post($CommentID,$Approved){	
	
	global $wpdb,$relation_table_name;

	if ($GLOBALS['wp_geomap']['enabled'] == "false"){
		return;
	}
	
	
	if ( !$wpdb->get_var("SELECT relation_id FROM {$relation_table_name} WHERE post_id = {$CommentID} AND post_type = 2")){
		
		if ( $GeoDataID = wpgeo_collect_data($_SERVER['REMOTE_ADDR']) ){
		
			$wpdb->insert($relation_table_name, array("geodata_id" => $GeoDataID, "post_id" => $PostID, "post_type" => 2) ); 
		
		}
	}
}

function wpgeo_menu() {

  add_options_page('WP-GeoMap Settings', 'WP-GeoMap', 'manage_options', 'wp-geomap', 'wpgeo_show_admin_page');

}

function wpgeo_admin_header(){
	
	$is_enabled = get_option("wpgeo_enabled");
	$error_message = get_option("wpgeo_error_message");
	
	if ( $is_enabled == "false" ){
		?>
		<div id='wpgeo_not_enabled' class='updated settings-error'>WPGeoMap isn't enabled, please visit the settings page and configure it.</div> 
		<?php 
	}
	
	if ( !empty($error_message)){		
		?>
		<div id='wpgeo_not_enabled' class='updated settings-error'><?php echo $error_message; ?></div>
		<?php 
	}
	
}

function wpgeo_display_map($GeoDataID,$PostID){
	
	global $wpdb,$main_table_name;
	
	$title = get_option("wpgeo_title");
	$zoom_level = get_option("wpgeo_zoom");
	$map_type = get_option("wpgeo_map_type");
	
	$row = $wpdb->get_row("SELECT longitude,latitude FROM {$main_table_name} WHERE geodata_id = {$GeoDataID}");
	
	if ( $row ){
	
	$Return = <<<Map
	<p><h3>{$GLOBALS['wp_geomap']['title']}</h3>
	<div id="map_canvas" style="width: 100%;height : 250px;"></div>
	</p>
<script type="text/javascript">
  function initialize() {
    var latlng = new google.maps.LatLng(  $row->latitude, $row->longitude  );
    var myOptions = {
      zoom: {$GLOBALS['wp_geomap']['zoom']},
      center: latlng,
      mapTypeId: google.maps.MapTypeId.{$GLOBALS['wp_geomap']['map_type']}
    };
    var map = new google.maps.Map(document.getElementById("map_canvas"),
        myOptions);
    var marker = new google.maps.Marker({
      position: latlng, 
      map: map, 
      title:"Hello World!"
  });  
  }
  initialize();
</script>
<!-- "SELECT longitude,latitude FROM $main_table_name WHERE geodata_id = $GeoDataID" 
$PostID
	-->
Map;
	}
	
	
	return $Return;
}


function wpgeo_the_content($Content){
	
	if ( !is_single() ){
		return $Content;
	}
	
	global $wpdb,$post,$relation_table_name;
	
	
	global $wp_query;
	$thePostID = $wp_query->post->ID;
	
	$GeoDataID = $wpdb->get_var("SELECT geodata_id FROM {$relation_table_name} WHERE post_id = {$thePostID} and post_type = 1");
	
	$Content .= wpgeo_display_map($GeoDataID,$thePostID);
	
	return $Content;
}

add_filter("the_content", "wpgeo_the_content");

add_action("admin_head", "wpgeo_admin_header");
add_action("publish_post","wpgeo_publish_post");
add_action("comment_post","wpgeo_comment_post");
add_action("init","wpgeo_init");

?>