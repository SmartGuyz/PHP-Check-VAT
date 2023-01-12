<?php
require './src/check-vat.php';

$oResult = (new viesChecker())->checkVat("IE", "6388047V");

if(!is_object($oResult))
{
	// Needs error handling
	exit;
}

if(isset($oResult->Error))
{
	// Needs error handling
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