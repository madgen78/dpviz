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
 * Node instruction.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Node extends BaseInstruction
{
    /** @var Graph Parent instruction. */
    protected $parent;

    /** @var string Identifier of the node. */
    protected $id;

    /** @var AttributeBag Attributes of the node. */
    protected $attributes;

    /**
     * Creates a new node.
     *
     * @param string $id         Identifier of the node
     * @param array  $attributes Attributes to set on node
     * @param Graph  $parent     Parent instruction
     */
    public function __construct($id, array $attributes = array(), $parent = null)
    {
        $this->parent = $parent;
        $this->id = $id;
        $this->attributes = new AttributeBag($attributes);
    }

    /**
     * Returns identifier of the node.
     *
     * @return string Identifier of the node
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns attributes of the graph.
     *
     * @return AttributeBag
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns the value of an attribute of the node.
     *
     * @param string $name name of the attribute
     * @param mixed $default default value if the attribute does not exist
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }

    /**
     * Sets an attribute of node.
     *
     * @param string $name  Name of the attribute to set
     * @param string $value Value to set
     *
     * @return \Alom\Graphviz\Node
     */
    public function attribute($name, $value)
    {
        $this->attributes->set($name, $value);

        return $this;
    }

    /**
     * Fluid interface method.
     *
     * @return Graph
     */
    public function end()
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function render($indent = 0, $spaces = self::DEFAULT_INDENT)
    {
        $attributes = $this->attributes->render($indent + 1, $spaces);

        return str_repeat($spaces, $indent) . $this->escape($this->id) . ($attributes ? ' ' . $attributes : '') . ";\n";
    }
}
