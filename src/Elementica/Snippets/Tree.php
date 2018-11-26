<?php

namespace Elementica\Snippets;

/**
 * Tree
 * 
 * manage and convert a tree structure represented internally with a
 * linear indexed array of nodes: each node is an associative array
 * with at least an 'id' - must be unique - and a 'parent' with the
 * value of parent's id that's null for the root. Presently root node
 * must be unique.
 * 
 * TODO:
 * - (all) check(s), maybe via exceptions
 * - manage options
 * 
 */
class Tree {
	private $tree;
	private $fields = array(
		'id'       => 'id',
		'parent'   => 'parent',
		'children' => 'children',
	);
	private $stats = array(
		'last_exec_time' => null,
	);
	private $timerStart, $timerRunning=0, $timerStop, $timerElasped;

	/**
	 * @param array $tree :    indexed array of tree's nodes (plain sequence)
	 * @param array $options : associative array for options
	 *                         key 'id', string:       name of id's field
	 *                         key 'parent', string:   name of parent's field
	 *                         key 'children', string: name of field to nest children
	 */
	public function __construct( $tree, $options=null ) {
		$this->tree = $tree;
	}

	private function timerStart() {
		if ($this->timerRunning==0) {
			$this->timerFlag = true;
			$this->timerStart = microtime(true);
		};
		$this->timerRunning++;
	}
	private function timerStop() {
		$this->timerRunning--;
		if ($this->timerRunning==0) {
			$this->timerStop = microtime(true);
			$this->timerElasped = ($this->timerStop)-($this->timerStart);
			$this->stats['last_exec_time'] = number_format($this->timerElasped, 10).'"';
		};
	}
	public function getStats() {
		$stats = $this->stats;
		return ($stats);
	}

	/**
	 * @param string $field : reference of field to retrieve ('id', 'parent' or 'children')
	 * @param array $node :   the node to check
	 * 
	 * @return <any> the value of the field
	 */
	private function getField($field, $node) {
		$name = $this->fields[$field];
		$value = $node[$name];
		return ($value);
	}
	
	/**
	 * Look for root node, i.e. the one with no parent
	 */
	public function getRoot() {
		$root = null;
		foreach ($this->tree as $node) {
			$parent = $this->getField('parent', $node);
			if (is_null($parent)) {
				$root = $node;
				break;
			};
		};
		return ($root);
	}
	/**
	 * Look for node's children
	 */
	public function getChildren($node) {
		$children = [];
		$id = $this->getField('id', $node);
		foreach ($this->tree as $node) {
			$parent = $this->getField('parent', $node);
			if ($parent === $id) array_push($children, $node);
		};
		return ($children);
	}

	/**
	 * Converts internal representation to a nested from stated node (null for root)
	 * using a recursive approach
	 * 
	 * @return array|null the (sub)tree or null if empty
	 */
	public function nestedRecursive( $twig = null ) {
		$this->timerStart();
		if ( is_null( $twig ) ) $twig = $this->getRoot();
		if ($twig) {
			$twig[$this->fields['children']] = array_map(
				function( $child ) {
					return $this->nestedRecursive( $child );
				},
				$this->getChildren( $twig )
			);
		};
		$this->timerStop();
		return $twig;
	}

	/**
	 * Converts internal representation to a nested from stated node (null for root)
	 * using an iterative approach
	 * 
	 * @return array|null the (sub)tree or null if empty
	 */
	public function nestedIterative( $twig = null ) {
		$this->timerStart();
		if ( is_null( $twig ) ) $twig = $this->getRoot();
		$r = array('id' => null, 'index' => null);
		if ($twig) {
			$nodes = [];
			$children = []; // compute references to children (by id)
			foreach ($this->tree as $n => $node) {
				$id = $this->getField('id', $node);
				$parent = $this->getField('parent', $node);
				if (is_null($parent)) {
					$r = array('id' => $id, 'index' => $n);
				} else {
					if (!isset($children[$parent])) $children[$parent] = [];
					array_push($children[$parent], array('id'=>$id, 'index'=>$n));
				};
			};
			$marked = []; $size = sizeof($this->tree);
			$f = 0;       // first index
			$t = $size-1; // last index
			$i = -1;      // loop index
			$count = 0;   // init counter (keep track of changes per loop)
			while (sizeof($marked)<$size) {
				if (++$i>$t) {                                   // circular indexing
					$i=$f;
					if ($count==0) throw new Exception('!!!');   // at least one node must be marked per loop
					$count=0;                                    // reset counter
				};
				$node = $this->tree[$i];                         // retrieve node (by index)
				$id = $this->getField('id', $node);
				if (!isset($children[$id])) $children[$id]=[];   // keep track of children
				$mark = (!isset($marked[$i]))?false:$marked[$i]; // retrieve marking
				$toadd = false;
				if (!$mark) {
					$numOfChildren = sizeof($children[$id]);
					if ($numOfChildren==0) { // if it has no children (a leaf) we can add it...
						$toadd = true;
					} else { // ...otherwise we check if we've already marked all children
						$childrenMarked = true;
						foreach ($children[$id] as $child) {
							$childrenMarked &= ( ( isset($marked[$child['index']]) )?( $marked[$child['index']] ):( false ) );
						};
						$toadd = $childrenMarked; // (if all children are marked, we can add it)
					};
				};
				if ($toadd) {
					$marked[$i] = true; $count++; // mark the node we're adding
					$node['children'] = [];       // nest children inside
					foreach ($children[$id] as $child) { // for a leaf this is an empty loop
						array_push($node['children'], $nodes[$child['index']]);
					};
					$nodes[$i] = $node;
				};
			};
		};
		$twig = (!is_null($r['index']))?$nodes[$r['index']]:null;
		$this->timerStop();
		return $twig;
	}

};

?>