<?php

namespace unit;

use app\components\HTMLTools;
use Yii;
use Codeception\Specify;

class HTMLNormalizeTest extends TestBase
{
    use Specify;

    public function testCreateParagraphs()
    {
        $orig     = "<p>Test<br><span style='color: red;'>Test2</span><br><span></span>";
        $expect = "<p>Test<br>\nTest2<br>\n";

        $orig .= "<strong onClick=\"alert('Alarm!');\">Test3</strong><br><br>\r\r";
        $expect .= "<strong>Test3</strong><br>\n<br>\n";

        $orig .= "Test4</p><ul><li>Test</li>\r";
        $expect .= "Test4</p>\n<ul>\n<li>Test</li>\n";

        $orig .= "<li>Test2\n<s>Test3</s></li>\r\n\r\n</ul>";
        $expect .= "<li>Test2\nTest3</li>\n</ul>\n";

        $orig .= "<a href='http://www.example.org/'>Example</a><u>Underlined</u>";
        $expect .= "<a href=\"http://www.example.org/\">Example</a>Underlined";

        $orig .= "<!-- Comment -->";
        $expect .= "";

        $out = HTMLTools::cleanSimpleHtml($orig);
        $this->assertEquals($expect, $out);
    }

    public function testAllowUnderlines()
    {
        $orig     = "<span class='underline'>Underlined</span> Normal";
        $expect = '<span class="underline">Underlined</span> Normal';

        $out = HTMLTools::cleanSimpleHtml($orig);
        $this->assertEquals($expect, $out);
    }

    public function testAllowStrike()
    {
        $orig     = "<span class='strike'>Strike</span> Normal";
        $expect = '<span class="strike">Strike</span> Normal';

        $out = HTMLTools::cleanSimpleHtml($orig);
        $this->assertEquals($expect, $out);
    }

    public function testAllowSubscript()
    {
        $orig     = "<span class='subscript'>Subscript</span> Normal";
        $expect = '<span class="subscript">Subscript</span> Normal';

        $out = HTMLTools::cleanSimpleHtml($orig);
        $this->assertEquals($expect, $out);
    }

    public function testSuberscript()
    {
        $orig     = "<span class='superscript'>Superscript</span> Normal";
        $expect = '<span class="superscript">Superscript</span> Normal';

        $out = HTMLTools::cleanSimpleHtml($orig);
        $this->assertEquals($expect, $out);
    }

    public function testStripUnknown()
    {
        $orig     = "<span class='unknown'>Strike</span> Normal";
        $expect = 'Strike Normal';

        $out = HTMLTools::cleanSimpleHtml($orig);
        $this->assertEquals($expect, $out);


        $orig     = "<span class='strike unknown'>Strike</span> Normal";
        $expect = '<span class="strike">Strike</span> Normal';

        $out = HTMLTools::cleanSimpleHtml($orig);
        $this->assertEquals($expect, $out);
    }
}
