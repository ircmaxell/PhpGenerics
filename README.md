Muahahahahahahaha
=================

I'll let the example speak for itself:

Example:

Test/Item.php

```php
namespace test;

class Item<T> {
    
    protected $item;
    
    public function __construct(T $item = null)
    {
        $this->item = $item;
    }

    public function getItem()
    {
        return $item;
    }

    public function setItem(T $item)
    {
        $this->item = $item;
    }
}
```

Test/Test.php

```php
namespace Test;

class Test {
    public function runTest()
    {
        $item = new Item<StdClass>;
        var_dump($item instanceof Item); // true
        $item->setItem(new StdClass); // works fine
        // $item->setItem([]); // E_RECOVERABLE_ERROR
    }
}
```
test.php

```php
require "vendor/autoload.php";

$test = new Test\Test;
$test->runTest();
```

## HOW???

Black magic and voodoo.

## Where can I use generics?

Right now, only class definitions can define generics, and any parameter or return type declaration can use them.

It also supports parameter-expansion:

```php
class Foo<T> {
    public function bar(): Foo<T> {}
}
```

As far as the rest, I don't know.

## Seriously, How???

Like I said, black magic. If you want to know, you're going to regret it.

## How do I install?

Since this is black voodoo evilness, I'm not adding it to packagist. Simply add a composer repository pointing here, and composer install. Then just use generics in your code and be happy.

## HOW ARE YOU DOING THIS?

You **really** don't want to know...

## Gotchas

Right now, generic types are not resolved according to use rules. So

```php
    new Item<StdClass>
```

Always points to `\StdClass`. It will not respect `use` or the present namespace. This is a TODO.

## FOR THE LOVE OF GOD, HOW???

Fine. Your loss. 

I hijack the composer autoloader, and substitute my own. I then pre-process all autoloaded files, transpiling them to eliminate generics from declarations. I also compile usages from generic syntax to namespace syntax (compiling the types as we go along).

So:
```php
new Item<StdClass>
```
Becomes
```php
new Item\①StdClass①
```
Then, the autoloader recognizes attempts to load these classes and will generate the templated code... The above 2 blocks of code will be compiled to:
```php
class test
{
    public function runTests()
    {
        $item = new \test\Item\①StdClass①(new \StdClass());
        $itemList = new \test\ItemList\①StdClass①();
        $itemList->addItem($item);
    }
}
```
And:
```php
namespace test\Item;

class ①StdClass① extends \test\Item
{
    protected $item;

    public function __construct(\StdClass $item)
    {
        $this->item = $item;
    }

    public function getItem()
    {
        return $item;
    }
    
    public function setItem(\StdClass $item)
    {
        $this->item = $item;
    }
}
```
## TL;DR

TL;DR: don't use this
