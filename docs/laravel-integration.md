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
