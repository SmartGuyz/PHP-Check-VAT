# Check-VAT-PHP
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)

Verify the validity of a VAT identification via VIES.\
No additional libraries are needed, just Vanilla (RAW) PHP.

USAGE
============

Incude the class fille src/check-vat.php
Then instance the class and load in the the method like this:

    $oResult = (new viesChecker())->checkVat("IE", "6388047V");

The above code will check IE6388047V in the VIES database and put the result in $aResult as an array.\
All of the code can easily be adjusted to your needs.

If you rather work with an array you can easily do the following:

    $aResult = (array)(new viesChecker())->checkVat("IE", "6388047V");

That is all. Goodluck with it :)

**DISCLAIMER:**

This service gets its data from the [VIES VAT number validation system](https://ec.europa.eu/taxation_customs/vies/#/vat-validation) provided by the European Commission.\
This site is in no way affiliated with or endorsed by the European Commission.\
See the [VIES disclaimer](https://ec.europa.eu/taxation_customs/vies/#/disclaimer) and [VIES FAQ](https://ec.europa.eu/taxation_customs/vies/#/faq) for further information on the data source and its terms of usage from the European Commission.