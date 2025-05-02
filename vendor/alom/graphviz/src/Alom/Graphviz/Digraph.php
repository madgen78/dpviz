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
 * Directed graph
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Digraph extends Graph
{
    /**
     * @inheritdoc
     */
    protected function createEdge($list, array $attributes = array(), BaseInstruction $parent = null)
    {
        return new DirectedEdge($list, $attributes, $parent);
    }

    /**
     * @inheritdoc
     */
    protected function getHeader($id)
    {
        return 'digraph ' . $id;
    }
}
