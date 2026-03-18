<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Casts\Data\StronglyTypedIdCast;
use Cline\Struct\Metadata\MetadataFactory;
use Cline\Struct\Metadata\PropertyMetadata;
use Tests\Fixtures\BusinessUnitId;
use Tests\Fixtures\RegularClass;
use Tests\Fixtures\RegularClassData;
use Tests\Fixtures\UnionValueData;
use Tests\Fixtures\UserData;
use Tests\Fixtures\UserId;

function propertyMetadataFor(string $dataClass, string $property): PropertyMetadata
{
    $metadata = new MetadataFactory()->for($dataClass);

    foreach ($metadata->properties as $candidate) {
        if ($candidate->name === $property) {
            return $candidate;
        }
    }

    throw new InvalidArgumentException("Property [{$property}] not found on [{$dataClass}].");
}

describe('StronglyTypedIdCast', function (): void {
    describe('Happy Paths', function (): void {
        test('casts string value to strongly typed ID instance', function (): void {
            // Arrange
            $uuid = '01999aaa-0000-7000-a000-000000000000';

            // Act
            $userData = UserData::create(['id' => $uuid]);

            // Assert
            expect($userData->id)->toBeInstanceOf(UserId::class);
            expect($userData->id->value)->toBe($uuid);
        });

        test('preserves existing strongly typed ID instance', function (): void {
            // Arrange
            $userId = UserId::fromString('01999aaa-0000-7000-a000-000000000000');

            // Act
            $userData = UserData::create(['id' => $userId]);

            // Assert
            expect($userData->id)->toBe($userId);
            expect($userData->id)->toBeInstanceOf(UserId::class);
        });

        test('handles different strongly typed ID subclasses', function (): void {
            // Arrange
            $uuid = '01999aaa-0000-7000-a000-000000000000';
            $buUuid = '01999bbb-0000-7000-a000-000000000000';

            // Act
            $userData = UserData::create([
                'id' => $uuid,
                'businessUnitId' => $buUuid,
            ]);

            // Assert
            expect($userData->id)->toBeInstanceOf(UserId::class);
            expect($userData->id->value)->toBe($uuid);
            expect($userData->businessUnitId)->toBeInstanceOf(BusinessUnitId::class);
            expect($userData->businessUnitId->value)->toBe($buUuid);
        });

        test('works with valid UUID v4 format', function (): void {
            // Arrange
            $uuid = '550e8400-e29b-41d4-a716-446655440000';

            // Act
            $userData = UserData::create(['id' => $uuid]);

            // Assert
            expect($userData->id)->toBeInstanceOf(UserId::class);
            expect($userData->id->value)->toBe($uuid);
        });

        test('works with valid UUID v7 format', function (): void {
            // Arrange
            $uuid = '01999aaa-0000-7000-a000-000000000000';

            // Act
            $userData = UserData::create(['id' => $uuid]);

            // Assert
            expect($userData->id)->toBeInstanceOf(UserId::class);
            expect($userData->id->value)->toBe($uuid);
        });

        test('preserves UUID case from string input', function (): void {
            // Arrange
            $mixedCaseUuid = '01999AAA-0000-7000-A000-000000000000';

            // Act
            $userData = UserData::create(['id' => $mixedCaseUuid]);

            // Assert
            expect($userData->id->value)->toBe($mixedCaseUuid);
        });

        test('handles null values in optional fields', function (): void {
            // Arrange
            $uuid = '01999aaa-0000-7000-a000-000000000000';

            // Act
            $userData = UserData::create([
                'id' => $uuid,
                'businessUnitId' => null,
            ]);

            // Assert
            expect($userData->id)->toBeInstanceOf(UserId::class);
            expect($userData->businessUnitId)->toBeNull();
        });

        test('handles mixed ID instances and strings', function (): void {
            // Arrange
            $userId = UserId::fromString('01999aaa-0000-7000-a000-000000000000');
            $buUuid = '01999bbb-0000-7000-a000-000000000000';

            // Act
            $userData = UserData::create([
                'id' => $userId,
                'businessUnitId' => $buUuid,
            ]);

            // Assert
            expect($userData->id)->toBe($userId);
            expect($userData->businessUnitId)->toBeInstanceOf(BusinessUnitId::class);
            expect($userData->businessUnitId->value)->toBe($buUuid);
        });
    });

    describe('Sad Paths', function (): void {
        test('throws exception when string is invalid UUID format', function (): void {
            // Arrange
            $invalidUuid = 'not-a-valid-uuid';

            // Act & Assert
            expect(fn (): UserData => UserData::create(['id' => $invalidUuid]))
                ->toThrow(InvalidArgumentException::class, 'Invalid UUID format');
        });

        test('throws exception when string is empty', function (): void {
            // Arrange & Act & Assert
            expect(fn (): UserData => UserData::create(['id' => '']))
                ->toThrow(InvalidArgumentException::class, 'cannot be empty');
        });

        test('throws exception for malformed UUID patterns', function (string $invalidUuid): void {
            // Arrange & Act & Assert
            expect(fn (): UserData => UserData::create(['id' => $invalidUuid]))
                ->toThrow(InvalidArgumentException::class);
        })->with([
            'missing segments' => ['01999aaa-0000-7000'],
            'extra segments' => ['01999aaa-0000-7000-a000-000000000000-extra'],
            'invalid characters' => ['01999aaa-0000-7000-g000-000000000000'],
            'wrong separators' => ['01999aaa_0000_7000_a000_000000000000'],
            'no separators' => ['01999aaa000070000a000000000000000'],
        ]);

        test('handles invalid type gracefully via data validation', function (): void {
            // Arrange & Act & Assert - Integer will fail type check before reaching cast
            expect(fn (): UserData => UserData::create([
                'id' => '01999aaa-0000-7000-a000-000000000000',
                'businessUnitId' => 12_345, // Invalid type - will fail constructor type check
            ]))->toThrow(TypeError::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('handles UUID with all zeros', function (): void {
            // Arrange
            $uuid = '00000000-0000-0000-0000-000000000000';

            // Act
            $userData = UserData::create(['id' => $uuid]);

            // Assert
            expect($userData->id)->toBeInstanceOf(UserId::class);
            expect($userData->id->value)->toBe($uuid);
        });

        test('handles UUID with all Fs', function (): void {
            // Arrange
            $uuid = 'ffffffff-ffff-ffff-ffff-ffffffffffff';

            // Act
            $userData = UserData::create(['id' => $uuid]);

            // Assert
            expect($userData->id)->toBeInstanceOf(UserId::class);
            expect($userData->id->value)->toBe($uuid);
        });

        test('handles mixed case UUID consistently', function (): void {
            // Arrange
            $mixedCase = '01999AaA-0000-7000-A000-000000000000';

            // Act
            $userData = UserData::create(['id' => $mixedCase]);

            // Assert
            expect($userData->id->value)->toBe($mixedCase);
        });

        test('serializes Data with strongly typed IDs to array', function (): void {
            // Arrange
            $uuid = '01999aaa-0000-7000-a000-000000000000';
            $buUuid = '01999bbb-0000-7000-a000-000000000000';
            $userData = UserData::create([
                'id' => $uuid,
                'businessUnitId' => $buUuid,
            ]);

            // Act
            $array = $userData->toArray();

            // Assert - Laravel Data serializes objects as-is without transformer
            expect($array['id'])->toBeInstanceOf(UserId::class);
            expect($array['id']->value)->toBe($uuid);
            expect($array['businessUnitId'])->toBeInstanceOf(BusinessUnitId::class);
            expect($array['businessUnitId']->value)->toBe($buUuid);
        });

        test('handles Data with only required ID field', function (): void {
            // Arrange
            $uuid = '01999aaa-0000-7000-a000-000000000000';

            // Act
            $userData = UserData::create(['id' => $uuid]);

            // Assert
            expect($userData->id)->toBeInstanceOf(UserId::class);
            expect($userData->businessUnitId)->toBeNull();
            expect($userData->name)->toBeNull();
        });

        test('roundtrip conversion maintains ID values', function (): void {
            // Arrange
            $uuid = '01999aaa-0000-7000-a000-000000000000';
            $buUuid = '01999bbb-0000-7000-a000-000000000000';

            // Act
            $userData1 = UserData::create([
                'id' => $uuid,
                'businessUnitId' => $buUuid,
                'name' => 'Test User',
            ]);
            $array = $userData1->toArray();
            $userData2 = UserData::create($array);

            // Assert
            expect($userData2->id->value)->toBe($uuid);
            expect($userData2->businessUnitId->value)->toBe($buUuid);
            expect($userData2->name)->toBe('Test User');
        });

        test('returns null when cast receives null value', function (): void {
            $cast = new StronglyTypedIdCast();
            $property = propertyMetadataFor(UserData::class, 'id');

            // Act - Cast should return null unchanged
            $result = $cast->get($property, null);

            // Assert
            expect($result)->toBeNull();
        });

        test('returns StronglyTypedId instance unchanged when cast receives it', function (): void {
            $cast = new StronglyTypedIdCast();
            $userId = UserId::fromString('01999aaa-0000-7000-a000-000000000000');
            $property = propertyMetadataFor(UserData::class, 'id');

            // Act - Cast should return the instance unchanged
            $result = $cast->get($property, $userId);

            // Assert - Same instance returned
            expect($result)->toBe($userId);
            expect($result)->toBeInstanceOf(UserId::class);
        });

        test('returns string unchanged when property has union type', function (): void {
            $cast = new StronglyTypedIdCast();
            $property = propertyMetadataFor(UnionValueData::class, 'value');
            $stringValue = '01999aaa-0000-7000-a000-000000000000';

            // Act - Cast should return string unchanged since type is not NamedType
            $result = $cast->get($property, $stringValue);

            // Assert
            expect($result)->toBe($stringValue);
            expect($result)->toBeString();
        });

        test('returns string unchanged when property class is not StronglyTypedId subclass', function (): void {
            $cast = new StronglyTypedIdCast();
            $property = propertyMetadataFor(RegularClassData::class, 'value');
            $stringValue = 'test-value';

            // Act - Cast should return string unchanged since RegularClass is not a StronglyTypedId
            $result = $cast->get($property, $stringValue);

            // Assert
            expect($result)->toBe($stringValue);
            expect($result)->toBeString();
        });
    });

    describe('Regressions', function (): void {
        test('prevents mixing different ID types with same UUID value', function (): void {
            // Arrange
            $uuid = '01999aaa-0000-7000-a000-000000000000';
            $userId = UserId::fromString($uuid);
            $businessUnitId = BusinessUnitId::fromString($uuid);

            // Act & Assert - Different classes with same value should not be equal
            expect($userId->equals($businessUnitId))->toBeFalse();
        });
    });
});
