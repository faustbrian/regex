<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex;

use Cline\Regex\Exceptions\RegexReplaceFailed;
use Exception;

use function implode;
use function is_array;
use function is_callable;
use function is_string;
use function preg_replace;
use function preg_replace_callback;

/**
 * Value object describing the outcome of a regex replacement operation.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ReplaceResult extends RegexResult
{
    /**
     * @param array<int, string>|string $result Replacement output from `preg_replace*`.
     */
    public function __construct(
        private readonly string|array $result,
        private readonly int $count,
    ) {}

    /**
     * @param array<int, string>|string                               $pattern
     * @param array<int, string>|callable(MatchResult): string|string $replacement
     * @param array<int, string>|string                               $subject
     *
     * Run a replacement operation and wrap the native result payload.
     */
    public static function for(
        string|array $pattern,
        string|array|callable $replacement,
        string|array $subject,
        int $limit,
    ): self {
        try {
            [$result, $count] = is_callable($replacement) && !is_string($replacement)
                ? self::doReplacementWithCallable($pattern, $replacement, $subject, $limit)
                : self::doReplacement(
                    $pattern,
                    is_array($replacement) ? $replacement : $replacement,
                    $subject,
                    $limit,
                );
        } catch (Exception $exception) {
            throw RegexReplaceFailed::forPatternAndSubject($pattern, $subject, $exception->getMessage());
        }

        if ($result === null) {
            throw RegexReplaceFailed::forPatternAndSubject($pattern, $subject, self::lastPregError());
        }

        return new self($result, $count);
    }

    /**
     * Return the transformed subject produced by the replacement.
     *
     * @return array<int, string>|string
     */
    public function result(): string|array
    {
        return $this->result;
    }

    /**
     * Return how many replacements were performed.
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @param array<int, string>|string $pattern
     * @param array<int, string>|string $replacement
     * @param array<int, string>|string $subject
     *
     * Execute a replacement using string or array replacement values.
     *
     * @return array{0: null|array<int, string>|string, 1: int}
     */
    private static function doReplacement(
        string|array $pattern,
        string|array|callable $replacement,
        string|array $subject,
        int $limit,
    ): array {
        $count = 0;

        $result = preg_replace($pattern, $replacement, $subject, $limit, $count);

        return [$result, $count];
    }

    /**
     * @param array<int, string>|string     $pattern
     * @param callable(MatchResult): string $replacement
     * @param array<int, string>|string     $subject
     *
     * Execute a replacement callback that receives `MatchResult` wrappers.
     *
     * @return array{0: null|array<int, string>|string, 1: int}
     */
    private static function doReplacementWithCallable(
        string|array $pattern,
        callable $replacement,
        string|array $subject,
        int $limit,
    ): array {
        /**
         * @param array<int|string, string> $matches
         */
        $callback = static fn (array $matches): string => $replacement(
            self::matchResultFromCallback($pattern, $subject, $matches),
        );

        $count = 0;
        $result = preg_replace_callback($pattern, $callback, $subject, $limit, $count);

        return [$result, $count];
    }

    /**
     * @param array<int, string>|string $pattern
     * @param array<int, string>|string $subject
     * @param array<int|string, mixed>  $matches
     *
     * Build a `MatchResult` instance from callback match data.
     */
    private static function matchResultFromCallback(
        string|array $pattern,
        string|array $subject,
        array $matches,
    ): MatchResult {
        /** @var array<int|string, null|string> $matches */
        return new MatchResult(
            is_array($pattern) ? implode('|', $pattern) : $pattern,
            is_array($subject) ? implode('', $subject) : $subject,
            true,
            $matches,
        );
    }
}
