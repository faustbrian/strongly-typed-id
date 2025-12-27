---
title: Getting Started
description: Strongly-typed ID value objects for PHP with support for UUID, ULID, NanoID, GUID, Sqid, Hashids and Laravel integration
---

## Installation

Install via Composer:

```bash
composer require cline/strongly-typed-id
```

## What is Strongly Typed ID?

Strongly Typed ID provides type-safe identifier value objects for PHP applications. Instead of passing raw strings or integers as IDs, you get compile-time type safety that prevents mixing IDs across different entity types.

### Key Features

- **Type Safety**: Distinct ID types prevent accidental mixing (e.g., `UserId` vs `OrderId`)
- **Immutability**: All IDs are readonly value objects
- **Multiple Formats**: UUID (all versions), ULID, NanoID, GUID, Sqid, Hashids
- **Laravel Integration**: Eloquent casts, Spatie Laravel Data support
- **DDD-Friendly**: Perfect for domain-driven design aggregates

## Quick Start

### Creating ID Classes

Create strongly-typed IDs by extending the base class:

```php
use Cline\StronglyTypedId\ValueObjects\StronglyTypedId;

final readonly class UserId extends StronglyTypedId {}
final readonly class OrderId extends StronglyTypedId {}
final readonly class ProductId extends StronglyTypedId {}
```

### Generating New IDs

```php
$userId = UserId::generate();
// e.g., "018c5d6e-5f89-7a9b-9c1d-2e3f4a5b6c7d" (UUID v7 by default)
```

### Creating from Strings

```php
$userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
```

### Type Safety in Action

```php
function findUser(UserId $id): User
{
    // Implementation
}

$userId = UserId::generate();
$orderId = OrderId::generate();

findUser($userId);   // ✓ Valid
findUser($orderId);  // ✗ Type error: Expected UserId, got OrderId
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=strongly-typed-id-config
```

Configure your preferred ID generator in `config/strongly-typed-id.php`:

```php
return [
    // Default generator: uuid_v1, uuid_v3, uuid_v4, uuid_v5, uuid_v6, uuid_v7, uuid_v8, ulid
    'generator' => env('STRONGLY_TYPED_ID_GENERATOR', 'uuid_v7'),
];
```

Or set it programmatically:

```php
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Facades\IdGenerator;

IdGenerator::setGenerator(GeneratorType::UuidV7);
```

## Choosing an ID Format

| Format | Length | Sortable | Best For |
|--------|--------|----------|----------|
| **UUID v7** | 36 chars | Yes | Database PKs (recommended) |
| **UUID v4** | 36 chars | No | Random IDs, legacy systems |
| **ULID** | 26 chars | Yes | Compact sortable IDs |
| **NanoID** | 21 chars | No | URL-safe short IDs |
| **Sqid** | Variable | No | Obfuscated integer encoding |
| **Hashid** | Variable | No | Reversible integer encoding |

## Next Steps

- **[Basic Usage](basic-usage)** - Core ID operations and patterns
- **[Laravel Integration](laravel-integration)** - Eloquent casts and Data DTOs
- **[UUID Variants](uuid-variants)** - All UUID versions explained
- **[ULID](ulid)** - Lexicographically sortable identifiers
- **[Advanced Patterns](advanced-patterns)** - DDD and complex use cases
