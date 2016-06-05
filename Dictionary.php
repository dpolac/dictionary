<?php
/**
 * This file is part of Dictionary.
 *
 * (c) Damian Polac <damian.polac.111@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DPolac;

/**
 * Collection indexed with objects and scalars
 */
class Dictionary implements \Countable , \ArrayAccess , \Serializable , \IteratorAggregate
{
    private $data = [];
    private $keys = [];

    /**
     * @internal
     *
     * Create hash for each element.
     *
     * @param   $value
     * @return  string
     */
    protected function generateHash($value)
    {
        if (is_object($value)) {
            if ($value instanceof \Closure) {
                throw new \InvalidArgumentException("Closure cannot be Dictionary key.");
            }
            return 'object:' . spl_object_hash($value);
        } elseif (is_string($value)) {
            return 'string:' . $value;
        } elseif (is_int($value)) {
            return 'int:' . $value;
        } elseif (is_real($value)) {
            return 'float:' . $value;
        } elseif (is_bool($value)) {
            return 'bool:' . ((int)$value);
        } elseif (is_null($value)) {
            return 'null:null';
        } else {
            throw new \InvalidArgumentException("Invalid Dictionary key.");
        }
    }

    /**
     * {@inheritDoc}
     * @return DictionaryIterator
     */
    public function getIterator()
    {
        return new DictionaryIterator($this);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        $hash = $this->generateHash($offset);
        return isset($this->data[$hash]);
    }

    /**
     * {@inheritdoc}
     */
    public function &offsetGet($offset)
    {
        $hash = $this->generateHash($offset);
        return $this->data[$hash];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $hash = $this->generateHash($offset);
        if (!isset($this->keys[$hash])) {
            $this->keys[$hash] = $offset;
        }
        $this->data[$hash] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $hash = $this->generateHash($offset);
        unset($this->data[$hash]);
        unset($this->keys[$hash]);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $pairs = [];
        foreach ($this as $key => $value) {
            $pairs[] = [$key, $value];
        }
        return \serialize($pairs);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $pairs = \unserialize($serialized);
        foreach ($pairs as $pair) {
            $this[$pair[0]] = $pair[1];
        }
    }

    /**
     * Create new Dictionary from array of key-value pairs.
     *
     * @param   $array      array   Array of pairs. Each pair must be 2-element array.
     * @return  Dictionary
     */
    public static function fromPairs($array)
    {
        if (!is_array($array) && !($array instanceof \Traversable)) {
            throw new \InvalidArgumentException(sprintf(
                'Dictionary::fromPairs() argument must be array or Traversable, '
                . 'but is "%s".', gettype($array)
            ));
        }
        
        $result = new Dictionary();
        foreach ($array as $pair) {
            if (!is_array($pair) || !isset($pair[0]) || !isset($pair[1])) {
                throw new \InvalidArgumentException(
                    'Each element of array or Traversable passed to Dictionary::FromPairs()'
                    . ' must be two-elements array.'
                );
            }
            
            $result[$pair[0]] = $pair[1];
        }
        return $result;
    }

    /**
     * Create new Dictionary from standard PHP array.
     *
     * @param  $array        array   Array to create Dictionary from.
     * @return Dictionary
     */
    public static function fromArray($array)
    {
        if (!is_array($array) && !($array instanceof \Traversable)) {
            throw new \InvalidArgumentException(sprintf(
                'Dictionary::fromArray() argument must be array or Traversable, '
                . 'but is "%s".', gettype($array)
            ));
        }

        $result = new Dictionary();
        foreach ($array as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * Get array of all key-value pairs in dictionary.
     *
     * @return  array   Array of pairs. Each pair is 2-element array.
     */
    public function toPairs()
    {
        $result = [];
        foreach ($this as $key => $value) {
            $result[] = [$key, $value];
        }
        return $result;
    }

    /**
     * Return array with all keys stored in dictionary.
     *
     * @return  array   Dictionary's keys.
     */
    public function keys()
    {
        return array_values($this->keys);
    }

    /**
     * Return array with all values stored in dictionary.
     *
     * @return  array   Dictionary's values.
     */
    public function values()
    {
        return array_values($this->data);
    }

    /**
     * Sort Dictionary by values returned by callback.
     *
     * @param   string|callable $callback   Callback returning value for each Dictionary's element.
     *                                      If argument is a string "values" or "keys", it will be sorted
     *                                      by Dictionary's values or keys.
     * @param   string          $direction  Sorting direction. "ASC" or "DESC".
     * @return  $this
     */
    public function sortBy($callback = null, $direction = 'ASC')
    {
        if (!is_callable($callback)) {
            if (is_null($callback) or is_string($callback) && 'values' === strtolower($callback)) {
                $callback = function ($value, $key) { return $value; };
            } else if (is_string($callback) && 'keys' === strtolower($callback)) {
                $callback = function ($value, $key) { return $key; };
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'Dictionary::sort() argument must be "keys", "values" or callable, '
                    . 'but is "%s".', gettype($callback)
                ));
            }
        }

        if ($direction !== SORT_ASC && $direction !== SORT_DESC) {
            if (is_string($direction)) {
                switch (strtolower($direction)) {
                    case 'asc':
                        $direction = SORT_ASC;
                        break;
                    case 'desc':
                        $direction = SORT_DESC;
                        break;
                    default:
                        throw new \InvalidArgumentException(sprintf(
                            'Direction must be "asc" or "desc", but is "%s".', $direction));
                }
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'Direction must be string "asc" or "desc", but is "%s".', gettype($direction)));
            }
        }

        $order = [];
        foreach ($this as $key => $value) {
            $order[] = $callback($value, $key);
        }

        array_multisort($order, $direction, SORT_REGULAR, $this->data, $this->keys);
        
        return $this; //to allow chaining
    }

    /**
     * Return copy of Dictionary.
     * Created for chaining with sortBy().
     *
     * @return  Dictionary  Exact copy of Dictionary.
     */
    public function getCopy()
    {
        return clone $this;
    }
}
