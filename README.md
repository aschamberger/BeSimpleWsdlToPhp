BeSimpleWsdlToPhp
=================
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/66Ton99/BeSimpleWsdlToPhp/badges/quality-score.png?s=b3739e8ed4453ba475fa5bac1f680f559c10fe5d)](https://scrutinizer-ci.com/g/66Ton99/BeSimpleWsdlToPhp/)
[![Code Coverage](https://scrutinizer-ci.com/g/66Ton99/BeSimpleWsdlToPhp/badges/coverage.png?s=0006eebfab27dc0d6a951df994a59f41b3e3db1e)](https://scrutinizer-ci.com/g/66Ton99/BeSimpleWsdlToPhp/)


It converts soap wsdl to PHP classes

Usage
-----

Run
```
php bin/wsdl2php.php "https://heartlandpaymentservices.net/BillingDataManagement/v3/BillingDataManagementService.svc?wsdl"
```

All parameters:
-w or --wsdl - Required! Path or url to wsdl file.
-c or --client - Name of client class, if empty client will not be generated.
-n or --namespace - Root namespace of generated classes.
-v or --soap_version - Soap version: 1.1 or 1.2. Default 1.1.
-o or --output_dir - Output dir for classes. Default current dir.

TODO
----

* Resolve namespaces
* Write more tests for parser
