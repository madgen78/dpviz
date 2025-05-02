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

class AssignTest extends \PHPUnit_Framework_TestCase
{
    public function testValue()
    {

        $assign = new Assign('foo');
        $this->assertEquals(null, $assign->getValue(), "Default value");

        $return = $assign->setValue('bar');
        $this->assertEquals('bar', $assign->getValue(), "Value getter is correct");
        $this->assertSame($return, $assign);
    }

    public function testName()
    {
        $assign = new Assign('foo');
        $this->assertEquals('foo', $assign->getName(), "name getter");
    }

    public function testRender()
    {
        $assign = new Assign('foo', '');
        $this->assertEquals("foo=\"\";\n", $assign->render(), "Empty string");

        $assign = new Assign('foo', '#bar');
        $this->assertEquals("foo=\"#bar\";\n", $assign->render(), "Escaping");

        $assign = new Assign('foo', 'a-b');
        $this->assertEquals("foo=\"a-b\";\n", $assign->render(), "Escaping hyphens");

        $assign = new Assign('foo', 'bar');
        $this->assertEquals("foo=bar;\n", $assign->render(), "Render method with simple strings");
        $this->assertEquals("    foo=bar;\n", $assign->render(1), "Render method with indent");
        $this->assertEquals("  foo=bar;\n", $assign->render(1, "  "), "Render method with indent and spaces");
    }
}
