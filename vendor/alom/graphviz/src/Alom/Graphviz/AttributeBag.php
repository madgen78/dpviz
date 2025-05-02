<?php
/*
 * This file is part of Alom Graphviz.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alom\Graphviz;

/**
 * Attribute holder for nodes and edges.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class AttributeBag extends BaseInstruction
{
    /** @var array Associative array of attributes. The key is the name. */
    protected $attributes;

    /**
     * Creates a new attribute bag.
     *
     * @param array $attributes An associative array of attributes values
     */
    public function __construct(array $attributes = array())
    {
        if (isset($attributes['_escaped'])) {
            $escaped = $attributes['_escaped'];
            unset($attributes['_escaped']);
        } else {
            $escaped = true;
        }

        if (!$escaped && isset($attributes['label'])) {
            $attributes['label'] = new RawText($attributes['label']);
        }

        $this->attributes = $attributes;
    }

    /**
     * Changes the value of an attribute.
     *
     * @param string $name  The name for the attribute
     * @param string $value Value to set
     *
     * @return AttributeBag Fluid interface
     */
    public function set($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Returns the value of an attribute.
     *
     * @param string $name    The name for the attribute
     * @param string $default Default value if attribute is not set.
     *
     * @return string|mixed The attribute value
     */
    public function get($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * Tests if the bag has an attribute.
     *
     * @param string $name The attribute name to check
     *
     * @return boolean Result of the test
     */
    public function has($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @inheritdoc
     */
    public function render($indent = 0, $spaces = self::DEFAULT_INDENT)
    {
        if (0 == count($this->attributes)) {
            return '';
        }

        $exp = array();
        foreach ($this->attributes as $name => $value) {
            $exp[] = $this->renderInlineAssignment($name, $value);
        }

        return '[' . implode(', ', $exp) . ']';
    }
}
