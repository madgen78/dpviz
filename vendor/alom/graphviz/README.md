# Graphviz

![Build status](https://travis-ci.org/alexandresalome/graphviz.png?branch=master) [![Latest Stable Version](https://poser.pugx.org/alom/graphviz/v/stable)](https://packagist.org/packages/alom/graphviz) [![Total Downloads](https://poser.pugx.org/alom/graphviz/downloads)](https://packagist.org/packages/alom/graphviz) [![License](https://poser.pugx.org/alom/graphviz/license)](https://packagist.org/packages/alom/graphviz) [![Monthly Downloads](https://poser.pugx.org/alom/graphviz/d/monthly)](https://packagist.org/packages/alom/graphviz) [![Daily Downloads](https://poser.pugx.org/alom/graphviz/d/daily)](https://packagist.org/packages/alom/graphviz)

Graphviz generation for PHP

* [View CHANGELOG](CHANGELOG.md)
* [View CONTRIBUTORS](CONTRIBUTORS.md)

[![Build Status](https://secure.travis-ci.org/alexandresalome/graphviz.png?branch=master)](http://travis-ci.org/alexandresalome/graphviz)

## Installation

Install the latest version with:

```bash
composer require alom/graphviz
```

## Usage

This library allow you to create Dot Graph with a PHP fluid interface:

```php
$graph = new Alom\Graphviz\Digraph('G');
$graph
    ->subgraph('cluster_1')
        ->attr('node', array('style' => 'filled', 'fillcolor' => 'blue'))
        ->node('A')
        ->node('B')
        ->edge(array('b0', 'b1', 'b2', 'b3'))
    ->end()
    ->edge(array('A', 'B', 'C'))
;
echo $graph->render();
```

### Escaping of labels

By default, labels will be escaped, so that your PHP string is represented "as it is" in the graph. If you don't want the label to be escaped, add set the special **_escaped** attribute to false:

```php
$graph = new Alom\Graphviz\Digraph('G');
$graph
    ->node('my_table', array(
        'label' => '<<table>...</table>>',
        '_escaped' => false
    ))
```

### Browsing the graph

When you have created lot of subgraphs and nodes, it might be useful to be able to browse it using identifiers. For example, if you have the following graph:

```php
$graph = new Alom\Graphviz\Digraph('G');
$graph
    ->subgraph('cluster_1')
        ->node('A')
        ->node('B')
    ->end()
    ->subgraph('cluster_2')
        ->node('C')
        ->node('D')
    ->end()
    ->edge(array('C', 'D'))
;
```

You can do the following to access the nodes in the existing graph:

```php
$cluster = $graph->get('cluster_1');
$node = $graph->get('cluster_2')->get('D');
```

When you have a node or an edge, you can manipulate its attributes:

```php
# read a value
echo $node->getAttribute('label', 'no label'); # second argument is default value

# write a value
$node->attribute('label', 'new label');
```

On a graph, you can access or verify edge existence:

```
$graph->hasEdge(array('A', 'B'));
$graph->getEdge(array('C', 'D'));
```

### Using cluster and record IDs

If you create an edge from/to an ID inside a record, use an array instead of a string:

```php
$graph = new Alom\Graphviz\Digraph('G');
$graph
    ->node('A', array('shape' => 'record', 'label' => '{ <1> Part 1 | <2> Part 2}'))
    ->node('B')
    ->edge(array('B', array('A', '1')))
;
```

As you can see in the example above, the edge is composed of two parts:

* ``'B'``: a regular node
* ``array('A', '1')``: targets the cell "1" inside the A node

This method also work for **getEdge**, **hasEdge** and every edge-related method.

## Samples

Take a look at examples located in **samples** folder:

* [00-readme.php](samples/00-readme.php): Example from graphviz README
* [01-basic.php](samples/01-basic.php): Basic styling of nodes
* [02-table.php](samples/02-table.php): An example for HTML table escaping

You can generate any of those graph by using the following commands:

```bash
php samples/00-readme.php | dot -Tpdf -oout.pdf
xdg-open out.pdf
```
