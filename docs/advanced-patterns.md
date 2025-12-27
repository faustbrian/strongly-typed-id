---
title: Advanced Patterns
description: DDD, event sourcing, CQRS, multi-tenancy, and complex architecture patterns
---

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
