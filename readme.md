# WhoisProxy

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![License][ico-license]][link-license]

Whois Proxy lets you easily query a whois server from behind an http proxy.

During the query process, the package will connect to the http proxy you provide. Once successfully connected the package will query the default whois server, or a provided whois server on port 43 with the supplied domain and when successful the package will return the raw whois query.

This is for educational purposes only and not meant to circumvent any of the rate-limiting or ip banning systems these whois servers may have implemented. Use at your own risk. 

## Installation

Install this package via composer:

``` bash
$ composer require xandco/whoisproxy
```

This service provider must be installed (if using anything below Laravel 5.5)

``` php
// config/app.php

'providers' => [
    xandco\WhoisProxy\WhoisProxyServiceProvider::class,
];
```

Publish and customize configuration file with:

``` bash
$ php artisan vendor:publish --provider="xandco\WhoisProxy\WhoisProxyServiceProvider"
```

## Usage

Create new `WhoisProxy` object:

``` php
use xandco\WhoisProxy\WhoisProxy;
...
$whoisProxy = new WhoisProxy( $options = [] );
```

Then call `query()` method to query a whois server:

``` php
// This will query the default whois server
$whoisProxy->query( 'example.com' );

// You can also provide a specific whois server
$whoisProxy->query( 'example.com', 'whois.verisign-grs.com' );
```

You can also call the `deepQuery()` method to automatically find and query the most authoritative whois server for the specified domain (usually the registrars whois server):

``` php
$whoisProxy->deepQuery( 'example.com' );
```

Here is an example of the output:

``` text
Domain Name: EXAMPLE.COM
Registry Domain ID: 2336799_DOMAIN_COM-VRSN
Registrar WHOIS Server: whois.iana.org
Registrar URL: http://res-dom.iana.org
Updated Date: 2019-08-14T07:04:41Z
Creation Date: 1995-08-14T04:00:00Z
Registry Expiry Date: 2020-08-13T04:00:00Z
Registrar: RESERVED-Internet Assigned Numbers Authority
Registrar IANA ID: 376
Registrar Abuse Contact Email:
Registrar Abuse Contact Phone:
Domain Status: clientDeleteProhibited https://icann.org/epp#clientDeleteProhibited
Domain Status: clientTransferProhibited https://icann.org/epp#clientTransferProhibited
Domain Status: clientUpdateProhibited https://icann.org/epp#clientUpdateProhibited
Name Server: A.IANA-SERVERS.NET
Name Server: B.IANA-SERVERS.NET
DNSSEC: signedDelegation
URL of the ICANN Whois Inaccuracy Complaint Form: https://www.icann.org/wicf/
>>> Last update of whois database: 2020-07-07T07:56:32Z <<<

For more information on Whois status codes, please visit https://icann.org/epp

...
```

### Options

When creating the `WhoisProxy` object, you can pass one parameter: `$options`.

Options array parameters:

| Option      | Notes                | Type     | Default          |
|-------------|----------------------|----------|------------------|
| `host`      | proxy host           | `string` | `127.0.0.1`      |
| `port`      | proxy port           | `int`    | `8080`           |
| `timeout`   | timeout in seconds   | `int`    | `10`             |
| `server`    | default whois server | `string` | `whois.iana.org` |
| `max_loops` | max while loops      | `int`    | `512`            |

Instead of setting these options when creating the object, you can alternatively set these globally in the configuration file. You can publish the configuration and customize it as shown in the [Installation](#installation) section.

## Changelog

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email [hello@xand.co](mailto:hello@xand.co) instead of using the issue tracker.

## Credits

- [X&Co][link-company]
- [Miguel Batres][link-author]
- [All Contributors][link-contributors]

## License

MIT - Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/xandco/whoisproxy.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/xandco/whoisproxy.svg?style=flat-square
[ico-license]: https://img.shields.io/packagist/l/xandco/whoisproxy?style=flat-square

[link-packagist]: https://packagist.org/packages/xandco/whoisproxy
[link-downloads]: https://packagist.org/packages/xandco/whoisproxy
[link-author]: https://github.com/btrsco
[link-company]: https://github.com/xandco
[link-license]: https://github.com/xandco/whoisproxy/blob/master/license.md
[link-contributors]: ../../contributors
