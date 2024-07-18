<?php

class GTKCountableGenerator implements IteratorAggregate, Countable 
{
    private $generator;
    private $count;

    public function __construct(Generator $generator, int $count) {
        $this->generator = $generator;
        $this->count = $count;
    }

    public function getIterator(): Generator {
        yield from $this->generator;
    }

    public function count(): int {
        return $this->count;
    }
}
