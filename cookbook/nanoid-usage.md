# NanoID Usage

NanoID is a modern, secure, URL-friendly unique identifier generator designed as a compact alternative to UUID. This guide covers using NanoIDs with strongly-typed IDs.

## What is NanoID?

NanoID is a tiny, secure, URL-friendly unique string ID generator with several advantages:

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

// In your service provider or bootstrap
IdGenerator::setGenerator(GeneratorType::NanoId);
```

Now all ID generation will use NanoIDs:

```php
$userId = UserId::generate();
// e.g., "V1StGXR8_Z5jdHi6B-myT"
```

## Creating IDs from NanoID Strings

NanoIDs are compatible with the standard `fromString()` method:

```php
$userId = UserId::fromString('V1StGXR8_Z5jdHi6B-myT');
```

Validation ensures the string is a valid NanoID format:

```php
try {
    $userId = UserId::fromString('invalid@nanoid!format');
} catch (InvalidArgumentException $e) {
    // Handle invalid NanoID
}
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
$longId = $generator->generate(); // "V1StGXR8_Z5jdHi6B-myTXZvwsGj9KqYnP3RhNbCdEfGhI"
```

## Custom Alphabet Configuration

Use custom character sets for specific requirements:

```php
use Cline\StronglyTypedId\Generators\NanoIdGenerator;

// Numeric only (e.g., for legacy systems)
$generator = new NanoIdGenerator(
    size: 21,
    alphabet: '0123456789'
);
$numericId = $generator->generate(); // "123456789012345678901"

// Hexadecimal
$generator = new NanoIdGenerator(
    size: 21,
    alphabet: '0123456789ABCDEF'
);
$hexId = $generator->generate(); // "1A3F5B7D9E2C4A6B8D0F2"

// No special characters (alphanumeric only)
$generator = new NanoIdGenerator(
    size: 21,
    alphabet: '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'
);
$alphanumericId = $generator->generate(); // "aB3dE5fG7hI9jK1lM2nO3"

// Custom safe alphabet (no ambiguous characters)
$generator = new NanoIdGenerator(
    size: 21,
    alphabet: '2346789ABCDEFGHJKLMNPQRTUVWXY' // Excludes: 0,O,1,I,5,S
);
$safeId = $generator->generate(); // "A2B4D6F8H9J2K4M6N8P2Q"
```

## NanoID vs UUID Comparison

### Advantages of NanoID

1. **More Compact**
   - NanoID: `V1StGXR8_Z5jdHi6B-myT` (21 chars)
   - UUID: `550e8400-e29b-41d4-a716-446655440000` (36 chars)
   - 42% shorter for same collision resistance

2. **URL-Friendly by Default**
   - No special characters requiring encoding
   - Safe for URLs, cookies, localStorage keys
   - Uses: `A-Za-z0-9_-`

3. **Flexible**
   - Configurable length
   - Custom alphabets for specific needs
   - Easy to adjust security vs size tradeoff

4. **Modern Algorithm**
   - Uniform distribution (no modulo bias)
   - Cryptographically secure
   - Optimized for performance

### Advantages of UUID

1. **Standardized**
   - RFC 4122 specification
   - Native database support (UUID column types)
   - Wider ecosystem adoption

2. **Multiple Versions**
   - Time-based (v1, v6, v7)
   - Name-based deterministic (v3, v5)
   - Version 8 for custom formats

3. **128-bit Fixed Size**
   - Consistent storage requirements
   - Binary optimization (16 bytes)

4. **Database Features**
   - Native UUID functions
   - Specialized indexes
   - Built-in validation

## Security Characteristics

### Cryptographic Strength

NanoID uses PHP's `random_bytes()` which provides cryptographically secure randomness:

```php
// Internally uses hardware random generator (same as UUID v4)
$id = UserId::generate(); // Cryptographically secure
```

### Collision Resistance

For the default 21-character length with 64-character alphabet:
- **Entropy**: ~126 bits (equivalent to UUID v4's 122 bits)
- **Collision probability**: Same as UUID v4
- **IDs needed for 1% probability of collision**: ~8.9×10¹⁸ IDs

### Size vs Security Tradeoff

```php
// Less secure (shorter)
$generator = new NanoIdGenerator(size: 10); // ~60 bits entropy
// 1% collision after ~1.2 billion IDs

// Default security (recommended)
$generator = new NanoIdGenerator(size: 21); // ~126 bits entropy
// 1% collision after ~8.9×10¹⁸ IDs

// Extra security (longer)
$generator = new NanoIdGenerator(size: 32); // ~192 bits entropy
// Practically zero collision probability
```

## Database Storage

### String Storage

Store NanoIDs as VARCHAR or CHAR:

```php
// Migration - VARCHAR for flexibility
Schema::create('users', function (Blueprint $table) {
    $table->string('id', 21)->primary(); // Default size
});

// Or CHAR for fixed-length optimization
Schema::create('products', function (Blueprint $table) {
    $table->char('id', 21)->primary();
});
```

### Custom Length Storage

Match column length to your NanoID configuration:

```php
// 10-character IDs
Schema::create('short_codes', function (Blueprint $table) {
    $table->char('code', 10)->unique();
});

// 50-character IDs for extra security
Schema::create('tokens', function (Blueprint $table) {
    $table->char('token', 50)->primary();
});
```

### Indexing Performance

NanoIDs are random and don't provide natural ordering like ULID/UUID v7:

```php
// For chronological queries, add a timestamp
Schema::create('events', function (Blueprint $table) {
    $table->char('id', 21)->primary();
    $table->timestamp('created_at')->index(); // Separate ordering
});
```

## Performance Characteristics

### Generation Speed
- Slightly faster than UUID v4 (fewer bytes needed)
- Comparable to ULID
- No timestamp calculation overhead

### Storage Efficiency
- 21 bytes (string) vs 36 bytes (UUID string)
- 42% storage reduction over UUID strings
- Cannot use binary optimization like UUID (16 bytes)

### Uniqueness Guarantees
- 64 possible characters ^ 21 positions
- ~2.8×10³⁷ possible IDs
- Collision probability: effectively zero for reasonable use cases

## Use Cases

**When to Choose NanoID:**

1. **User-Facing IDs**
   - Shorter than UUID for better UX
   - URL-safe without encoding
   - Easy to copy/paste

2. **API Keys & Tokens**
   - Compact representation
   - Customizable alphabet (exclude ambiguous characters)
   - Strong security with shorter strings

3. **QR Codes & Links**
   - Fewer characters = smaller QR codes
   - URL-safe by design
   - Mobile-friendly

4. **Client-Side IDs**
   - JavaScript-friendly format
   - No special character issues
   - Works in all contexts (URLs, JSON, HTML attributes)

5. **Obfuscated References**
   - Shorter than UUID but still secure
   - Custom alphabets for specific formats
   - Non-sequential (prevents enumeration)

**When to Choose UUID:**

1. **Database Standards**
   - Native UUID column types needed
   - UUID-specific indexes and functions
   - Binary storage requirement (16 bytes)

2. **Time-Ordered IDs**
   - UUID v7 provides chronological ordering
   - NanoID is purely random
   - Better for B-tree indexes

3. **Deterministic Generation**
   - Name-based UUIDs (v3/v5)
   - Not supported by NanoID

4. **Enterprise Integration**
   - Legacy systems expecting UUIDs
   - RFC 4122 compliance required
   - Wider ecosystem support

**When to Choose ULID:**

1. **Sortable Requirements**
   - Time-based ordering needed
   - Database index performance critical
   - Chronological queries frequent

2. **Compact + Sortable**
   - Want shorter than UUID
   - Need chronological ordering
   - ULID: 26 chars, sortable

## Alphabet Best Practices

### Default Alphabet (Recommended)
```php
// URL-safe, balanced distribution
'_-0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
```

### Safe Alphabet (No Ambiguous Characters)
```php
// Excludes: 0,O,1,I,l for manual entry
'23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz'
```

### Numeric Only (Legacy Systems)
```php
// Numbers only - requires longer IDs for same security
'0123456789'
// Use 32+ characters for adequate entropy
```

### Case-Insensitive (Uppercase Only)
```php
// Uppercase letters and numbers (like ULID)
'0123456789ABCDEFGHJKLMNPQRSTUVWXYZ'
```

## Migration from UUID to NanoID

If migrating an existing application from UUID to NanoID:

1. **Update Generator Configuration**
   ```php
   IdGenerator::setGenerator(GeneratorType::NanoId);
   ```

2. **Update Database Schema**
   ```php
   Schema::table('users', function (Blueprint $table) {
       // Increase length to accommodate both formats during transition
       $table->string('id', 36)->change(); // Supports both UUID (36) and NanoID (21)
   });
   ```

3. **Handle Mixed IDs**
   ```php
   // During transition, support both formats
   function parseId(string $id): UserId {
       // Both work with fromString() - validation handles format detection
       return UserId::fromString($id);
   }
   ```

4. **Consider Gradual Migration**
   - New records use NanoIDs (21 chars)
   - Existing records keep UUIDs (36 chars)
   - Both formats work with `fromString()`
   - Once migration complete, reduce column to 21 chars

## Advanced: Custom Implementation

For highly specialized needs, extend the generator:

```php
use Cline\StronglyTypedId\Generators\NanoIdGenerator;

final readonly class CustomNanoIdGenerator extends NanoIdGenerator
{
    public function __construct()
    {
        // 16-character IDs with only uppercase alphanumeric (no ambiguous chars)
        parent::__construct(
            size: 16,
            alphabet: '23456789ABCDEFGHJKLMNPQRSTUVWXY'
        );
    }
}

// Use in your application
$generator = new CustomNanoIdGenerator();
$id = $generator->generate(); // "A2B4D6F8H9J2K4M6"
```

## Collision Probability Calculator

Estimate collision probability for your NanoID configuration:

```php
/**
 * Calculate collision probability for NanoID configuration
 *
 * Formula: P ≈ (n² / 2) × (1 / alphabet_size^id_length)
 */
function collisionProbability(int $alphabetSize, int $idLength, int $numberOfIds): float
{
    $entropy = log($alphabetSize ** $idLength, 2); // Bits of entropy
    $probability = ($numberOfIds ** 2 / 2) / (2 ** $entropy);
    return $probability;
}

// Examples:
// Default NanoID (21 chars, 64 alphabet)
collisionProbability(64, 21, 1_000_000);        // ~0.000000000001% (1 million IDs)
collisionProbability(64, 21, 1_000_000_000);    // ~0.0001% (1 billion IDs)

// Short NanoID (10 chars, 64 alphabet)
collisionProbability(64, 10, 1_000_000);        // ~0.04% (1 million IDs)
collisionProbability(64, 10, 1_000_000_000);    // ~40% (1 billion IDs) - TOO HIGH!

// Numeric only (21 chars, 10 alphabet)
collisionProbability(10, 21, 1_000_000);        // ~0.05% (1 million IDs)
collisionProbability(10, 21, 1_000_000_000);    // ~50% (1 billion IDs) - TOO HIGH!
```

**Recommendation**: Keep collision probability below 0.01% (1 in 10,000) for production systems.
