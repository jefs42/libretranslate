<?php
declare(strict_types=1);
namespace Jefs42;
use PHPUnit\Framework\TestCase;

final class fileTranslateTest extends TestCase
{

    public function testTranslateFile_Success():void{
        $translate = new LibreTranslate('translate.fortytwo-it.com', 5000);

        $file = __DIR__ . "/test-file1.txt";
        $translatedFileText = $translate->translateFiles($file);
        print $translatedFileText;
        $this->assertIsString($translatedFileText);
    }

    public function testTranslateFile_InvalidFile():void{
        $translate = new LibreTranslate('translate.fortytwo-it.com', 5000);

        $this->expectException(\Exception::class);
        $file = __DIR__ . "/test-file42.txt";
        $translatedFileText = $translate->translateFiles($file);
    }

}
