<?php

namespace HtmlObject;

use HtmlObject\TestCases\HtmlObjectTestCase;
use HtmlObject\Traits\Tag;

class Icon extends Tag
{
    protected $bar = 'bar';

    public function __construct($icon)
    {
        $this->setTag('i', null, array('class' => 'icon-'.$icon));
    }

    public function injectProperties()
    {
        return array(
            'foo' => $this->bar,
        );
    }
}

class TagTest extends HtmlObjectTestCase
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

    public function testCanCreateCustomElementClasses()
    {
        $icon = new Icon('bookmark');

        $this->assertEquals('<i class="icon-bookmark" foo="bar"></i>', $icon->render());
    }

    public function testCanCreateHtmlObject()
    {
        $this->assertHTML($this->getMatcher(), $this->getObject());
    }

    public function testCanCreateDefaultElement()
    {
        $this->assertHTML($this->getMatcher(), Element::create()->setValue('foo'));
    }

    public function testCanUseXhtmlStandards()
    {
        $previous = Tag::$config['doctype'];
        Tag::$config['doctype'] = 'xhtml';
        $field = Input::hidden('foo', 'bar');
        $xhtml = $field->render();
        Tag::$config['doctype'] = $previous;

        $this->assertSame(' />', substr($xhtml, -3), 'Tag should end with " />"');
    }

    public function testCanSetAnAttribute()
    {
        $this->getObject()->setAttribute('data-foo', 'bar');
        $matcher = $this->getMatcher();
        $matcher['attributes']['data-foo'] = 'bar';

        $this->assertHTML($matcher, $this->getObject());
    }

    public function testCanSetJsonAttributes()
    {
        $json = '{"foo":"bar","baz":"qux"}';
        $this->getObject()->dataTags($json);
        $matcher = $this->getMatcher();
        $matcher['attributes']['data-tags'] = $json;

        $this->assertHTML($matcher, $this->getObject());
        $this->assertEquals("<p data-tags='".$json."'>foo</p>", $this->getObject()->render());

        $json = '["foo", "bar", "baz"]';
        $this->getObject()->dataTags($json);
        $matcher = $this->getMatcher();
        $matcher['attributes']['data-tags'] = $json;

        $this->assertHTML($matcher, $this->getObject());
        $this->assertEquals("<p data-tags='".$json."'>foo</p>", $this->getObject()->render());
    }

    public function testCanGetAttributes()
    {
        $this->getObject()->setAttribute('data-foo', 'bar');

        $this->assertEquals(array('data-foo' => 'bar'), $this->getObject()->getAttributes());
    }

    public function testCanGetAttribute()
    {
        $this->getObject()->setAttribute('data-foo', 'bar');

        $this->assertEquals('bar', $this->getObject()->getAttribute('data-foo'));
    }

    public function testCanDynamicallySetAttributes()
    {
        $this->getObject()->data_foo('bar');
        $this->getObject()->foo = 'bar';

        $matcher = $this->getMatcher();
        $matcher['attributes']['data-foo'] = 'bar';
        $matcher['attributes']['foo'] = 'bar';

        $this->assertHTML($matcher, $this->getObject());
    }

    public function testCanDynamicallySetAttributeWithCamelCase()
    {
        $this->getObject()->dataFoo('bar');
        $this->getObject()->foo = 'bar';

        $matcher = $this->getMatcher();
        $matcher['attributes']['data-foo'] = 'bar';
        $matcher['attributes']['foo'] = 'bar';

        $this->assertHTML($matcher, $this->getObject());
    }

    public function testCanDynamicallySetBooleanAttributesByDefault()
    {
        $this->getObject()->required();

        // cannot use assertHTML; it uses assertTag, which cannot find boolean attributes
        $this->assertEquals('<p required>foo</p>', $this->getObject()->render());
    }

    public function testCanDynamicallyGetChild()
    {
        $two = Element::p('foo');
        $one = Element::div()->setChild($two, 'two');
        $zero = Element::div()->setChild($one, 'one');

        $this->assertEquals('foo', $zero->oneTwo->getValue());
    }

    public function testCanReplaceAttributes()
    {
        $this->getObject()->setAttribute('data-foo', 'bar');
        $this->getObject()->replaceAttributes(array('foo' => 'bar'));

        $matcher = $this->getMatcher();
        $matcher['attributes']['foo'] = 'bar';

        $this->assertHTML($matcher, $this->getObject());
    }

    public function testCanMergeAttributes()
    {
        $this->getObject()->setAttribute('data-foo', 'bar');
        $this->getObject()->setAttributes(array('foo' => 'bar'));

        $matcher = $this->getMatcher();
        $matcher['attributes']['data-foo'] = 'bar';
        $matcher['attributes']['foo'] = 'bar';

        $this->assertHTML($matcher, $this->getObject());
    }

    public function testCanAppendClass()
    {
        $this->getObject()->setAttribute('class', 'foo');
        $this->getObject()->addClass('foo');
        $this->getObject()->addClass('bar');

        $matcher = $this->getMatcher();
        $matcher['attributes']['class'] = 'foo bar';

        $this->assertHTML($matcher, $this->getObject());
    }

    public function testCanFetchAttributes()
    {
        $this->getObject()->foo('bar');

        $this->assertEquals('bar', $this->getObject()->foo);
    }

    public function testCanChangeElement()
    {
        $this->getObject()->setElement('strong');

        $this->assertHTML($this->getMatcher('strong', 'foo'), $this->getObject());
    }

    public function testCanChangeValue()
    {
        $this->getObject()->setValue('bar');

        $this->assertHTML($this->getMatcher('p', 'bar'), $this->getObject());
    }

    public function testCanGetValue()
    {
        $this->assertEquals('foo', $this->getObject()->getValue());
    }

    public function testSimilarClassesStillGetAdded()
    {
        $this->getObject()->addClass('alert-success');
        $this->getObject()->addClass('alert');

        $this->assertEquals('<p class="alert-success alert">foo</p>', $this->getObject()->render());
    }

    public function testCanRemoveClasses()
    {
        $this->getObject()->addClass('foo');
        $this->getObject()->addClass('bar');
        $this->getObject()->removeClass('foo');

        $this->assertEquals('<p class="bar">foo</p>', $this->getObject()->render());
    }

    public function testCannotRemoveWrongClasses()
    {
        $this->getObject()->addClass('foo');
        $this->getObject()->addClass('bar');
        $this->getObject()->removeClass('unknow');

        $this->assertEquals('<p class="foo bar">foo</p>', $this->getObject()->render());
    }

    public function testCanManuallyOpenElement()
    {
        $element = $this->getObject()->open().'foobar'.$this->getObject()->close();

        $this->assertEquals('<p>foobar</p>', $element);
    }

    public function testCanWrapValue()
    {
        $this->getObject()->wrapValue('strong');

        $this->assertEquals('<p><strong>foo</strong></p>', $this->getObject()->render());
    }

    public function testCanWrapItself()
    {
        $object = $this->getObject()->wrapWith('div');

        $this->assertEquals('<div><p>foo</p></div>', $object->getParent()->render());
    }

    public function testCanManuallyOpenComplexStructures()
    {
        $object = Element::div(array(
            'title' => Element::div('foo')->class('title'),
            'body' => Element::div()->class('body'),
            'footer' => Element::div('footer'),
        ));
        $object = $object->openOn('body').'CONTENT'.$object->close();

        $this->assertEquals('<div><div class="title">foo</div><div class="body">CONTENT</div><div>footer</div></div>', $object);
    }

    public function testCanManipulateComplexStructures()
    {
        $object = Element::div(array(
            'title' => Element::div('foo')->class('title'),
            'body' => Element::div()->class('body'),
        ));

        $wrapper = Link::create('#', '');
        $wrapped = $object->wrapWith($wrapper, 'complex');
        $render = $wrapped->getParent()->openOn('complex.body').'foo'.$wrapped->getParent()->close();

        $this->assertEquals('<a href="#"><div><div class="title">foo</div><div class="body">foo</div></div></a>', $render);
    }

    public function testCanReplaceChildren()
    {
        $object = Element::div(array(
            'alpha' => Element::i(),
            'beta' => Element::b(),
        ));
        $object->nest(array('beta' => Element::a()));
        $this->assertEquals('<div><i></i><a></a></div>', $object->render());
    }

    public function testCanWrapChildren()
    {
        /* @var Element $object */
        $alpha = Element::i();
        $beta = Element::b();
        $object = Element::div(array(
            'alpha' => $alpha,
            'beta' => $beta,
        ));
        $gamma = Element::a();
        $wrapped = $object->getChild('beta')->wrapWith($gamma, 'gamma');

        $this->assertEquals($beta, $wrapped);
        // check tree
        $this->assertEquals($gamma, $object->getChild('gamma'));
        $this->assertEquals($gamma, $object->gamma);
        $this->assertEquals($beta, $object->getChild('gamma.beta'));
        $this->assertEquals($beta, $object->gammaBeta);

        // expecting that element wrapped had replaced itself with wrap element in tree
        $this->assertEquals('<div><i></i><a><b></b></a></div>', $object->render());

        // also check implicit element creation
        $object->gamma->wrapWith('u', 'underline');
        $this->assertEquals($beta, $object->underlineGammaBeta);
        $this->assertEquals($beta, $object->getChild('underline.gamma.beta'));
        $this->assertEquals('<div><i></i><u><a><b></b></a></u></div>', $object->render());
    }

    public function testCanCheckIfTagIsOpened()
    {
        $this->getObject()->open();

        $this->assertTrue($this->getObject()->isOpened());
    }

    public function testCanCreateShadowDom()
    {
        $tag = Element::div('foo')->foo('bar')->element('');

        $this->assertEquals('foo', $tag->render());
    }

    public function testCanReturnItselfIfInvalidChildren()
    {
        $tag = Element::div('foo');

        $this->assertEquals($tag, $tag->nestChildren('foo'));
    }

    public function testCanAttemptToRemoveUnexistingClasses()
    {
        $tag = Element::div('foo')->removeClass('foobar');

        $this->assertEquals('', $tag->class);
    }

    public function testCanRemoveClassIfOtherClassesMatch()
    {
        $tag = Element::div('foo')->class('btn btn-primary btn-large')->removeClass(array('btn', 'foobar'));

        $this->assertEquals('btn-primary btn-large', $tag->class);
    }

    public function testCanRemoveMultipleClassesInStringNotation()
    {
        $tag = Element::div('foo')->class('btn btn-primary btn-large')->removeClass('btn btn-primary');

        $this->assertEquals('btn-large', $tag->class);
    }
}
