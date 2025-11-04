# Sqid Usage

Sqids (pronounced "squids") are short, unique, URL-safe identifiers that provide a compact alternative to UUIDs. This guide covers using Sqids with strongly-typed IDs.

## What is Sqid?

Sqid is a library that generates short, unique, URL-safe identifiers by encoding numeric values:

- **Short**: Configurable minimum length (default: 8 characters)
- **URL-safe**: Uses alphanumeric characters only
- **Customizable**: Configurable alphabet and minimum length
- **Deterministic encoding**: Same numbers always produce same Sqid
- **Human-friendly**: Short and readable compared to UUIDs
- **Collision-free**: Within the same configuration

Example Sqid: `4d9fND1xQ`

## Configuring Sqid Generator

Enable Sqid generation in your application:

```php
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Facades\IdGenerator;

// In your service provider or bootstrap
IdGenerator::setGenerator(GeneratorType::Sqid);
```

Now all ID generation will use Sqids:

```php
$userId = UserId::generate();
// e.g., "4d9fND1xQ"
```

## Creating IDs from Sqid Strings

Sqids are compatible with the standard `fromString()` method:

```php
$userId = UserId::fromString('4d9fND1xQ');
```

Validation ensures the string is a valid Sqid format:

```php
try {
    $userId = UserId::fromString('invalid-sqid!@#');
} catch (InvalidArgumentException $e) {
    // Handle invalid Sqid
}
```

## Custom Configuration

### Minimum Length

Configure minimum length for generated Sqids:

```php
use Cline\StronglyTypedId\Generators\SqidGenerator;

// Create generator with custom minimum length
$generator = new SqidGenerator(minLength: 16);
$id = $generator->generate();
// e.g., "4d9fND1xQ8bWePmY"
```

Common minimum lengths:
- **8 characters** (default): Compact, suitable for most use cases
- **16 characters**: Higher collision resistance
- **32 characters**: Maximum security for sensitive applications

### Custom Alphabet

Define a custom alphabet for encoding:

```php
use Cline\StronglyTypedId\Generators\SqidGenerator;

// Use only uppercase letters and numbers
$generator = new SqidGenerator(
    alphabet: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
    minLength: 8
);
$id = $generator->generate();
// e.g., "X4M9FND1"
```

**Important:**
- Custom alphabets must be at least 3 characters long
- All characters must be unique
- Same alphabet must be used for encoding and decoding

## Sqid vs UUID vs ULID Comparison

### Advantages of Sqid

1. **Extremely Compact**
   - Sqid: `4d9fND1xQ` (8-16 chars, configurable)
   - ULID: `01ARZ3NDEKTSV4RRFFQ69G5FAV` (26 chars)
   - UUID: `01902ed6-76cf-7bd2-b228-e11038cf0756` (36 chars)

2. **Configurable Length**
   - Adjust length based on your needs
   - Balance between compactness and collision resistance

3. **Custom Alphabets**
   - Define character set for specific requirements
   - Exclude confusing characters (0/O, 1/l/I)

4. **URL-Safe**
   - Alphanumeric only (no special characters)
   - No encoding required for URLs

5. **Human-Friendly**
   - Short enough to read and type
   - Less error-prone than long identifiers

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
   - Better collision resistance than Sqids

3. **Case-Insensitive**
   - More forgiving for manual entry
   - Reduces user errors

## Database Storage

### String Storage

Store Sqids as VARCHAR with appropriate length:

```php
// Migration
Schema::create('users', function (Blueprint $table) {
    $table->string('id', 16)->primary(); // Adjust based on minLength
});
```

**Storage requirements:**
- 8-char Sqids: VARCHAR(8) or CHAR(8)
- 16-char Sqids: VARCHAR(16) or CHAR(16)
- Variable length: VARCHAR(32) for safety

### Storage Efficiency Comparison

For 1 million records:

| Type | Length | Storage Size |
|------|--------|-------------|
| Sqid (8 chars) | 8 bytes | 8 MB |
| Sqid (16 chars) | 16 bytes | 16 MB |
| ULID | 26 bytes | 26 MB |
| UUID (string) | 36 bytes | 36 MB |
| UUID (binary) | 16 bytes | 16 MB |

Sqids with 8-character minimum length offer the most compact storage.

## Performance Characteristics

### Generation Speed
- Fast random number generation
- Minimal encoding overhead
- Comparable to ULID and UUID v7

### Database Indexing
- Good B-tree index performance
- No fragmentation issues
- Compact index size

### Uniqueness Guarantees
- Based on random number range (1 to PHP_INT_MAX)
- Collision probability increases with volume
- Use longer minimum length for high-volume applications

## Use Cases

**When to Choose Sqid:**

1. **URL Shorteners**
   - Compact IDs save characters
   - Human-readable for sharing

2. **Public-Facing IDs**
   - Short, memorable identifiers
   - Easy to communicate verbally

3. **QR Codes**
   - Shorter IDs = simpler QR codes
   - Easier scanning

4. **Mobile Applications**
   - Reduced bandwidth usage
   - Faster API responses

5. **Invoice/Order Numbers**
   - Professional appearance
   - Easy to reference

6. **Storage-Constrained Systems**
   - Minimize database size
   - Reduce network overhead

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

Random Sqids prevent sequential enumeration:

```php
// Generated IDs are non-sequential
$id1 = UserId::generate(); // "4d9fND1xQ"
$id2 = UserId::generate(); // "x7mPqR3sY"
$id3 = UserId::generate(); // "b2kLnM9wT"
```

Unlike auto-incrementing integers, attackers cannot guess the next ID.

### Custom Alphabets for Obfuscation

Use custom alphabets to make IDs less predictable:

```php
// Custom alphabet without similar-looking characters
$generator = new SqidGenerator(
    alphabet: 'abcdefghjkmnpqrstuvwxyz23456789', // No 0/O, 1/l/I
    minLength: 12
);
```

### Length vs. Security

Longer minimum lengths increase security:

- **8 chars**: Suitable for low-security applications
- **12-16 chars**: Recommended for most applications
- **24-32 chars**: High-security sensitive data

## Common Patterns

### API Resource Identifiers

```php
// Short, clean API endpoints
GET /api/users/4d9fND1xQ
GET /api/orders/x7mPqR3sY
GET /api/products/b2kLnM9wT
```

### Shareable Links

```php
$inviteId = InviteId::generate();
$shareUrl = "https://example.com/invite/{$inviteId}";
// https://example.com/invite/4d9fND1xQ
```

### Human-Readable References

```php
$orderId = OrderId::generate();
echo "Your order number is: {$orderId}";
// Your order number is: 4d9fND1xQ
```

### QR Code Generation

```php
$ticketId = TicketId::generate();
$qrUrl = "https://example.com/verify/{$ticketId}";
// Shorter URL = simpler QR code
```

## Migration Strategies

### From UUID to Sqid

1. **Update Generator Configuration**
   ```php
   IdGenerator::setGenerator(GeneratorType::Sqid);
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
       // Sqid format: 8-16 chars alphanumeric
       return UserId::fromString($id); // Both validated
   }
   ```

### Gradual Migration

- New records use Sqids
- Existing records keep UUIDs/ULIDs
- Both formats work with `fromString()`
- Migrate incrementally as records are updated

## Best Practices

1. **Choose Appropriate Minimum Length**
   - Consider your collision tolerance
   - Balance compactness vs. uniqueness

2. **Use Consistent Configuration**
   - Same alphabet and minLength across application
   - Document configuration for team

3. **Avoid Custom Alphabets Unless Necessary**
   - Default alphabet is well-tested
   - Custom alphabets require careful validation

4. **Consider Context**
   - Public IDs: shorter is better
   - Internal IDs: prioritize uniqueness

5. **Test Uniqueness Requirements**
   - Validate collision probability for your volume
   - Increase length if needed

## Limitations

1. **No Embedded Timestamp**
   - Unlike ULID/UUID v7, Sqids have no time information
   - Cannot sort by creation time

2. **Configuration Coupling**
   - Alphabet changes break existing IDs
   - Stick with initial configuration

3. **Database Support**
   - No native Sqid column types
   - Must use VARCHAR/CHAR

4. **Collision Probability**
   - Higher than UUIDs with same length
   - Mitigate with longer minimum length

## Advanced Usage

### Dynamic Configuration

```php
// Different configurations for different contexts
class SqidConfig
{
    public static function forPublicIds(): SqidGenerator
    {
        return new SqidGenerator(minLength: 8);
    }

    public static function forSecureIds(): SqidGenerator
    {
        return new SqidGenerator(
            alphabet: 'abcdefghjkmnpqrstuvwxyz23456789',
            minLength: 16
        );
    }
}
```

### Validation Helpers

```php
function isValidSqid(string $id, int $minLength = 8): bool
{
    return strlen($id) >= $minLength
        && ctype_alnum($id);
}
```

### Batch Generation

```php
$generator = new SqidGenerator();
$ids = array_map(
    fn() => $generator->generate(),
    range(1, 1000)
);

// Verify uniqueness
assert(count($ids) === count(array_unique($ids)));
```
