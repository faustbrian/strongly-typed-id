[![GitHub Workflow Status][ico-tests]][link-tests]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

------

This library provides strongly-typed ID value objects for PHP 8.4+, designed to create type-safe entity identifiers with support for multiple ID generation strategies including UUID variants (v1, v3, v4, v5, v6, v7, v8), ULID, NanoID, GUID, Sqid, and Hashids.

## Requirements

> **Requires [PHP 8.4+](https://php.net/releases/)**

## Installation

```bash
composer require cline/strongly-typed-id
```

## Documentation

- **[Basic Usage](cookbook/basic-usage.md)** - Creating and using strongly-typed IDs
- **[UUID Variants](cookbook/uuid-variants.md)** - Comprehensive guide to UUID versions v1-v8
- **[ULID Usage](cookbook/ulid-usage.md)** - Working with ULIDs for better sortability
- **[NanoID Usage](cookbook/nanoid-usage.md)** - Compact, secure, URL-friendly IDs
- **[GUID Usage](cookbook/guid-usage.md)** - Using GUIDs for Windows/.NET compatibility
- **[Sqid Usage](cookbook/sqid-usage.md)** - Short, unique, URL-safe identifiers
- **[Hashids Usage](cookbook/hashids-usage.md)** - Obfuscated short IDs with salt configuration
- **[Laravel Integration](cookbook/laravel-integration.md)** - Eloquent, casts, relationships, and Spatie Laravel Data
- **[Advanced Patterns](cookbook/advanced-patterns.md)** - DDD, CQRS, event sourcing, and multi-tenancy

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
