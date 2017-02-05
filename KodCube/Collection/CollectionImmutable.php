<?php
namespace KodCube\Collection;

use BadMethodCallException;

class CollectionImmutable extends Collection
{
    public function with($item,$offset=null)
    {
        $items = $this->items;
        if ( $offset === null) {
            $items[] = $item;
        } else {
            $items[$offset] = $item;
        }
        return new static($items);
    }

    public function without($item=null,bool $isKey=false):CollectionInterface
    {
        if ( $isKey ) {

            if ( ! isset($this->items[$item]) ) return $this;

            $items = $this->items;

            unset($items[$item]);

            return new static($items);

        }

        $items = $this->items;
        while($key = array_search($item,$items)) {
            unset($items[$key]);
        }
        return new static($items);
    }


    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException('Please use ->with method');
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException('Please use ->with method');
    }
}