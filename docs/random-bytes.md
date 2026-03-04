Random Bytes generator creates cryptographically secure hexadecimal identifiers using PHP's `random_bytes()` function, ideal for security tokens, session IDs, and contexts requiring strong randomness.

## What is Random Bytes?

Random Bytes generates secure, hexadecimal identifiers:

- **Cryptographically secure**: Uses PHP's CSPRNG
- **Hexadecimal encoding**: Output contains only `0-9a-f`
- **Configurable byte length**: Any length you need
- **Predictable output**: Always `bytes * 2` characters
- **Native PHP**: No external dependencies

Example Random Bytes: `4f3b7c2a8e1d9f6b5a3c8e2d7f1b4a9c`

## Basic Usage

Generate hexadecimal strings from random bytes:

```php
use Cline\StronglyTypedId\Generators\RandomBytesGenerator;

// Default 16 bytes = 32 hex characters
$generator = new RandomBytesGenerator();
$id = $generator->generate();
// "4f3b7c2a8e1d9f6b5a3c8e2d7f1b4a9c"

// Custom byte count
$generator = new RandomBytesGenerator(bytes: 32);
$token = $generator->generate();
// "4f3b7c2a8e1d9f6b5a3c8e2d7f1b4a9c1e2d3c4b5a6978869786a5b4c3d2e1f0" (64 chars)
```

## Output Length Calculation

Output is always exactly `bytes * 2` characters:

```php
// 8 bytes = 16 hex characters
$generator = new RandomBytesGenerator(bytes: 8);
echo strlen($generator->generate()); // 16

// 16 bytes = 32 hex characters (default)
$generator = new RandomBytesGenerator(bytes: 16);
echo strlen($generator->generate()); // 32

// 32 bytes = 64 hex characters
$generator = new RandomBytesGenerator(bytes: 32);
echo strlen($generator->generate()); // 64
```

## Common Use Cases

### Security Tokens
```php
// 32-byte (256-bit) security token
$generator = new RandomBytesGenerator(bytes: 32);
$securityToken = $generator->generate();
// "4f3b7c2a8e1d9f6b5a3c8e2d7f1b4a9c1e2d3c4b5a6978869786a5b4c3d2e1f0"
```

### Session IDs
```php
// 20-byte session identifier
$generator = new RandomBytesGenerator(bytes: 20);
$sessionId = $generator->generate();
// "4f3b7c2a8e1d9f6b5a3c8e2d7f1b4a9c1e2d3c4b" (40 chars)
```

### CSRF Tokens
```php
// 16-byte CSRF token
$generator = new RandomBytesGenerator(bytes: 16);
$csrfToken = $generator->generate();
// "4f3b7c2a8e1d9f6b5a3c8e2d7f1b4a9c" (32 chars)
```

### Encryption Keys
```php
// 32-byte (256-bit) encryption key
$generator = new RandomBytesGenerator(bytes: 32);
$encryptionKey = $generator->generate();
```

## Value Object Integration

Create strongly-typed random bytes IDs:

```php
use Cline\StronglyTypedId\Generators\RandomBytesGenerator;
use Cline\StronglyTypedId\ValueObjects\StronglyTypedId;

final readonly class SecurityToken extends StronglyTypedId
{
    protected static function generator(): IdGeneratorInterface
    {
        return new RandomBytesGenerator(bytes: 32);
    }
}

// Usage
$token = SecurityToken::generate();
echo $token; // "4f3b7c2a8e1d9f6b5a3c8e2d7f1b4a9c1e2d3c4b5a6978869786a5b4c3d2e1f0"
```

## Laravel Model Integration

Use with Eloquent models:

```php
use Illuminate\Database\Eloquent\Model;

class SecureToken extends Model
{
    protected $casts = [
        'token' => SecurityToken::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (SecureToken $token) {
            $token->token = SecurityToken::generate();
        });
    }
}
```

Migration:

```php
Schema::create('secure_tokens', function (Blueprint $table) {
    $table->id();
    $table->string('token', 64)->unique(); // 32 bytes = 64 hex chars
    $table->timestamp('expires_at');
    $table->timestamps();
});
```

## Combining with Prefixed IDs

Create prefixed hexadecimal IDs:

```php
use Cline\StronglyTypedId\Generators\PrefixedIdGenerator;
use Cline\StronglyTypedId\Generators\RandomBytesGenerator;

// Security token with prefix
$generator = new PrefixedIdGenerator(
    'sec',
    new RandomBytesGenerator(bytes: 32)
);
$secToken = $generator->generate();
// "sec_4f3b7c2a8e1d9f6b5a3c8e2d7f1b4a9c1e2d3c4b5a6978869786a5b4c3d2e1f0"
```

## Security Characteristics

### Randomness Source
- Uses PHP's `random_bytes()` function
- Cryptographically secure pseudo-random number generator (CSPRNG)
- Platform-dependent source (e.g., `/dev/urandom` on Unix)

### Entropy Calculation

Hexadecimal provides 4 bits per character:

| Bytes | Hex Chars | Entropy | Security Level |
|-------|-----------|---------|----------------|
| 8 | 16 | 64 bits | Basic |
| 16 | 32 | 128 bits | Standard |
| 32 | 64 | 256 bits | High |
| 64 | 128 | 512 bits | Maximum |

### Recommended Byte Counts

| Use Case | Bytes | Hex Length | Entropy |
|----------|-------|------------|---------|
| CSRF Tokens | 16 | 32 | 128 bits |
| Session IDs | 20 | 40 | 160 bits |
| API Tokens | 32 | 64 | 256 bits |
| Encryption Keys | 32 | 64 | 256 bits |
| Master Secrets | 64 | 128 | 512 bits |

## Database Storage

Store as fixed-length CHAR or VARCHAR:

```php
Schema::create('tokens', function (Blueprint $table) {
    // 16 bytes = 32 hex characters
    $table->char('token', 32)->unique();

    // 32 bytes = 64 hex characters
    $table->char('secure_token', 64)->unique();

    $table->timestamps();
});
```

Binary storage for space efficiency:

```php
// Store as binary instead of hex string
Schema::create('tokens', function (Blueprint $table) {
    $table->binary('token', 32)->unique(); // 32 bytes raw
    $table->timestamps();
});

// Custom casting
protected $casts = [
    'token' => 'binary',
];
```

## Hexadecimal vs Binary Storage

### String Storage (Hexadecimal)
```php
// 32 bytes = 64 character hex string
$table->char('token', 64);

// Pros: Human-readable, easy to debug
// Cons: 2x storage space
```

### Binary Storage
```php
// 32 bytes = 32 byte binary
$table->binary('token', 32);

// Pros: 50% storage savings
// Cons: Not human-readable
```

## Random Bytes vs Other Generators

### vs Random String
- **Random Bytes**: Hexadecimal (16 chars), predictable length
- **Random String**: Alphanumeric (62 chars), configurable

### vs UUID
- **Random Bytes**: Configurable length, no structure
- **UUID**: Fixed 36-char format, version metadata

### vs NanoID
- **Random Bytes**: Hex only, higher entropy per char
- **NanoID**: URL-safe alphabet, more compact

## Best Practices

### Token Hashing
```php
use Illuminate\Support\Facades\Hash;

$generator = new RandomBytesGenerator(bytes: 32);
$plainToken = $generator->generate();

// Hash before storage
$hashedToken = Hash::make($plainToken);
```

### Constant-Time Comparison
```php
// Prevent timing attacks
if (hash_equals($storedToken, $providedToken)) {
    // Valid token
}
```

### Token Expiration
```php
Schema::create('tokens', function (Blueprint $table) {
    $table->char('token', 64)->unique();
    $table->timestamp('expires_at')->index();
});

// Clean up expired tokens
Token::where('expires_at', '<', now())->delete();
```

### Rate Limiting
```php
use Illuminate\Support\Facades\RateLimiter;

public function generateToken()
{
    return RateLimiter::attempt(
        'token-gen:'.$this->user->id,
        $perMinute = 5,
        function () {
            $generator = new RandomBytesGenerator(bytes: 32);
            return $generator->generate();
        }
    );
}
```

## Use Cases

**Choose Random Bytes when:**
- Maximum cryptographic security needed
- Generating encryption keys
- Creating security tokens
- Building authentication systems
- Need deterministic output length
- Working with binary data

**Choose other generators for:**
- **Random String**: Need alphanumeric (more compact)
- **UUID**: Need time-ordered or RFC-compliant IDs
- **NanoID**: Need URL-safe with custom alphabet
- **Sqids**: Need encodeable/decodeable IDs

## Security Recommendations

### Minimum Lengths
- **Never use less than 16 bytes** for security tokens
- **Use 32 bytes** for high-security applications
- **Use 64 bytes** for master secrets and encryption keys

### Storage Security
```php
// Encrypt tokens at rest
use Illuminate\Support\Facades\Crypt;

$encrypted = Crypt::encryptString($token);
$decrypted = Crypt::decryptString($encrypted);
```

### Transmission Security
- Always use HTTPS for token transmission
- Never log tokens in plain text
- Rotate tokens periodically
- Implement token revocation
