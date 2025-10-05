<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Casts\Eloquent\StronglyTypedIdCast;
use Cline\StronglyTypedId\Contracts\IdGeneratorInterface;
use Cline\StronglyTypedId\Enums\GeneratorType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Uuid;
use Tests\Fixtures\BusinessUnitId;
use Tests\Fixtures\UserId;

describe('StronglyTypedId', function (): void {
    describe('Construction', function (): void {
        test('can be created with valid UUID string', function (): void {
            $uuid = '01999aaa-0000-7000-a000-000000000000';
            $userId = new UserId($uuid);

            expect($userId->value)->toBe($uuid);
        });

        test('throws exception when empty string provided', function (): void {
            new UserId('');
        })->throws(InvalidArgumentException::class, 'UserId cannot be empty');

        test('throws exception when invalid UUID format provided', function (): void {
            new UserId('not-a-uuid');
        })->throws(InvalidArgumentException::class, 'Invalid UUID format');

        test('accepts uppercase UUID and stores as-is', function (): void {
            $uuid = '01999AAA-0000-7000-A000-000000000000';
            $userId = new UserId($uuid);

            expect($userId->value)->toBe($uuid);
        });

        test('throws exception for malformed UUID patterns', function (string $invalidUuid): void {
            new UserId($invalidUuid);
        })->with([
            'missing segments' => ['01999aaa-0000-7000'],
            'extra segments' => ['01999aaa-0000-7000-a000-000000000000-extra'],
            'invalid characters' => ['01999aaa-0000-7000-g000-000000000000'],
            'wrong separators' => ['01999aaa_0000_7000_a000_000000000000'],
            'no separators' => ['01999aaa000070000a000000000000000'],
            'sql injection attempt' => ["'; DROP TABLE users; --"],
            'script injection' => ['<script>alert("xss")</script>'],
        ])->throws(InvalidArgumentException::class);
    });

    describe('fromString', function (): void {
        test('creates instance from valid UUID string', function (): void {
            $uuid = '01999aaa-0000-7000-a000-000000000000';
            $userId = UserId::fromString($uuid);

            expect($userId)->toBeInstanceOf(UserId::class);
            expect($userId->value)->toBe($uuid);
        });

        test('throws exception for invalid UUID string', function (): void {
            UserId::fromString('invalid');
        })->throws(InvalidArgumentException::class);

        test('accepts various valid UUID versions', function (string $validUuid): void {
            $userId = UserId::fromString($validUuid);

            expect($userId)->toBeInstanceOf(UserId::class);
            expect($userId->value)->toBe($validUuid);
            expect(Uuid::isValid($userId->value))->toBeTrue();
        })->with([
            'uuid v4' => [Uuid::uuid4()->toString()],
            'uuid v7' => [Uuid::uuid7()->toString()],
            'uuid v1' => [Uuid::uuid1()->toString()],
        ]);
    });

    describe('fromUuid', function (): void {
        test('creates instance from UuidInterface', function (): void {
            $uuid = Uuid::uuid7();
            $userId = UserId::fromUuid($uuid);

            expect($userId)->toBeInstanceOf(UserId::class);
            expect($userId->value)->toBe(mb_strtolower($uuid->toString()));
        });

        test('converts UUID to lowercase', function (): void {
            $uuid = Uuid::fromString('01999AAA-0000-7000-A000-000000000000');
            $userId = UserId::fromUuid($uuid);

            expect($userId->value)->toBe('01999aaa-0000-7000-a000-000000000000');
        });
    });

    describe('generate', function (): void {
        test('generates new ID using configured generator', function (): void {
            $userId = UserId::generate();

            expect($userId)->toBeInstanceOf(UserId::class);
            expect(Uuid::isValid($userId->value))->toBeTrue();
        });

        test('generates unique IDs', function (): void {
            $userId1 = UserId::generate();
            $userId2 = UserId::generate();

            expect($userId1->value)->not->toBe($userId2->value);
        });

        test('generates lowercase ID', function (): void {
            $userId = UserId::generate();

            expect($userId->value)->toBe(mb_strtolower($userId->value));
        });

        test('uses facade to generate IDs', function (): void {
            // Arrange - No setup needed, uses default configuration

            // Act
            $userId = UserId::generate();

            // Assert
            expect($userId)->toBeInstanceOf(UserId::class);
            expect($userId->value)->toBeString();
        });
    });

    describe('toString', function (): void {
        test('returns UUID string', function (): void {
            $uuid = '01999aaa-0000-7000-a000-000000000000';
            $userId = UserId::fromString($uuid);

            expect($userId->toString())->toBe($uuid);
        });
    });

    describe('__toString', function (): void {
        test('magic method returns UUID string', function (): void {
            $uuid = '01999aaa-0000-7000-a000-000000000000';
            $userId = UserId::fromString($uuid);

            expect((string) $userId)->toBe($uuid);
        });

        test('can be used in string context', function (): void {
            $uuid = '01999aaa-0000-7000-a000-000000000000';
            $userId = UserId::fromString($uuid);

            $result = 'User ID: '.$userId;

            expect($result)->toBe('User ID: '.$uuid);
        });
    });

    describe('equals', function (): void {
        test('returns true for same UUID value', function (): void {
            $uuid = '01999aaa-0000-7000-a000-000000000000';
            $userId1 = UserId::fromString($uuid);
            $userId2 = UserId::fromString($uuid);

            expect($userId1->equals($userId2))->toBeTrue();
        });

        test('returns false for different UUID values', function (): void {
            $userId1 = UserId::fromString('01999aaa-0000-7000-a000-000000000000');
            $userId2 = UserId::fromString('01999bbb-0000-7000-a000-000000000000');

            expect($userId1->equals($userId2))->toBeFalse();
        });

        test('returns false for different ID types with same value', function (): void {
            $uuid = '01999aaa-0000-7000-a000-000000000000';
            $userId = UserId::fromString($uuid);
            $businessUnitId = BusinessUnitId::fromString($uuid);

            expect($userId->equals($businessUnitId))->toBeFalse();
        });
    });

    describe('asEloquentCast', function (): void {
        test('returns cast string with class name', function (): void {
            $cast = UserId::asEloquentCast();

            expect($cast)->toBe(StronglyTypedIdCast::class.':'.UserId::class);
        });
    });

    describe('asEloquentAttribute', function (): void {
        test('creates attribute that converts string to UserId on get', function (): void {
            $attribute = UserId::asEloquentAttribute();
            $uuid = '01999aaa-0000-7000-a000-000000000000';

            $result = ($attribute->get)($uuid);

            expect($result)->toBeInstanceOf(UserId::class);
            expect($result->value)->toBe($uuid);
        });

        test('returns null when getting null value', function (): void {
            $attribute = UserId::asEloquentAttribute();

            $result = ($attribute->get)(null);

            expect($result)->toBeNull();
        });

        test('converts UserId to string on set', function (): void {
            $attribute = UserId::asEloquentAttribute();
            $userId = UserId::fromString('01999aaa-0000-7000-a000-000000000000');

            $result = ($attribute->set)($userId);

            expect($result)->toBe('01999aaa-0000-7000-a000-000000000000');
        });

        test('converts string to UserId then to string on set', function (): void {
            $attribute = UserId::asEloquentAttribute();
            $uuid = '01999aaa-0000-7000-a000-000000000000';

            $result = ($attribute->set)($uuid);

            expect($result)->toBe($uuid);
        });

        test('returns null when setting null value', function (): void {
            $attribute = UserId::asEloquentAttribute();

            $result = ($attribute->set)(null);

            expect($result)->toBeNull();
        });
    });

    describe('Immutability', function (): void {
        test('is marked as readonly', function (): void {
            $reflection = new ReflectionClass(UserId::class);

            expect($reflection->isReadOnly())->toBeTrue();
        });

        test('value property is public and readonly', function (): void {
            $userId = UserId::generate();
            $reflection = new ReflectionProperty(UserId::class, 'value');

            expect($reflection->isPublic())->toBeTrue();
            expect($reflection->isReadOnly())->toBeTrue();
        });
    });

    describe('JSON Serialization', function (): void {
        test('serializes as object with value property', function (): void {
            $uuid = '01999aaa-0000-7000-a000-000000000000';
            $userId = UserId::fromString($uuid);

            $json = json_encode(['id' => $userId]);
            $decoded = json_decode($json, true);

            expect($decoded['id'])->toBeArray();
            expect($decoded['id']['value'])->toBe($uuid);
        });

        test('requires explicit toString for string serialization', function (): void {
            $uuid = '01999aaa-0000-7000-a000-000000000000';
            $userId = UserId::fromString($uuid);

            $json = json_encode(['id' => $userId->toString()]);

            expect($json)->toBe('{"id":"'.$uuid.'"}');
        });

        test('can be reconstructed from serialized value property', function (): void {
            $originalId = UserId::generate();
            $json = json_encode(['id' => $originalId]);
            $decoded = json_decode($json, true);
            $reconstructedId = UserId::fromString($decoded['id']['value']);

            expect($reconstructedId->equals($originalId))->toBeTrue();
        });

        test('toString enables proper API response formatting', function (): void {
            $userId = UserId::generate();
            $apiResponse = [
                'user_id' => $userId->toString(),
                'created_at' => now()->toISOString(),
            ];

            $json = json_encode($apiResponse);
            $decoded = json_decode($json, true);

            expect($decoded['user_id'])->toBeString();
            expect(Uuid::isValid($decoded['user_id']))->toBeTrue();
        });
    });

    describe('Cast Integration', function (): void {
        test('cast handles null values correctly', function (): void {
            $cast = new StronglyTypedIdCast(UserId::class);
            $model = new class() extends Model
            {
                use HasFactory;
            };

            $result = $cast->get($model, 'id', null, []);

            expect($result)->toBeNull();
        });

        test('cast converts string to UserId on get', function (): void {
            $cast = new StronglyTypedIdCast(UserId::class);
            $model = new class() extends Model
            {
                use HasFactory;
            };
            $uuid = '01999aaa-0000-7000-a000-000000000000';

            $result = $cast->get($model, 'id', $uuid, []);

            expect($result)->toBeInstanceOf(UserId::class);
            expect($result->value)->toBe($uuid);
        });

        test('cast returns same instance if already UserId on get', function (): void {
            $cast = new StronglyTypedIdCast(UserId::class);
            $model = new class() extends Model
            {
                use HasFactory;
            };
            $userId = UserId::generate();

            $result = $cast->get($model, 'id', $userId, []);

            expect($result)->toBe($userId);
        });

        test('cast converts UserId to string on set', function (): void {
            $cast = new StronglyTypedIdCast(UserId::class);
            $model = new class() extends Model
            {
                use HasFactory;
            };
            $userId = UserId::generate();

            $result = $cast->set($model, 'id', $userId, []);

            expect($result)->toBe($userId->value);
        });

        test('cast validates and stores string on set', function (): void {
            $cast = new StronglyTypedIdCast(UserId::class);
            $model = new class() extends Model
            {
                use HasFactory;
            };
            $uuid = '01999aaa-0000-7000-a000-000000000000';

            $result = $cast->set($model, 'id', $uuid, []);

            expect($result)->toBe($uuid);
        });

        test('cast serializes UserId to string', function (): void {
            $cast = new StronglyTypedIdCast(UserId::class);
            $model = new class() extends Model
            {
                use HasFactory;
            };
            $userId = UserId::generate();

            $result = $cast->serialize($model, 'id', $userId, []);

            expect($result)->toBe($userId->value);
        });

        test('cast serializes null correctly', function (): void {
            $cast = new StronglyTypedIdCast(UserId::class);
            $model = new class() extends Model
            {
                use HasFactory;
            };

            $result = $cast->serialize($model, 'id', null, []);

            expect($result)->toBeNull();
        });
    });

    describe('Type Safety', function (): void {
        test('enforces type through constructor parameter', function (): void {
            $userId = UserId::generate();

            expect($userId->value)->toBeString();
            expect(Uuid::isValid($userId->value))->toBeTrue();
        });

        test('cannot be created with non-string via fromString', function (): void {
            expect(fn (): UserId => UserId::fromString(123))
                ->toThrow(TypeError::class);
        });

        test('maintains type through all factory methods', function (): void {
            $fromString = UserId::fromString('01999aaa-0000-7000-a000-000000000000');
            $fromUuid = UserId::fromUuid(Uuid::uuid7());
            $generated = UserId::generate();

            expect($fromString->value)->toBeString();
            expect($fromUuid->value)->toBeString();
            expect($generated->value)->toBeString();
        });
    });

    describe('Edge Cases', function (): void {
        test('handles UUID with all zeros', function (): void {
            $uuid = '00000000-0000-0000-0000-000000000000';
            $userId = UserId::fromString($uuid);

            expect($userId->value)->toBe($uuid);
        });

        test('handles UUID with all Fs', function (): void {
            $uuid = 'ffffffff-ffff-ffff-ffff-ffffffffffff';
            $userId = UserId::fromString($uuid);

            expect($userId->value)->toBe($uuid);
        });

        test('handles mixed case UUID consistently', function (): void {
            $mixedCase = '01999AaA-0000-7000-A000-000000000000';
            $userId = new UserId($mixedCase);

            expect($userId->value)->toBe($mixedCase);
        });

        test('toString and __toString return identical values', function (): void {
            $userId = UserId::generate();

            expect($userId->toString())->toBe((string) $userId);
        });

        test('multiple equals calls with same object return true', function (): void {
            $userId1 = UserId::generate();
            $userId2 = UserId::fromString($userId1->value);

            expect($userId1->equals($userId2))->toBeTrue();
            expect($userId1->equals($userId2))->toBeTrue();
            expect($userId2->equals($userId1))->toBeTrue();
        });
    });

    describe('Performance Characteristics', function (): void {
        test('generate creates time-sortable IDs with default uuid_v7 generator', function (): void {
            $first = UserId::generate();

            Carbon::setTestNow(now()->addMillisecond());

            $second = UserId::generate();

            Carbon::setTestNow();

            // UUID v7 is time-sortable, so first should be < second
            expect($first->value < $second->value)->toBeTrue();
        });

        test('can generate multiple IDs rapidly', function (): void {
            $ids = [];

            for ($i = 0; $i < 100; $i++) {
                $ids[] = UserId::generate()->value;
            }

            // All should be unique
            expect(count(array_unique($ids)))->toBe(100);
        });
    });

    describe('Generator Configuration Integration', function (): void {
        describe('Happy Paths', function (): void {
            test('generates valid UUID when using uuid_v4 generator', function (): void {
                // Arrange
                Config::set('strongly-typed-id.generator', GeneratorType::UuidV4->value);
                app()->forgetInstance(IdGeneratorInterface::class);

                // Act
                $userId = UserId::generate();

                // Assert
                expect($userId)->toBeInstanceOf(UserId::class);
                expect(Uuid::isValid($userId->value))->toBeTrue();
            });

            test('generates valid UUID when using uuid_v7 generator', function (): void {
                // Arrange
                Config::set('strongly-typed-id.generator', GeneratorType::UuidV7->value);
                app()->forgetInstance(IdGeneratorInterface::class);

                // Act
                $userId = UserId::generate();

                // Assert
                expect($userId)->toBeInstanceOf(UserId::class);
                expect(Uuid::isValid($userId->value))->toBeTrue();
            });

            test('different UUID generator configurations produce valid UUIDs', function (): void {
                // Arrange & Act - Generate with UUID v7
                Config::set('strongly-typed-id.generator', GeneratorType::UuidV7->value);
                app()->forgetInstance(IdGeneratorInterface::class);
                $uuidV7Id = UserId::generate();

                // Arrange & Act - Generate with UUID v4
                Config::set('strongly-typed-id.generator', GeneratorType::UuidV4->value);
                app()->forgetInstance(IdGeneratorInterface::class);
                $uuidV4Id = UserId::generate();

                // Assert - Both are valid UUIDs
                expect(Uuid::isValid($uuidV7Id->value))->toBeTrue();
                expect(Uuid::isValid($uuidV4Id->value))->toBeTrue();
                expect($uuidV7Id->value)->not->toBe($uuidV4Id->value);
            });
        });

        describe('Edge Cases', function (): void {
            test('handles generator switching between UUID generators', function (): void {
                // Arrange - Start with uuid_v7
                Config::set('strongly-typed-id.generator', GeneratorType::UuidV7->value);
                app()->forgetInstance(IdGeneratorInterface::class);

                // Act - Create first ID with uuid_v7
                $id1 = UserId::generate();

                // Arrange - Switch to uuid_v4
                Config::set('strongly-typed-id.generator', GeneratorType::UuidV4->value);
                app()->forgetInstance(IdGeneratorInterface::class);

                // Act - Create second ID with uuid_v4
                $id2 = UserId::generate();

                // Assert
                expect(Uuid::isValid($id1->value))->toBeTrue();
                expect(Uuid::isValid($id2->value))->toBeTrue();

                // Cleanup - Reset to default
                Config::set('strongly-typed-id.generator', GeneratorType::UuidV7->value);
                app()->forgetInstance(IdGeneratorInterface::class);
            });

            test('maintains lowercase output across UUID generator types', function (): void {
                $generators = ['uuid_v7', 'uuid_v4'];

                foreach ($generators as $generator) {
                    // Arrange
                    Config::set('strongly-typed-id.generator', $generator);
                    app()->forgetInstance(IdGeneratorInterface::class);

                    // Act
                    $userId = UserId::generate();

                    // Assert
                    expect($userId->value)->toBe(mb_strtolower($userId->value));
                    expect(Uuid::isValid($userId->value))->toBeTrue();
                }

                // Cleanup
                Config::set('strongly-typed-id.generator', GeneratorType::UuidV7->value);
                app()->forgetInstance(IdGeneratorInterface::class);
            });
        });

        describe('Sad Paths', function (): void {
            test('throws exception when ULID generator used with UUID-only StronglyTypedId', function (): void {
                // Arrange - Configure ULID generator (incompatible with UUID validation)
                Config::set('strongly-typed-id.generator', GeneratorType::Ulid->value);
                app()->forgetInstance(IdGeneratorInterface::class);

                // Act & Assert - ULID format fails UUID validation in StronglyTypedId
                expect(fn (): UserId => UserId::generate())
                    ->toThrow(InvalidArgumentException::class, 'Invalid UUID format');

                // Cleanup
                Config::set('strongly-typed-id.generator', GeneratorType::UuidV7->value);
                app()->forgetInstance(IdGeneratorInterface::class);
            });
        });
    });
});
