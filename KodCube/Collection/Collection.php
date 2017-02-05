<?php
namespace KodCube\Collection;

use ArrayAccess;
use ArrayIterator;
use JsonSerializable;
use Countable;
use IteratorAggregate;
use InvalidArgumentException;

class Collection implements CollectionInterface,ArrayAccess,Countable,IteratorAggregate,JsonSerializable
{
    protected $items = [];

    /**
     * Static Make Constructor
     * @param array $items
     * @return CollectionInterface
     */
    public static function make(array $items):CollectionInterface
    {
        return new static($items);
    }

    /**
     * Collection constructor.
     * @param array $items
     */
    public function __construct(array $items=[])
    {
        $this->items = $items;
    }

    /**
     * Convert Collection into String (JSON)
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @inheritdoc
     */
    public function toJson($options = 0):string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * @inheritdoc
     */
    public function toArray():array
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function all():array
    {
        return $this->toArray();
    }

    /**
     * @inheritdoc
     */
    public function map(callable $callback):CollectionInterface
    {
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    /**
     * @inheritdoc
     */
    public function filter(callable $callback):CollectionInterface
    {
        return new static(
            array_filter(
                $this->items,
                $callback,
                ARRAY_FILTER_USE_BOTH
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function each(callable $callback):CollectionInterface
    {
        foreach ( $this->items as $key => $item ) {
            if ( $callback($item, $key) === false ) break;
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * @inheritdoc
     */
    public function merge(array $items):CollectionInterface
    {
        return new static(array_merge($this->items, $items));
    }

    /**
     * @inheritdoc
     */
    public function sum($callback = null):float
    {
        return $this->numericValues($callback)
                    ->reduce(function ($result,$item) {
                        return $result + $item;
                    }, 0);
    }

    /**
     * @inheritdoc
     */
    public function min($callback = null):float
    {
        return $this->numericValues($callback)
                    ->reduce(
                        function ($result,$item) {
                            return min($result,$item);
                        },
                        PHP_INT_MAX
                    );
    }

    /**
     * @inheritdoc
     */
    public function max($callback = null):float
    {
        return $this->numericValues($callback)
                    ->reduce(
                        function ($result,$item) {
                            return max($result,$item);
                        },
                        PHP_INT_MIN
                    );
    }

    /**
     * @inheritdoc
     */
    public function avg($callback = null):float
    {
        $count = $this->count();

        if ($count == 0) {

            throw new InvalidArgumentException('No Items in Collection');

        }

        return $this->numericValues($callback)->sum() / $count;

    }

    /**
     * @inheritdoc
     */
    public function median($callback = null):float
    {
        $count = $this->count();

        $sorted = $this->numericValues($callback)->sort();

        $middle = floor(($count-1)/2);

        if( $count % 2 ) { // odd number

            return $sorted[$middle];

        } else { // even number

            $low = $sorted[$middle];
            $high = $sorted[$middle+1];

            return (($low+$high)/2);

        }
    }

    /**
     * @inheritdoc
     */
    public function mean($callback = null):float
    {
        return $this->numericValues($callback)->avg();
    }

    /**
     * @inheritdoc
     */
    public function mode($callback = null):float
    {
        $counts = new static;
        $this->numericValues($callback)->each(function ($item) use ($counts) {
            $counts[$item] = isset($counts[$item]) ? $counts[$item] + 1 : 1;
        });
        return $counts->sort()->lastKey();

    }

    /**
     * @inheritdoc
     */
    protected function numericValues($callback=null):CollectionInterface
    {
        return $this->map(
            function($item) use ($callback) {

                if ( is_callable($callback) ) return $callback($item);

                if ( is_numeric($item) ) return $item;

                if ( ! is_string($callback) ) return null;

                if ( is_array($item) && isset($item[$callback]) && is_numeric($item[$callback]) ) {

                    return $item[$callback];
                }

                if ( is_object($item) ) {

                    if ( isset($item->$callback) && is_numeric($item->$callback) ) {

                        return $item->$callback;

                    }
                    if ( method_exists($item,$callback) ) {

                        return $item->callback();
                    }
                }
                return null;
            }
        )->filter(function ($item,$key) {
            return is_numeric($item);
        });
    }

    /**
     * @inheritdoc
     */
    public function sort(callable $callback = null)
    {
        $items = $this->all();

        if ( $callback ) {

            uasort( $items , $callback );

            return new static( $items );

        }
        asort( $items );

        return new static( $items  );
    }

    /**
     * @inheritdoc
     */
    public function reverse():CollectionInterface
    {
        return new static(array_reverse($this->items, true));
    }

    /**
     * @inheritdoc
     */
    public function keys():CollectionInterface
    {
        return new static(array_keys($this->items));
    }

    /**
     * @inheritdoc
     */
    public function values():CollectionInterface
    {
        return new static(array_values($this->items));
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset,$this->items);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function first()
    {
        return $this->firstItem();
    }

    /**
     * @inheritdoc
     */
    public function firstKey()
    {
        $keys = array_keys($this->items);
        return reset($keys);
    }

    /**
     * @inheritdoc
     */
    public function firstItem()
    {
        return reset($this->items);
    }

    /**
     * @inheritdoc
     */
    public function last()
    {
        return $this->lastItem();
    }

    /**
     * @inheritdoc
     */
    public function lastKey()
    {
        $keys = array_keys($this->items);
        return end($keys);
    }

    /**
     * @inheritdoc
     */
    public function lastItem()
    {
        return end($this->items);
    }


}