<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex\Exceptions;

use function implode;
use function is_string;
use function mb_strlen;
use function mb_substr;
use function sprintf;

/**
 * Exception raised when a replacement operation fails.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class RegexReplaceFailed extends RegexFailed
{
    /**
     * @param array<int, string>|string $pattern
     * @param array<int, string>|string $subject
     *
     * Create an exception for a failed replacement operation.
     */
    public static function forPatternAndSubject(
        string|array $pattern,
        string|array $subject,
        string $message,
    ): self {
        return new self(sprintf(
            'Error replacing pattern `%s` in subject `%s`. %s',
            self::stringify($pattern),
            self::trimString(self::stringify($subject)),
            $message,
        ));
    }

    /**
     * @param array<int, string>|string $value
     *
     * Normalize array and string inputs for exception messages.
     */
    private static function stringify(string|array $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        return implode(', ', $value);
    }

    /**
     * Truncate long subjects to keep exception messages readable.
     */
    private static function trimString(string $string): string
    {
        if (mb_strlen($string) < 40) {
            return $string;
        }

        return mb_substr($string, 0, 40).'...';
    }
}
