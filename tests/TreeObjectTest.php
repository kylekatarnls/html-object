<?php

namespace HtmlObject;

use HtmlObject\TestCases\HtmlObjectTestCase;

class TreeObjectTest extends HtmlObjectTestCase
{
    /** @var Element */
    private $object;

    private function getObject()
    {
        if (!isset($this->object)) {
            $this->object = new Element('p', 'foo');
        }

        return $this->object;
    }

    public function testCanNest()
    {
        $this->getObject()->nest('strong', 'foo');

        $this->assertEquals('<p>foo<strong>foo</strong></p>', $this->getObject()->render());
    }

    public function testCanNestStrings()
    {
        $this->getObject()->nest('<strong>foo</strong>');

        $this->assertEquals('<p>foo<strong>foo</strong></p>', $this->getObject()->render());
    }

    public function testCanNestObjects()
    {
        $object = Element::strong('foo');
        $this->getObject()->nest($object);

        $this->assertEquals('<p>foo<strong>foo</strong></p>', $this->getObject()->render());
    }

    public function testCanNestObjectsInChildren()
    {
        $object = Element::strong('foo');
        $link = Element::a('foo');
        $this->getObject()->nest($object, 'body');
        $this->getObject()->nest($link, 'body.link');

        $this->assertEquals('<p>foo<strong>foo<a>foo</a></strong></p>', $this->getObject()->render());
    }

    public function testCanNestStringsInChildren()
    {
        $strong = Element::strong('title');
        $title = Element::h1('bar')->nest($strong, 'strong');
        $object = Element::div()->nest($title, 'title');
        $this->getObject()->nest($object, 'body');
        $this->getObject()->nest('by <a>someone</a>', 'body.title');

        $this->assertEquals('<p>foo<div><h1>bar<strong>title</strong>by <a>someone</a></h1></div></p>', $this->getObject()->render());
    }

    public function testCanGetNestedElements()
    {
        $object = Element::strong('foo');
        $this->getObject()->nest($object, 'foo');

        $this->assertEquals($object, $this->getObject()->getChild('foo'));
    }

    public function testCanGetChildWithDotsInName()
    {
        $object = Element::p('foo');
        $this->getObject()->setChild($object, '11:30 a.m.', true);

        $this->assertEquals($object, $this->getObject()->getChild('11:30 a.m.'));
    }

    public function testCanAppendElementToAll()
    {
        $object = Element::create('p', 'foo')->nest(array(
            'foo' => Element::strong('foo'),
            'baz' => Element::strong('foo'),
        ));
        $object->appendChild(Element::strong('foo'), 'append');

        $this->assertEquals(array('foo', 'baz', 'append'), array_keys($object->getChildren()));
    }

    public function testCanPrependElementToAll()
    {
        $object = Element::create('p', 'foo')->nest(array(
            'foo' => Element::strong('foo'),
            'baz' => Element::strong('foo'),
        ));
        $object->prependChild(Element::strong('foo'), 'prepend');

        $this->assertEquals(array('prepend', 'foo', 'baz'), array_keys($object->getChildren()));
    }

    public function testCanPrependToChild()
    {
        $object = Element::create('p', 'foo')->nest(array(
            'foo' => Element::strong('foo'),
            'baz' => Element::strong('foo'),
        ));
        $object->prependChild(Element::strong('foo'), 'prepend', 'baz');

        $this->assertEquals(array('foo', 'prepend', 'baz'), array_keys($object->getChildren()));
    }

    public function testCanAppendToChild()
    {
        $object = Element::create('p', 'foo')->nest(array(
            'foo' => Element::strong('foo'),
            'baz' => Element::strong('foo'),
        ));
        $object->appendChild(Element::strong('foo'), 'append', 'foo');

        $this->assertEquals(array('foo', 'append', 'baz'), array_keys($object->getChildren()));
    }

    public function testCanNestMultipleValues()
    {
        $this->getObject()->nestChildren(array('strong' => 'foo', 'em' => 'bar'));

        $this->assertEquals('<p>foo<strong>foo</strong><em>bar</em></p>', $this->getObject()->render());
    }

    public function testWontNestIfTagDoesntExist()
    {
        $this->getObject()->nest(array('strong' => 'foo', 'foobar' => 'bar'));

        $this->assertEquals('<p>foo<strong>foo</strong>bar</p>', $this->getObject()->render());
    }

    public function testCanNestMultipleValuesUsingNest()
    {
        $this->getObject()->nest(array('strong' => 'foo', 'em' => 'bar'));

        $this->assertEquals('<p>foo<strong>foo</strong><em>bar</em></p>', $this->getObject()->render());
    }

    public function testCanNestMultipleElements()
    {
        $foo = Element::strong('foo');
        $bar = Element::p('bar');
        $this->getObject()->nestChildren(array(
            'foo' => $foo,
            'bar' => $bar,
        ));

        $this->assertEquals($foo, $this->getObject()->getChild('foo'));
        $this->assertEquals($bar, $this->getObject()->getChild('bar'));
    }

    public function testCanNestMultipleObjects()
    {
        $strong = Element::strong('foo');
        $em = Element::em('bar');
        $this->getObject()->nestChildren(array($strong, $em));

        $this->assertEquals('<p>foo<strong>foo</strong><em>bar</em></p>', $this->getObject()->render());
    }

    public function testCanWalkTree()
    {
        $strong = Element::strong('foo');
        $this->getObject()->nest($strong);

        $this->assertEquals($this->getObject(), $this->getObject()->getChild(0)->getParent());
    }

    public function testCanModifyChildren()
    {
        $strong = Element::strong('foo');
        $this->getObject()->nest($strong);
        $this->getObject()->getChild(0)->addClass('foo');

        $this->assertEquals('<p>foo<strong class="foo">foo</strong></p>', $this->getObject()->render());
    }

    public function testCanCrawlToTextNode()
    {
        $this->getObject()->nest('<strong>foo</strong>');
        $this->getObject()->getChild(0)->addClass('foo');

        $this->assertEquals('<p>foo<strong>foo</strong></p>', $this->getObject()->render());
    }

    public function testCanCrawlSeveralLayersDeep()
    {
        $strong = Element::strong('foo');
        $em = Element::em('bar');
        $this->getObject()->nest($strong, 'strong')->getChild('strong')->nest($em, 'em');

        $this->assertEquals('<p>foo<strong>foo<em>bar</em></strong></p>', $this->getObject()->render());
        $this->assertEquals($em, $this->getObject()->getChild('strong.em'));
    }

    public function testCanCrawlAnonymousLayers()
    {
        $strong = Element::strong('foo');
        $em = Element::em('bar');
        $this->getObject()->nest($strong)->getChild(0)->nest($em);

        $this->assertEquals('<p>foo<strong>foo<em>bar</em></strong></p>', $this->getObject()->render());
        $this->assertEquals($em, $this->getObject()->getChild('0.0'));
    }

    public function testCanGoBackUpSeveralLevels()
    {
        $strong = Element::strong('foo');
        $em = Element::em('bar');
        $this->getObject()->nest($strong, 'strong')->getChild('strong')->nest($em, 'em');
        $child = $this->getObject()->getChild('strong.em');

        $this->assertEquals($child->getParent()->getParent(), $this->getObject());
        $this->assertEquals($child->getParent()->getParent(), $child->getParent(1));
    }

    public function testCanCheckIfObjectHasParent()
    {
        $this->getObject()->setParent(Element::div());

        $this->assertTrue($this->getObject()->hasParent());
    }

    public function testCanCheckIfObjectHasChildren()
    {
        $this->assertFalse($this->getObject()->hasChildren());

        $this->getObject()->nest(Element::div());
        $this->assertTrue($this->getObject()->hasChildren());
    }

    public function testCanHaveSelfClosingChildren()
    {
        $tag = Element::div('foo')->nest(array(
            'foo' => Input::create('text'),
        ));

        $this->assertEquals('<div>foo<input type="text"></div>', $tag->render());
    }

    public function testCanCheckIfChildrenIsAfterSibling()
    {
        $this->getObject()->nestChildren(array(
            'first' => Element::div(),
            'last' => Element::div(),
        ));
        $first = $this->getObject()->first;
        $last = $this->getObject()->last;

        $this->assertTrue($last->isAfter('first'));
        $this->assertFalse($first->isAfter('last'));
    }

    public function testCanCheckIfElementHasChild()
    {
        $element = Element::create('div', 'foo');
        $this->getObject()->nest($element, 'body');

        $this->assertTrue($this->getObject()->hasChild('body'));
        $this->assertFalse($this->getObject()->hasChild('title'));
    }
}
