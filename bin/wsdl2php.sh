#!/bin/bash
/usr/bin/env php5 -f "$(dirname "$0")/wsdl2php.php" -- "$@"