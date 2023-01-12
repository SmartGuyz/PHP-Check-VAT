<?php
ini_set("soap.wsdl_cache_enabled", 0);

function checkVat(string $sCountry = "", string $sNumber = ""): object|string
{
	$oReturn = new stdClass();

	if(!preg_match("~[A-Z]{2}~", $sCountry))
	{
		$oReturn->Error = "Not a valid country code";
		return $oReturn;
	}

	if(!preg_match("~^[A-Z0-9]+$~", $sNumber))
	{
		$oReturn->Error = "Not a valid VAT code";
		return $oReturn;
	}

	/*
	 * Set outgoing IP for this service.
	 * If you have multiple IP's, you can switch them here.
	 * This can be helpfull if you get blocked for some reason.
	 *
	 * Change 127.0.0.1 to your external IP (https://www.myip.com)
	 */
	$sExternalIP    = "127.0.0.1";
	$aOptions       = (($sExternalIP != '127.0.0.1') ? ['socket' => ['bindto' => "{$sExternalIP}:0"]] : []);
	$rStreamContext = stream_context_create($aOptions);

	try
	{
		$aOptions    = ['soap_version' => SOAP_1_1, 'exceptions' => true, 'trace' => 1, 'cache_wsdl' => WSDL_CACHE_NONE, 'stream_context' => $rStreamContext];
		$cSoapClient = new SoapClient("https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl", $aOptions);

		$aParams['countryCode'] = $sCountry;
		$aParams['vatNumber']   = $sNumber;
		$oParams                = (object)$aParams;

		$oResult = $cSoapClient->checkVat($oParams);
	}
	catch(SoapFault $e)
	{
		/*
		 * Here is the list of VAT Number to use to receive each kind of answer:
		 *
		 * 100 = Valid request with Valid VAT Number
		 * 200 = Valid request with an Invalid VAT Number
		 * 201 = Error : INVALID_INPUT
		 * 202 = Error : INVALID_REQUESTER_INFO
		 * 300 = Error : SERVICE_UNAVAILABLE
		 * 301 = Error : MS_UNAVAILABLE
		 * 302 = Error : TIMEOUT
		 * 400 = Error : VAT_BLOCKED
		 * 401 = Error : IP_BLOCKED
		 * 500 = Error : GLOBAL_MAX_CONCURRENT_REQ
		 * 501 = Error : GLOBAL_MAX_CONCURRENT_REQ_TIME
		 * 600 = Error : MS_MAX_CONCURRENT_REQ
		 * 601 = Error : MS_MAX_CONCURRENT_REQ_TIME
		 *
		 * For all the other cases, The web service will responds with a "SERVICE_UNAVAILABLE" error.
		 */
		$oReturn->Error = match($e->getMessage())
		{
			'IP_BLOCKED' => "IP_BLOCKED: The external IP address is blocked by VIES",
			'MS_UNAVAILABLE' => "MS_UNAVAILABLE: The application at the Member State is not replying or not available.",
			'SERVICE_UNAVAILABLE' => "SERVICE_UNAVAILABLE: An error encountered either at the network level or the Web application level, try again later.",
			default => $e->getMessage()
		};

		return $oReturn;
	}

	// Create return object (for address, remove newlines and replace them with spaces)
	$oReturn          = new stdClass();
	$oReturn->Valid   = (($oResult->valid) ? "YES" : "NO");
	$oReturn->Name    = (($oResult->valid) ? preg_replace("#&#", "&amp;", (string)$oResult->name) : "NA");
	$oReturn->Address = (($oResult->valid) ? preg_replace("#&#", "&amp;", implode(' ', explode("\n", trim((string)$oResult->address)))) : "NA");

	return $oReturn;
}

$oResult = checkVat("IE", "6388047V");

if(!is_object($oResult))
{
	// Needs error handling (VAT invalid)
	exit;
}

if($oResult->Valid === 'NO')
{
	// Needs handling (VAT invalid)
	exit;
}

// Print results or do something else with them
echo "Valid: {$oResult->Valid }<br />";
echo "Name: {$oResult->Name}<br />";
echo "Address: {$oResult->Address}<br />";