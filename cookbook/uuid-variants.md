# UUID Variants

This library supports all UUID versions (v1, v3, v4, v5, v6, v7, v8) through configurable generators. Each variant has different characteristics and use cases.

## Configuring the Generator

Set your preferred UUID version in your application's configuration or service provider:

```php
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Facades\IdGenerator;

// In your service provider or bootstrap
IdGenerator::setGenerator(GeneratorType::UUID_V7);
```

## UUID Version Overview

### UUID v1 - Time-Based

UUID v1 generates IDs based on timestamp and MAC address:

```php
IdGenerator::setGenerator(GeneratorType::UUID_V1);

$userId = UserId::generate();
// e.g., "6ba7b810-9dad-11d1-80b4-00c04fd430c8"
```

**Characteristics:**
- Contains timestamp (100-nanosecond intervals since 1582-10-15)
- Contains MAC address or random node ID
- Sortable by creation time
- Potential privacy concern (reveals MAC address)

**Use cases:**
- When you need time-ordered IDs
- Distributed systems where sorting by creation time is valuable
- When privacy concerns about MAC address are acceptable

### UUID v3 - Name-Based (MD5)

UUID v3 generates deterministic IDs using MD5 hashing of a namespace and name:

```php
use Ramsey\Uuid\Uuid;

IdGenerator::setGenerator(GeneratorType::UUID_V3);

// Requires namespace UUID and name
$namespace = Uuid::NAMESPACE_DNS;
$name = 'example.com';
$id = Uuid::uuid3($namespace, $name);

$userId = UserId::fromUuid($id);
// Always produces: "9073926b-929f-31c2-abc9-fad77ae3e8eb"
```

**Characteristics:**
- Deterministic (same input = same UUID)
- Uses MD5 hashing
- Requires namespace UUID and name
- MD5 considered cryptographically weak

**Use cases:**
- When you need reproducible IDs based on known inputs
- Generating IDs from external identifiers (URLs, DNS names, etc.)
- When cryptographic strength is not critical

### UUID v4 - Random

UUID v4 generates completely random IDs (default):

```php
IdGenerator::setGenerator(GeneratorType::UUID_V4);

$userId = UserId::generate();
// e.g., "550e8400-e29b-41d4-a716-446655440000"
```

**Characteristics:**
- 122 bits of randomness
- No embedded information
- No ordering guarantees
- Cryptographically secure random

**Use cases:**
- Default choice for most applications
- When you need truly random IDs
- When you don't need time-ordering
- Privacy-sensitive applications

### UUID v5 - Name-Based (SHA-1)

UUID v5 is like v3 but uses SHA-1 instead of MD5:

```php
use Ramsey\Uuid\Uuid;

IdGenerator::setGenerator(GeneratorType::UUID_V5);

$namespace = Uuid::NAMESPACE_DNS;
$name = 'example.com';
$id = Uuid::uuid5($namespace, $name);

$userId = UserId::fromUuid($id);
// Always produces: "cfbff0d1-9375-5685-968c-48ce8b15ae17"
```

**Characteristics:**
- Deterministic (same input = same UUID)
- Uses SHA-1 hashing (stronger than MD5)
- Requires namespace UUID and name
- More secure than v3

**Use cases:**
- Same as v3, but when stronger hashing is preferred
- Industry standard for name-based UUIDs
- Interoperability with systems using RFC 4122

### UUID v6 - Reordered Time-Based

UUID v6 is a reordered version of v1 with better database indexing:

```php
IdGenerator::setGenerator(GeneratorType::UUID_V6);

$userId = UserId::generate();
// e.g., "1d19dad6-ba7b-6810-80b4-00c04fd430c8"
```

**Characteristics:**
- Time-ordered (timestamp first)
- Better for database indexing than v1
- Contains MAC address or random node
- Maintains v1 compatibility

**Use cases:**
- Database-heavy applications needing efficient indexing
- When you want v1 benefits with better performance
- Systems migrating from v1

### UUID v7 - Unix Timestamp-Based (Recommended)

UUID v7 uses Unix timestamps and is optimized for modern databases:

```php
IdGenerator::setGenerator(GeneratorType::UUID_V7);

$userId = UserId::generate();
// e.g., "017f22e2-79b0-7cc3-98c4-dc0c0c07398f"
```

**Characteristics:**
- Timestamp first (millisecond precision)
- Optimized for database indexing
- Random component for uniqueness
- No privacy concerns (no MAC address)
- Monotonically increasing

**Use cases:**
- **Recommended for most new applications**
- High-performance database applications
- When you need both time-ordering and privacy
- Distributed systems with database clustering

### UUID v8 - Custom/Vendor-Specific

UUID v8 allows custom implementations:

```php
IdGenerator::setGenerator(GeneratorType::UUID_V8);

$userId = UserId::generate();
// Format depends on implementation
```

**Characteristics:**
- Application-defined format
- Maximum flexibility
- Requires custom generator implementation

**Use cases:**
- Specialized requirements not met by other versions
- Custom business logic in ID generation
- Vendor-specific implementations

## Choosing a UUID Version

**Quick Decision Guide:**

- **Default/General Use**: UUID v4 (random)
- **Database Performance**: UUID v7 (timestamp-based)
- **Time-Ordered IDs**: UUID v6 or v7
- **Deterministic IDs**: UUID v5 (or v3 for legacy)
- **Custom Requirements**: UUID v8
- **Legacy Compatibility**: UUID v1

## Mixing UUID Versions

You can use different generators for different ID types:

```php
// Global default
IdGenerator::setGenerator(GeneratorType::UUID_V7);

// For specific cases, create from custom-generated UUIDs
$timestampId = UserId::fromUuid(Uuid::uuid7());
$randomId = SessionId::fromUuid(Uuid::uuid4());
$deterministicId = ApiKeyId::fromUuid(
    Uuid::uuid5(Uuid::NAMESPACE_DNS, 'api-key-name')
);
```

## Performance Considerations

**UUID v7** offers the best balance:
- Fast generation
- Excellent database indexing (sequential)
- No fragmentation in B-tree indexes
- Better cache locality

**UUID v4** is fast but can cause:
- Index fragmentation (random order)
- Slower inserts at scale
- Poor cache locality

**UUID v1/v6** provide time-ordering but:
- May reveal MAC address
- v1 has poor index performance
- v6 improves on v1's indexing issues
