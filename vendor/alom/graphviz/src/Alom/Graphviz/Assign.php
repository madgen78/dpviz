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
 * Graph attribute assignment.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Assign extends BaseInstruction
{
    /** @var string Name of the attribute */
    protected $name;

    /** @var string Value of the assignment */
    protected $value;

    /**
     * Creates a new assignment
     *
     * @param string $name  Name of the attribute to set
     * @param string $value Value of the attribute
     */
    public function __construct($name, $value = NULL)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Returns the name of assignment.
     *
     * @return string The assignment name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value of assignment.
     *
     * @return string the assignment value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Changes the value of assignment.
     *
     * @param string $value The new value to set
     *
     * @return Assign Fluid interface
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function render($indent = 0, $spaces = self::DEFAULT_INDENT)
    {
        return str_repeat($spaces, $indent) . $this->renderInlineAssignment($this->name, $this->value) . ";\n";
    }
}
