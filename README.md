[![GitHub Workflow Status][ico-tests]][link-tests]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

------

This library provides strongly-typed ID value objects for PHP 8.4+, designed to create type-safe entity identifiers with support for multiple ID generation strategies including UUID variants (v1, v3, v4, v5, v6, v7, v8), ULID, NanoID, GUID, Sqids, Hashids, Prefixed IDs (Stripe-style with configurable generators), Random String, and Random Bytes.

## Requirements

> **Requires [PHP 8.4+](https://php.net/releases/)**

## Installation

```bash
composer require cline/strongly-typed-id
```

## Documentation

- **[Getting Started](https://docs.cline.sh/strongly-typed-id/getting-started/)** - Installation and quick start
- **[Basic Usage](https://docs.cline.sh/strongly-typed-id/basic-usage/)** - Creating and using IDs
- **[Laravel Integration](https://docs.cline.sh/strongly-typed-id/laravel-integration/)** - Eloquent casts and Data DTOs
- **[UUID Variants](https://docs.cline.sh/strongly-typed-id/uuid-variants/)** - UUID versions v1-v8
- **[ULID](https://docs.cline.sh/strongly-typed-id/ulid/)** - Sortable identifiers
- **[NanoID](https://docs.cline.sh/strongly-typed-id/nanoid/)** - Compact URL-friendly IDs
- **[GUID](https://docs.cline.sh/strongly-typed-id/guid/)** - Windows/.NET compatibility
- **[Sqid](https://docs.cline.sh/strongly-typed-id/sqid/)** - Short URL-safe identifiers
- **[Hashids](https://docs.cline.sh/strongly-typed-id/hashids/)** - Obfuscated IDs with salt
- **[Prefixed IDs](https://docs.cline.sh/strongly-typed-id/prefixed-id/)** - Stripe-style prefixed identifiers
- **[Random String](https://docs.cline.sh/strongly-typed-id/random-string/)** - Alphanumeric with Laravel's Str::random()
- **[Random Bytes](https://docs.cline.sh/strongly-typed-id/random-bytes/)** - Hexadecimal with PHP's random_bytes()
- **[Advanced Patterns](https://docs.cline.sh/strongly-typed-id/advanced-patterns/)** - DDD, CQRS, multi-tenancy

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please use the [GitHub security reporting form][link-security] rather than the issue queue.

## Credits

- [Brian Faust][link-maintainer]
- [All Contributors][link-contributors]

## License

The MIT License. Please see [License File](LICENSE.md) for more information.

[ico-tests]: https://github.com/faustbrian/strongly-typed-id/actions/workflows/quality-assurance.yaml/badge.svg
[ico-version]: https://img.shields.io/packagist/v/cline/strongly-typed-id.svg
[ico-license]: https://img.shields.io/badge/License-MIT-green.svg
[ico-downloads]: https://img.shields.io/packagist/dt/cline/strongly-typed-id.svg

[link-tests]: https://github.com/faustbrian/strongly-typed-id/actions
[link-packagist]: https://packagist.org/packages/cline/strongly-typed-id
[link-downloads]: https://packagist.org/packages/cline/strongly-typed-id
[link-security]: https://github.com/faustbrian/strongly-typed-id/security
[link-maintainer]: https://github.com/faustbrian
[link-contributors]: ../../contributors
