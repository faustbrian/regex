<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex\Helpers;

use function array_keys;
use function array_map;
use function count;
use function reset;

/**
 * Lightweight array helpers used when normalizing regex match matrices.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Arr
{
    /**
     * @template TKey of array-key
     * @template TValue
     * @template TResult
     *
     * @param array<TKey, TValue>       $array
     * @param callable(TValue): TResult $callback
     *
     * Apply a callback to each value while preserving the original keys.
     *
     * @return array<TKey, TResult>
     */
    public static function map(array $array, callable $callback): array
    {
        return array_map($callback, $array);
    }

    /**
     * @param array<int|string, array<int, null|string>> $array
     *
     * Rotate a group-keyed match matrix into a list keyed by individual match.
     *
     * @return array<int, array<int|string, null|string>>
     */
    public static function transpose(array $array): array
    {
        if ($array === []) {
            return [];
        }

        if (count($array) === 1) {
            $first = self::first($array);

            if ($first === null) {
                return [];
            }

            return array_map(
                static fn (?string $element): array => [$element],
                $first,
            );
        }

        $groupNames = array_keys($array);

        /** @var array<int, null|string> $firstGroup */
        $firstGroup = $array[$groupNames[0]];
        $result = [];

        foreach (array_keys($firstGroup) as $hit) {
            $group = [];

            foreach ($groupNames as $groupName) {
                $group[$groupName] = $array[$groupName][$hit];
            }

            $result[] = $group;
        }

        return $result;
    }

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param array<TKey, TValue> $array
     *
     * Return the first value from an array or `null` when it is empty.
     *
     * @return null|TValue
     */
    public static function first(array $array): mixed
    {
        if ($array === []) {
            return null;
        }

        return reset($array);
    }
}
