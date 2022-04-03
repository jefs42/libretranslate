<?php
declare(strict_types=1);
namespace Jefs42;
use PHPUnit\Framework\TestCase;

final class texttranslateTest extends TestCase
{
    public function testDetectLanguage():void
    {
        $translate = new LibreTranslate('http://167.99.221.187', 5000);
        $translate->setApiKey('740fd0fb-e664-439f-9509-705ea84aa99c');
        $detect = $translate->detect("Mi nombre es Jefs42");
        $this->assertEquals('es', $detect);
    }
    public function testTranslateText_AvailableLanguages():void
    {
        $translate = new LibreTranslate('http://167.99.221.187', 5000);
        $translate->setApiKey('740fd0fb-e664-439f-9509-705ea84aa99c');
        $text = $translate->translate("My name is Jefs42");
        $this->assertEquals('Mi nombre es Jefs42', $text);
    }
    public function testTranslateText_UnsupportedLanguage():void
    {
        $this->expectException(\Exception::class);
        $translate = new LibreTranslate('http://167.99.221.187', 5000);
        $translate->setApiKey('740fd0fb-e664-439f-9509-705ea84aa99c');
        $text = $translate->translate("My name is Jefs42",'en','da');
    }


}
