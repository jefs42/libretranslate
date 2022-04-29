<?php
declare(strict_types=1);
namespace Jefs42;
use PHPUnit\Framework\TestCase;

final class texttranslateTest extends TestCase
{
    public function testDetectLanguage():void
    {
        $translate = new LibreTranslate('translate.fortytwo-it.com', 5000);
        $detect = $translate->detect("Mi nombre es Jefs42");
        $this->assertEquals('es', $detect);
    }
    public function testTranslateText_AvailableLanguages():void
    {
        $translate = new LibreTranslate('translate.fortytwo-it.com', 5000);
        $text = $translate->translate("My name is Jefs42");
        $this->assertEquals('Mi nombre es Jefs42', $text);
    }
    public function testTranslateMultipeTexts():void
    {
        $translate = new LibreTranslate('translate.fortytwo-it.com', 5000);
        $text = $translate->translate(["My name is Jefs42","Where is the bathroom"]);
        $this->assertIsArray($text, "Response is not an array");

    }
    public function testTranslateText_UnsupportedLanguage():void
    {
        $this->expectException(\Exception::class);
        $translate = new LibreTranslate('translate.fortytwo-it.com', 5000);
        $text = $translate->translate("My name is Jefs42",'en','da');
    }


}
