# Hashids Usage

Hashids generate short, unique, URL-safe identifiers that encode numeric values with obfuscation. This guide covers using Hashids with strongly-typed IDs.

## What is Hashids?

Hashids is a library that generates short, unique, URL-safe identifiers by encoding numeric values:

- **Short**: Configurable minimum length (default: 8 characters)
- **URL-safe**: Uses alphanumeric characters only
- **Customizable**: Configurable salt, alphabet, and minimum length
- **Bidirectional**: Decode back to original numbers
- **Obfuscated**: Salt prevents guessing encoded values
- **Human-friendly**: Short and readable compared to UUIDs
- **Enumeration-resistant**: Salt prevents sequential ID discovery

Example Hashids: `Xb9kLm2N`

## Configuring Hashids Generator

Enable Hashids generation in your application:

```php
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Facades\IdGenerator;

// In your service provider or bootstrap
IdGenerator::setGenerator(GeneratorType::Hashids);
```

Now all ID generation will use Hashids:

```php
$userId = UserId::generate();
// e.g., "Xb9kLm2N"
```

## Creating IDs from Hashids Strings

Hashids are compatible with the standard `fromString()` method:

```php
$userId = UserId::fromString('Xb9kLm2N');
```

Validation ensures the string is a valid Hashids format:

```php
try {
    $userId = UserId::fromString('invalid-hashid!@#');
} catch (InvalidArgumentException $e) {
    // Handle invalid Hashids
}
```

## Custom Configuration

### Salt Configuration

Configure a unique salt for obfuscation:

```php
use Cline\StronglyTypedId\Generators\HashidsGenerator;

// Create generator with custom salt
$generator = new HashidsGenerator(salt: 'my-secret-salt');
$id = $generator->generate();
// e.g., "Xb9kLm2N"
```

**Important:**
- Salt makes IDs unpredictable and prevents enumeration attacks
- Use different salts for different applications/environments
- Never expose your salt publicly
- Keep salt consistent across your application

### Minimum Length

Configure minimum length for generated Hashids:

```php
use Cline\StronglyTypedId\Generators\HashidsGenerator;

// Create generator with custom minimum length
$generator = new HashidsGenerator(
    salt: 'my-secret-salt',
    minLength: 16
);
$id = $generator->generate();
// e.g., "Xb9kLm2NpQ7rT3vY"
```

Common minimum lengths:
- **8 characters** (default): Compact, suitable for most use cases
- **16 characters**: Higher collision resistance
- **32 characters**: Maximum security for sensitive applications

### Custom Alphabet

Define a custom alphabet for encoding:

```php
use Cline\StronglyTypedId\Generators\HashidsGenerator;

// Use only uppercase letters and numbers
$generator = new HashidsGenerator(
    salt: 'my-secret-salt',
    minLength: 8,
    alphabet: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
);
$id = $generator->generate();
// e.g., "X4M9FND1"
```

**Important:**
- Custom alphabets must have at least 16 unique characters
- Duplicate characters are automatically removed by Hashids
- Alphabets with spaces are rejected
- Same alphabet must be used for encoding and decoding
- Consider excluding confusing characters (0/O, 1/l/I)

## Hashids vs Sqid vs UUID Comparison

### Advantages of Hashids

1. **Obfuscation via Salt**
   - Prevents enumeration attacks
   - Makes sequential patterns unpredictable
   - Requires salt to decode

2. **Bidirectional Encoding**
   - Can decode back to original numbers
   - Useful for database ID obfuscation
   - Deterministic with same input

3. **Extremely Compact**
   - Hashids: `Xb9kLm2N` (8-16 chars, configurable)
   - Sqid: `4d9fND1xQ` (8-16 chars, configurable)
   - ULID: `01ARZ3NDEKTSV4RRFFQ69G5FAV` (26 chars)
   - UUID: `01902ed6-76cf-7bd2-b228-e11038cf0756` (36 chars)

4. **URL-Safe**
   - Alphanumeric only (no special characters)
   - No encoding required for URLs

5. **Human-Friendly**
   - Short enough to read and type
   - Less error-prone than long identifiers

### Advantages of Sqid

1. **No Salt Required**
   - Simpler configuration
   - No secret management
   - Stateless generation

2. **Similar Compactness**
   - Same length as Hashids
   - Configurable alphabet and minimum length

### Advantages of UUID

1. **Universally Recognized**
   - Industry standard (RFC 4122)
   - Widespread tool support

2. **Multiple Versions**
   - Specialized variants for different use cases
   - Time-based and name-based options

3. **Native Database Support**
   - UUID column types in PostgreSQL, MySQL, etc.
   - Built-in functions and indexes

4. **Guaranteed Uniqueness**
   - 122-128 bits of entropy
   - Virtually zero collision probability

### Advantages of ULID

1. **Timestamp-Based Sorting**
   - Chronological ordering
   - Natural time-series queries

2. **Larger Entropy**
   - 80 bits of randomness
   - Better collision resistance than Hashids/Sqids

3. **Case-Insensitive**
   - More forgiving for manual entry
   - Reduces user errors

## Database Storage

### String Storage

Store Hashids as VARCHAR with appropriate length:

```php
// Migration
Schema::create('users', function (Blueprint $table) {
    $table->string('id', 16)->primary(); // Adjust based on minLength
});
```

**Storage requirements:**
- 8-char Hashids: VARCHAR(8) or CHAR(8)
- 16-char Hashids: VARCHAR(16) or CHAR(16)
- Variable length: VARCHAR(32) for safety

### Storage Efficiency Comparison

For 1 million records:

| Type | Length | Storage Size |
|------|--------|-------------|
| Hashids (8 chars) | 8 bytes | 8 MB |
| Hashids (16 chars) | 16 bytes | 16 MB |
| Sqid (8 chars) | 8 bytes | 8 MB |
| ULID | 26 bytes | 26 MB |
| UUID (string) | 36 bytes | 36 MB |
| UUID (binary) | 16 bytes | 16 MB |

Hashids with 8-character minimum length offer the most compact storage.

## Performance Characteristics

### Generation Speed
- Fast random number generation
- Minimal encoding overhead
- Comparable to Sqid, ULID, and UUID v7

### Database Indexing
- Good B-tree index performance
- No fragmentation issues
- Compact index size

### Uniqueness Guarantees
- Based on random number range (1 to PHP_INT_MAX)
- Collision probability increases with volume
- Use longer minimum length for high-volume applications

## Use Cases

**When to Choose Hashids:**

1. **Database ID Obfuscation**
   - Hide sequential database IDs
   - Prevent enumeration attacks
   - Maintain decodability

2. **URL Shorteners**
   - Encode URL IDs into short strings
   - Decode back to database IDs
   - Compact and shareable

3. **Public-Facing IDs**
   - Short, memorable identifiers
   - Security through obfuscation
   - Easy to communicate verbally

4. **QR Codes**
   - Shorter IDs = simpler QR codes
   - Easier scanning
   - Decodable back to original values

5. **Invoice/Order Numbers**
   - Professional appearance
   - Easy to reference
   - Non-sequential for privacy

6. **API Resource Identifiers**
   - Short, clean endpoints
   - Prevents resource enumeration
   - Decodable for internal use

**When to Choose Sqid:**

1. **No Secret Management Required**
   - Simpler deployment
   - No salt configuration
   - Stateless generation

2. **Similar Compactness Needs**
   - Same length benefits as Hashids
   - URL-safe identifiers

**When to Choose UUID:**

1. **Enterprise Applications**
   - Industry standard compliance
   - Wider tool support

2. **Distributed Systems**
   - Guaranteed global uniqueness
   - No coordination required

3. **High-Volume Systems**
   - Better collision resistance
   - Proven at scale

**When to Choose ULID:**

1. **Time-Series Data**
   - Natural chronological ordering
   - Efficient range queries

2. **Database Performance**
   - Sequential insertion benefits
   - Better indexing than random UUIDs

3. **Audit Trails**
   - Embedded timestamp information
   - Sortable by creation time

## Security Considerations

### Enumeration Protection

Salt prevents sequential enumeration:

```php
// Without salt (predictable)
$gen1 = new HashidsGenerator();
// Potential for pattern discovery

// With salt (unpredictable)
$gen2 = new HashidsGenerator(salt: 'secret-salt-xyz');
// IDs are obfuscated and non-guessable
```

Unlike auto-incrementing integers or saltless encodings, salted Hashids prevent attackers from guessing the next ID.

### Salt Management

Best practices for salt security:

```php
// ❌ BAD: Hardcoded salt
$generator = new HashidsGenerator(salt: 'my-salt');

// ✅ GOOD: Environment variable
$generator = new HashidsGenerator(salt: env('HASHIDS_SALT'));

// ✅ BETTER: Different salts per context
$userIdGenerator = new HashidsGenerator(salt: env('USER_ID_SALT'));
$orderIdGenerator = new HashidsGenerator(salt: env('ORDER_ID_SALT'));
```

**Important:**
- Store salt in environment variables or secure config
- Never commit salts to version control
- Use different salts for different entity types
- Rotate salts carefully (breaks existing IDs)

### Length vs. Security

Longer minimum lengths increase security:

- **8 chars**: Suitable for low-security applications
- **12-16 chars**: Recommended for most applications
- **24-32 chars**: High-security sensitive data

### Alphabet Security

Custom alphabets for additional obfuscation:

```php
// Remove similar-looking characters for better readability
$generator = new HashidsGenerator(
    salt: env('HASHIDS_SALT'),
    minLength: 12,
    alphabet: 'abcdefghjkmnpqrstuvwxyz23456789' // No 0/O, 1/l/I
);
```

## Common Patterns

### API Resource Identifiers

```php
// Short, clean API endpoints with obfuscated IDs
GET /api/users/Xb9kLm2N
GET /api/orders/pQ7rT3vY
GET /api/products/mN4wK8xZ
```

### Shareable Links

```php
$inviteId = InviteId::generate();
$shareUrl = "https://example.com/invite/{$inviteId}";
// https://example.com/invite/Xb9kLm2N
```

### Human-Readable References

```php
$orderId = OrderId::generate();
echo "Your order number is: {$orderId}";
// Your order number is: Xb9kLm2N
```

### QR Code Generation

```php
$ticketId = TicketId::generate();
$qrUrl = "https://example.com/verify/{$ticketId}";
// Shorter URL = simpler QR code
```

### Database ID Obfuscation

```php
// Encode sequential database ID
$hashids = new Hashids(salt: env('HASHIDS_SALT'));
$publicId = $hashids->encode(12345); // "Xb9kLm2N"

// Decode back to database ID
$dbId = $hashids->decode('Xb9kLm2N')[0]; // 12345
```

## Migration Strategies

### From UUID to Hashids

1. **Update Generator Configuration**
   ```php
   IdGenerator::setGenerator(GeneratorType::Hashids);
   ```

2. **Update Database Schema**
   ```php
   Schema::table('users', function (Blueprint $table) {
       $table->string('id', 16)->change();
   });
   ```

3. **Handle Mixed IDs During Transition**
   ```php
   function parseId(string $id): UserId {
       // UUID format: 36 chars with hyphens
       // Hashids format: 8-16 chars alphanumeric
       return UserId::fromString($id); // Both validated
   }
   ```

### Gradual Migration

- New records use Hashids
- Existing records keep UUIDs/Sqids
- Both formats work with `fromString()`
- Migrate incrementally as records are updated

## Best Practices

1. **Always Use Salt**
   - Never use empty salt in production
   - Store salt securely in environment variables
   - Use different salts for different contexts

2. **Choose Appropriate Minimum Length**
   - Consider your collision tolerance
   - Balance compactness vs. uniqueness

3. **Use Consistent Configuration**
   - Same salt, alphabet, and minLength across application
   - Document configuration for team

4. **Avoid Custom Alphabets Unless Necessary**
   - Default alphabet is well-tested
   - Custom alphabets require careful validation

5. **Consider Context**
   - Public IDs: shorter is better
   - Internal IDs: prioritize uniqueness and obfuscation

6. **Test Uniqueness Requirements**
   - Validate collision probability for your volume
   - Increase length if needed

## Limitations

1. **No Embedded Timestamp**
   - Unlike ULID/UUID v7, Hashids have no time information
   - Cannot sort by creation time

2. **Salt Dependency**
   - Changing salt breaks existing IDs
   - Must maintain salt consistency
   - Salt rotation requires migration

3. **Configuration Coupling**
   - Alphabet changes break existing IDs
   - Stick with initial configuration

4. **Database Support**
   - No native Hashids column types
   - Must use VARCHAR/CHAR

5. **Collision Probability**
   - Higher than UUIDs with same length
   - Mitigate with longer minimum length

6. **Decodability Security**
   - IDs can be decoded if salt is compromised
   - Not suitable for cryptographic purposes
   - Use encryption for true security

## Advanced Usage

### Dynamic Configuration

```php
// Different configurations for different contexts
class HashidsConfig
{
    public static function forPublicIds(): HashidsGenerator
    {
        return new HashidsGenerator(
            salt: env('PUBLIC_ID_SALT'),
            minLength: 8
        );
    }

    public static function forSecureIds(): HashidsGenerator
    {
        return new HashidsGenerator(
            salt: env('SECURE_ID_SALT'),
            minLength: 16,
            alphabet: 'abcdefghjkmnpqrstuvwxyz23456789'
        );
    }

    public static function forInvoices(): HashidsGenerator
    {
        return new HashidsGenerator(
            salt: env('INVOICE_SALT'),
            minLength: 12,
            alphabet: 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'
        );
    }
}
```

### Validation Helpers

```php
function isValidHashids(string $id, int $minLength = 8): bool
{
    return strlen($id) >= $minLength
        && ctype_alnum($id);
}
```

### Batch Generation

```php
$generator = new HashidsGenerator(salt: env('HASHIDS_SALT'));
$ids = array_map(
    fn() => $generator->generate(),
    range(1, 1000)
);

// Verify uniqueness
assert(count($ids) === count(array_unique($ids)));
```

### Context-Specific Salts

```php
class EntityHashids
{
    private static function generator(string $entity): HashidsGenerator
    {
        $salt = env('APP_KEY') . $entity;
        return new HashidsGenerator(salt: $salt, minLength: 12);
    }

    public static function users(): HashidsGenerator
    {
        return self::generator('users');
    }

    public static function orders(): HashidsGenerator
    {
        return self::generator('orders');
    }

    public static function products(): HashidsGenerator
    {
        return self::generator('products');
    }
}

// Usage
$userId = EntityHashids::users()->generate();
$orderId = EntityHashids::orders()->generate();
```

## Hashids vs Sqid Decision Guide

**Choose Hashids when:**
- You need to obfuscate database IDs
- Enumeration attacks are a concern
- Bidirectional encoding is valuable
- You can manage salt securely

**Choose Sqid when:**
- No secret management preferred
- Simple stateless generation needed
- Salt management is impractical
- Similar compactness is sufficient
