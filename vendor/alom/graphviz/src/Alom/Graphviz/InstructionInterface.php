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
 * Interface of Graphviz instructions.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
interface InstructionInterface
{
    const DEFAULT_INDENT = '    ';

    /**
     * Renders the assign statement.
     *
     * @param int    $indent Current level of indentation
     * @param string $spaces
     *
     * @return string The rendered line
     */
    function render($indent = 0, $spaces = self::DEFAULT_INDENT);
}
