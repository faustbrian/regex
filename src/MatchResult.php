<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex;

use Cline\Regex\Exceptions\RegexFailed;
use Cline\Regex\Exceptions\RegexGroupDoesNotExist;
use Cline\Regex\Exceptions\RegexMatchFailed;
use Exception;

use const PREG_UNMATCHED_AS_NULL;

use function array_key_exists;
use function preg_match;

/**
 * Value object describing the outcome of a single `preg_match` operation.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MatchResult extends RegexResult
{
    /**
     * @param array<int|string, null|string> $matches Captured groups keyed by index or group name.
     */
    public function __construct(
        private readonly string $pattern,
        private readonly string $subject,
        private readonly bool $hasMatch,
        private readonly array $matches,
    ) {}

    /**
     * Run the regex and wrap the native single-match result.
     */
    public static function for(string $pattern, string $subject): self
    {
        $matches = [];

        try {
            $result = preg_match($pattern, $subject, $matches, PREG_UNMATCHED_AS_NULL);
        } catch (Exception $exception) {
            throw RegexMatchFailed::forPatternAndSubject($pattern, $subject, $exception->getMessage());
        }

        if ($result === false) {
            throw RegexMatchFailed::forPatternAndSubject($pattern, $subject, self::lastPregError());
        }

        return new self($pattern, $subject, $result === 1, $matches);
    }

    /**
     * Determine whether the pattern matched the subject.
     */
    public function hasMatch(): bool
    {
        return $this->hasMatch;
    }

    /**
     * Return the full matched value or `null` when no match occurred.
     */
    public function result(): ?string
    {
        return $this->matches[0] ?? null;
    }

    /**
     * Return the full match or the provided fallback value.
     */
    public function resultOr(string $default): string
    {
        return $this->result() ?? $default;
    }

    /**
     * Return a captured group by numeric index or named key.
     */
    public function group(int|string $group): string
    {
        if (!array_key_exists($group, $this->matches)) {
            throw RegexGroupDoesNotExist::forPatternSubjectAndGroup($this->pattern, $this->subject, $group);
        }

        return $this->matches[$group] ?? throw RegexGroupDoesNotExist::forPatternSubjectAndGroup(
            $this->pattern,
            $this->subject,
            $group,
        );
    }

    /**
     * Return every captured group exactly as reported by `preg_match`.
     *
     * @return array<int|string, null|string>
     */
    public function groups(): array
    {
        return $this->matches;
    }

    /**
     * Return a captured group or the provided fallback value.
     */
    public function groupOr(int|string $group, string $default): string
    {
        try {
            return $this->group($group);
        } catch (RegexFailed) {
            return $default;
        }
    }

    /**
     * Return a captured group by name.
     */
    public function namedGroup(int|string $group): string
    {
        return $this->group($group);
    }
}
