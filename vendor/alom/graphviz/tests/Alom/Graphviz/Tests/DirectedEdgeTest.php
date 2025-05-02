<?php
/*
 * This file is part of Alom Graphviz.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alom\Graphviz\Tests;

use Alom\Graphviz\DirectedEdge;

class DirectedEdgeTest extends \PHPUnit_Framework_TestCase
{
    public function testStructure()
    {
        $mock = $this->getMock('Alom\Graphviz\BaseInstruction');
        $edge = new DirectedEdge(array('A', 'B'), array(), $mock);

        $this->assertCount(2, $edge->getList(), "2 elements in the edge");
        $edge->attribute('foo', 'bar');
        $this->assertEquals('bar', $edge->getAttributes()->get('foo'), "Value of attribute is correct");
        $this->assertSame($mock, $edge->end(), "End returns parent");
    }

    public function testRender()
    {
        $edge = new DirectedEdge(array('A', 'B'));

        $this->assertEquals("A -> B;\n", $edge->render());
    }

    public function testAttributes()
    {
        $edge = new DirectedEdge(array('A', 'B'), array('foo' => 'bar'));

        $this->assertEquals('bar', $edge->getAttribute('foo'));
        $this->assertEquals('foo', $edge->getAttribute('not-existing', 'foo'));

        $edge->attribute('bar', 'baz');

        $this->assertEquals('baz', $edge->getAttribute('bar'));
    }
}
