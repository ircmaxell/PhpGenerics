<?php

namespace PhpGenerics;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\Node;

class Engine {
	const CLASS_TOKEN = "\xe2\x91\xa0";
	const NS_TOKEN = "\xe2\x91\xa1";

	private $compiler;
	private $parser;
	private $traverser;
	private $prettyPrinter;

	public function __construct(Parser $parser = null, Compiler $compiler = null) {
		if (!$parser) {
			$parser = new Parser(new Lexer);
		}
		$this->parser = $parser;
		if (!$compiler) {
			$compiler = new Compiler;
		}
		$this->compiler = $compiler;
		$this->prettyPrinter = new Standard;
		$this->traverser = new NodeTraverser;
		$this->traverser->addVisitor(new NameResolver);
		$this->traverser->addVisitor($this->compiler);
	}

	public function process($file) {
		$code = file_get_contents($file);
		$ast = $this->parser->parse($code);
		$processed = $this->traverser->traverse($ast);
		return $this->prettyPrinter->prettyPrint($processed);
	}

	public function implement($class) {
		$parts = $orig_parts = explode("\\", ltrim($class, "\\"));
		$real_class_parts = [];
		while ($part = array_shift($parts)) {
			if (strpos($part, self::CLASS_TOKEN) !== false) {
				break;
			}
			$real_class_parts[] = $part;
		}
		array_unshift($parts, $part);

		$types = [];
		foreach ($parts as $part) {
			$types[] = str_replace([self::CLASS_TOKEN, self::NS_TOKEN], ["", "\\"], $part);
		}

		$real_class = implode("\\", $real_class_parts);
		if (!class_exists($real_class)) {
			throw new \RuntimeException("Attempting to use generics on unknown class $real_class");
		}
		if (!($ast = $this->compiler->getClass($real_class))) {
			throw new \RuntimeException("Attempting to use generics with non-generic class");
		}



		$generator = new Generator($ast->getAttribute("generics"), $types);
		$traverser = new NodeTraverser;
		$traverser->addVisitor($generator);
		$ast = $traverser->traverse([$ast]);
		$ast[0]->name = array_pop($orig_parts);
		$ast[0]->extends = new Node\Name\FullyQualified($real_class_parts);
		array_unshift($ast, new Node\Stmt\Namespace_(new Node\Name($orig_parts)));
		return $this->prettyPrinter->prettyPrint($ast);
	}
}