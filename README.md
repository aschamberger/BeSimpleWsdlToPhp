# BeSimpleWsdlToPhp [![Build Status](https://travis-ci.org/aschamberger/BeSimpleWsdlToPhp.png?branch=master)](https://travis-ci.org/aschamberger/BeSimpleWsdlToPhp) [![Coverage Status](https://coveralls.io/repos/aschamberger/BeSimpleWsdlToPhp/badge.png?branch=master)](https://coveralls.io/r/aschamberger/BeSimpleWsdlToPhp)

wsdl2php - takes a WSDL document and generates PHP code from which to implement
a service. The WSDL document must have a valid portType element, but it does not
need to contain a binding element or a service element. It is always assumed the
given [doc-lit WSDLs "unwrap" nicely](http://pzf.fremantle.org/2007/05/handlign.html)

Using the optional arguments you can customize the generated code.

# Installation

If you do not yet have composer, install it like this:

```sh
curl -s http://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin
```

Create a `composer.json` file:

```json
{
    "require": {
        "besimple/wsdl2php": "dev-master"
    }
}
```

Now you are ready to install the library:

```sh
php /usr/local/bin/composer.phar install
```

# Run

```sh
php bin/wsdl2php.php -w"myservice.wsdl"
```
