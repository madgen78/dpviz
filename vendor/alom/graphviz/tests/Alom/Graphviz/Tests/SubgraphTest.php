<?php
/*
 * This file is part of Alom Graphviz.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alom\Graphviz\Tests;

use Alom\Graphviz\Subgraph;

class SubgraphTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException LogicException
     */
    public function testUnknownParent()
    {
        $subgraph = new Subgraph('S');
        $subgraph->edge(array('A', 'B', 'C'));

        $subgraph->render();

        $this->markTestIncomplete('This test has not been fully implemented yet.');
    }
}
