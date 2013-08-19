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

--wsdl <option> or -w <option> — Required! Path or url to wsdl file.

--client <option> or -c <option> — Name of client class, if it is empty client will not be generated.

--namespace <option> or -n <option> — Root namespace of generated classes.

--soap_version <option> or -v <option> — Soap version: 1 => 1.1 or 2 => 1.2. Default value: 1 => 1.1

--output_dir <option> or -o <option> — Output dir for classes. Default current dir.

--extension <option> — Extension of generated files. Default value: php

--spaces <option> — How much indent would be used in generated files. Default value: 4

--overwrite — Disable overwrite present files. It does not have parameters.

--backup — Disable backup old files. It does not have parameters.

--generate_classes — Generate classes in Types. It does not have parameters.


TODO
----

* Resolve namespaces
* Write more tests for parser
