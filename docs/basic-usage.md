Strongly-typed IDs provide a type-safe way to handle entity identifiers in your PHP applications. This guide covers the fundamental patterns for creating and using strongly-typed IDs.

## Creating ID Classes

To create a strongly-typed ID for your entity, simply extend the `StronglyTypedId` base class:

```php
use Cline\StronglyTypedId\ValueObjects\StronglyTypedId;

final readonly class UserId extends StronglyTypedId {}
final readonly class OrderId extends StronglyTypedId {}
final readonly class ProductId extends StronglyTypedId {}
```

Each ID class is a distinct type, preventing accidental mixing of IDs across different entity types.

## Generating New IDs

The simplest way to create a new ID is using the `generate()` method:

```php
$userId = UserId::generate();
// e.g., "550e8400-e29b-41d4-a716-446655440000"
```

By default, this generates a UUID v7 (time-ordered), but you can configure other generators.

## Creating IDs from Strings

When you have an existing ID string (e.g., from a database or API), use `fromString()`:

```php
$userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
```

The method validates the format and throws an `InvalidArgumentException` if invalid:

```php
try {
    $userId = UserId::fromString('invalid-uuid');
} catch (InvalidArgumentException $e) {
    // Handle invalid format
}
```

## Creating IDs from UUID Objects

If you're working with Ramsey UUID objects, you can convert them directly:

```php
use Ramsey\Uuid\Uuid;

$uuid = Uuid::uuid4();
$userId = UserId::fromUuid($uuid);
```

## Converting to Strings

Strongly-typed IDs implement `Stringable` and provide multiple ways to get the string value:

```php
$userId = UserId::generate();

// Via __toString()
echo $userId; // "550e8400-e29b-41d4-a716-446655440000"

// Via toString()
$idString = $userId->toString();

// Via string cast
$idString = (string) $userId;

// Accessing the value property directly
$idString = $userId->value;
```

## Comparing IDs

Use the `equals()` method to compare two IDs:

```php
$userId1 = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
$userId2 = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
$userId3 = UserId::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');

$userId1->equals($userId2); // true
$userId1->equals($userId3); // false
```

The `equals()` method enforces type safety - IDs of different types are never equal:

```php
$userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
$orderId = OrderId::fromString('550e8400-e29b-41d4-a716-446655440000');

$userId->equals($orderId); // false (different types)
```

## Type Safety Benefits

The primary benefit of strongly-typed IDs is compile-time type safety:

```php
function findUser(UserId $id): User
{
    // Implementation
}

function findOrder(OrderId $id): Order
{
    // Implementation
}

$userId = UserId::generate();
$orderId = OrderId::generate();

findUser($userId);   // ✓ Valid
findUser($orderId);  // ✗ Type error: Expected UserId, got OrderId
```

This prevents common bugs where IDs are accidentally mixed between different entity types.

## Immutability

All strongly-typed IDs are immutable (`readonly`). Once created, their values cannot be changed:

```php
$userId = UserId::generate();

// This will cause an error:
$userId->value = 'new-value'; // Error: Cannot modify readonly property
```

This immutability ensures IDs remain stable throughout their lifetime.
