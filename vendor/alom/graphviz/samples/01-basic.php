<?php
/*
 * This file is part of Alom Graphviz.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/../vendor/autoload.php';

$graph = new Alom\Graphviz\Digraph('G');
$graph
    ->subgraph('cluster_0')
        ->set('style', 'filled')
        ->set('color', 'lightgrey')
        ->attr('node', array('style' => 'filled', 'color' => 'white'))
        ->edge(array('a0', 'a1', 'a2', 'a3'))
        ->set('label', 'process #1')
    ->end()
    ->subgraph('cluster_1')
        ->attr('node', array('style' => 'filled'))
        ->edge(array('b0', 'b1', 'b2', 'b3'))
        ->set('label', 'process #2')
        ->set('color', 'blue')
    ->end()
    ->edge(array('start', 'a0'))
    ->edge(array('start', 'b0'))
    ->edge(array('a1', 'b3'))
    ->edge(array('b2', 'a3'))
    ->edge(array('a3', 'a0'))
    ->edge(array('a3', 'end'))
    ->edge(array('b3', 'end'))
;

echo $graph->render();