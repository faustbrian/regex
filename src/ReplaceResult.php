<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class ReplaceResult
{
    public function __construct(
        private string $pattern,
        private string $originalSubject,
        private string $subject,
        private int $replacements,
    ) {}

    public function pattern(): string
    {
        return $this->pattern;
    }

    public function originalSubject(): string
    {
        return $this->originalSubject;
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function replacements(): int
    {
        return $this->replacements;
    }
}
