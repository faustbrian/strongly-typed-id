---
title: Hashids
description: Obfuscated short identifiers with salt-based encoding for enumeration protection
---

Hashids generate short, unique, URL-safe identifiers with obfuscation via salt, preventing enumeration attacks.

## What is Hashids?

Hashids encode numeric values with obfuscation:

- **Short**: Configurable minimum length (default: 8 characters)
- **URL-safe**: Uses alphanumeric characters only
- **Obfuscated**: Salt prevents guessing encoded values
- **Bidirectional**: Can decode back to original numbers
- **Enumeration-resistant**: Salt prevents sequential ID discovery

Example Hashids: `Xb9kLm2N`

## Configuring Hashids Generator

Enable Hashids generation in your application:

```php
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Facades\IdGenerator;

IdGenerator::setGenerator(GeneratorType::Hashids);
```

Now all ID generation will use Hashids:

```php
$userId = UserId::generate();
// e.g., "Xb9kLm2N"
```

## Custom Configuration

### Salt Configuration

Configure a unique salt for obfuscation:

```php
use Cline\StronglyTypedId\Generators\HashidsGenerator;

$generator = new HashidsGenerator(salt: 'my-secret-salt');
$id = $generator->generate();
```

**Important:**
- Salt makes IDs unpredictable
- Use different salts for different applications
- Never expose your salt publicly
- Store salt in environment variables

### Minimum Length

```php
$generator = new HashidsGenerator(
    salt: 'my-secret-salt',
    minLength: 16
);
```

### Custom Alphabet

```php
$generator = new HashidsGenerator(
    salt: 'my-secret-salt',
    minLength: 8,
    alphabet: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
);
```

## Hashids vs Sqid

| Feature | Hashids | Sqid |
|---------|---------|------|
| Salt support | Yes | No |
| Enumeration protection | Strong | Basic |
| Configuration | Salt required | Simpler |
| Security | Higher | Lower |

**Choose Hashids when:**
- Enumeration attacks are a concern
- Obfuscating database IDs
- Security through obscurity needed

**Choose Sqid when:**
- No secret management preferred
- Simpler stateless generation

## Salt Management

```php
// Store salt securely in environment
$generator = new HashidsGenerator(salt: env('HASHIDS_SALT'));

// Different salts per entity type
$userIdGenerator = new HashidsGenerator(salt: env('USER_ID_SALT'));
$orderIdGenerator = new HashidsGenerator(salt: env('ORDER_ID_SALT'));
```

## Database Storage

Store Hashids as VARCHAR with appropriate length:

```php
Schema::create('users', function (Blueprint $table) {
    $table->string('id', 16)->primary();
});
```

## Use Cases

**Choose Hashids when:**
- Database ID obfuscation
- Preventing enumeration attacks
- Public-facing IDs needing security
- URL shorteners with security
- API resource identifiers

**Choose UUID/ULID when:**
- No obfuscation needed
- Time-ordered IDs required
- Maximum collision resistance
- No secret management desired
