# ULID Usage

ULID (Universally Unique Lexicographically Sortable Identifier) is an alternative to UUIDs that provides better sortability and readability. This guide covers using ULIDs with strongly-typed IDs.

## What is ULID?

ULID is a 128-bit identifier like UUID, but with several advantages:

- **Lexicographically sortable**: Natural sort order matches creation time
- **Compact**: 26 characters vs 36 for UUID (no hyphens)
- **URL-safe**: Uses Crockford's Base32 alphabet
- **Case-insensitive**: Reduces errors in manual entry
- **Timestamp-based**: First 48 bits encode Unix timestamp (millisecond precision)
- **Random component**: Last 80 bits ensure uniqueness

Example ULID: `01ARZ3NDEKTSV4RRFFQ69G5FAV`

## Configuring ULID Generator

Enable ULID generation in your application:

```php
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Facades\IdGenerator;

// In your service provider or bootstrap
IdGenerator::setGenerator(GeneratorType::Ulid);
```

Now all ID generation will use ULIDs:

```php
$userId = UserId::generate();
// e.g., "01ARZ3NDEKTSV4RRFFQ69G5FAV"
```

## Creating IDs from ULID Strings

ULIDs are compatible with the standard `fromString()` method:

```php
$userId = UserId::fromString('01ARZ3NDEKTSV4RRFFQ69G5FAV');
```

Validation ensures the string is a valid ULID format:

```php
try {
    $userId = UserId::fromString('invalid-ulid-format');
} catch (InvalidArgumentException $e) {
    // Handle invalid ULID
}
```

## ULID Format Structure

```
01ARZ3NDEKTSV4RRFFQ69G5FAV
|----------|--------------|
 Timestamp      Randomness
  10 chars       16 chars
```

**Timestamp Component** (48 bits):
- First 10 characters
- Millisecond precision Unix timestamp
- Provides natural chronological ordering

**Random Component** (80 bits):
- Last 16 characters
- Cryptographically strong randomness
- Ensures uniqueness

## Chronological Ordering

ULIDs sort naturally by creation time:

```php
$id1 = UserId::generate(); // 01ARZ3NDEKTSV4RRFFQ69G5FAV
sleep(1);
$id2 = UserId::generate(); // 01ARZ3NDEKTSV4RRFFQ69G5FB0

// String comparison reflects chronological order
$id1->toString() < $id2->toString(); // true
```

This makes ULIDs excellent for database indexing and time-based queries.

## ULID vs UUID Comparison

### Advantages of ULID

1. **Better Readability**
   - ULID: `01ARZ3NDEKTSV4RRFFQ69G5FAV` (26 chars)
   - UUID: `01902ed6-76cf-7bd2-b228-e11038cf0756` (36 chars)

2. **Natural Sorting**
   - ULIDs sort lexicographically by creation time
   - UUIDs (except v6/v7) have no natural ordering

3. **URL-Safe**
   - ULIDs use Base32 (no special characters)
   - UUIDs include hyphens

4. **Case-Insensitive**
   - Reduces user input errors
   - UUIDs are case-sensitive

5. **Database Performance**
   - Sequential IDs improve B-tree indexing
   - Less index fragmentation than random UUIDs

### Advantages of UUID

1. **Wider Adoption**
   - More tools and libraries support UUIDs
   - Industry standard (RFC 4122)

2. **Multiple Versions**
   - Specialized variants for different use cases
   - Name-based deterministic generation (v3/v5)

3. **Native Database Support**
   - Many databases have UUID data types
   - ULIDs typically stored as strings or binary

## Database Storage

### String Storage

Store ULIDs as CHAR(26) or VARCHAR(26):

```php
// Migration
Schema::create('users', function (Blueprint $table) {
    $table->char('id', 26)->primary();
    // or
    $table->string('id', 26)->primary();
});
```

### Binary Storage (More Efficient)

Convert ULIDs to binary for storage efficiency:

```php
use Ulid\Ulid;

// Convert to binary (16 bytes)
$binary = Ulid::fromString($userId->toString())->toBytes();

// Store in database
DB::table('users')->insert([
    'id' => $binary,
]);

// Retrieve from database
$binary = DB::table('users')->value('id');
$ulid = Ulid::fromBytes($binary);
$userId = UserId::fromString($ulid->toString());
```

Binary storage saves 10 bytes per record (26 chars vs 16 bytes).

## Performance Characteristics

### Generation Speed
- ULIDs generate slightly faster than UUID v4
- Comparable to UUID v7

### Database Indexing
- Excellent B-tree index performance
- Sequential insertion reduces fragmentation
- Similar to UUID v7

### Uniqueness Guarantees
- Same millisecond: 80 bits of randomness (1.2×10²⁴ possible values)
- Collision probability: effectively zero in practice

## Use Cases

**When to Choose ULID:**

1. **High-Volume Applications**
   - Better database indexing performance
   - Reduced storage overhead (binary format)

2. **User-Facing IDs**
   - More readable than UUIDs
   - URL-safe without encoding
   - Case-insensitive reduces errors

3. **Time-Series Data**
   - Natural chronological ordering
   - Efficient range queries

4. **Distributed Systems**
   - No coordination required
   - Timestamp provides ordering across nodes

5. **API Design**
   - Compact representation
   - No special character handling

**When to Choose UUID:**

1. **Compatibility Requirements**
   - Legacy systems expecting UUIDs
   - Tools with UUID-specific features

2. **Database Features**
   - Native UUID column types
   - UUID-specific functions and indexes

3. **Deterministic Generation**
   - Name-based IDs (UUID v3/v5)
   - Not supported by ULID

4. **Industry Standards**
   - RFC 4122 compliance required
   - Interoperability with UUID-based systems

## Monotonicity Guarantee

ULIDs generated within the same millisecond maintain monotonic ordering:

```php
// Within the same millisecond
$ids = [];
for ($i = 0; $i < 100; $i++) {
    $ids[] = UserId::generate();
}

// All IDs maintain strict increasing order
for ($i = 1; $i < count($ids); $i++) {
    assert($ids[$i-1]->toString() < $ids[$i]->toString());
}
```

This monotonicity is valuable for maintaining order even under high-frequency generation.

## Timestamp Extraction

While not directly exposed by the library, you can extract timestamps from ULIDs:

```php
use Ulid\Ulid;

$userId = UserId::generate();
$ulid = Ulid::fromString($userId->toString());

// Get timestamp
$timestamp = $ulid->toDateTime();
$milliseconds = $ulid->getTimestamp();
```

This can be useful for time-based queries and analytics.

## Migration from UUID to ULID

If migrating an existing application from UUID to ULID:

1. **Update Generator Configuration**
   ```php
   IdGenerator::setGenerator(GeneratorType::Ulid);
   ```

2. **Update Database Schema**
   ```php
   Schema::table('users', function (Blueprint $table) {
       $table->string('id', 26)->change();
   });
   ```

3. **Handle Mixed IDs**
   ```php
   // During transition, support both formats
   function parseId(string $id): UserId {
       return strlen($id) === 26
           ? UserId::fromString($id)  // ULID
           : UserId::fromString($id); // UUID (also validated)
   }
   ```

4. **Consider Gradual Migration**
   - New records use ULIDs
   - Existing records keep UUIDs
   - Both formats work with `fromString()`
