<?php
/*
 * This file is part of Alom Graphviz.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alom\Graphviz\Tests;

use Alom\Graphviz\Assign;
use Alom\Graphviz\AttributeSet;
use Alom\Graphviz\Digraph;
use Alom\Graphviz\DirectedEdge;
use Alom\Graphviz\Node;
use Alom\Graphviz\Subgraph;

class DigraphTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $graph = new Digraph('G');
        $graph->subgraph('foo')
            ->node('bar', array('label' => 'baz'))
        ;

        $this->assertEquals('baz', $graph->get('foo')->get('bar')->getAttribute('label'));
    }

    public function testGet_NotExisting()
    {
        $graph = new Digraph('G');
        $graph->node('foo');

        try {
            $graph->get('bar');
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            // ok
        }
    }

    public function testGetEdge()
    {
        $graph = new Digraph('G');
        $graph->edge(array('A', 'B'));
        $graph->edge(array('B', array('C', '1')));

        $edge = $graph->getEdge(array('A', 'B'));
        $this->assertEquals(array('A', 'B'), $edge->getList());

        $edge = $graph->getEdge(array('B', array('C', '1')));
        $this->assertEquals(array('B', array('C', 1)), $edge->getList());
    }

    public function testGetEdge_notExisting()
    {
        $graph = new Digraph('G');
        $graph->edge(array('A', 'B'));
        $graph->edge(array('B', array('C', '1')));

        try {
            $edge = $graph->getEdge(array('A', 'C'));
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Found no edge "A -> C".', $e->getMessage());
        }

        try {
            $edge = $graph->getEdge(array('A', array('C', '2')));
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Found no edge "A -> C:2".', $e->getMessage());
        }
    }

    public function testRawText()
    {
        $graph = new Digraph('G');
        $node = $graph->beginNode('foo', array(
            'label' => '<bar<BR>baz>',
            '_escaped' => false
        ));

        $this->assertInstanceOf('Alom\Graphviz\RawText', $node->getAttributes()->get('label'));
        $this->assertEquals("digraph G {\n    foo [label=<bar<BR>baz>];\n}\n", $graph->render());
    }

    public function testRender()
    {
        $graph = new Digraph('G');

        $this->assertEquals("digraph G {\n}\n", $graph->render(), "Render empty graph");
        $this->assertEquals("    digraph G {\n    }\n", $graph->render(1), "Render empty graph with indent");
        $this->assertEquals("  digraph G {\n  }\n", $graph->render(1, "  "), "Render empty graph with indent and spaces");

        $mock = $this->getMock('Alom\Graphviz\InstructionInterface', array('render'));

        $mock
            ->expects($this->once())
            ->method('render')
            ->with(2, "  ")
            ->will($this->returnValue("    foobarbaz;\n"))
        ;

        $graph->append($mock);

        $this->assertEquals("  digraph G {\n    foobarbaz;\n  }\n", $graph->render(1, "  "), "Render with statements");
    }

    public function testFluidInterfaceShort()
    {
        $graph = new Digraph('G');

        $graph
            ->set('rankdir', 'LR')
            ->node('A')
            ->node('B')
            ->edge(array('A', 'B'))
        ;

        $this->assertCount(4, $instructions = $graph->getInstructions(), "3 instructions");

        $this->assertTrue($instructions[0] instanceof Assign, "First instruction is assignment");
        $this->assertEquals('rankdir', $instructions[0]->getName(), "First instruction name");
        $this->assertEquals('LR', $instructions[0]->getValue(), "First instruction value");

        $this->assertTrue($instructions[1] instanceof Node, "Second instruction is a node");
        $this->assertEquals("A", $instructions[1]->getId(), "Id of first node");

        $this->assertTrue($instructions[2] instanceof Node, "Third instruction is a node");
        $this->assertEquals("B", $instructions[2]->getId(), "Id of second node");

        $this->assertTrue($instructions[3] instanceof DirectedEdge, "Fourth instruction is an edge");
    }

    public function testFluidInterfaceVerbose()
    {
        $graph = new Digraph('G');

        $graph
            ->beginNode('A')
                ->attribute('color', 'red')
            ->end()
            ->beginEdge(array('A', 'B'))
                ->attribute('color', 'blue')
            ->end()
        ;

        $this->assertCount(2, $instructions = $graph->getInstructions(), "2 instructions");

        $this->assertTrue($instructions[0] instanceof Node, "First instructions is a node");
        $this->assertEquals('A', $instructions[0]->getId(), "Node identifier");
        $this->assertEquals('red', $instructions[0]->getAttributes()->get('color'), "Node attribute");

        $this->assertTrue($instructions[1] instanceof DirectedEdge, "Second instructions is a node");
        $this->assertEquals('blue', $instructions[1]->getAttributes()->get('color'), "Edge attribute");
    }

    public function testAttr()
    {
        $graph = new Digraph('G');
        $graph->attr('node', array('color' => 'blue'));

        $this->assertCount(1, $instructions = $graph->getInstructions(), "Instruction count");
        $this->assertTrue($instructions[0] instanceof AttributeSet, "Instruction is an attribute set");
        $this->assertEquals("node", $instructions[0]->getName(), "Name is correct");
        $this->assertEquals("blue", $instructions[0]->getAttributes()->get('color'), "Attribute is correct");
    }

    /**
     * @expectedException InvalidArgumentException
     * @dataProvider provideIncorrectSetUsage
     */
    public function testIncorrectSetUsage($name)
    {
        $graph = new Digraph('G');
        $graph->set($name, 'foo');
    }

    public function provideIncorrectSetUsage()
    {
        return array(
            array('graph'),
            array('edge'),
            array('node'),
        );
    }

    public function testSubGraph()
    {
        $graph = new Digraph('G');
        $subgraph = $graph->subgraph('foo');
        $subgraph->edge(array('A', 'B'));

        $this->assertCount(1, $graph->getInstructions(), "Count of instructions");
        $this->assertTrue($subgraph instanceof Subgraph, "Subgraph return");
        $this->assertSame('foo', $subgraph->getId(), "Subgraph identifier");
        $this->assertSame($graph, $subgraph->end(), "Subgraph end");

        $this->assertEquals("subgraph foo {\n    A -> B;\n}\n", $subgraph->render(), "Subgraph rendering");
    }
}
