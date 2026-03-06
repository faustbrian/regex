<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex;

use Cline\Regex\Exceptions\RegexCaptureDoesNotExist;

use function array_key_exists;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class MatchResult
{
    /**
     * @param array<int|string, null|string> $captures
     * @param array<int|string, null|int>    $captureOffsets
     */
    public function __construct(
        private string $pattern,
        private string $subject,
        private bool $matched,
        private array $captures,
        private ?int $offset,
        private array $captureOffsets,
    ) {}

    /**
     * @param array<int|string, array{0: null, 1: -1}|array{0: string, 1: int}> $captures
     */
    public static function fromPregMatch(
        string $pattern,
        string $subject,
        bool $matched,
        array $captures,
    ): self {
        $values = [];
        $offsets = [];

        foreach ($captures as $group => $capture) {
            $values[$group] = $capture[0];
            $offsets[$group] = $capture[1] === -1 ? null : $capture[1];
        }

        return new self(
            $pattern,
            $subject,
            $matched,
            $values,
            $offsets[0] ?? null,
            $offsets,
        );
    }

    public function matched(): bool
    {
        return $this->matched;
    }

    public function pattern(): string
    {
        return $this->pattern;
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function value(): ?string
    {
        return $this->captures[0] ?? null;
    }

    public function offset(): ?int
    {
        return $this->offset;
    }

    public function capture(int|string $capture): ?string
    {
        if (!array_key_exists($capture, $this->captures)) {
            throw RegexCaptureDoesNotExist::forPatternAndCapture($this->pattern, $capture);
        }

        return $this->captures[$capture];
    }

    public function captureOffset(int|string $capture): ?int
    {
        if (!array_key_exists($capture, $this->captureOffsets)) {
            throw RegexCaptureDoesNotExist::forPatternAndCapture($this->pattern, $capture);
        }

        return $this->captureOffsets[$capture];
    }

    /**
     * @return array<int|string, null|string>
     */
    public function captures(): array
    {
        return $this->captures;
    }
}
