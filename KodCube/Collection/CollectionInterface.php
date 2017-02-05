<?php
namespace KodCube\Collection;

interface CollectionInterface
{

    /**
     * Convert Collection to JSON String
     * @param int $options json_encode options
     * @return string
     */
    public function toJson($options = 0):string;

    /**
     * Convert Collection to Array
     * @return array
     */
    public function toArray():array;

    /** Retrieve all collection items as an array
     * Convert Collection to Array
     * @return array
     */
    public function all():array;

    /**
     * Map over each of the items
     * @param callable $callback
     * @return CollectionInterface
     */
    public function map(callable $callback):CollectionInterface;

    /**
     * Return Filtered Collection Items
     * @param callable $callback
     * @return CollectionInterface
     */
    public function filter(callable $callback):CollectionInterface;

    /**
     * Invoke Callable with each item in Collection
     * @param callable $callback
     * @return CollectionInterface
     */
    public function each(callable $callback):CollectionInterface;

    /**
     * Reduce Collection Items
     * @param callable $callback
     * @param null $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null);

    public function merge(array $items):CollectionInterface;

    public function sum($callback = null):float;

    public function min($callback = null):float;

}