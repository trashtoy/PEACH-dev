<?php
/**
 * Test class for Peach_DT_SimpleFormat.
 */
class Peach_DT_SimpleFormatTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @var array
     */
    private $testFormats;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->testFormats = array(
            array("YmdHis", "Y年n月j日G時f分b秒", "\\Y=Y \\n=n \\j=j \\H=H \\i=i \\s=s"),
            array("Y/m/d",  "Y.n.j"),
            array("H:i:s",  "G時f分b秒")
        );
    }
    
    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
    
    private function getObjectList()
    {
        return array_map("Peach_DT_SimpleFormatTest::createObject", $this->testFormats);
    }
    
    public static function createObject($arg)
    {
        if (is_array($arg)) {
            return array_map("Peach_DT_SimpleFormatTest::createObject", $arg);
        } else {
            return new Peach_DT_SimpleFormat($arg);
        }
    }
    
    private function getParseDataList()
    {
        static $parseDataList = array(
            array(
                array("20120521073009",                 "2012052112345"),
                array("2012年5月21日7時30分9秒",        "2012年05月21日07時30分09秒"),
                array("Y=2012 n=5 j=21 H=07 i=30 s=09", "Y=2012 n=5 j=21 H=7 i=30 s=9"),
            ),
            array(
                array("2012/03/07", "2012-03-07"),
                array("2012.3.7",   "hogehoge"),
            ),
            array(
                array("08:06:04",   "8:06:04"),
                array("8時6分4秒",  "8じ6分4秒"),
            ),
        );
        return array_map("Peach_DT_SimpleFormatTest::createTestDataList", $parseDataList, $this->testFormats);
    }
    
    public static function createTestDataList($dataList, $formats)
    {
        return array_map("Peach_DT_SimpleFormatTest::createTestData", $dataList, $formats);
    }
    
    /*
     * array(
     *     0 => Peach_DT_SimpleFormat オブジェクト
     *     1 => フォーマット
     *     2 => テスト用のパース文字列
     *     3 => 失敗するパース文字列
     * )
     */
    public static function createTestData($testData, $format)
    {
        return array_merge(array(new Peach_DT_SimpleFormat($format), $format), $testData);
    }
    
    /**
     * @covers Peach_DT_SimpleFormat::getFormat
     */
    public function testGetFormat()
    {
        $arg = "\\Y=Y \\n=n \\j=j \\H=H \\i=i \\s=s";
        $f = new Peach_DT_SimpleFormat($arg);
        $this->assertSame($arg, $f->getFormat());
    }
    
    /**
     * @covers Peach_DT_SimpleFormat::parseDate
     */
    public function testParseDate()
    {
        $testData = $this->getParseDataList();
        $expected = array(
            new Peach_DT_Date(2012, 5, 21),
            new Peach_DT_Date(2012, 3, 7),
            Peach_DT_Date::now()
        );
        for ($i = 0; $i < 3; $i++) {
            foreach ($testData[$i] as $formats) {
                $this->assertEquals($expected[$i], $formats[0]->parseDate($formats[2]));
                try {
                    $formats[0]->parseDate($formats[3]);
                    $this->fail();
                } catch (Exception $e) {}
            }
        }
    }
    
    /**
     * @covers Peach_DT_SimpleFormat::parseDatetime
     */
    public function testParseDatetime()
    {
        $testData = $this->getParseDataList();
        $expected = array(
            new Peach_DT_Datetime(2012, 5, 21, 7, 30),
            new Peach_DT_Datetime(2012, 3, 7,  0,  0),
            Peach_DT_Datetime::now()->setAll(array("hour" => 8, "minute" => 6)),
        );
        for ($i = 0; $i < 3; $i++) {
            foreach ($testData[$i] as $formats) {
                $this->assertEquals($expected[$i], $formats[0]->parseDatetime($formats[2]));
                try {
                    $formats[0]->parseDatetime($formats[3]);
                    $this->fail();
                } catch (Exception $e) {}
            }
        }
    }
    
    /**
     * @covers Peach_DT_SimpleFormat::parseTimestamp
     */
    public function testParseTimestamp()
    {
        $testData = $this->getParseDataList();
        $expected = array(
            new Peach_DT_Timestamp(2012, 5, 21, 7, 30, 9),
            new Peach_DT_Timestamp(2012, 3, 7,  0,  0, 0),
            Peach_DT_Timestamp::now()->setAll(array("hour" => 8, "minute" => 6, "second" => 4)),
        );
        for ($i = 0; $i < 3; $i ++) {
            foreach ($testData[$i] as $formats) {
                $this->assertEquals($expected[$i], $formats[0]->parseTimestamp($formats[2]));
                try {
                    $formats[0]->parseTimestamp($formats[3]);
                    $this->fail();
                } catch (Exception $e) {}
            }
        }
    }
    
    /**
     * @covers Peach_DT_SimpleFormat::formatDate
     */
    public function testFormatDate()
    {
        $d    = new Peach_DT_Date(2012, 5, 21);
        $obj  = $this->getObjectList();
        $test = array(
            array("20120521000000", "2012年5月21日0時0分0秒", "Y=2012 n=5 j=21 H=00 i=00 s=00"),
            array("2012/05/21",  "2012.5.21"),
            array("00:00:00",  "0時0分0秒")
        );
        for ($i = 0; $i < 3; $i ++) {
            for ($j = 0, $len = count($test[$i]); $j < $len; $j ++) {
                $this->assertSame($test[$i][$j], $obj[$i][$j]->formatDate($d));
            }
        }
    }
    
    /**
     * @covers Peach_DT_SimpleFormat::formatDatetime
     */
    public function testFormatDatetime()
    {
        $d    = new Peach_DT_Datetime(2012, 5, 21, 7, 30);
        $obj  = $this->getObjectList();
        $test = array(
            array("20120521073000", "2012年5月21日7時30分0秒", "Y=2012 n=5 j=21 H=07 i=30 s=00"),
            array("2012/05/21",  "2012.5.21"),
            array("07:30:00",  "7時30分0秒")
        );
        for ($i = 0; $i < 3; $i ++) {
            for ($j = 0, $len = count($test[$i]); $j < $len; $j ++) {
                $this->assertSame($test[$i][$j], $obj[$i][$j]->formatDatetime($d));
            }
        }
    }
    
    /**
     * @covers Peach_DT_SimpleFormat::formatTimestamp
     */
    public function testFormatTimestamp()
    {
        $d    = new Peach_DT_Timestamp(2012, 5, 21, 7, 30, 9);
        $obj  = $this->getObjectList();
        $test = array(
            array("20120521073009", "2012年5月21日7時30分9秒", "Y=2012 n=5 j=21 H=07 i=30 s=09"),
            array("2012/05/21",  "2012.5.21"),
            array("07:30:09",  "7時30分9秒")
        );
        for ($i = 0; $i < 3; $i ++) {
            for ($j = 0, $len = count($test[$i]); $j < $len; $j ++) {
                $this->assertSame($test[$i][$j], $obj[$i][$j]->formatTimestamp($d));
            }
        }
    }
}
?>