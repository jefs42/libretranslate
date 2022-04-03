<?php
declare(strict_types=1);
namespace Jefs42;
use PHPUnit\Framework\TestCase;

final class fileTranslateTest extends TestCase
{

    public function testTranslateFile_Success():void{
        $translate = new LibreTranslate('http://167.99.221.187', 5000);
        $translate->setApiKey('740fd0fb-e664-439f-9509-705ea84aa99c');

        $file = __DIR__ . "/test-file1.txt";
        $translatedFileText = $translate->translateFiles($file);
        $this->assertIsString($translatedFileText);
    }

    public function testTranslateFile_InvalidFile():void{
        $translate = new LibreTranslate('http://167.99.221.187', 5000);
        $translate->setApiKey('740fd0fb-e664-439f-9509-705ea84aa99c');

        $this->expectException(\Exception::class);
        $file = __DIR__ . "/test-file42.txt";
        $translatedFileText = $translate->translateFiles($file);
    }

}
