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
 * Base class for Graphviz instructions.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
abstract class BaseInstruction implements InstructionInterface
{
    /**
     * Renders an inline assignment (without indent or end return line).
     *
     * It will handle escaping, according to the value.
     *
     * @param string $name  A name
     * @param string $value A value
     *
     * @return string
     */
    protected function renderInlineAssignment($name, $value)
    {
        if ($value instanceof RawText) {
            $value = $value->getText();
        } else {
            $value = $this->escape($value);
        }

        return $this->escape($name).'='.$value;
    }

    /**
     * Escapes a value if needed.
     *
     * @param string $value The value to set
     *
     * @return string The escaped string
     */
    protected function escape($value)
    {
// dpplog(5, "in escape: value: $value");
// dpplog(5, "in escape: print_r(value): " . print_r($value,true));
        $x = ($this->needsEscaping($value)) ? '"' . str_replace('"', '""', str_replace('\\', '\\\\', $value)) . '"' : $value;
// dpplog(5, "in escape: value2: $x");
        return ($this->needsEscaping($value)) ? '"' . str_replace('"', '""', str_replace('\\', '\\\\', $value)) . '"' : $value;
    }

    protected function escapePath(array $path)
    {
// dpplog(9, "in escapePath: value: zzzzz");
        $list = array();
        foreach ($path as $element) {
            $list[] = $this->escape($element);
        }

        return implode(':', $list);
    }

    /**
     * Tests if a string needs escaping.
     *
     * @param string $value
     *
     * @return boolean Result of test
     */
    protected function needsEscaping($value)
    {
        return preg_match('/[{} "#-:\\\\\\/\\.,]/', $value) || in_array($value, array('graph', 'node', 'edge')) || empty($value);
    }
}
