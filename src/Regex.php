<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex;

/**
 * Entry point for fluent regex matching and replacement helpers.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Regex
{
    /**
     * Execute a single regular-expression match against the provided subject.
     */
    public static function match(string $pattern, string $subject): MatchResult
    {
        return MatchResult::for($pattern, $subject);
    }

    /**
     * Execute a global regular-expression match against the provided subject.
     */
    public static function matchAll(string $pattern, string $subject): MatchAllResult
    {
        return MatchAllResult::for($pattern, $subject);
    }

    /**
     * @param array<int, string>|string                               $pattern
     * @param array<int, string>|callable(MatchResult): string|string $replacement
     * @param array<int, string>|string                               $subject
     *
     * Replace matches and return both the transformed value and match count.
     */
    public static function replace(
        string|array $pattern,
        string|array|callable $replacement,
        string|array $subject,
        int $limit = -1,
    ): ReplaceResult {
        return ReplaceResult::for($pattern, $replacement, $subject, $limit);
    }
}
