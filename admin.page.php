<?php
 /**
  * 
  * @package WP-GeoMap
  * @author Iain Cambridge
  * @copyright All rights reserved 2010 (c)
  * @license http://backie.org/copyright/freebsd-license FreeBSD License
  */
?>
<div class="wrap"><h1>WP-GeoMap Settings</h1></div>
<?php 
if ( !empty($GLOBALS['wp_geomap']['errors']) ){

?>		
<div id='wpgeo_errors' class='updated settings-error'>
<ul>
<?php foreach ($GLOBALS['wp_geomap']['errors'] as $error) {?>
	<li><?php echo $error; ?></li>
<?php } ?>
</ul>
</div> 
<?php 
}

if ( !empty($GLOBALS['wp_geomap']['updated']) ){

?>		
<div id='wpgeo_errors' class='updated settings-error'>
Settings have been updated.
</div> 
<?php 
}
?>

<form method="post" action="options-general.php?page=wp-geomap">

	<h2>GeoIP Settings</h2>

	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td width="150" rowspan="2" valign="top"><strong>Enabled</strong></td>
			<td><input type="radio" name="frm_enabled" value="true" <?php if ($GLOBALS['wp_geomap']['enabled'] == "true"){?>checked="yes" <?php }?>/> Yes</td>
		</tr>
		<tr>
			<td><input type="radio" name="frm_enabled" value="false" <?php if ($GLOBALS['wp_geomap']['enabled'] == "false"){?>checked="yes" <?php }?>/> No</td>
		</tr>
		<tr>
			<td rowspan="3" valign="top"><strong>Method</strong></td>
			<td><input type="radio" name="frm_method" value="module" <?php if ($GLOBALS['wp_geomap']['method'] == "module"){?>checked="yes" <?php }?>/> Module [ <a href="#module">?</a> ]</td> 
		</tr>
		<tr>
			<td><input type="radio" name="frm_method" value="paid_api" <?php if ($GLOBALS['wp_geomap']['method'] == "paid_api"){?>checked="yes" <?php }?>/> Paid API [ <a href="#paid_api">?</a> ]</td> 
		</tr>
		<tr>
			<td><input type="radio" name="frm_method" value="free_api" <?php if ($GLOBALS['wp_geomap']['method'] == "free_api"){?>checked="yes" <?php }?>/> Free API [ <a href="#free_api">?</a> ]</td> 
		</tr>
		<tr>
			<td><strong>API Key</strong></td>
			<td><input type="text" name="frm_api_key" value="<?php echo $GLOBALS['wp_geomap']['api_key']; ?>" /></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="Save Options" /></td>
		</tr>		
	</table>
	
	<h2>Google Map Settings</h2>

	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td width="150" valign="top"><strong>Map Type</strong></td>
			<td><select name="frm_map_type">
				<?php foreach ($GLOBALS['wp_geomap']['map_types'] as $type){?>
					<option value="<?php echo $type; ?>" <?php if ($GLOBALS['wp_geomap']['map_type'] == $type){ ?>selected="selected" <?php }?>><?php echo ucfirst($type); ?></option>
				<?php } ?>
				</select></td>
		</tr>
		<tr>
			<td valign="top"><strong>Zoom</strong></td>
			<td><select name="frm_zoom">
				<?php for($i = 1; $i <= 23; $i++){?>
					<option value="<?php echo $i; ?>" <?php if ($GLOBALS['wp_geomap']['zoom'] == $i){ ?>selected="selected" <?php }?>><?php echo $i; ?></option>
				<?php } ?>
			</select></td> 
		</tr>
		<tr>
			<td width="150" valign="top"><strong>Map Title</strong></td>
			<td><input name="frm_title" value="<?php echo $GLOBALS['wp_geomap']['title']; ?>" /></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="Save Options" /></td>
		</tr>		
	</table>
	
	<h2>More Info</h2>
	
	<a name="module"></a>
	<h3>Module</h3>
	<p>To use this method of getting your geo location you have to have the geoip module install, this requires having admin ability on the server. More details of this can be found at <a href="http://www.maxmind.com/app/city">here</a>.</p>
	
	<a name="paid_api"></a>
	<h3>Paid API</h3>
	<p>This method is powered by maxmind's city web service. Best for people who need the best accuracy avaible and are on shared hosting. More details of this can be found at <a href="http://www.maxmind.com/app/city">here</a>.</p>
	
	<a name="free_api"></a>
	<h3>Free API</h3>
	<p>This method is powered by maxmind's GeoCityLite database and run on one of my servers. Best for people who don't need better accuracy and are on shared hosting. <strong>NO API KEY NEEDED</strong></p>
	
</form>