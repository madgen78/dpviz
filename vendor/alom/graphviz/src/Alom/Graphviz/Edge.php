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
 * Base edge class
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
abstract class Edge extends BaseInstruction
{
    /** @var array List of elements */
    protected $list;

    /** @var AttributeBag Attributes of the edge */
    protected $attributes;

    /** @var BaseInstruction Parent instruction */
    protected $parent;

    /**
     * Returns operator for associating elements
     *
     * @return string The operator
     */
    abstract protected function getOperator();

    /**
     * Creates an edge.
     *
     * @param array           $list       List of edges
     * @param array           $attributes Associative array of attributes
     * @param BaseInstruction $parent     Parent instruction
     */
    public function __construct(array $list, array $attributes = array(), BaseInstruction $parent = NULL)
    {
        $this->list = $list;
        $this->attributes = new AttributeBag($attributes);
        $this->parent = $parent;
    }

    /**
     * Returns list of elements composing the edge.
     *
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * Returns the value of an attribute of the edge.
     *
     * @param string $name name of the attribute
     * @param mixed $default default value if the attribute does not exist
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }

    /**
     * Sets an attribute.
     *
     * @param string $name  Name of the attribute to set
     * @param string $value Value of the attribute to set
     *
     * @return Edge Fluid-interface
     */
    public function attribute($name, $value)
    {
        $this->attributes->set($name, $value);

        return $this;
    }

    /**
     * Returns list of attributes.
     *
     * @return AttributeBag
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function render($indent = 0, $spaces = self::DEFAULT_INDENT)
    {
        $edges = array();
        foreach ($this->list as $edge) {
            // Each element of the list should be a node, right?  So why are we
            // calling them edges, for a start?  And why are we sending objects to
            // a function that escapes strings.  Either this is really brain damaged
            // or there is something i fundamentally do not understand here.
            //
            // I'm commenting out the code that was here and replacing it with node->getId()
            // cheeks 8/4/18
            $edges[] = $this->escape($edge->getId());

/*
            if (is_array($edge)) {
                $edges[] = $this->escapePath($edge);
            } else {
                $edges[] = $this->escape($edge);
            }
*/
        }

        $edge = implode($this->getOperator(), $edges);

        $attributes = $this->attributes->render($indent + 1);
        return str_repeat($spaces, $indent) . $edge . ($attributes ? ' ' . $attributes : $attributes) . ";\n";
    }

    /**
     * End function for fluid-interface.
     *
     * @return BaseInstruction|null The parent or null
     */
    public function end()
    {
        return $this->parent;
    }
}
