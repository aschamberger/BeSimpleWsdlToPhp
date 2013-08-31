# BeSimpleWsdlToPhp

wsdl2php - takes a WSDL document and generates PHP code from which to implement a service.

# Installation

If you do not yet have composer, install it like this:

```sh
curl -s http://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin
```

Create a `composer.json` file:

```json
{
    "require": {
        "besimple/soap-common": "dev-master"
    }
}
```

Now you are ready to install the library:

```sh
php /usr/local/bin/composer.phar install
```

# Run

wsdl2php -w myservice.wsdl