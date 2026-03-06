<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex\Exceptions;

use RuntimeException;
use Throwable;

use function mb_strlen;
use function mb_substr;
use function sprintf;

/**
 * @author Brian Faust <brian@cline.sh>
 */
abstract class RegexFailed extends RuntimeException implements RegexException
{
    final public function __construct(
        private readonly string $pattern,
        private readonly string $subject,
        private readonly string $operation,
        private readonly string $pregMessage,
        private readonly int $pregErrorCode = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($this->buildMessage(), 0, $previous);
    }

    public function operation(): string
    {
        return $this->operation;
    }

    public function pattern(): string
    {
        return $this->pattern;
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function subjectPreview(): string
    {
        if (mb_strlen($this->subject) <= 40) {
            return $this->subject;
        }

        return mb_substr($this->subject, 0, 40).'...';
    }

    public function pregMessage(): string
    {
        return $this->pregMessage;
    }

    public function pregErrorCode(): int
    {
        return $this->pregErrorCode;
    }

    protected function buildMessage(): string
    {
        return sprintf(
            'Regex %s failed for pattern `%s` on subject `%s`. %s',
            $this->operation,
            $this->pattern,
            $this->subjectPreview(),
            $this->pregMessage,
        );
    }
}
