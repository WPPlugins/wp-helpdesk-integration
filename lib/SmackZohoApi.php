<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists( "SmackZohoApi" ) )
{
	class SmackZohoApi{

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

		public $zohocrmurl;
		public function __construct()
		{
			$this->zohocrmurl = "https://crm.zoho.com/crm/private/xml/";
		}

		public function APIMethod($module, $methodname, $authkey , $param="", $recordId = "")
		{
			$uri = $this->zohocrmurl . $module . "/".$methodname."";
			/* Append your parameters here */
			$postContent = "scope=crmapi";
			$postContent .= "&authtoken={$authkey}";//Give your authtoken
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

		public function insertRecord( $modulename, $methodname, $authkey , $xmlData="" , $extraParams = "" )
		{
			$uri = $this->zohocrmurl . $modulename . "/".$methodname."";
			/* Append your parameters here */
			$postContent = "scope=crmapi";
			$postContent .= "&authtoken={$authkey}";//Give your authtoken
			if($extraParams != "")
			{
				$postContent .= $extraParams;
			}
			$postContent .= "&xmlData={$xmlData}";
			$postContent .= "&wfTrigger=true";
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

		public function getRecords( $modulename, $methodname, $authkey , $selectColumns ="" , $xmlData="" , $extraParams = "" )
		{
			$uri = $this->zohocrmurl . $modulename . "/".$methodname."";
			/* Append your parameters here */
			$postContent = "scope=crmapi";
			$postContent .= "&authtoken={$authkey}";//Give your authtoken
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
			$postContent .= "&xmlData={$xmlData}";
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

		public function convertLeads($modulename , $crm_id , $order_id , $lead_no , $authkey , $sales_order )
		{

	//Convert Leads And get Contact Id
			$methodname = 'convertLead';
			$uri = $this->zohocrmurl . $modulename . "/".$methodname."";
                        /* Append your parameters here */
                        $postContent = "scope=crmapi";
                        $postContent .= "&authtoken={$authkey}";//Give your authtoken
			$postContent .= "&leadId={$lead_no}";
			$LEAD_OWNER = $this->getConvertLeadOwner( $authkey , $lead_no );

			$xmlData  = "<Potentials>\n<row no=\"1\">\n";
			$xmlData .= "<option val=\"createPotential\">false</option>\n
				     <option val=\"assignTo\">".$LEAD_OWNER."</option>\n
				     <option val=\"notifyLeadOwner\">true</option>\n
				     <option val=\"notifyNewEntityOwner\">true</option>\n
				     </row>\n</Potentials>";
                        $postContent .= "&xmlData={$xmlData}";

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
			$CONTACT_ID = $result_array['Contact'];
			$ACCOUNT_ID = $result_array['Account'];
			//END Convert Lead

			$final_array = array();
			$final_array['SMOWNERID'] = $LEAD_OWNER;
			$final_array['CONTACT_ID'] = $CONTACT_ID;
			$final_array['ACCOUNT_ID'] = $ACCOUNT_ID;
			return $final_array;
		}

		public function getAccountId($authkey)
		{
			$Account_uri = "https://crm.zoho.com/crm/private/xml/Accounts/getRecords";
                        $Account_postContent = "scope=crmapi";
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

		public function getModules($TFA_authtoken)
		{
			$uri = "https://crm.zoho.com/crm/private/xml/Info/getModules?"; // Check Auth token present in Zoho //ONLY FOR TFA CHECK
			$postContent = "scope=crmapi";
			$postContent .= "&authtoken={$TFA_authtoken}";
			$ch = curl_init($uri );
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

		public function getConvertLeadOwner($modulename , $authkey , $record_id )
		{
			$zohourl = "https://crm.zoho.com/crm/private/xml/";
                        $methodname = 'getRecords';
			$module_slug = rtrim( $modulename , 's' );
                        $uri = $zohourl . $modulename . "/".$methodname."";
                        $postContent = "scope=crmapi";
                        $postContent .= "&authtoken={$authkey}";//Give your authtoken
                        $postContent .= "&id={$record_id}&selectColumns={$modulename}({$module_slug} Owner)";

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
			$Lead_owner = $result_array['result'][$modulename]['row']['FL'][1];
			return $Lead_owner;
		}
	
		public function getAuthenticationToken( $username , $password  )
		{
			$username = urlencode( $username );
			$password = urlencode( $password );
			$param = "SCOPE=ZohoCRM/crmapi&EMAIL_ID=".$username."&PASSWORD=".$password;
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