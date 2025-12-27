---
title: Random String
description: Cryptographically secure alphanumeric identifiers using Laravel's Str::random()
---

Random String generator creates cryptographically secure alphanumeric identifiers using Laravel's `Str::random()` helper, ideal for tokens, passwords, and general-purpose unique identifiers.

## What is Random String?

Random String generates secure, alphanumeric identifiers:

- **Cryptographically secure**: Uses `random_bytes()` internally
- **Alphanumeric**: Contains only `a-z`, `A-Z`, `0-9`
- **Configurable length**: Any length you need
- **Lightweight**: Fast generation
- **Laravel native**: Leverages Laravel's proven implementation

Example Random String: `aB3dEf9Hi2kLmN5pQ7r`

## Basic Usage

Generate random strings with default or custom length:

```php
use Cline\StronglyTypedId\Generators\RandomStringGenerator;

// Default 21-character string
$generator = new RandomStringGenerator();
$id = $generator->generate();
// "aB3dEf9Hi2kLmN5pQ7rSt"

// Custom length
$generator = new RandomStringGenerator(length: 16);
$token = $generator->generate();
// "aB3dEf9Hi2kLmN5p"
```

## Common Use Cases

### API Tokens
```php
// 32-character API token
$generator = new RandomStringGenerator(length: 32);
$apiToken = $generator->generate();
// "aB3dEf9Hi2kLmN5pQ7rStUv1Wx2Yz3A4"
```

### Session IDs
```php
// 40-character session identifier
$generator = new RandomStringGenerator(length: 40);
$sessionId = $generator->generate();
```

### Verification Codes
```php
// 6-character verification code (still alphanumeric)
$generator = new RandomStringGenerator(length: 6);
$code = $generator->generate();
// "aB3dEf"
```

### Reset Tokens
```php
// 64-character password reset token
$generator = new RandomStringGenerator(length: 64);
$resetToken = $generator->generate();
```

## Value Object Integration

Create strongly-typed random string IDs:

```php
use Cline\StronglyTypedId\Generators\RandomStringGenerator;
use Cline\StronglyTypedId\ValueObjects\StronglyTypedId;

final readonly class ApiToken extends StronglyTypedId
{
    protected static function generator(): IdGeneratorInterface
    {
        return new RandomStringGenerator(length: 32);
    }
}

// Usage
$token = ApiToken::generate();
echo $token; // "aB3dEf9Hi2kLmN5pQ7rStUv1Wx2Yz3A4"
```

## Laravel Model Integration

Use with Eloquent models:

```php
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $casts = [
        'token' => ApiToken::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (ApiKey $key) {
            $key->token = ApiToken::generate();
        });
    }
}
```

Migration:

```php
Schema::create('api_keys', function (Blueprint $table) {
    $table->id();
    $table->string('token', 32)->unique();
    $table->timestamps();
});
```

## Combining with Prefixed IDs

Create prefixed random string IDs:

```php
use Cline\StronglyTypedId\Generators\PrefixedIdGenerator;
use Cline\StronglyTypedId\Generators\RandomStringGenerator;

// Session ID with prefix
$generator = new PrefixedIdGenerator(
    'sess',
    new RandomStringGenerator(length: 40)
);
$sessionId = $generator->generate();
// "sess_aB3dEf9Hi2kLmN5pQ7rStUv1Wx2Yz3A4B5C6D7"
```

## Security Characteristics

### Randomness Source
- Uses PHP's `random_bytes()` via Laravel's `Str::random()`
- Cryptographically secure random number generator (CSPRNG)
- Suitable for security-sensitive applications

### Entropy Calculation

For alphanumeric charset (62 characters: a-z, A-Z, 0-9):

| Length | Entropy | Collision Resistance |
|--------|---------|---------------------|
| 6 chars | ~36 bits | ~68 billion combinations |
| 16 chars | ~95 bits | ~3.5×10²⁸ combinations |
| 21 chars | ~125 bits | ~5.2×10³⁷ combinations |
| 32 chars | ~190 bits | ~1.6×10⁵⁷ combinations |

### Use for Security Tokens

```php
// High-entropy token for password reset
$generator = new RandomStringGenerator(length: 64);
$resetToken = $generator->generate();
// 381 bits of entropy - extremely secure
```

## Database Storage

Store as VARCHAR with exact length:

```php
Schema::create('tokens', function (Blueprint $table) {
    $table->string('token', 32)->unique(); // Exact length
    $table->timestamp('expires_at');
    $table->timestamps();
});
```

Index for lookups:

```php
$table->string('token', 32)->unique()->index();
```

## Length Recommendations

| Use Case | Recommended Length | Rationale |
|----------|-------------------|-----------|
| Public IDs | 21 | Balance of security and usability |
| API Keys | 32 | High security, reasonable length |
| Session IDs | 40 | Session fixation resistance |
| Password Reset | 64 | Maximum security for sensitive ops |
| Verification Codes | 6-8 | User-friendly while secure enough |
| Internal Tokens | 16 | Fast generation, adequate security |

## Random String vs Other Generators

### vs UUID
- **Random String**: Shorter, configurable length, alphanumeric only
- **UUID**: Standardized, 36 chars with hyphens, time-ordered variants

### vs NanoID
- **Random String**: Pure alphanumeric (62 chars)
- **NanoID**: URL-safe with `_-` (64 chars), configurable alphabet

### vs Random Bytes
- **Random String**: Alphanumeric output
- **Random Bytes**: Hexadecimal output (only 16 chars)

### vs Sqids
- **Random String**: Pure random, not encodeable/decodeable
- **Sqids**: Encodes numbers, deterministic, decodeable

## Best Practices

### Token Storage
```php
// Hash tokens before storage (for API keys)
use Illuminate\Support\Facades\Hash;

$generator = new RandomStringGenerator(length: 32);
$plainToken = $generator->generate();
$hashedToken = Hash::make($plainToken);

// Store $hashedToken in database
// Return $plainToken to user (only once)
```

### Expiration Handling
```php
Schema::create('tokens', function (Blueprint $table) {
    $table->string('token', 32)->unique();
    $table->timestamp('expires_at')->index();
    $table->timestamps();
});

// Query only non-expired tokens
Token::where('expires_at', '>', now())
    ->where('token', $plainToken)
    ->first();
```

### Rate Limiting Token Generation
```php
use Illuminate\Support\Facades\RateLimiter;

public function generateApiToken()
{
    RateLimiter::attempt(
        'generate-token:'.$this->user->id,
        $perMinute = 5,
        function () {
            $generator = new RandomStringGenerator(length: 32);
            return $generator->generate();
        }
    );
}
```

## Use Cases

**Choose Random String when:**
- Generating API tokens
- Creating session identifiers
- Building password reset tokens
- Needing verification codes
- Want pure alphanumeric output
- Leveraging Laravel's ecosystem

**Choose other generators for:**
- **UUID**: Need time-ordered IDs or RFC compliance
- **NanoID**: Want URL-safe with custom alphabet
- **Random Bytes**: Need hexadecimal output
- **Sqids**: Need encodeable/decodeable IDs
