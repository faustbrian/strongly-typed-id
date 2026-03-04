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
