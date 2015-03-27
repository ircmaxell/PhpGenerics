<?php

namespace PhpGenerics;

use PhpParser\NodeVisitor;
use PhpParser\Node;

class Compiler implements NodeVisitor {	

	protected $currentGenerics = [];

	protected $classes = [];
	
	public function getClass($class) {
		if (isset($this->classes[$class])) {
			return $this->classes[$class];
		}
		return null;
	}

	public function getClasses() {
		return $this->classes;
	}

	/**
     * Called once before traversal.
     *
     * Return value semantics:
     *  * null:      $nodes stays as-is
     *  * otherwise: $nodes is set to the return value
     *
     * @param Node[] $nodes Array of nodes
     *
     * @return null|Node[] Array of nodes
     */
    public function beforeTraverse(array $nodes) {

    }

    /**
     * Called when entering a node.
     *
     * Return value semantics:
     *  * null:      $node stays as-is
     *  * otherwise: $node is set to the return value
     *
     * @param Node $node Node
     *
     * @return null|Node Node
     */
    public function enterNode(Node $node) {
    	switch ($node->getType()) {
    		case 'Stmt_Class':
    			$this->classes[(string) $node->namespacedName] = $node;
    			$this->addGenerics($node);
    			break;
    		case 'Param':
    			if ($node->type) {
    				$type = $node->type instanceof Node\Name ? $node->type->getLast() : $node->type;
    				if ($node->type instanceof Node\Name && $node->type->hasAttribute("generics") && $node->type->getAttribute("generics")) {
    					$node->setAttribute("generics", $node->type->getAttribute("generics"));
						$node->setAttribute("original_type", $node->type);
					}
    				if (isset($this->currentGenerics[$type])) {
    					$node->type = null;
    					$node->setAttribute("generic_name", $type);
    				} 

    			}
    			break;
    		case 'Expr_New':
    			// replace generics
    			if (!$node->getAttribute("generics")) {
    				break;
    			}
    			if ($node->class instanceof Node\Name) {
    				foreach ($node->getAttribute("generics") as $generic) {
    					$generic = str_replace("\\", Engine::NS_TOKEN, $generic);
    					$node->class->append(Engine::CLASS_TOKEN . $generic . Engine::CLASS_TOKEN);
    				}
    			} else {
    				// dirty hack!
    				$node->class = $this->appendDynamicVariable($node->class, $node->getAttribute("generics"));
    			}
    			break;
    		case 'Name_FullyQualified':
    			break;
    		default:
    			if ($node->hasAttribute("generics") && $node->getAttribute("generics")) {
    				throw new \RuntimeException("Not implemented yet " . $node->getType());
    			}
    	}
    	if (isset($node->returnType) && version_compare(PHP_VERSION, "7.0", "<")) {
    		unset($node->returnType);
    	}
    }

    /**
     * Called when leaving a node.
     *
     * Return value semantics:
     *  * null:      $node stays as-is
     *  * false:     $node is removed from the parent array
     *  * array:     The return value is merged into the parent array (at the position of the $node)
     *  * otherwise: $node is set to the return value
     *
     * @param Node $node Node
     *
     * @return null|Node|false|Node[] Node
     */
    public function leaveNode(Node $node) {
		switch ($node->getType()) {
    		case 'Stmt_Class':
    			$this->removeGenerics($node);
    			break;
    	}
    }

    /**
     * Called once after traversal.
     *
     * Return value semantics:
     *  * null:      $nodes stays as-is
     *  * otherwise: $nodes is set to the return value
     *
     * @param Node[] $nodes Array of nodes
     *
     * @return null|Node[] Array of nodes
     */
    public function afterTraverse(array $nodes) {

    }

    protected function appendDynamicVariable($existing, array $generics) {
    	throw new \Exception("Not implemented");
    }

    protected function addGenerics(Node $node) {
    	if (!$node->hasAttribute('generics') || !$node->getAttribute('generics')) {
    		return;
    	}
    	foreach ($node->getAttribute('generics') as $type) {
    		$this->currentGenerics[$type] = $type;
    	}
    }

	protected function removeGenerics(Node $node) {
    	if (!$node->hasAttribute('generics') || !$node->getAttribute('generics')) {
    		return;
    	}
    	foreach ($node->getAttribute('generics') as $type) {
    		unset($this->currentGenerics[$type]);
    	}
    }

}