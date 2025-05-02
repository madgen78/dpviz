<?php
/*
 * This file is part of Alom Graphviz.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alom\Graphviz\Tests;

use Alom\Graphviz\AttributeSet;

class AttributeSetTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $set = new AttributeSet('node', array('foo' => 'bar'));

        $this->assertEquals('node', $set->getName(), "getName");
        $this->assertEquals('bar', $set->getAttributes()->get('foo'), "Attributes");
    }

    public function testRender()
    {
        $set = new AttributeSet('node', array('foo' => 'bar'));

        $this->assertEquals("node [foo=bar];\n", $set->render(), "Simple render");
        $this->assertEquals("    node [foo=bar];\n", $set->render(1), "Render with indent");
        $this->assertEquals("  node [foo=bar];\n", $set->render(1, "  "), "Render with indent and spaces");
    }

    /**
     * @dataProvider provideIncorrectElement
     */
    public function testElement($element, $isCorrect)
    {
        if (!$isCorrect) {
            $this->setExpectedException('InvalidArgumentException');
        }
        $set = new AttributeSet($element);
    }

    public function provideIncorrectElement()
    {
        return array(
            array('node', true),
            array('edge', true),
            array('graph', true),
            array('foo', false)
        );
    }
}
