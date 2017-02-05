<?php

use KodCube\Collection\{ Collection,CollectionImmutable,CollectionInterface };
use PHPUnit\Framework\TestCase;
use ArrayIterator;

class CollectionTest extends TestCase
{

/*****************************************************************************************************
    Collection Construction
******************************************************************************************************/

    /**
     * Check Make static constructor
     *
     * @test
     */
    public function make()
    {
        $c = Collection::make([1,2,3]);
        $this->assertTrue(true);

    }

    /**
     * @test
     * @expectedException TypeError
     */
    public function makeNoArguments()
    {
        $c = Collection::make();
        $this->assertTrue(true);
    }

    /**
     * Check passing string to constructor
     * @test
     * @expectedException TypeError
     */
    public function makeStringArgument()
    {
        $c = Collection::make('test');
        $this->assertTrue(true);
    }

    /**
     * Check Instantiation
     * @test
     */

    public function contructorArrayArgument()
    {        
        $c = new Collection([1,2,3]);
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function contructorNoArguments()
    {        
        $c = new Collection();
        $this->assertTrue(true);
    }

    /**
     * @test
     * @expectedException TypeError
     */
    public function constructorStringArgument()
    {        
        $c = new Collection('test');
        $this->assertTrue(false);
    }

/*****************************************************************************************************
    Collection Conversions
******************************************************************************************************/

    /**
     * Collection to String (JSON)
     *
     * @test
     */
    public function toString()
    {
        $c = new Collection([1,2,3]);
        $this->assertEquals('[1,2,3]',(string)$c);
    }


    /**
     * Collection to Array
     * @test
     */
    public function toArray()
    {
        $c = new Collection([1,2,3]);
        $this->assertEquals([1,2,3],$c->toArray());
    }

    /**
     * Retrieve all Items in the Collection
     * @test
     */
    public function all()
    {
        $c = new Collection([1,2,3]);
        $this->assertEquals([1,2,3],$c->all());
    }

    /**
     * Retrieve all Items ignoring keys
     * @test
     */
    public function values()
    {
        $c = new Collection(['a'=>1,'b'=>2,'c'=>3]);
        $this->assertEquals([1,2,3],$c->values()->all());
    }

    /**
     * Sort Numeric Items
     * @test
     */
    public function sortNumeric()
    {
        $c = new Collection([5,2,1,4,3]);

        $this->assertSame([1,2,3,4,5],$c->sort()->values()->all());
    }

    /**
     * Sort Alpha Items
     * @test
     */
    public function sortAlpha()
    {
        $c = new Collection(['d','e','a','c','b']);

        $this->assertSame(['a','b','c','d','e'],$c->sort()->values()->all());
    }

    /**
     * Sort Objects Items
     * @test
     */
    public function sortCallback()
    {
        $c = new Collection([
            new Collection([1,2,3,4]),
            new Collection([5,6,7,8]),
            new Collection([3,4,5,6]),
            new Collection([2,3,4,5]),
            new Collection([4,5,6,7])
        ]);

        $this->assertEquals(
            [
                new Collection([1,2,3,4]),
                new Collection([2,3,4,5]),
                new Collection([3,4,5,6]),
                new Collection([4,5,6,7]),
                new Collection([5,6,7,8])
            ],
            $c->sort(
                function($a,$b) {
                    return $a->sum() > $b->sum();
                }
            )->values()->all()
        );
    }

    /**
     * Reverse Numeric Items
     * @test
     */
    public function reverseNumeric()
    {
        $c = new Collection([1,2,3,4,5]);

        $this->assertSame([5,4,3,2,1],$c->reverse()->values()->all());
    }

    /**
     * Reverse Alpha Items
     * @test
     */
    public function reverseAlpha()
    {
        $c = new Collection(['a','b','c','d','e']);

        $this->assertSame(['e','d','c','b','a'],$c->reverse()->values()->all());

    }

/*****************************************************************************************************
    Collection Iteration
******************************************************************************************************/

    /**
     * Test Map
     * @test
     */
    public function map()
    {
        $c = new Collection([1,2,3]);
        $c = $c->map(function ($item, $key) {
            return $key+$item;
        });
        $this->assertSame([1,3,5],$c->all());
    }

    /**
     * Test Filter
     * @test
     */
    public function filter()
    {
        $c = new Collection([1,2,3]);
        $c = $c->filter(function ($item) { return $item == 2; });
        $this->assertSame([ 1 => 2 ], $c->all());
    }

    /**
     * Test Each
     * @test
     */
    public function each()
    {
        $original = [1, 2, 3];
        $result = [];

        $c = new Collection($original);

        $c->each(function ($item, $key) use (&$result) {
            $result[$key] = $item;
        });
        $this->assertSame($original, $result);
    }

    /**
     * Test Reduce
     * @test
     */
    public function reduce()
    {
        $c = new Collection([1,2,3]);

        $result = $c->reduce(function ($result, $item) {
            return $result+$item;
        },0);
        $this->assertEquals(6,$result);
    }

    /**
     * Test Merge - No Keys
     * @test
     */
    public function mergeNoKeys()
    {

        $c = new Collection([1,2,3]);

        $c = $c->merge([4,5,6]);

        $this->assertEquals([1,2,3,4,5,6],$c->all());

    }

    /**
     * Test Merge - Keys
     * @test
     */
    public function mergeKeys()
    {

        $c = new Collection(['a' => 1,'b' => 2,'c'=>3]);

        $c = $c->merge(['c'=>4,'d'=>5,'e'=>6]);

        $this->assertEquals(['a' => 1,'b' => 2,'c'=>4,'d'=>5,'e'=>6],$c->all());

    }

    /**
     * Test Keys
     * @test
     */
    public function keys()
    {

        $c = new Collection(['a' => 1,'b' => 2,'c'=>3]);
        $this->assertEquals(['a','b','c'],$c->keys()->all());

    }


    /**
     * Get First Element of Collection
     * @test
     */
    public function first()
    {
        $c = new Collection([1,2,3]);
        $this->assertEquals(1,$c->first());
    }

    /**
     * Get First Element key of Collection
     * @test
     */
    public function firstKey()
    {
        $c = new Collection([1,2,3]);
        $this->assertEquals(0,$c->firstKey());
    }

    /**
     * Get Last Element of Collection
     * @test
     */
    public function last()
    {
        $c = new Collection([1,2,3]);
        $this->assertEquals(3,$c->last());
    }

    /**
     * Get Last Element key of Collection
     * @test
     */
    public function lastKey()
    {
        $c = new Collection([1,2,3]);
        $this->assertEquals(2,$c->lastKey());
    }

/*****************************************************************************************************
    Math Tests
******************************************************************************************************/

    /**
     * Test Sum
     * @test
     */
    public function sum()
    {

        $c = new Collection([1,2,3]);

        $this->assertEquals(6,$c->sum());

    }

    /**
     * Test Min
     * @test
     */
    public function min()
    {

        $c = new Collection([1,2,3]);

        $this->assertEquals(1,$c->min());

    }

    /**
     * Test Max
     * @test
     */
    public function max()
    {

        $c = new Collection([1,2,3]);

        $this->assertEquals(3,$c->max());

    }

    /**
     * Test Avg
     * @test
     */
    public function avg()
    {

        $c = new Collection([1,2,3]);

        $this->assertEquals(2,$c->avg());

    }

    /**
     * Test Avg No Items in Collection
     * @test
     * @expectedException InvalidArgumentException
     */
    public function avgNoItems()
    {

        $c = new Collection([]);

        $this->assertEquals(2,$c->avg());

    }

    /**
     * Test Median Items in Collection
     * @test
     */
    public function median()
    {
        $c = new Collection([1,1,2,5,6,6,9]);

        $this->assertEquals(5,$c->median());


        $c = new Collection([1,1,2,6,6,9]);

        $this->assertEquals(4,$c->median());


    }

    /**
     * Test Mode
     * @test
     */
    public function mode()
    {
        $c = new Collection([3,7,5,13,20,23,39,23,40,23,14,12,56,23,29]);

        $this->assertEquals(23,$c->mode());

    }

/*****************************************************************************************************
    Array Compatibility
******************************************************************************************************/

    /**
     * offsetExists
     * @test
     */
    public function offsetExists()
    {
        $c = new Collection([1,2,3]);
        $this->assertTrue($c->offsetExists(2));
    }

    /**
     * offsetGet
     * @test
     */
    public function offsetGet()
    {
        $c = new Collection([1,2,3]);
        $this->assertEquals(3,$c->offsetGet(2));
    }

    /**
     * offsetSet
     * @test
     */
    public function offsetSet()
    {
        $c = new Collection([1,2,3]);
        $c->offsetSet(3,4);
        $this->assertEquals(4,$c->offsetGet(3));
    }

    /**
     * offsetUnset
     * @test
     */
    public function offsetUnset()
    {
        $c = new Collection([1,2,3]);
        $c->offsetUnset(1);
        $this->assertEquals([1,3],$c->values()->all());
    }

    /**
     * countItems
     * @test
     */
    public function countItems()
    {
        $c = new Collection([1,2,3]);
        $this->assertEquals(3,$c->count());
    }

/*****************************************************************************************************
    Iterator Compatibility
******************************************************************************************************/

    /**
     * getIterator
     * @test
     */
    public function getIterator()
    {
        $items = [1,2,3];

        $c = new Collection($items);
        $this->assertEquals(new ArrayIterator($items),$c->getIterator());
    }

/*****************************************************************************************************
    JSON Compatibility
******************************************************************************************************/

    /**
     * Collection to JSON
     *
     * @test
     */
    public function toJson()
    {
        $c = new Collection([1,2,3]);
        $this->assertEquals('[1,2,3]',$c->toJson());
    }

    /**
     * Collection to JSON
     *
     * @test
     */
    public function jsonSerialize()
    {
        $c = new Collection([1,2,3]);
        $this->assertEquals('[1,2,3]',json_encode($c));
    }

/*****************************************************************************************************
    Immutability
******************************************************************************************************/

    /**
     * Test Offset Set throws Exception
     * @test
     * @expectedException BadMethodCallException
     */
    public function immutableOffsetSet(){

        $c = new CollectionImmutable([1,2,3]);
        $c[3] = 4;
        $this->assertTrue(true);
    }

    /**
     * Test Offset Unset throws Exception
     * @test
     * @expectedException BadMethodCallException
     */
    public function immutableOffsetUnset(){

        $c = new CollectionImmutable([1,2,3]);
        unset($c[2]);
        $this->assertTrue(true);
    }

    /**
     * with
     * @test
     */
    public function with(){

        $c = new CollectionImmutable([1,2,3]);
        $c2 = $c->with(4);
        $this->assertEquals([1,2,3,4],$c2->all());
    }

    /**
     * without
     * @test
     */
    public function without(){

        $c = new CollectionImmutable([1,2,3]);
        $c2 = $c->without(2);
        $this->assertEquals([1,3],$c2->values()->all());
    }


}
