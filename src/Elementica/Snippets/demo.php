<?php

// -------------------------------------------------- //
if (php_sapi_name() != 'cli') exit(2);
print "\n";
if ($argc!=3) {
    print "Usage: $argv[0] Tree <num_of_nodes> (e.g. $argv[0] Tree 10000)\n";
    exit(3);
};
if ($argv[1]!='Tree') exit(4);
$argv2 = intval($argv[2]);

// -------------------------------------------------- //

require_once(__DIR__.'/Tree.php');
use Elementica\Snippets\Tree as Tree;

// -------------------------------------------------- //

// create "random" tree
$num = $argv2;
$tree = array( array('id' => 1, 'name' => '1', 'parent' => null), );
for ($n=2; $n<($num+1); $n++) {
	$parent = rand(1, ($n-1));
	$node = array('id' => $n, 'name' => (string)$n, 'parent' => $parent);
	array_push($tree, $node);
};
ob_end_clean();
print "Size of tree = ".sizeof($tree)."\n"; sleep(2);
$Tree = new Tree($tree);

print "\nRecursive...\n";
$nestedRecursive = $Tree->nestedRecursive();
$timingRecursive = $Tree->getStats()['last_exec_time'];

print "\nIterative...\n";
$nestedIterative = $Tree->nestedIterative();
$timingIterative = $Tree->getStats()['last_exec_time'];
$gain = number_format((floatval($timingRecursive)/floatval($timingIterative))*100, 2, '.', '').'%';

print "\n$timingRecursive (recursive) vs $timingIterative (iterative): $gain\n";

//print "\n".var_export($nestedRecursive, true)."\n\n";print var_export($nestedIterative, true)."\n\n";
print "COMPARE: ".var_export(serialize($nestedRecursive)==serialize($nestedIterative), true)."\n\n"; sleep(2);

// -------------------------------------------------- //

?>