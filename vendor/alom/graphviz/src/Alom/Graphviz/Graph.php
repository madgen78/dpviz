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
 * Base graph instruction.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
abstract class Graph extends BaseInstruction
{
    /** @var BaseInstruction Parent node */
    protected $parent;

    /** @var string Graph identifier */
    protected $id;

    /** @var string Name of the graph */
    protected $name;

    /** @var BaseInstruction[] Instructions list */
    protected $instructions = array();

    /**
     * Creates a new edge for the graph.
     *
     * @param array           $list       List of elements of the edge
     * @param array           $attributes Associative array of attributes
     * @param BaseInstruction $parent     Parent element
     *
     * @return Edge The created edge
     */
    abstract protected function createEdge($list, array $attributes = array(), BaseInstruction $parent = null);

    /**
     * Returns the graph header (digraph G as example).
     *
     * @param string $id Identifier of graph
     *
     * @return string The graph header
     */
    abstract protected function getHeader($id);

    /**
     * Creates a new graph.
     *
     * @param string          $id     Identifier of the graph
     * @param BaseInstruction $parent Parent element
     */
    public function __construct($id, $parent = null)
    {
        $this->parent = $parent;
        $this->id = $id;
    }

    /**
     * Returns identifier of graph.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Adds a new instruction to graph.
     *
     * @param InstructionInterface $instruction Instruction to add
     *
     * @return Graph Fluid-interface
     */
    public function append(InstructionInterface $instruction)
    {
        $this->instructions[] = $instruction;

        return $this;
    }

    /**
     * Returns list of instructions.
     *
     * @return array
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * Returns a node or a subgraph, given his id.
     *
     * @param string $id the identifier of the node/graph to fetch
     *
     * @return Node|Graph
     *
     * @throws InvalidArgumentException node or graph not found
     */
    public function get($id)
    {
        foreach ($this->instructions as $instruction) {
            if (!$instruction instanceof Node && !$instruction instanceof Subgraph) {
                continue;
            }

            if ($instruction->getId() == $id) {
                return $instruction;
            }
        }

        throw new \InvalidArgumentException(sprintf('Found no node or graph with id "%s" in "%s".', $id, $this->id));
    }

    /**
     * Tests if the graph has an edge.
     *
     * @param (string|string[])[] a path
     *
     * @return boolean
     */
    public function hasEdge(array $edge)
    {
        try {
            $this->getEdge($edge);

            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Returns an edge by its path.
     *
     * @param (string|string[])[] a path
     *
     * @return Edge
     *
     * @throws InvalidArgumentException path not found
     */
    public function getEdge(array $edge)
    {
        foreach ($this->instructions as $instruction) {
            if (!$instruction instanceof Edge) {
                continue;
            }

            if ($instruction->getList() == $edge) {
                return $instruction;
            }
        }

        $label = implode(' -> ', array_map(function ($edge) {
            if (is_string($edge)) {
                return $edge;
            }

            /* added this because the latest FreePBX with PHP 5.6.40 was throwing
               an exception if edge was not an array.  When I print_r($edge)
               in that case, it's an Edge object.   cheeks@swcp.com 9/18/19 */
	        if (is_array($edge)) {
                return implode(':', $edge);
            }
        }, $edge));

        throw new \InvalidArgumentException(sprintf('Found no edge "%s".', $label));
    }

    /**
     * Adds an assignment instruction.
     *
     * @param string $name  Name of the value to assign
     * @param string $value Value to assign
     *
     * @throws \InvalidArgumentException
     * @return Graph Fluid-interface
     */
    public function set($name, $value)
    {
        if (in_array($name, array('graph', 'node', 'edge'))) {
            throw new \InvalidArgumentException(sprintf('Use method attr for setting %s', $name));
        }

        $this->instructions[] = new Assign($name, $value);

        return $this;
    }

    /**
     * Define attributes for node/edge/graph.
     *
     * @param string $name       Name of type
     * @param array  $attributes Attributes of the type
     *
     * @return \Alom\Graphviz\Graph
     */
    public function attr($name, array $attributes)
    {
        $this->instructions[] = new AttributeSet($name, $attributes);

        return $this;
    }

    /**
     * Starts a subgraph.
     *
     * @param string $id Identifier of subgraph
     *
     * @return Subgraph
     */
    public function subgraph($id)
    {
        return $this->instructions[] = new Subgraph($id, $this);
    }

    /**
     * Created a new node on graph.
     *
     * @param string $id         Identifier of node
     * @param array  $attributes Attributes to set on node
     *
     * @return Graph Fluid-interface
     */
    public function node($id, array $attributes = array())
    {
        $this->instructions[] = new Node($id, $attributes, $this);

        return $this;
    }

    /**
     * Created a new node on graph.
     *
     * @param string $id         Identifier of node
     * @param array  $attributes Attributes to set on node
     *
     * @return Node
     */
    public function beginNode($id, array $attributes = array())
    {
        return $this->instructions[] = new Node($id, $attributes, $this);
    }

    /**
     * Created a new edge on graph.
     *
     * @param array $list       List of edges
     * @param array $attributes Attributes to set on edge
     *
     * @return Graph Fluid-interface
     */
    public function edge($list, array $attributes = array())
    {
        $this->instructions[] = $this->createEdge($list, $attributes, $this);

        return $this;
    }

    /**
     * Created a new edge on graph.
     *
     * @param array $list       List of edges
     * @param array $attributes Attributes to set on edge
     *
     * @return Edge
     */
    public function beginEdge($list, array $attributes = array())
    {
        return $this->instructions[] = $this->createEdge($list, $attributes, $this);
    }

    /**
     * Fluid-interface to access parent.
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
        $margin = str_repeat($spaces, $indent);
        $result = $margin . $this->getHeader($this->id) . ' {' . "\n";
        foreach ($this->instructions as $instruction) {
            $result .= $instruction->render($indent + 1, $spaces);
        }
        $result .= $margin . '}' . "\n";

        return $result;
    }
}
