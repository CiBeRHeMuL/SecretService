<?php

namespace App\Domain\Iterator;

use Closure;
use Iterator;

/**
 * @template-covariant TItem
 * @template-covariant TProj
 * @template-covariant TKey
 * @template-implements Iterator<TKey, TProj>
 */
class ProjectionIterator implements Iterator
{
    /**
     * @param Iterator $iterator
     * @param Closure(TItem): TProj $projection
     */
    public function __construct(
        private Iterator $iterator,
        private Closure $projection,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function current(): mixed
    {
        return ($this->projection)($this->iterator->current());
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->iterator->next();
    }

    /**
     * @inheritDoc
     */
    public function key(): mixed
    {
        return $this->iterator->key();
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->iterator->rewind();
    }
}
