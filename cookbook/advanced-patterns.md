# Advanced Patterns

This guide covers advanced usage patterns and best practices for strongly-typed IDs in complex applications.

## Domain-Driven Design

### Aggregates and Entities

Use strongly-typed IDs to enforce aggregate boundaries:

```php
// Domain entities
final readonly class User
{
    public function __construct(
        public UserId $id,
        public string $name,
        public string $email,
        public OrganizationId $organizationId,
    ) {}
}

final readonly class Organization
{
    public function __construct(
        public OrganizationId $id,
        public string $name,
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

### Value Objects

Combine IDs with other value objects:

```php
final readonly class UserIdentifier
{
    public function __construct(
        public UserId $id,
        public Email $email,
    ) {}

    public static function create(string $id, string $email): self
    {
        return new self(
            UserId::fromString($id),
            Email::fromString($email),
        );
    }
}

final readonly class OrderReference
{
    public function __construct(
        public OrderId $id,
        public OrderNumber $number,
    ) {}
}
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

    public function save(User $user): void
    {
        UserModel::updateOrCreate(
            ['id' => $user->id],
            [
                'name' => $user->name,
                'email' => $user->email,
                'organization_id' => $user->organizationId,
            ]
        );
    }

    public function delete(UserId $id): void
    {
        UserModel::destroy($id);
    }

    public function findByOrganization(OrganizationId $organizationId): array
    {
        return UserModel::where('organization_id', $organizationId)
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    private function toDomain(UserModel $model): User
    {
        return new User(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            organizationId: $model->organization_id,
        );
    }
}
```

## Event Sourcing

### Domain Events

Use typed IDs in domain events:

```php
abstract readonly class DomainEvent
{
    public function __construct(
        public string $eventId,
        public DateTimeImmutable $occurredAt,
    ) {}
}

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
    /** @return array<DomainEvent> */
    public function getEventsForAggregate(StronglyTypedId $aggregateId): array;

    public function append(StronglyTypedId $aggregateId, DomainEvent $event): void;
}

final class DatabaseEventStore implements EventStoreInterface
{
    public function getEventsForAggregate(StronglyTypedId $aggregateId): array
    {
        return EventModel::where('aggregate_id', $aggregateId->toString())
            ->orderBy('version')
            ->get()
            ->map(fn($model) => $this->deserialize($model))
            ->all();
    }

    public function append(StronglyTypedId $aggregateId, DomainEvent $event): void
    {
        EventModel::create([
            'aggregate_id' => $aggregateId->toString(),
            'aggregate_type' => $aggregateId::class,
            'event_type' => $event::class,
            'event_data' => json_encode($event),
            'occurred_at' => $event->occurredAt,
        ]);
    }
}
```

## CQRS (Command Query Responsibility Segregation)

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

final readonly class PlaceOrderCommand
{
    public function __construct(
        public OrderId $orderId,
        public UserId $userId,
        public array $items,
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

### Read Models

Separate read models can use simplified ID representations:

```php
// Write model (domain)
final readonly class User
{
    public function __construct(
        public UserId $id,
        public string $name,
        public Email $email,
        public OrganizationId $organizationId,
    ) {}
}

// Read model (projection)
final readonly class UserListItem
{
    public function __construct(
        public string $id,              // Plain string for read-side
        public string $name,
        public string $email,
        public string $organizationName,
    ) {}
}
```

## Multi-Tenancy

### Tenant-Scoped IDs

Ensure IDs include tenant context:

```php
final readonly class TenantId extends StronglyTypedId {}

final readonly class TenantScopedUser
{
    public function __construct(
        public UserId $id,
        public TenantId $tenantId,
        public string $name,
        public string $email,
    ) {}
}

interface TenantScopedRepositoryInterface
{
    public function find(TenantId $tenantId, UserId $userId): ?TenantScopedUser;
    public function findAllByTenant(TenantId $tenantId): array;
}
```

### Global Scope

Use Eloquent global scopes with tenant IDs:

```php
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class TenantScope implements Scope
{
    public function __construct(
        private TenantId $tenantId,
    ) {}

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

    private function findOrCreateCustomer(UserId $userId): StripeCustomerId
    {
        // Implementation
    }
}
```

Store external IDs in your database:

```php
class User extends Model
{
    protected function casts(): array
    {
        return [
            'id' => UserId::asEloquentCast(),
            'stripe_customer_id' => StripeCustomerId::asEloquentCast(),
        ];
    }
}
```

## Generator Selection Strategy

Choose different generators for different ID types based on requirements:

```php
// Use Sqids for public-facing, short IDs
final readonly class InviteId extends StronglyTypedId {}
final readonly class ShareLinkId extends StronglyTypedId {}

// Configure in service provider
use Cline\StronglyTypedId\Generators\SqidGenerator;

$inviteGenerator = new SqidGenerator(minLength: 8);
$inviteId = InviteId::fromString($inviteGenerator->generate());
// e.g., "4d9fND1xQ"

// Use UUID v7 for internal database IDs (better indexing)
IdGenerator::setGenerator(GeneratorType::UuidV7);
$userId = UserId::generate();
// e.g., "017f22e2-79b0-7cc3-98c4-dc0c0c07398f"

// Use ULID for time-series data
IdGenerator::setGenerator(GeneratorType::Ulid);
$eventId = EventId::generate();
// e.g., "01ARZ3NDEKTSV4RRFFQ69G5FAV"
```

**Selection Guide:**

| Generator | Best For | Example Use Cases |
|-----------|----------|-------------------|
| Sqid | Short, user-facing IDs | Invite codes, share links, QR codes |
| UUID v7 | Database primary keys | User IDs, order IDs, product IDs |
| ULID | Time-series data | Event IDs, log entries, audit trails |
| UUID v4 | General purpose | Session IDs, API keys, tokens |

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

## Soft Deletes with Typed IDs

Track who deleted an entity:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'id' => UserId::asEloquentCast(),
            'deleted_by' => UserId::asEloquentCast(),
            'deleted_at' => 'datetime',
        ];
    }
}

// Usage
$user->delete();
$user->deleted_by = $currentUserId;
$user->save();
```

## Polymorphic Relationships

Use typed IDs with polymorphic relationships:

```php
class Comment extends Model
{
    protected function casts(): array
    {
        return [
            'id' => CommentId::asEloquentCast(),
            'user_id' => UserId::asEloquentCast(),
        ];
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}

class Post extends Model
{
    protected function casts(): array
    {
        return [
            'id' => PostId::asEloquentCast(),
        ];
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

// Usage
$post = Post::find($postId);
$comment = $post->comments()->create([
    'user_id' => $userId,
    'content' => 'Great post!',
]);
```

## Caching Strategies

Use typed IDs in cache keys:

```php
final class UserCache
{
    public function __construct(
        private Cache $cache,
    ) {}

    public function get(UserId $userId): ?User
    {
        $key = $this->buildKey($userId);

        return $this->cache->get($key);
    }

    public function put(User $user, int $ttl = 3600): void
    {
        $key = $this->buildKey($user->id);

        $this->cache->put($key, $user, $ttl);
    }

    public function forget(UserId $userId): void
    {
        $key = $this->buildKey($userId);

        $this->cache->forget($key);
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
    private string $name = 'Test User';
    private string $email = 'test@example.com';
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

    public function withOrganization(OrganizationId $organizationId): self
    {
        $this->organizationId = $organizationId;
        return $this;
    }

    public function build(): User
    {
        return new User(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            organizationId: $this->organizationId,
        );
    }
}

// Usage in tests
$user = (new UserBuilder())
    ->withId(UserId::fromString('550e8400-e29b-41d4-a716-446655440000'))
    ->build();
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

    public static function janeDoeId(): UserId
    {
        return UserId::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');
    }
}

// Usage
$john = User::factory()->create(['id' => UserFixtures::johnDoeId()]);
```
