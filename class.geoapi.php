<?php

	/**
	 * PHP Class to provide an easy interface to the
	 * maxmind geolocation web service.
	 * @author Iain Cambridge
	 * @copyright All rights reserved Iain Cambridge 2010 (c)
	 * @license http://codeninja.me.uk/copyright/freebsd-license/ FreeBSD License
	 * @todo Add error handling of data.
	 */

class GeoApi {
	
	/**
	 * Constant for declaring a country web service request.
	 * @var integer
	 */
	const REQUEST_COUNTRY = 1;
	/**
	 * Constant for declaring a city web service request.
	 * @var integer
	 */
	const REQUEST_CITY = 2;
	/**
	 * Constant for declaring a city and isp web service request.
	 * @var integer
	 */
	const REQUEST_CITY_ISP = 3;
	/**
	 * Constant for declaring a city and isp web service request.
	 * @var integer
	 */
	const REQUEST_OPEN_CITY = 4;
	/**
	 * Constant for declaring class version for web sent as a user agent.
	 * @var integer
	 */
	const VERISON = 0.3;	
	
	/**
	 * License key for the using maxmind's web service.
	 * @var string
	 */
	private $licenseKey;
	/**
	 * The url to be used for the maxmind web service
	 * request, changes depending on the type of
	 * being done.
	 * @var string
	 */
	private $queryUrl;
	/**
	 * The IP address that information is going to be requested about.
	 * @var string
	 */
	private $ipAddress;
	/**
	 * Type of request that is going to be done.
	 * @var integer
	 */
	private $queryType;
	/**
	 * An array containing the data returned from the web servie.
	 * Accessible via a magic get method.
	 * @var array
	 */
	private $returnData = array();
	
	/**
	 * Constructor method.
	 * @param $licenseKey license key for Maxmind's web service.
	 * @param $ipAddress  IP Address to be get information about.
	 * @param $queryType Which kind of data is to be requested.
	 */
	
	public function __construct($ipAddress,$queryType = self::REQUEST_COUNTRY, $licenseKey = false ){
		
		if ( empty($licenseKey) && $queryType != self::REQUEST_OPEN_CITY  ){
			throw new Exception("Invalid license key given");
		}
		
		if (  empty($ipAddress) ){
			throw new Exception("Invalid IP address given");
		}

		if ( $queryType != self::REQUEST_CITY && $queryType != self::REQUEST_OPEN_CITY && $queryType != self::REQUEST_CITY_ISP && $queryType != self::REQUEST_COUNTRY ){
			throw new Exception("Invalid query type given");
		}
		
		$this->licenseKey = $licenseKey;
		$this->ipAddress = $ipAddress;
		$this->queryType = $queryType;
		
		switch ( $queryType ){			
			case self::REQUEST_CITY:
				$this->queryUrl = "http://geoip3.maxmind.com/b";
				break;
			case self::REQUEST_CITY_ISP:
				$this->queryUrl = "http://geoip3.maxmind.com/f";
				break;
			case self::REQUEST_OPEN_CITY:
				$this->queryUrl = "http://api.codeninja.me.uk/geo.php";
				break;
			default :
				$this->queryUrl = "http://geoip3.maxmind.com/a";
				break;
		}
		
		return;
	}
	
	/**
	 * Executes the get request to the maxmind web service.
	 */
	
	public function execute(){
		
		$curlControl = curl_init();
		curl_setopt($curlControl, CURLOPT_USERAGENT , "WP-GeoMap ".self::VERISON." (http://codeninja.me.uk)");
		curl_setopt($curlControl, CURLOPT_URL, $this->queryUrl."?l={$this->licenseKey}&i={$this->ipAddress}");
		curl_setopt($curlControl, CURLOPT_RETURNTRANSFER, 1);
	
		$returnData = curl_exec($curlControl);
		curl_close($curlControl);
		
		$returnData = explode(",", $returnData);
			
		if (strlen($returnData[0]) != 2){
			throw new Exception("Invalid result");
		}
		
		$this->returnData['country_code'] = $returnData[0]; 
		
		if ($this->queryType == GeoApi::REQUEST_CITY || $this->queryType == GeoApi::REQUEST_CITY_ISP || $this->queryType == GeoApi::REQUEST_OPEN_CITY){
			$this->returnData['region'] = $returnData[1];
			$this->returnData['city'] = $returnData[2];
		}
		
		if ($this->queryType == GeoApi::REQUEST_CITY || $this->queryType == GeoApi::REQUEST_OPEN_CITY){			
			$this->returnData['latitude'] =  $returnData[3];
			$this->returnData['longitude'] = $returnData[4];
		}
		elseif ($this->queryType == GeoApi::REQUEST_CITY_ISP){
			$this->returnData['post_code'] = $returnData[3];	
			$this->returnData['latitude'] =  $returnData[4];
			$this->returnData['longitude'] = $returnData[5];
			$this->returnData['metro_code'] = $returnData[6];
			$this->returnData['area_code'] = $returnData[7];
			$this->returnData['isp'] = $returnData[8];
			$this->returnData['organization'] = $returnData[9];
		}
		
	}
	
	public function getRecord(){
		return $this->returnData;
	}
	
	/**
	 * Magic get method to allow access to data returned from
	 * the maxmind web service.
	 * @param mixed $optionValue
	 */
	public function __get($optionValue){
		return $this->returnData[$optionValue];
	}
	
}

?>