---
title: Sqid
description: Short, unique, URL-safe identifiers with configurable length and alphabet
---

Sqids (pronounced "squids") generate short, unique, URL-safe identifiers by encoding numeric values.

## What is Sqid?

Sqids generate short, unique, URL-safe identifiers:

- **Short**: Configurable minimum length (default: 8 characters)
- **URL-safe**: Uses alphanumeric characters only
- **Customizable**: Configurable alphabet and minimum length
- **Deterministic**: Same numbers always produce same Sqid
- **Human-friendly**: Short and readable compared to UUIDs

Example Sqid: `4d9fND1xQ`

## Configuring Sqid Generator

Enable Sqid generation in your application:

```php
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Facades\IdGenerator;

IdGenerator::setGenerator(GeneratorType::Sqid);
```

Now all ID generation will use Sqids:

```php
$userId = UserId::generate();
// e.g., "4d9fND1xQ"
```

## Custom Configuration

### Minimum Length

```php
use Cline\StronglyTypedId\Generators\SqidGenerator;

$generator = new SqidGenerator(minLength: 16);
$id = $generator->generate();
// e.g., "4d9fND1xQ8bWePmY"
```

### Custom Alphabet

```php
$generator = new SqidGenerator(
    alphabet: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
    minLength: 8
);
$id = $generator->generate();
// e.g., "X4M9FND1"
```

## Comparison

| Type | Length | Example |
|------|--------|---------|
| Sqid | 8+ chars | `4d9fND1xQ` |
| ULID | 26 chars | `01ARZ3NDEKTSV4RRFFQ69G5FAV` |
| UUID | 36 chars | `550e8400-e29b-41d4-a716-446655440000` |

**Advantages:**
- 42-78% shorter than UUID/ULID
- URL-safe without encoding
- Configurable for specific needs

**Limitations:**
- No embedded timestamp (not sortable)
- Higher collision probability at short lengths

## Database Storage

Store Sqids as VARCHAR with appropriate length:

```php
Schema::create('users', function (Blueprint $table) {
    $table->string('id', 16)->primary();
});
```

## Use Cases

**Choose Sqid when:**
- URL shorteners
- Public-facing IDs (cleaner URLs)
- QR codes (shorter = simpler)
- Mobile applications (bandwidth savings)
- Invoice/order numbers

**Choose UUID/ULID when:**
- Time-ordered IDs needed
- Maximum collision resistance required
- Database UUID types preferred
