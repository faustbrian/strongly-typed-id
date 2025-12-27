NanoID is a modern, secure, URL-friendly unique identifier generator designed as a compact alternative to UUID.

## What is NanoID?

NanoID is a tiny, secure, URL-friendly unique string ID generator:

- **Compact**: 21 characters vs 36 for UUID (no hyphens)
- **URL-safe**: Uses alphabet with no special URL encoding needed (`A-Za-z0-9_-`)
- **Secure**: Cryptographically strong randomness using hardware random generator
- **Uniform distribution**: No modulo bias (uses masking + rejection sampling)
- **Collision resistant**: Same probability as UUID v4 for default 21-char length
- **Customizable**: Configurable size and alphabet

Example NanoID: `V1StGXR8_Z5jdHi6B-myT`

## Configuring NanoID Generator

Enable NanoID generation in your application:

```php
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Facades\IdGenerator;

IdGenerator::setGenerator(GeneratorType::NanoId);
```

Now all ID generation will use NanoIDs:

```php
$userId = UserId::generate();
// e.g., "V1StGXR8_Z5jdHi6B-myT"
```

## Custom Size Configuration

Create NanoIDs with custom lengths:

```php
use Cline\StronglyTypedId\Generators\NanoIdGenerator;

// 10-character IDs
$generator = new NanoIdGenerator(size: 10);
$shortId = $generator->generate(); // "V1StGXR8_Z"

// 50-character IDs for extra security
$generator = new NanoIdGenerator(size: 50);
$longId = $generator->generate();
```

## Custom Alphabet Configuration

Use custom character sets for specific requirements:

```php
use Cline\StronglyTypedId\Generators\NanoIdGenerator;

// Numeric only (e.g., for legacy systems)
$generator = new NanoIdGenerator(size: 21, alphabet: '0123456789');

// Hexadecimal
$generator = new NanoIdGenerator(size: 21, alphabet: '0123456789ABCDEF');

// No ambiguous characters (0,O,1,I,5,S)
$generator = new NanoIdGenerator(
    size: 21,
    alphabet: '2346789ABCDEFGHJKLMNPQRTUVWXY'
);
```

## NanoID vs UUID Comparison

| Feature | NanoID | UUID |
|---------|--------|------|
| Length | 21 chars | 36 chars |
| URL-safe | Yes | No (hyphens) |
| Configurable | Yes (size, alphabet) | No (fixed format) |
| Sortable | No | Only v6/v7 |
| Storage | 21 bytes | 36 bytes (string) |

**Advantages of NanoID:**
- 42% shorter for same collision resistance
- URL-friendly by default
- Configurable for specific needs

**Advantages of UUID:**
- Standardized (RFC 4122)
- Native database support
- Time-based versions (v7)

## Security Characteristics

### Collision Resistance

For the default 21-character length with 64-character alphabet:
- **Entropy**: ~126 bits (equivalent to UUID v4's 122 bits)
- **IDs needed for 1% collision probability**: ~8.9×10¹⁸ IDs

### Size vs Security Tradeoff

| Size | Entropy | 1% Collision After |
|------|---------|-------------------|
| 10 chars | ~60 bits | ~1.2 billion IDs |
| 21 chars | ~126 bits | ~8.9×10¹⁸ IDs |
| 32 chars | ~192 bits | Practically never |

## Database Storage

Store NanoIDs as VARCHAR or CHAR:

```php
Schema::create('users', function (Blueprint $table) {
    $table->char('id', 21)->primary(); // Default size
});
```

For chronological queries, add a separate timestamp:

```php
Schema::create('events', function (Blueprint $table) {
    $table->char('id', 21)->primary();
    $table->timestamp('created_at')->index();
});
```

## Alphabet Best Practices

### Default (Recommended)
```php
'_-0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
```

### No Ambiguous Characters
```php
'23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz'
```

### Case-Insensitive (Uppercase Only)
```php
'0123456789ABCDEFGHJKLMNPQRSTUVWXYZ'
```

## Use Cases

**Choose NanoID when:**
- User-facing IDs (shorter, cleaner URLs)
- API keys and tokens
- QR codes (fewer chars = smaller codes)
- Client-side ID generation
- Custom format requirements

**Choose UUID when:**
- Database UUID column types needed
- Time-ordered IDs required (use v7)
- Deterministic generation needed (v3/v5)
- Enterprise system integration
