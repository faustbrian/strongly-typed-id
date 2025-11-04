<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\Carbon;
use Cline\StronglyTypedId\Exceptions\InvalidTypeException;
use Tests\Fixtures\UserId;

describe('InvalidTypeException', function (): void {
    describe('Happy Paths', function (): void {
        test('creates exception for getter with invalid integer type', function (): void {
            // Arrange
            $key = 'user_id';
            $value = 123;

            // Act
            $exception = InvalidTypeException::createForGetter($key, $value);

            // Assert
            expect($exception)->toBeInstanceOf(InvalidTypeException::class);
            expect($exception->getMessage())->toContain('Invalid type for attribute "user_id"');
            expect($exception->getMessage())->toContain('expected string from database');
            expect($exception->getMessage())->toContain('got int');
            expect($exception->getMessage())->toContain('Ensure the database column stores string values');
        });

        test('creates exception for setter with invalid array type', function (): void {
            // Arrange
            $key = 'user_id';
            $value = ['invalid'];
            $expectedClass = UserId::class;

            // Act
            $exception = InvalidTypeException::createForSetter($key, $value, $expectedClass);

            // Assert
            expect($exception)->toBeInstanceOf(InvalidTypeException::class);
            expect($exception->getMessage())->toContain('Invalid value for attribute "user_id"');
            expect($exception->getMessage())->toContain('expected null, string, or '.UserId::class.' instance');
            expect($exception->getMessage())->toContain('got array');
            expect($exception->getMessage())->toContain('Pass a valid ID string, the typed ID object, or null to clear the value');
        });

        test('creates exception for serializer with invalid object type', function (): void {
            // Arrange
            $key = 'user_id';
            $value = new stdClass();

            // Act
            $exception = InvalidTypeException::createForSerializer($key, $value);

            // Assert
            expect($exception)->toBeInstanceOf(InvalidTypeException::class);
            expect($exception->getMessage())->toContain('Cannot serialize attribute "user_id"');
            expect($exception->getMessage())->toContain('unexpected type stdClass');
            expect($exception->getMessage())->toContain('Expected StronglyTypedId instance or string value');
            expect($exception->getMessage())->toContain('Check the attribute cast configuration');
        });
    });

    describe('Sad Paths', function (): void {
        test('getter exception correctly identifies boolean type', function (): void {
            // Arrange
            $key = 'is_active';
            $value = true;

            // Act
            $exception = InvalidTypeException::createForGetter($key, $value);

            // Assert
            expect($exception->getMessage())->toContain('got bool');
        });

        test('setter exception correctly identifies float type', function (): void {
            // Arrange
            $key = 'amount';
            $value = 123.45;
            $expectedClass = 'App\ValueObjects\AmountId';

            // Act
            $exception = InvalidTypeException::createForSetter($key, $value, $expectedClass);

            // Assert
            expect($exception->getMessage())->toContain('got float');
            expect($exception->getMessage())->toContain('App\ValueObjects\AmountId');
        });

        test('serializer exception correctly identifies resource type', function (): void {
            // Arrange
            $key = 'file_handle';
            $value = fopen('php://memory', 'rb');

            // Act
            $exception = InvalidTypeException::createForSerializer($key, $value);

            // Assert
            expect($exception->getMessage())->toContain('unexpected type resource');

            // Cleanup
            fclose($value);
        });
    });

    describe('Edge Cases', function (): void {
        test('handles null value in getter', function (): void {
            // Arrange
            $key = 'optional_id';
            $value = null;

            // Act
            $exception = InvalidTypeException::createForGetter($key, $value);

            // Assert
            expect($exception->getMessage())->toContain('got null');
            expect($exception->getMessage())->toContain('optional_id');
        });

        test('handles empty array in setter', function (): void {
            // Arrange
            $key = 'user_id';
            $value = [];
            $expectedClass = UserId::class;

            // Act
            $exception = InvalidTypeException::createForSetter($key, $value, $expectedClass);

            // Assert
            expect($exception->getMessage())->toContain('got array');
        });

        test('handles closure in serializer', function (): void {
            // Arrange
            $key = 'callback';
            $value = fn (): string => 'test';

            // Act
            $exception = InvalidTypeException::createForSerializer($key, $value);

            // Assert
            expect($exception->getMessage())->toContain('unexpected type Closure');
        });

        test('handles anonymous class instance in getter', function (): void {
            // Arrange
            $key = 'custom_object';
            $value = new class() {};

            // Act
            $exception = InvalidTypeException::createForGetter($key, $value);

            // Assert
            expect($exception->getMessage())->toContain('got class@anonymous');
        });

        test('preserves attribute name with special characters', function (): void {
            // Arrange
            $key = 'user_id_v2';
            $value = 123;

            // Act
            $exception = InvalidTypeException::createForGetter($key, $value);

            // Assert
            expect($exception->getMessage())->toContain('"user_id_v2"');
        });

        test('handles long attribute names', function (): void {
            // Arrange
            $key = 'very_long_attribute_name_that_describes_something_specific';
            $value = false;

            // Act
            $exception = InvalidTypeException::createForGetter($key, $value);

            // Assert
            expect($exception->getMessage())->toContain($key);
        });

        test('handles nested object types in serializer', function (): void {
            // Arrange
            $key = 'nested';
            $value = Carbon::now();

            // Act
            $exception = InvalidTypeException::createForSerializer($key, $value);

            // Assert
            expect($exception->getMessage())->toContain('unexpected type Carbon\Carbon');
        });

        test('setter exception includes fully qualified class name', function (): void {
            // Arrange
            $key = 'business_unit_id';
            $value = 999;
            $expectedClass = 'App\Domain\BusinessUnit\ValueObjects\BusinessUnitId';

            // Act
            $exception = InvalidTypeException::createForSetter($key, $value, $expectedClass);

            // Assert
            expect($exception->getMessage())->toContain('App\Domain\BusinessUnit\ValueObjects\BusinessUnitId');
        });

        test('handles object type with namespace in getter', function (): void {
            // Arrange
            $key = 'user_id';
            $value = UserId::fromString('01999aaa-0000-7000-a000-000000000000');

            // Act
            $exception = InvalidTypeException::createForGetter($key, $value);

            // Assert
            expect($exception->getMessage())->toContain('got Tests\Fixtures\UserId');
        });

        test('handles array with mixed types in setter', function (): void {
            // Arrange
            $key = 'mixed_data';
            $value = ['string', 123, true, null];
            $expectedClass = 'App\ValueObjects\DataId';

            // Act
            $exception = InvalidTypeException::createForSetter($key, $value, $expectedClass);

            // Assert
            expect($exception->getMessage())->toContain('got array');
        });
    });

    describe('Regressions', function (): void {
        test('exception message format remains consistent for getter', function (): void {
            // Arrange
            $key = 'id';
            $value = 42;

            // Act
            $exception = InvalidTypeException::createForGetter($key, $value);

            // Assert - Verify exact message structure
            expect($exception->getMessage())->toBe(
                'Invalid type for attribute "id": expected string from database, got int. '.
                'Ensure the database column stores string values.',
            );
        });

        test('exception message format remains consistent for setter', function (): void {
            // Arrange
            $key = 'id';
            $value = 42;
            $expectedClass = 'App\Models\Id';

            // Act
            $exception = InvalidTypeException::createForSetter($key, $value, $expectedClass);

            // Assert - Verify exact message structure
            expect($exception->getMessage())->toBe(
                'Invalid value for attribute "id": expected null, string, or App\Models\Id instance, got int. '.
                'Pass a valid ID string, the typed ID object, or null to clear the value.',
            );
        });

        test('exception message format remains consistent for serializer', function (): void {
            // Arrange
            $key = 'id';
            $value = 42;

            // Act
            $exception = InvalidTypeException::createForSerializer($key, $value);

            // Assert - Verify exact message structure
            expect($exception->getMessage())->toBe(
                'Cannot serialize attribute "id": unexpected type int. '.
                'Expected StronglyTypedId instance or string value. Check the attribute cast configuration.',
            );
        });

        test('is instance of InvalidArgumentException', function (): void {
            // Arrange
            $exception = InvalidTypeException::createForGetter('id', 123);

            // Assert
            expect($exception)->toBeInstanceOf(InvalidArgumentException::class);
        });

        test('all factory methods return InvalidTypeException instances', function (): void {
            // Arrange & Act
            $getterException = InvalidTypeException::createForGetter('id', 123);
            $setterException = InvalidTypeException::createForSetter('id', 123, 'App\Id');
            $serializerException = InvalidTypeException::createForSerializer('id', 123);

            // Assert
            expect($getterException)->toBeInstanceOf(InvalidTypeException::class);
            expect($setterException)->toBeInstanceOf(InvalidTypeException::class);
            expect($serializerException)->toBeInstanceOf(InvalidTypeException::class);
        });
    });
});
