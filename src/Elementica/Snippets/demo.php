<?php

define ('ANSI', (function_exists('posix_isatty')&&posix_isatty(STDOUT)));
define ('ESC',   "\033");
define ('NL',    "\n");
define ('TAB',   "\t");
define ('BOLD',  ANSI?ESC."[1m":'');
define ('DARKBLUE',  ANSI?ESC."[34m":'');
define ('LIGHTBLUE',  ANSI?ESC."[94m":'');
define ('RESET', ANSI?ESC."[0m":'');
define ('HR',    ANSI?"\n".DARKBLUE.str_repeat("-", intval(shell_exec('tput cols'))).RESET."\n":"\n");
define ('BRK',   ANSI?NL.HR.NL:HR);

// -------------------------------------------------- //
if (php_sapi_name() != 'cli') exit(2);
print BRK.BOLD.LIGHTBLUE."Elementica\Snippets DEMO".RESET.NL.NL;
if ( ($argc!=3) || ($argc==3 && ($argv[1]!='Tree')) ) {
    print "Usage:".TAB."php [-d memory_limit=1024M] -f $argv[0] -- Tree <num_of_nodes>".NL.TAB."(e.g. php -f $argv[0] -- Tree 10000)".NL;
    print BRK; exit(3);
};
if ($argv[1]!='Tree') exit(4);
$argv2 = intval($argv[2]);

// -------------------------------------------------- //

require_once(__DIR__.'/Tree.php');
use Elementica\Snippets\Tree as Tree;

// -------------------------------------------------- //

// create "random" tree
$num = $argv2;
$tree = ($num>0)?(array( array('id' => 1, 'name' => '1', 'parent' => null), )):[];
for ($n=2; $n<($num+1); $n++) {
	$parent = rand(1, ($n-1));
	$node = array('id' => $n, 'name' => (string)$n, 'parent' => $parent);
	array_push($tree, $node);
};

print "Size of tree = ".sizeof($tree).NL; sleep(2);
$Tree = new Tree($tree);

print NL."Recursive...".NL;
$nestedRecursive = $Tree->nestedRecursive();
$timingRecursive = $Tree->getStats()['last_exec_time'];

print NL."Iterative...".NL;
$nestedIterative = $Tree->nestedIterative();
$timingIterative = $Tree->getStats()['last_exec_time'];
$gain = number_format((floatval($timingRecursive)/floatval($timingIterative))*100, 2, '.', '').'%';

print NL."$timingRecursive (recursive) vs $timingIterative (iterative): $gain".NL;

//print "\n".var_export($nestedRecursive, true)."\n\n";print var_export($nestedIterative, true)."\n\n";
print "COMPARE: ".var_export(serialize($nestedRecursive)==serialize($nestedIterative), true); sleep(2);

print BRK;

// -------------------------------------------------- //

//print "\n\n".print_r($nestedRecursive, true)."\n\n";
//print "\n\n".print_r($nestedIterative, true)."\n\n";

?>