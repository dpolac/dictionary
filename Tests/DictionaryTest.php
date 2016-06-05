<?php
/**
 * This file is part of Dictionary.
 *
 * (c) Damian Polac <damian.polac.111@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DPolac\Tests;
use DPolac\Dictionary;

/**
 * Class DictionaryTests
 * @package DPolac\Tests
 */
class DictionaryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider getOffsets
     */
    public function testArrayAccess($offset)
    {
        $d = new Dictionary();
        $this->assertFalse($d->offsetExists($offset));

        $d->offsetSet($offset, 34);
        $this->assertTrue($d->offsetExists($offset));
        $this->assertEquals(34, $d->offsetGet($offset));

        $d->offsetUnset($offset);
        $this->assertFalse($d->offsetExists($offset));
    }

    public function getOffsets()
    {
        return [
            'int' => [12],
            'object' => [new \stdClass()],
            'string' => ['foobar'],
            'bool' => [true],
            'null' => [null],
            'float' => [1.4],
        ];
    }

    /**
     * Check if Dictionary recognize difference between
     * key types.
     */
    public function testKeyTypeAwareness()
    {
        $keys = [
            1,
            0,
            true,
            false,
            '1',
            '0',
            1.0,
            0.0,
            null,
        ];

        $d = new Dictionary();
        foreach ($keys as $value => $key) {
            $d[$key] = $value;
        }

        foreach ($keys as $value => $key) {
            $this->assertEquals($value, $d[$key]);
        }
    }

    public function testCount()
    {
        $d = new Dictionary();

        $this->assertEquals(0, $d->count());

        $d[1] = 12;
        $d[true] = new \stdClass();
        $d[false] = new \stdClass();
        $d['false'] = 'abc';
        $d[(string) '1'] = 'def';
        $d[null] = 13;
        $d[new \stdClass()] = 1;

        $this->assertEquals(7, $d->count());
    }

    /**
     * @dataProvider getInvalidOffsets
     * @expectedException \InvalidArgumentException
     */
    public function testOffsetSet_InvalidOffset_ThrowException($offset)
    {
        $d = new Dictionary();
        $d[$offset] = 1;
    }

    /**
     * @dataProvider getInvalidOffsets
     * @expectedException \InvalidArgumentException
     */
    public function testOffsetGet_InvalidOffset_ThrowException($offset)
    {
        $d = new Dictionary();
        $a = $d[$offset];
    }

    /**
     * @dataProvider getInvalidOffsets
     * @expectedException \InvalidArgumentException
     */
    public function testOffsetExists_InvalidOffset_ThrowException($offset)
    {
        $d = new Dictionary();
        $a = isset($d[$offset]);
    }

    /**
     * @dataProvider getInvalidOffsets
     * @expectedException \InvalidArgumentException
     */
    public function testOffsetUnset_InvalidOffset_ThrowException($offset)
    {
        $d = new Dictionary();
        unset($d[$offset]);
    }

    public function getInvalidOffsets()
    {
        return [
            'array' => [[1, 2, 3]],
            'Closure' => [function() {}],
        ];
    }

    public function testFromPairs_CreateFromArray()
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();
        $object3 = new \stdClass();
        $pairs = [
            [$object1, 1],
            [$object2, 2],
            [$object3, 3],
        ];
        $d = Dictionary::fromPairs($pairs);

        $this->assertSame(1, $d[$object1]);
        $this->assertSame(2, $d[$object2]);
        $this->assertSame(3, $d[$object3]);
        $this->assertEquals(3, count($d));
    }

    public function testFromPairs_CreateFromTraversable()
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();
        $pairs = new \ArrayIterator([
            [$object1, 1],
            [$object2, 2],
        ]);
        $d = Dictionary::fromPairs($pairs);

        $this->assertSame(1, $d[$object1]);
        $this->assertSame(2, $d[$object2]);
        $this->assertEquals(2, count($d));
    }

    /**
     * @dataProvider getInvalidPairs
     * @expectedException \InvalidArgumentException
     */
    public function testFromPairs_CreateFromInvalidPairs_ThrowException($pairs)
    {
        Dictionary::fromPairs($pairs);
    }

    public function getInvalidPairs()
    {
        return [
            'array with incomplete pair' => [
                [
                    [1, 2],
                    [3],
                    ['foo', 'bar'],
                ]
            ],
            'Traversable with incomplete pair' => [
                new \ArrayIterator([
                    [1, 'a'],
                    [],
                ])
            ],
            'array with invalid element' => [
                [
                    [1, 2],
                    'foo',
                    1,
                    new \stdClass(),
                ]
            ],
            'integer' => [ 12 ],
            'string' => ['abc'],
            'object' => [new \stdClass()],
        ];
    }

    public function testFromArray_CreateFromArray()
    {
        $array = [
            'ab' => new \stdClass(),
            12 => 'cd',
            0 => 'ef',
            'gh' => 10.2,
        ];
        $d = Dictionary::fromArray($array);

        $this->assertSame($array['ab'], $d['ab']);
        $this->assertSame($array[12], $d[12]);
        $this->assertSame($array[0], $d[0]);
        $this->assertSame($array['gh'], $d['gh']);
        $this->assertEquals(4, $d->count());
    }

    public function testFromArray_CreateFromTraversable()
    {
        $array = [
            'ab' => new \stdClass(),
            12 => 'cd',
            0 => 'ef',
            'gh' => 10.2,
        ];
        $d = Dictionary::fromArray(new \ArrayIterator($array));

        $this->assertSame($array['ab'], $d['ab']);
        $this->assertSame($array[12], $d[12]);
        $this->assertSame($array[0], $d[0]);
        $this->assertSame($array['gh'], $d['gh']);
        $this->assertEquals(4, $d->count());
    }

    /**
     * @dataProvider getInvalidArrays
     * @expectedException \InvalidArgumentException
     */
    public function testFromArray_InvalidArray_ThrowException($array)
    {
        Dictionary::fromArray($array);
    }

    public function getInvalidArrays()
    {
        return [
            'int'       => [12],
            'float'     => [1.2],
            'string'    => ['abcdef'],
            'object'    => [new \stdClass()],
            'null'      => [null],
            'false'     => [false],
            'true'      => [true],
        ];
    }

    public function testSortBy_ValuesAsc()
    {
        $d = Dictionary::fromPairs([
            ['a', 1],
            ['b', 3],
            ['c', 2],
            ['d', 0],
        ]);

        $sorted = $d->getCopy()->sortBy();
        $sorted2 = $d->getCopy()->sortBy('values');
        $sorted3 = $d->getCopy()->sortBy('values', 'asc');
        $sorted4 = $d->getCopy()->sortBy('values', SORT_ASC);

        $this->assertEquals($sorted, $sorted2);
        $this->assertEquals($sorted, $sorted3);
        $this->assertEquals($sorted, $sorted4);

        $this->assertEquals([0, 1, 2, 3], $sorted->values());
        $this->assertEquals(['d', 'a', 'c', 'b'], $sorted->keys());
    }

    public function testSortBy_ValuesDesc()
    {
        $d = Dictionary::fromPairs([
            ['a', 1],
            ['b', 3],
            ['c', 2],
            ['d', 0],
        ]);

        $sorted = $d->getCopy()->sortBy('values', 'desc');
        $sorted2 = $d->getCopy()->sortBy('values', SORT_DESC);

        $this->assertEquals($sorted, $sorted2);

        $this->assertEquals([3, 2, 1, 0], $sorted->values());
        $this->assertEquals(['b', 'c', 'a', 'd'], $sorted->keys());
    }

    public function testSortBy_KeysAsc()
    {
        $d = Dictionary::fromPairs([
            ['d', 0],
            ['b', 1],
            ['a', 2],
            ['c', 3],
        ]);

        $sorted = $d->getCopy()->sortBy('keys', 'asc');
        $sorted2 = $d->getCopy()->sortBy('keys', SORT_ASC);

        $this->assertEquals($sorted, $sorted2);

        $this->assertEquals([2, 1, 3, 0], $sorted->values());
        $this->assertEquals(['a', 'b', 'c', 'd'], $sorted->keys());
    }

    public function testSortBy_KeysDesc()
    {
        $d = Dictionary::fromPairs([
            ['d', 0],
            ['b', 1],
            ['a', 2],
            ['c', 3],
        ]);

        $sorted = $d->getCopy()->sortBy('keys', 'desc');
        $sorted2 = $d->getCopy()->sortBy('keys', SORT_DESC);

        $this->assertEquals($sorted, $sorted2);

        $this->assertEquals([0, 3, 1, 2], $sorted->values());
        $this->assertEquals(['d', 'c', 'b', 'a'], $sorted->keys());
    }

    public function testSortBy_Callable()
    {
        $callback = function ($value, $key) {
            return strtoupper($value . $key);
        };

        $d = Dictionary::fromPairs([
            ['b', 1],
            ['c', 2],
            ['a', 2],
            ['d', 1],
        ]);

        $sorted = $d->getCopy()->sortBy($callback);

        $this->assertEquals([1, 1, 2, 2], $sorted->values());
        $this->assertEquals(['b', 'd', 'a', 'c'], $sorted->keys());
    }

    public function testSortBy_Called_OriginalDictionaryModified()
    {
        $d = Dictionary::fromArray([ 1, 3, 4, 0 ]);

        $originalValues = $d->values();
        $d->sortBy('values', 'asc');

        $this->assertNotEquals($originalValues, $d->values());
    }

    /**
     * @dataProvider getInvalidSortByCallbacks
     * @expectedException \InvalidArgumentException
     */
    public function testSortBy_InvalidCallback_ThrowException($callback)
    {
        (new Dictionary())->sortBy($callback);
    }

    public function getInvalidSortByCallbacks()
    {
        return [
            'object'    => [new \stdClass()],
            'int'       => [12],
            'array'     => [[1, 2, 3]],
            'string'    => ['lfothdsusalfd'],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider getInvalidSortByDirections
     */
    public function testSortBy_InvalidDirection_ThrowException($direction)
    {
        (new Dictionary())->sortBy('keys', $direction);
    }

    public function getInvalidSortByDirections()
    {
        return [
            'int' => [12],
            'object' => [new \stdClass()],
            'invalid string' => ['asd'],
            'array' => [1, 2, 3],
            'null' => [null],
        ];
    }

    public function testGetCopy()
    {
        $d = new Dictionary();
        $d['a'] = 12;
        $d[new \stdClass()] = 34;
        $d[12] = new \stdClass();

        $clone = $d->getCopy();

        $this->assertEquals($d, $clone);
        $this->assertNotSame($d, $clone);
        $this->assertSame($d[12], $clone[12]);
    }

    public function testSerialize()
    {
        $obj1 = new \stdClass();
        $obj2 = new \stdClass();
        $obj3 = new \stdClass();

        $d = Dictionary::fromPairs([
            [$obj1, 12],
            ['foo', 'bar'],
            [$obj2, 'tet'],
            [12, $obj3],
            [15, $obj3],
        ]);

        $serialized = 'C:17:"DPolac\Dictionary":203:{a:5:{i:0;a:2:{i:0;O:8:"stdClass":0:{}i:1;i:12;}i:1;a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}i:2;a:2:{i:0;O:8:"stdClass":0:{}i:1;s:3:"tet";}i:3;a:2:{i:0;i:12;i:1;O:8:"stdClass":0:{}}i:4;a:2:{i:0;i:15;i:1;r:14;}}}';
        $this->assertEquals($serialized, \serialize($d));
    }

    public function testUnserialize()
    {
        $serialized = 'C:17:"DPolac\Dictionary":203:{a:5:{i:0;a:2:{i:0;O:8:"stdClass":0:{}i:1;i:12;}i:1;a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}i:2;a:2:{i:0;O:8:"stdClass":0:{}i:1;s:3:"tet";}i:3;a:2:{i:0;i:12;i:1;O:8:"stdClass":0:{}}i:4;a:2:{i:0;i:15;i:1;r:14;}}}';
        /* @var $d Dictionary */
        $d = \unserialize($serialized);

        $this->assertEquals('bar', $d['foo']);
        $this->assertSame($d[12], $d[15]);
        $this->assertEquals(5, count($d));

        $it = $d->getIterator();
        
        $it->rewind();
        $obj1 = $it->key();
        $this->assertEquals(12, $it->current());
        $it->next();
        $it->next();
        $obj2 = $it->key();
        $this->assertEquals('tet', $it->current());
        $this->assertNotSame($obj1, $obj2);
    }

    public function testToPairs()
    {
        $obj1 = new \stdClass();
        $obj2 = new \stdClass();
        $pairs = [
            [$obj1, 'foo'],
            [$obj2, 'bar'],
            [12, $obj1],
        ];
        $dict = Dictionary::fromPairs($pairs);

        $this->assertEquals($pairs, $dict->toPairs());
    }

}
