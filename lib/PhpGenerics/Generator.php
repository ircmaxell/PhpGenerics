<?php

namespace PhpGenerics;

use PhpParser\NodeVisitor;
use PhpParser\Node;

class Generator implements NodeVisitor {    

    protected $genericTypes = [];

    public function __construct(array $names, array $types) {
        if (count($names) != count($types)) {
            throw new \RuntimeException("Generic type count mismatch");
        }
        $this->genericTypes = array_combine($names, $types);
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
        if (isset($node->returnType)) {
            var_dump($node->returnType);
            die();
        } elseif ($node instanceof Node\Param) {
            if ($node->hasAttribute("generic_name")) {
                $type = $node->getAttribute("generic_name");
                if (isset($this->genericTypes[$type])) {
                    $node->type = new Node\Name\FullyQualified($this->genericTypes[$type]);
                } else {
                    throw new \LogicException("Bad generic found");
                }
            } elseif ($node->type instanceof Node\Name && $node->type->hasAttribute("generics") && $node->type->getAttribute("generics")) {
                $type = $node->getAttribute("original_type")->parts;
                foreach ($node->type->getAttribute("generics") as $generic) {
                    if (isset($this->genericTypes[$generic])) {
                        $value =  str_replace("\\", Engine::NS_TOKEN, $this->genericTypes[$generic]);
                        $type[] = Engine::CLASS_TOKEN . $value . Engine::CLASS_TOKEN;
                    } else {
                        throw new \LogicException("Bad generic found");
                    }
                }
                $node->type = new Node\Name\FullyQualified($type);
            } elseif (((string) $node->name) == "item") {
                var_dump($node);
                die();
            }
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

}
