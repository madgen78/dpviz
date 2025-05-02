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
    ->subgraph('cluster_1')
        ->attr('node', array('style' => 'filled', 'fillcolor' => 'blue'))
        ->node('A')
        ->node('B')
    ->end()
    ->edge(array('A', 'B', 'C'))
;

echo $graph->render();