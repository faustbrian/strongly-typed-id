## Table of Contents

1. [Overview](#doc-docs-readme)
2. [Advanced Patterns](#doc-docs-advanced-patterns)
3. [Basic Usage](#doc-docs-basic-usage)
4. [Guid](#doc-docs-guid)
5. [Hashids](#doc-docs-hashids)
6. [Laravel Integration](#doc-docs-laravel-integration)
7. [Nanoid](#doc-docs-nanoid)
8. [Prefixed Id](#doc-docs-prefixed-id)
9. [Random Bytes](#doc-docs-random-bytes)
10. [Random String](#doc-docs-random-string)
11. [Sqid](#doc-docs-sqid)
12. [Ulid](#doc-docs-ulid)
13. [Uuid Variants](#doc-docs-uuid-variants)
<a id="doc-docs-readme"></a>

## Installation

Install via Composer:

```bash
composer require cline/strongly-typed-id
```

## What is Strongly Typed ID?

Strongly Typed ID provides type-safe identifier value objects for PHP applications. Instead of passing raw strings or integers as IDs, you get compile-time type safety that prevents mixing IDs across different entity types.

### Key Features

- **Type Safety**: Distinct ID types prevent accidental mixing (e.g., `UserId` vs `OrderId`)
- **Immutability**: All IDs are readonly value objects
- **Multiple Formats**: UUID (all versions), ULID, NanoID, GUID, Sqid, Hashids
- **Laravel Integration**: Eloquent casts, Spatie Laravel Data support
- **DDD-Friendly**: Perfect for domain-driven design aggregates

## Quick Start

### Creating ID Classes

Create strongly-typed IDs by extending the base class:

```php
use Cline\StronglyTypedId\ValueObjects\StronglyTypedId;

final readonly class UserId extends StronglyTypedId {}
final readonly class OrderId extends StronglyTypedId {}
final readonly class ProductId extends StronglyTypedId {}
```

### Generating New IDs

```php
$userId = UserId::generate();
// e.g., "018c5d6e-5f89-7a9b-9c1d-2e3f4a5b6c7d" (UUID v7 by default)
```

### Creating from Strings

```php
$userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
```

### Type Safety in Action

```php
function findUser(UserId $id): User
{
    // Implementation
}

$userId = UserId::generate();
$orderId = OrderId::generate();

findUser($userId);   // ✓ Valid
findUser($orderId);  // ✗ Type error: Expected UserId, got OrderId
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=strongly-typed-id-config
```

Configure your preferred ID generator in `config/strongly-typed-id.php`:

```php
return [
    // Default generator: uuid_v1, uuid_v3, uuid_v4, uuid_v5, uuid_v6, uuid_v7, uuid_v8, ulid
    'generator' => env('STRONGLY_TYPED_ID_GENERATOR', 'uuid_v7'),
];
```

Or set it programmatically:

```php
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Facades\IdGenerator;

IdGenerator::setGenerator(GeneratorType::UuidV7);
```

## Choosing an ID Format

| Format | Length | Sortable | Best For |
|--------|--------|----------|----------|
| **UUID v7** | 36 chars | Yes | Database PKs (recommended) |
| **UUID v4** | 36 chars | No | Random IDs, legacy systems |
| **ULID** | 26 chars | Yes | Compact sortable IDs |
| **NanoID** | 21 chars | No | URL-safe short IDs |
| **Sqid** | Variable | No | Obfuscated integer encoding |
| **Hashid** | Variable | No | Reversible integer encoding |

## Next Steps

- **[Basic Usage](basic-usage)** - Core ID operations and patterns
- **[Laravel Integration](laravel-integration)** - Eloquent casts and Data DTOs
- **[UUID Variants](uuid-variants)** - All UUID versions explained
- **[ULID](ulid)** - Lexicographically sortable identifiers
- **[Advanced Patterns](advanced-patterns)** - DDD and complex use cases

<a id="doc-docs-advanced-patterns"></a>

This guide covers advanced usage patterns and best practices for strongly-typed IDs in complex applications.

## Domain-Driven Design

### Aggregates and Entities

Use strongly-typed IDs to enforce aggregate boundaries:

```php
final readonly class User
{
    public function __construct(
        public UserId $id,
        public string $name,
        public string $email,
        public OrganizationId $organizationId,
    ) {}
}

final readonly class Order
{
    public function __construct(
        public OrderId $id,
        public UserId $userId,
        public Money $total,
    ) {}
}
```

Type safety prevents mixing IDs across aggregates:

```php
function findUser(UserId $id): User;
function findOrder(OrderId $id): Order;

$userId = UserId::generate();
$orderId = OrderId::generate();

findUser($userId);   // ✓ Valid
findUser($orderId);  // ✗ Type error
```

### Repository Pattern

Use typed IDs in repository interfaces:

```php
interface UserRepositoryInterface
{
    public function find(UserId $id): ?User;
    public function save(User $user): void;
    public function delete(UserId $id): void;
    public function findByOrganization(OrganizationId $organizationId): array;
}

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function find(UserId $id): ?User
    {
        $model = UserModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function findByOrganization(OrganizationId $organizationId): array
    {
        return UserModel::where('organization_id', $organizationId)
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }
}
```

## Event Sourcing

### Domain Events

Use typed IDs in domain events:

```php
final readonly class UserRegistered extends DomainEvent
{
    public function __construct(
        public UserId $userId,
        public string $email,
        string $eventId,
        DateTimeImmutable $occurredAt,
    ) {
        parent::__construct($eventId, $occurredAt);
    }
}

final readonly class OrderPlaced extends DomainEvent
{
    public function __construct(
        public OrderId $orderId,
        public UserId $userId,
        public Money $total,
        string $eventId,
        DateTimeImmutable $occurredAt,
    ) {
        parent::__construct($eventId, $occurredAt);
    }
}
```

### Event Store

Store events with typed IDs:

```php
interface EventStoreInterface
{
    public function getEventsForAggregate(StronglyTypedId $aggregateId): array;
    public function append(StronglyTypedId $aggregateId, DomainEvent $event): void;
}
```

## CQRS

### Commands with Typed IDs

```php
final readonly class CreateUserCommand
{
    public function __construct(
        public UserId $userId,
        public string $name,
        public string $email,
        public OrganizationId $organizationId,
    ) {}
}

final class CreateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $repository,
    ) {}

    public function handle(CreateUserCommand $command): void
    {
        $user = new User(
            id: $command->userId,
            name: $command->name,
            email: $command->email,
            organizationId: $command->organizationId,
        );

        $this->repository->save($user);
    }
}
```

## Multi-Tenancy

### Tenant-Scoped IDs

Ensure IDs include tenant context:

```php
final readonly class TenantId extends StronglyTypedId {}

interface TenantScopedRepositoryInterface
{
    public function find(TenantId $tenantId, UserId $userId): ?User;
    public function findAllByTenant(TenantId $tenantId): array;
}
```

### Global Scope

Use Eloquent global scopes with tenant IDs:

```php
final class TenantScope implements Scope
{
    public function __construct(private TenantId $tenantId) {}

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('tenant_id', $this->tenantId);
    }
}

class User extends Model
{
    protected static function booted(): void
    {
        $tenantId = app(CurrentTenant::class)->id();
        static::addGlobalScope(new TenantScope($tenantId));

        static::creating(function (self $model) use ($tenantId): void {
            $model->tenant_id = $tenantId;
        });
    }

    protected function casts(): array
    {
        return [
            'id' => UserId::asEloquentCast(),
            'tenant_id' => TenantId::asEloquentCast(),
        ];
    }
}
```

## API Integration

### External System IDs

Wrap external IDs with type safety:

```php
final readonly class StripeCustomerId extends StronglyTypedId {}
final readonly class StripePaymentIntentId extends StronglyTypedId {}

final class PaymentService
{
    public function createPaymentIntent(
        UserId $userId,
        Money $amount
    ): StripePaymentIntentId {
        $customer = $this->findOrCreateCustomer($userId);

        $intent = $this->stripe->paymentIntents->create([
            'customer' => $customer->toString(),
            'amount' => $amount->getAmount(),
            'currency' => $amount->getCurrency()->getCode(),
        ]);

        return StripePaymentIntentId::fromString($intent->id);
    }
}
```

## Generator Selection Strategy

Choose different generators for different ID types:

```php
// Short IDs for public-facing use
$inviteGenerator = new SqidGenerator(minLength: 8);
$inviteId = InviteId::fromString($inviteGenerator->generate());
// e.g., "4d9fND1xQ"

// UUID v7 for database primary keys
IdGenerator::setGenerator(GeneratorType::UuidV7);
$userId = UserId::generate();
// e.g., "017f22e2-79b0-7cc3-98c4-dc0c0c07398f"

// ULID for time-series data
IdGenerator::setGenerator(GeneratorType::Ulid);
$eventId = EventId::generate();
// e.g., "01ARZ3NDEKTSV4RRFFQ69G5FAV"
```

**Selection Guide:**

| Generator | Best For | Example Use Cases |
|-----------|----------|-------------------|
| Sqid | Short, user-facing IDs | Invite codes, share links |
| UUID v7 | Database primary keys | User IDs, order IDs |
| ULID | Time-series data | Event IDs, audit trails |
| UUID v4 | General purpose | Session IDs, tokens |

## Composite Keys

Handle composite identifiers:

```php
final readonly class CompositeId
{
    public function __construct(
        public TenantId $tenantId,
        public UserId $userId,
    ) {}

    public function toString(): string
    {
        return sprintf('%s:%s', $this->tenantId, $this->userId);
    }

    public static function fromString(string $value): self
    {
        [$tenantId, $userId] = explode(':', $value);
        return new self(
            TenantId::fromString($tenantId),
            UserId::fromString($userId),
        );
    }

    public function equals(self $other): bool
    {
        return $this->tenantId->equals($other->tenantId)
            && $this->userId->equals($other->userId);
    }
}
```

## Caching Strategies

Use typed IDs in cache keys:

```php
final class UserCache
{
    public function get(UserId $userId): ?User
    {
        return $this->cache->get($this->buildKey($userId));
    }

    public function put(User $user, int $ttl = 3600): void
    {
        $this->cache->put($this->buildKey($user->id), $user, $ttl);
    }

    private function buildKey(UserId $userId): string
    {
        return sprintf('user:%s', $userId->toString());
    }
}
```

## Testing Patterns

### Test Builders

Create test builders with typed IDs:

```php
final class UserBuilder
{
    private UserId $id;
    private OrganizationId $organizationId;

    public function __construct()
    {
        $this->id = UserId::generate();
        $this->organizationId = OrganizationId::generate();
    }

    public function withId(UserId $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function build(): User
    {
        return new User(
            id: $this->id,
            name: 'Test User',
            email: 'test@example.com',
            organizationId: $this->organizationId,
        );
    }
}
```

### Fixtures

Define well-known IDs for tests:

```php
final class UserFixtures
{
    public static function johnDoeId(): UserId
    {
        return UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
    }
}

// Usage
$john = User::factory()->create(['id' => UserFixtures::johnDoeId()]);
```

<a id="doc-docs-basic-usage"></a>

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

<a id="doc-docs-guid"></a>

GUID (Globally Unique Identifier) is Microsoft's implementation of UUID, formatted in uppercase for compatibility with Windows and .NET ecosystems.

## What is GUID?

GUID is functionally identical to UUID version 4 but follows Microsoft's naming and formatting conventions:

- **Uppercase formatting**: Uses uppercase hexadecimal characters (A-F, 0-9)
- **Same structure as UUID**: 128-bit identifier with standard format
- **Microsoft compatibility**: Matches .NET's `System.Guid` output
- **RFC 4122 compliant**: Follows UUID v4 specification

Example GUID: `550E8400-E29B-41D4-A716-446655440000`

## GUID vs UUID

The only difference is formatting:

| Aspect | GUID | UUID |
|--------|------|------|
| Format | `550E8400-E29B-...` | `550e8400-e29b-...` |
| Case | Uppercase | Lowercase |
| Ecosystem | Microsoft/.NET/Windows | Unix/Web/General |

## Configuring GUID Generator

Enable GUID generation in your application:

```php
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Facades\IdGenerator;

IdGenerator::setGenerator(GeneratorType::Guid);
```

Now all ID generation will use GUIDs:

```php
$userId = UserId::generate();
// e.g., "550E8400-E29B-41D4-A716-446655440000"
```

## Interoperability with Microsoft Systems

### .NET Integration

GUIDs are compatible with .NET's `System.Guid`:

```csharp
// C# code
var guid = Guid.Parse("550E8400-E29B-41D4-A716-446655440000");
var newGuid = Guid.NewGuid();
```

### SQL Server

Store GUIDs in SQL Server's native `uniqueidentifier` type:

```sql
CREATE TABLE users (
    id UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWID(),
    name NVARCHAR(255)
);
```

### Web Services

When interfacing with Microsoft web services:

```php
$response = Http::post('https://api.microsoft.com/endpoint', [
    'userId' => $userId->toString(), // Uppercase GUID
]);
```

## Case Sensitivity Considerations

PHP strings are case-sensitive by default:

```php
$guid1 = UserId::fromString('550E8400-E29B-41D4-A716-446655440000');
$guid2 = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');

// Different string values
$guid1->toString() === $guid2->toString(); // false

// Normalize for comparison
mb_strtoupper($guid1->toString()) === mb_strtoupper($guid2->toString()); // true
```

## Database Storage

Store GUIDs as CHAR(36) or use Laravel's uuid() method:

```php
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary();
});
```

For case-insensitive comparison:

```php
Schema::create('users', function (Blueprint $table) {
    $table->char('id', 36)
        ->collation('utf8mb4_unicode_ci')
        ->primary();
});
```

## Braces Notation

Microsoft tools sometimes use GUIDs with braces:

```php
$userId = UserId::generate();
$guidWithBraces = '{' . $userId->toString() . '}';
// Result: "{550E8400-E29B-41D4-A716-446655440000}"
```

## Use Cases

**Choose GUID when:**
- Interfacing with .NET applications
- SQL Server databases
- Azure services
- Windows-based systems
- APIs requiring uppercase UUID format

**Choose UUID when:**
- Web standards compliance
- Unix/Linux environments
- Existing lowercase systems
- Non-Microsoft tech stacks

<a id="doc-docs-hashids"></a>

Hashids generate short, unique, URL-safe identifiers with obfuscation via salt, preventing enumeration attacks.

## What is Hashids?

Hashids encode numeric values with obfuscation:

- **Short**: Configurable minimum length (default: 8 characters)
- **URL-safe**: Uses alphanumeric characters only
- **Obfuscated**: Salt prevents guessing encoded values
- **Bidirectional**: Can decode back to original numbers
- **Enumeration-resistant**: Salt prevents sequential ID discovery

Example Hashids: `Xb9kLm2N`

## Configuring Hashids Generator

Enable Hashids generation in your application:

```php
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Facades\IdGenerator;

IdGenerator::setGenerator(GeneratorType::Hashids);
```

Now all ID generation will use Hashids:

```php
$userId = UserId::generate();
// e.g., "Xb9kLm2N"
```

## Custom Configuration

### Salt Configuration

Configure a unique salt for obfuscation:

```php
use Cline\StronglyTypedId\Generators\HashidsGenerator;

$generator = new HashidsGenerator(salt: 'my-secret-salt');
$id = $generator->generate();
```

**Important:**
- Salt makes IDs unpredictable
- Use different salts for different applications
- Never expose your salt publicly
- Store salt in environment variables

### Minimum Length

```php
$generator = new HashidsGenerator(
    salt: 'my-secret-salt',
    minLength: 16
);
```

### Custom Alphabet

```php
$generator = new HashidsGenerator(
    salt: 'my-secret-salt',
    minLength: 8,
    alphabet: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
);
```

## Hashids vs Sqid

| Feature | Hashids | Sqid |
|---------|---------|------|
| Salt support | Yes | No |
| Enumeration protection | Strong | Basic |
| Configuration | Salt required | Simpler |
| Security | Higher | Lower |

**Choose Hashids when:**
- Enumeration attacks are a concern
- Obfuscating database IDs
- Security through obscurity needed

**Choose Sqid when:**
- No secret management preferred
- Simpler stateless generation

## Salt Management

```php
// Store salt securely in environment
$generator = new HashidsGenerator(salt: env('HASHIDS_SALT'));

// Different salts per entity type
$userIdGenerator = new HashidsGenerator(salt: env('USER_ID_SALT'));
$orderIdGenerator = new HashidsGenerator(salt: env('ORDER_ID_SALT'));
```

## Database Storage

Store Hashids as VARCHAR with appropriate length:

```php
Schema::create('users', function (Blueprint $table) {
    $table->string('id', 16)->primary();
});
```

## Use Cases

**Choose Hashids when:**
- Database ID obfuscation
- Preventing enumeration attacks
- Public-facing IDs needing security
- URL shorteners with security
- API resource identifiers

**Choose UUID/ULID when:**
- No obfuscation needed
- Time-ordered IDs required
- Maximum collision resistance
- No secret management desired

<a id="doc-docs-laravel-integration"></a>

This library provides seamless integration with Laravel through Eloquent casts, model attributes, and Spatie Laravel Data support.

## Eloquent Model Integration

### Using Casts

The recommended approach for Eloquent models is using the built-in cast:

```php
use Illuminate\Database\Eloquent\Model;

final readonly class UserId extends StronglyTypedId {}

class User extends Model
{
    protected function casts(): array
    {
        return [
            'id' => UserId::asEloquentCast(),
            'organization_id' => OrganizationId::asEloquentCast(),
        ];
    }
}
```

The cast automatically handles conversion between database strings and ID objects:

```php
// Retrieving from database
$user = User::find('550e8400-e29b-41d4-a716-446655440000');
$user->id; // UserId instance

// Setting values
$user->id = UserId::generate();
$user->id = '550e8400-e29b-41d4-a716-446655440000'; // String also works
$user->save();

// Queries work with both strings and ID objects
User::where('id', $userId)->first();
User::where('id', '550e8400-e29b-41d4-a716-446655440000')->first();
```

### Using Attributes

Alternatively, use Laravel's attribute API:

```php
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Model
{
    protected function id(): Attribute
    {
        return UserId::asEloquentAttribute();
    }

    protected function organizationId(): Attribute
    {
        return OrganizationId::asEloquentAttribute();
    }
}
```

## Database Schema

### String-Based Primary Keys

For UUID/ULID IDs, use string columns:

```php
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('organization_id');
    $table->timestamps();

    $table->foreign('organization_id')
        ->references('id')
        ->on('organizations')
        ->onDelete('cascade');
});
```

For ULIDs, use `char(26)`:

```php
Schema::create('users', function (Blueprint $table) {
    $table->char('id', 26)->primary();
    $table->char('organization_id', 26);
    $table->timestamps();
});
```

### Model Configuration

Configure models to use string-based IDs:

```php
class User extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'id' => UserId::asEloquentCast(),
        ];
    }
}
```

## Automatic ID Generation

### Using Model Events

Generate IDs automatically when creating new models:

```php
class User extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if ($model->id === null) {
                $model->id = UserId::generate();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'id' => UserId::asEloquentCast(),
        ];
    }
}
```

### Using Traits

Create a reusable trait for ID generation:

```php
trait GeneratesStronglyTypedId
{
    protected static function bootGeneratesStronglyTypedId(): void
    {
        static::creating(function (Model $model): void {
            if ($model->id === null) {
                $idClass = $model->getIdClass();
                $model->id = $idClass::generate();
            }
        });
    }

    abstract protected function getIdClass(): string;
}

class User extends Model
{
    use GeneratesStronglyTypedId;

    public $incrementing = false;
    protected $keyType = 'string';

    protected function getIdClass(): string
    {
        return UserId::class;
    }
}
```

## Relationships

Strongly-typed IDs work seamlessly with Eloquent relationships:

```php
class User extends Model
{
    protected function casts(): array
    {
        return [
            'id' => UserId::asEloquentCast(),
            'organization_id' => OrganizationId::asEloquentCast(),
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
```

Usage:

```php
$user = User::find($userId);
$organization = $user->organization;
$orders = $user->orders;

$order = $user->orders()->create(['amount' => 100.00]);
$order->user_id; // UserId instance
```

## Spatie Laravel Data Integration

The library includes a cast for Spatie Laravel Data:

```php
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Cline\StronglyTypedId\Casts\Data\StronglyTypedIdCast;

class UserData extends Data
{
    public function __construct(
        #[WithCast(StronglyTypedIdCast::class)]
        public UserId $id,
        public string $name,
        public string $email,
        #[WithCast(StronglyTypedIdCast::class)]
        public ?OrganizationId $organizationId = null,
    ) {}
}
```

The cast automatically converts strings to ID objects:

```php
// From array
$userData = UserData::from([
    'id' => '550e8400-e29b-41d4-a716-446655440000',
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);
$userData->id; // UserId instance

// From Eloquent model
$userData = UserData::from($user);
```

## API Resources

Strongly-typed IDs serialize cleanly in API responses:

```php
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, // Automatically converts to string
            'name' => $this->name,
            'organization_id' => $this->organization_id,
        ];
    }
}
```

Response:

```json
{
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "John Doe",
    "organization_id": "6ba7b810-9dad-11d1-80b4-00c04fd430c8"
}
```

## Form Requests

Use strongly-typed IDs in form validation:

```php
class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'uuid', 'exists:organizations,id'],
        ];
    }

    public function organizationId(): OrganizationId
    {
        return OrganizationId::fromString($this->input('organization_id'));
    }
}

// In controller
public function update(UpdateUserRequest $request, UserId $userId): Response
{
    $user = User::find($userId);
    $user->organization_id = $request->organizationId();
    $user->save();

    return response()->json($user);
}
```

## Route Model Binding

Laravel's route model binding works with strongly-typed IDs:

```php
// routes/api.php
Route::get('/users/{user}', function (User $user) {
    return $user;
});
```

For explicit binding:

```php
Route::bind('userId', function (string $value) {
    $userId = UserId::fromString($value);
    return User::where('id', $userId)->firstOrFail();
});

Route::get('/users/{userId}', function (User $user) {
    return $user;
});
```

## Testing

### Factory Integration

Use strongly-typed IDs in model factories:

```php
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'id' => UserId::generate(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'organization_id' => OrganizationId::generate(),
        ];
    }
}
```

### Testing Helpers

```php
test('user creation', function () {
    $userId = UserId::generate();

    $user = User::create([
        'id' => $userId,
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    expect($userId->equals($user->id))->toBeTrue();

    $this->assertDatabaseHas('users', [
        'id' => $userId->toString(),
        'email' => 'test@example.com',
    ]);
});
```

## Configuration

Configure the ID generator in your service provider:

```php
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Facades\IdGenerator;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Use UUID v7 for all IDs (recommended)
        IdGenerator::setGenerator(GeneratorType::UuidV7);
    }
}
```

<a id="doc-docs-nanoid"></a>

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

<a id="doc-docs-prefixed-id"></a>

Prefixed IDs enable Stripe-style identifier generation by combining a custom prefix with any underlying ID generator, creating identifiers like `cus_01906e2c-8e16-7e90-a1c1-6e47e5e6e3a0`.

## Configuring Prefixed ID Generator

Enable prefixed IDs as your default generator:

```php
// In config/strongly-typed-id.php or .env
STRONGLY_TYPED_ID_GENERATOR=prefixed
STRONGLY_TYPED_ID_PREFIX=id
STRONGLY_TYPED_ID_PREFIXED_GENERATOR=random_string  // uuid_v7, nanoid, random_string, random_bytes
```

Now all ID generation will use prefixed IDs:

```php
$userId = UserId::generate();
// "id_aB3dEf9Hi2kLmN5pQ7rStUv1Wx2Y"
```

**Configuration options:**

```php
'generators' => [
    'prefixed' => [
        'prefix' => 'id',              // Default prefix
        'generator' => 'random_string', // Underlying generator
    ],
],
```

**Supported underlying generators:**
- `random_string` - 24-character alphanumeric (default, Stripe-style)
- `uuid_v7` - Time-ordered UUID
- `nanoid` - 21-character URL-friendly
- `random_bytes` - 32-character hexadecimal

## What are Prefixed IDs?

Prefixed IDs are composite identifiers that combine a human-readable prefix with any ID generator:

- **Human-readable**: Prefix identifies entity type at a glance
- **Type-safe**: Different prefixes for different entities
- **Composable**: Works with any `IdGeneratorInterface` implementation
- **URL-friendly**: Maintains properties of underlying generator
- **Stripe-compatible**: Follows industry-standard format

Example Prefixed IDs:
- `cus_01906e2c-8e16-7e90-a1c1-6e47e5e6e3a0` (Customer with UUID v7)
- `ch_V1StGXR8_Z5jdHi6B-myT` (Charge with NanoID)
- `acct_4d9fND1xQ` (Account with Sqids)

## Creating Prefixed ID Generator

Combine any prefix with any generator:

```php
use Cline\StronglyTypedId\Generators\PrefixedIdGenerator;
use Cline\StronglyTypedId\Generators\UuidV7Generator;

// Customer IDs with UUID v7
$customerGen = new PrefixedIdGenerator('cus', new UuidV7Generator());
$customerId = $customerGen->generate();
// "cus_01906e2c-8e16-7e90-a1c1-6e47e5e6e3a0"
```

## Common Prefix Patterns

Following Stripe's conventions:

```php
use Cline\StronglyTypedId\Generators\{
    PrefixedIdGenerator,
    UuidV7Generator,
    NanoIdGenerator,
    SqidsGenerator
};

// Customer IDs
$customerGen = new PrefixedIdGenerator('cus', new UuidV7Generator());

// Charge IDs
$chargeGen = new PrefixedIdGenerator('ch', new NanoIdGenerator());

// Account IDs
$accountGen = new PrefixedIdGenerator('acct', new SqidsGenerator());

// Payment Intent IDs
$paymentGen = new PrefixedIdGenerator('pi', new UuidV7Generator());

// Token IDs
$tokenGen = new PrefixedIdGenerator('tok', new RandomStringGenerator(21));
```

## Generator Combinations

Mix and match prefixes with any generator:

### Time-Ordered (UUID v7)
```php
// Sortable by creation time
$generator = new PrefixedIdGenerator('ord', new UuidV7Generator());
$orderId = $generator->generate();
// "ord_01906e2c-8e16-7e90-a1c1-6e47e5e6e3a0"
```

### Compact (Sqids)
```php
// Short, URL-friendly IDs
$generator = new PrefixedIdGenerator('inv', new SqidsGenerator());
$invoiceId = $generator->generate();
// "inv_4d9fND1xQ"
```

### Secure Random (Random Bytes)
```php
// Cryptographically secure hexadecimal
$generator = new PrefixedIdGenerator('sec', new RandomBytesGenerator(16));
$secretId = $generator->generate();
// "sec_4f3b7c2a8e1d9f6b5a3c8e2d7f1b4a9c"
```

### Alphanumeric (Random String)
```php
// Laravel's Str::random() based
$generator = new PrefixedIdGenerator('sess', new RandomStringGenerator(16));
$sessionId = $generator->generate();
// "sess_aB3dEf9Hi2kLmN5p"
```

## Value Object Integration

Create strongly-typed prefixed IDs:

```php
use Cline\StronglyTypedId\Generators\PrefixedIdGenerator;
use Cline\StronglyTypedId\Generators\UuidV7Generator;
use Cline\StronglyTypedId\ValueObjects\StronglyTypedId;

final readonly class CustomerId extends StronglyTypedId
{
    protected static function generator(): IdGeneratorInterface
    {
        return new PrefixedIdGenerator('cus', new UuidV7Generator());
    }
}

// Usage
$customerId = CustomerId::generate();
echo $customerId; // "cus_01906e2c-8e16-7e90-a1c1-6e47e5e6e3a0"
```

## Laravel Model Integration

Use with Eloquent models:

```php
use Illuminate\Database\Eloquent\Model;
use Cline\StronglyTypedId\Casts\StronglyTypedIdCast;

class Customer extends Model
{
    protected $casts = [
        'id' => CustomerId::class.':prefixed',
    ];

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            $customer->id = CustomerId::generate();
        });
    }
}
```

Migration:

```php
Schema::create('customers', function (Blueprint $table) {
    $table->string('id', 41)->primary(); // 'cus_' (4) + UUID (36) + margin (1)
    $table->timestamps();
});
```

## Database Storage

Calculate column length based on generator:

```php
// UUID v7: prefix + '_' + 36 chars
$table->string('id', strlen('cus_') + 36)->primary();

// NanoID (21): prefix + '_' + 21 chars
$table->string('id', strlen('tok_') + 21)->primary();

// Sqids (varies, ~8 min): prefix + '_' + estimated max
$table->string('id', strlen('acct_') + 20)->primary();

// Random Bytes (32 hex): prefix + '_' + 32 chars
$table->string('id', strlen('sec_') + 32)->primary();
```

## Prefix Conventions

Industry-standard prefix patterns:

| Entity Type | Prefix | Example |
|-------------|--------|---------|
| Customer | `cus` | `cus_...` |
| Charge | `ch` | `ch_...` |
| Payment Intent | `pi` | `pi_...` |
| Account | `acct` | `acct_...` |
| Invoice | `in` | `in_...` |
| Subscription | `sub` | `sub_...` |
| Token | `tok` | `tok_...` |
| Order | `ord` | `ord_...` |
| Product | `prod` | `prod_...` |

## Parsing Prefixed IDs

Extract prefix and ID parts:

```php
$prefixedId = 'cus_01906e2c-8e16-7e90-a1c1-6e47e5e6e3a0';
[$prefix, $id] = explode('_', $prefixedId, 2);

echo $prefix; // "cus"
echo $id;     // "01906e2c-8e16-7e90-a1c1-6e47e5e6e3a0"
```

Type detection from prefix:

```php
function getEntityType(string $prefixedId): string
{
    return match (substr($prefixedId, 0, strpos($prefixedId, '_'))) {
        'cus' => 'customer',
        'ch' => 'charge',
        'acct' => 'account',
        'pi' => 'payment_intent',
        default => 'unknown',
    };
}
```

## Nested Prefixes

Compose multiple prefix layers:

```php
// Create hierarchical IDs
$innerGen = new PrefixedIdGenerator('user', new UuidV7Generator());
$outerGen = new PrefixedIdGenerator('org', $innerGen);

$id = $outerGen->generate();
// "org_user_01906e2c-8e16-7e90-a1c1-6e47e5e6e3a0"
```

## Use Cases

**Choose Prefixed IDs when:**
- Building Stripe-style APIs
- Need human-readable entity type identification
- Working with multiple entity types in same system
- Want to identify ID type without database lookup
- Building webhook systems (identify entity from ID)
- Creating public-facing IDs

**Combine with UUID v7 for:**
- Time-ordered prefixed IDs
- Sortable entity identifiers
- Audit trails with entity types

**Combine with Sqids for:**
- Short, user-friendly IDs
- URL slugs with type prefix
- Compact API responses

**Combine with Random Bytes/String for:**
- Secure tokens with type identification
- API keys with purpose prefix
- Session identifiers

<a id="doc-docs-random-bytes"></a>

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

<a id="doc-docs-random-string"></a>

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

<a id="doc-docs-sqid"></a>

Sqids (pronounced "squids") generate short, unique, URL-safe identifiers by encoding numeric values.

## What is Sqid?

Sqids generate short, unique, URL-safe identifiers:

- **Short**: Configurable minimum length (default: 8 characters)
- **URL-safe**: Uses alphanumeric characters only
- **Customizable**: Configurable alphabet and minimum length
- **Deterministic**: Same numbers always produce same Sqid
- **Human-friendly**: Short and readable compared to UUIDs

Example Sqid: `4d9fND1xQ`

## Configuring Sqid Generator

Enable Sqid generation in your application:

```php
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Facades\IdGenerator;

IdGenerator::setGenerator(GeneratorType::Sqid);
```

Now all ID generation will use Sqids:

```php
$userId = UserId::generate();
// e.g., "4d9fND1xQ"
```

## Custom Configuration

### Minimum Length

```php
use Cline\StronglyTypedId\Generators\SqidGenerator;

$generator = new SqidGenerator(minLength: 16);
$id = $generator->generate();
// e.g., "4d9fND1xQ8bWePmY"
```

### Custom Alphabet

```php
$generator = new SqidGenerator(
    alphabet: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
    minLength: 8
);
$id = $generator->generate();
// e.g., "X4M9FND1"
```

## Comparison

| Type | Length | Example |
|------|--------|---------|
| Sqid | 8+ chars | `4d9fND1xQ` |
| ULID | 26 chars | `01ARZ3NDEKTSV4RRFFQ69G5FAV` |
| UUID | 36 chars | `550e8400-e29b-41d4-a716-446655440000` |

**Advantages:**
- 42-78% shorter than UUID/ULID
- URL-safe without encoding
- Configurable for specific needs

**Limitations:**
- No embedded timestamp (not sortable)
- Higher collision probability at short lengths

## Database Storage

Store Sqids as VARCHAR with appropriate length:

```php
Schema::create('users', function (Blueprint $table) {
    $table->string('id', 16)->primary();
});
```

## Use Cases

**Choose Sqid when:**
- URL shorteners
- Public-facing IDs (cleaner URLs)
- QR codes (shorter = simpler)
- Mobile applications (bandwidth savings)
- Invoice/order numbers

**Choose UUID/ULID when:**
- Time-ordered IDs needed
- Maximum collision resistance required
- Database UUID types preferred

<a id="doc-docs-ulid"></a>

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

<a id="doc-docs-uuid-variants"></a>

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

UUID v4 generates completely random IDs:

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

| Use Case | Recommended Version |
|----------|---------------------|
| Default/General Use | UUID v4 (random) |
| Database Performance | UUID v7 (timestamp-based) |
| Time-Ordered IDs | UUID v6 or v7 |
| Deterministic IDs | UUID v5 (or v3 for legacy) |
| Custom Requirements | UUID v8 |
| Legacy Compatibility | UUID v1 |

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
