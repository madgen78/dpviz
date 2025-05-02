<?php
/*
 * This file is part of Alom Graphviz.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alom\Graphviz\Tests;

use Alom\Graphviz\AttributeBag;

class AttributeBagTest extends \PHPUnit_Framework_TestCase
{
    public function testGetterSetterHasser()
    {
        $bag = new AttributeBag(array('foo' => 'bar', 'bar' => 'baz'));

        $this->assertEquals('bar', $bag->get('foo'), "Get existing");
        $this->assertTrue($bag->has('foo'), "has() with existing");

        $this->assertFalse($bag->has('baz'), "has() with inexisting");
        $this->assertNull($bag->get('baz'), "Inexisting");
        $this->assertFalse($bag->get('baz', false), "Default value");

        $bag->set('name', 'alice');
        $this->assertEquals('alice', $bag->get('name'));
    }

    public function testRender()
    {
        $bag = new AttributeBag();
        $this->assertEquals('', $bag->render(), "Empty attribute bag");

        $bag->set('foo', 'bar');
        $this->assertEquals('[foo=bar]', $bag->render(), "Render with one simple string");

        $bag->set('bar', 'foo bar');
        $this->assertEquals('[foo=bar, bar="foo bar"]', $bag->render(), "Render with multiple attributes");
    }
}
