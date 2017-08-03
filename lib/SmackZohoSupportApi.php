<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists( "SmackZohoSupportApi" ) )
{
	class SmackZohoSupportApi{

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

		public $zohosupporturl;
		public function __construct()
		{
			$this->zohosupporturl = "https://support.zoho.com/api/xml/";
		}

		public function APIMethod($module, $methodname, $authkey , $portalname , $departmentname , $param="", $recordId = "")
		{
			$uri = $this->zohosupporturl . $module . "/".$methodname."";
			/* Append your parameters here */
			$postContent = "scope=supportapi";
			$postContent .= "&authtoken={$authkey}";//Give your authtoken
			$postContent .= "&portal={$portalname}";
			$postContent .= "&portal={$departmentname}";
			$ch = curl_init($uri);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postContent);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$result = curl_exec($ch);
			$xml = simplexml_load_string($result);
			$json = json_encode($xml);
			$result_array = json_decode($json,TRUE);
			curl_close($ch);
			return $result_array;
		}

		public function addrecords( $modulename, $methodname, $authkey ,$portalname , $departmentname , $xmlData="" , $extraParams = "" )
		{	
			$uri = $this->zohosupporturl . $modulename . "/".$methodname;
			/* Append your parameters here */
			$postContent = "&authtoken={$authkey}";//Give your authtoken
			$postContent .= "&scope=supportapi";
			$postContent .= "&portal={$portalname}";
			$postContent .= "&department={$departmentname}";
			$postContent .= "&xml={$xmlData}";
			if($extraParams != "")
			{
				$postContent .= $extraParams;
			}
			
			$ch = curl_init($uri);
			curl_setopt($ch, CURLOPT_POST, true);
			//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			//curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postContent);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$result = curl_exec($ch);
			$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$xml = simplexml_load_string($result);
			$json = json_encode($xml);
			$result_array = json_decode($json,TRUE);
			curl_close($ch);
			return $result_array;
		
		}

	        public function getrecords( $modulename, $methodname, $authkey , $portalname ,$departmentname , $selectColumns ="" , $xmlData="" , $extraParams = "" )
		{
			$uri = $this->zohosupporturl . $modulename . "/".$methodname."";
			/* Append your parameters here */
			$postContent = "scope=supportapi";
			$postContent .= "&authtoken={$authkey}";//Give your authtoken
			$postContent .= "&portal={$portalname}";
			$postContent .= "&department={$departmentname}";
			if($selectColumns == "")
			{
				$postContent .= "&selectColumns=All";
			}
			else
			{
				$postContent .= "&selectColumns={$modulename}( {$selectColumns} )";
			}

			if($extraParams != "")
			{
				$postContent .= $extraParams;
			}
			$postContent .= "&xml={$xmlData}";
			$ch = curl_init($uri);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postContent);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$result = curl_exec($ch);
			$str = preg_replace('#<!\[CDATA\[(.+?)\]\]>#s', '$1', $result);
			$xml = simplexml_load_string($str);
			$json = json_encode($xml);
			$result_array = json_decode($json,TRUE);
			curl_close($ch);
			return $result_array;
		}

		public function getAccountId($authkey)
		{
			$Account_uri = "https://support.zoho.com/api/private/xml/accounts/getrecords";
                        $Account_postContent = "scope=supportapi";
                        $Account_postContent .= "&authtoken={$authkey}";//Give your authtoken
                        $Account_postContent .= "&selectColumns=Accounts(ACCOUNTID)";

                        $ch = curl_init($Account_uri);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $Account_postContent);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        $result = curl_exec($ch);

                        $xml = simplexml_load_string($result);
                        $json = json_encode($xml);
                        $result_array = json_decode($json,TRUE);
                        curl_close($ch);
                        $ACCOUNT_ID = $result_array['result']['Accounts']['row'][0]['FL'];
			return $ACCOUNT_ID;
		}

		public function getAuthenticationToken( $username , $password  )
		{
			$username = urlencode( $username );
			$password = urlencode( $password );
			$param = "SCOPE=ZohoSUPPORT/supportapi,ZohoSearch/SearchAPI&EMAIL_ID=".$username."&PASSWORD=".$password;
			$ch = curl_init("https://accounts.zoho.com/apiauthtoken/nb/create");
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$result = curl_exec($ch);
			$anArray = explode("\n",$result);
			$authToken = explode("=",$anArray['2']);
			$cmp = strcmp($authToken['0'],"AUTHTOKEN");
			if ($cmp == 0)
			{
				$return_array['authToken'] = $authToken['1'];
			}
			$return_result = explode("=" , $anArray['3'] );
			$cmp1 = strcmp($return_result['0'],"RESULT");
			if($cmp1 == 0)
			{
				$return_array['result'] = $return_result['1'];
			}
			if($return_result[1] == 'FALSE'){
				$return_cause = explode("=",$anArray[2]);
				$cmp2 = strcmp($return_cause[0],'CAUSE');
				if($cmp2 == 0)
					$return_array['cause'] = $return_cause[1];
			}
			curl_close($ch);
			return $return_array;
		}
	}
}