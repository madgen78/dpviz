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
    ->node('escaped', array(
        'label' => '<<table><tr><td>Should be escaped</td></tr></table>>',
    ))
    ->node('unescaped', array(
        'label' => '<<table><tr><td>Should not be escaped</td></tr></table>>',
        '_escaped' => false,
    ))
    ->edge(array('escaped', 'unescaped'), array(
        'label' => '<<table><tr><td>label</td></tr></table>>',
        '_escaped' => false,
    ))
;

echo $graph->render();
