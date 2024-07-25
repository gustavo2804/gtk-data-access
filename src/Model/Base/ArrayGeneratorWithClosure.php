<?php


class ArrayGeneratorWithClosure
{
    public $count;
    public $items;
    public $currentIndex = 0;
    public $closure;
    public $currentItem = null;

    public function __construct($items, $closure)
    {
        $this->items   = $items;
        $this->count   = count($items);
        $this->closure = $closure;
    }

    public function next()
    {
        $debug = false;

        while ($this->currentIndex < $this->count)
        {
            $maybeItem = $this->items[$this->currentIndex];

            if ($this->closure)
            {
                $closure = $this->closure;
                $item    = $closure($maybeItem);
            }
            else
            {
                $item = $maybeItem;
            }
           
            $this->currentIndex++;

            if ($item)
            {
                $this->currentItem = $item;
                return $item;
            }
        }

        return null;
    }
}
