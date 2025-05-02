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
 * Subgraph
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Subgraph extends Graph
{
    /**
     * @inheritdoc
     */
    protected function createEdge($list, array $attributes = array(), BaseInstruction $parent = null)
    {
        $currentParent = $parent;
        while ($currentParent !== null) {
            if ($currentParent instanceof Digraph) {
                return new DirectedEdge($list, $attributes, $parent);
            }

            $currentParent = $parent->end();
        }

        throw new \LogicException('Unable to find edge type');
    }

    /**
     * @inheritdoc
     */
    protected function getHeader($id)
    {
        return 'subgraph ' . $id;
    }
}
