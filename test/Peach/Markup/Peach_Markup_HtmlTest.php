<?php
require_once(__DIR__ . "/Peach_Markup_TestUtil.php");

class Peach_Markup_HtmlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Peach_Markup_Html
     */
    protected $object;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }
    
    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }
    
    /**
     * init() のテストです. 以下を確認します.
     * 
     * - 引数に true を指定した場合は XHTML 形式, false を指定した場合は HTML 形式のグローバル Helper が初期化されること
     * - 引数を省略した場合は HTML 形式で初期化されること
     * 
     * @covers Peach_Markup_Html::init
     */
    public function testInit()
    {
        $xr = Peach_Markup_XmlRenderer::getInstance();
        $sr = Peach_Markup_SgmlRenderer::getInstance();
        
        Peach_Markup_Html::init(true);
        $b1 = Peach_Markup_Html::getBuilder();
        $this->assertSame($xr, $b1->getRenderer());
        
        Peach_Markup_Html::init(false);
        $b2 = Peach_Markup_Html::getBuilder();
        $this->assertSame($sr, $b2->getRenderer());
        
        Peach_Markup_Html::init();
        $b3 = Peach_Markup_Html::getBuilder();
        $this->assertSame($sr, $b3->getRenderer());
    }
    
    /**
     * getHelper() のテストです.
     * @covers Peach_Markup_Html::getHelper
     */
    public function testGetHelper()
    {
        $breakControl   = new Peach_Markup_NameBreakControl(
            array("html", "head", "body", "ul", "ol", "dl", "table"),
            array("pre", "code", "textarea")
        );
        $emptyNodeNames = array("area", "base", "basefont", "br", "col", "command", "embed", "frame", "hr", "img", "input", "isindex", "keygen", "link", "meta", "param", "source", "track", "wbr");
        
        $b1  = new Peach_Markup_DefaultBuilder();
        $b1->setBreakControl($breakControl);
        $b1->setRenderer("HTML");
        $ex1 = new Peach_Markup_Helper($b1, $emptyNodeNames);
        Peach_Markup_Html::init();
        $this->assertEquals($ex1, Peach_Markup_Html::getHelper());
        
        $b2  = new Peach_Markup_DefaultBuilder();
        $b2->setBreakControl($breakControl);
        $b2->setRenderer("XHTML");
        $ex2 = new Peach_Markup_Helper($b2, $emptyNodeNames);
        Peach_Markup_Html::init(true);
        $this->assertEquals($ex2, Peach_Markup_Html::getHelper());
    }
    
    /**
     * getBuilder() のテストです.
     * 返り値の Builder に対する変更が, グローバル Helper に適用されることを確認します.
     * @covers Peach_Markup_Html::getBuilder
     */
    public function testGetBuilder()
    {
        $builder = Peach_Markup_Html::getBuilder();
        $builder->setRenderer("html");
        $builder->setIndent(new Peach_Markup_Indent(0, "  ", Peach_Markup_Indent::LF));
        $result  = Peach_Markup_Html::tag(Peach_Markup_TestUtil::getTestNode())->write();
        $this->assertSame(Peach_Markup_TestUtil::getCustomBuildResult(), $result);
    }
    
    /**
     * tag() のテストです. 引数によって, 生成される HelperObject が以下の Component をラップすることを確認します.
     * - 文字列の場合: 引数を要素名に持つ Element
     * - null または引数なしの場合: 空の NodeList
     * - Node オブジェクトの場合: 引数の Node 自身
     * 
     * また, HTML4.01 および最新の HTML5 の勧告候補 (2014-02-04 時点) の仕様を元に,
     * 以下の要素が「空要素」として生成されることを確認します.
     * - HTML4.01: area, base, basefont, br, col, frame, hr, img, input, isindex, link, meta, param
     * - HTML5: area, base, br, col, command, embed, hr, img, input, keygen, link, meta, param, source, track, wbr
     * 
     * @covers Peach_Markup_Html::tag
     */
    public function testTag()
    {
        $containerExamples = array("html", "body", "div", "span");
        foreach ($containerExamples as $name) {
            $obj = Peach_Markup_Html::tag($name);
            $this->assertInstanceOf("Peach_Markup_ContainerElement", $obj->getNode());
        }
        
        $nodeList  = new Peach_Markup_NodeList();
        $obj2      = Peach_Markup_Html::tag(null);
        $this->assertEquals($nodeList, $obj2->getNode());
        $obj3      = Peach_Markup_Html::tag();
        $this->assertEquals($nodeList, $obj3->getNode());
        
        $text      = new Peach_Markup_Text("SAMPLE TEXT");
        $obj4      = Peach_Markup_Html::tag($text);
        $this->assertSame($text, $obj4->getNode());
        
        $emptyHtml4 = array("area", "base", "basefont", "br", "col", "frame", "hr", "img", "input", "isindex", "link", "meta", "param");
        $emptyHtml5 = array("area", "base", "br", "col", "command", "embed", "hr", "img", "input", "keygen", "link", "meta", "param", "source", "track", "wbr");
        $emptyList  = array_unique(array_merge($emptyHtml4, $emptyHtml5));
        foreach ($emptyList as $name) {
            $obj = Peach_Markup_Html::tag($name);
            $this->assertInstanceOf("Peach_Markup_EmptyElement", $obj->getNode());
        }
    }
    
    /**
     * @covers Peach_Markup_Html::comment
     * @todo   Implement testComment().
     */
    public function testComment()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
    
    /**
     * @covers Peach_Markup_Html::conditionalComment
     * @todo   Implement testConditionalComment().
     */
    public function testConditionalComment()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
    
    /**
     * @covers Peach_Markup_Html::select
     * @todo   Implement testSelect().
     */
    public function testSelect()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
}
