<?php
declare(strict_types=1);
namespace Jefs42;
use PHPUnit\Framework\TestCase;

final class initialTest extends TestCase
{
    private $serverSettings;
    private $serverLanguages;


    public function testRetrieveServerSettings():void
    {
        $translate = new LibreTranslate('translate.fortytwo-it.com', 5000);
        $this->serverSettings = $translate->Settings();
        $this->assertIsArray($this->serverSettings, "Server settings unavailable");
    }
    public function testRetrieveAvailableLanguages():void
    {
        $translate = new LibreTranslate('translate.fortytwo-it.com', 5000);
        $this->serverSettings = $translate->Languages();
        $this->assertIsArray($this->serverSettings, "Server languages unavailable");
    }





}
