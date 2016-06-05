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
 * Iterator for Dictionary collection
 */
class DictionaryIterator implements \Iterator
{
    private $data = [];
    private $keys = [];

    /**
     * DictionaryIterator constructor.
     * @param Dictionary $dictionary
     */
    public function __construct(Dictionary $dictionary)
    {
        $this->data = $dictionary->values();
        $this->keys = $dictionary->keys();
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->data);
        next($this->keys);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return current($this->keys);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return key($this->data) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->data);
        reset($this->keys);
    }
}
