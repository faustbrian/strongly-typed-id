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
