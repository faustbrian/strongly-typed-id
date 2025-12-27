ULID (Universally Unique Lexicographically Sortable Identifier) is an alternative to UUIDs that provides better sortability and readability.

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

| Feature | ULID | UUID |
|---------|------|------|
| Length | 26 chars | 36 chars |
| Sortable | Yes (chronological) | Only v6/v7 |
| URL-safe | Yes (Base32) | No (hyphens) |
| Case-sensitive | No | Yes |
| Index performance | Excellent | Good (v7) / Poor (v4) |

### Advantages of UUID

- Wider adoption and tooling support
- Native database types
- Multiple specialized versions (v1-v8)
- Name-based deterministic generation (v3/v5)

## Database Storage

### String Storage

Store ULIDs as CHAR(26) or VARCHAR(26):

```php
Schema::create('users', function (Blueprint $table) {
    $table->char('id', 26)->primary();
});
```

### Binary Storage (More Efficient)

Convert ULIDs to binary for storage efficiency:

```php
use Ulid\Ulid;

// Convert to binary (16 bytes)
$binary = Ulid::fromString($userId->toString())->toBytes();

// Store in database
DB::table('users')->insert(['id' => $binary]);
```

Binary storage saves 10 bytes per record (26 chars vs 16 bytes).

## Monotonicity Guarantee

ULIDs generated within the same millisecond maintain monotonic ordering:

```php
$ids = [];
for ($i = 0; $i < 100; $i++) {
    $ids[] = UserId::generate();
}

// All IDs maintain strict increasing order
for ($i = 1; $i < count($ids); $i++) {
    assert($ids[$i-1]->toString() < $ids[$i]->toString());
}
```

## Timestamp Extraction

Extract timestamps from ULIDs when needed:

```php
use Ulid\Ulid;

$userId = UserId::generate();
$ulid = Ulid::fromString($userId->toString());

$timestamp = $ulid->toDateTime();
$milliseconds = $ulid->getTimestamp();
```

## Use Cases

**Choose ULID when:**
- High-volume applications needing indexing performance
- User-facing IDs (more readable, URL-safe)
- Time-series data with chronological ordering
- Distributed systems needing ordering across nodes

**Choose UUID when:**
- Legacy system compatibility
- Native UUID database column types needed
- Deterministic generation required (v3/v5)
- RFC 4122 compliance required
