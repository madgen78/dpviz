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
 * Directed edge
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class DirectedEdge extends Edge
{
    /**
     * @inheritdoc
     */
    protected function getOperator()
    {
        return ' -> ';
    }
}
