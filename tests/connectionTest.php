<?php
declare(strict_types=1);
namespace Jefs42;
use PHPUnit\Framework\TestCase;

final class connectionTest extends TestCase
{
    public function testCanConnectToServer() : void
    {
        $translate = new LibreTranslate('http://167.99.221.187','5000');
        $this->assertInstanceOf(LibreTranslate::class, $translate);
    }
    public function testCannotConnectToServer() : void
    {
        $this->expectException(\Exception::class);
        $translate = new LibreTranslate('http://167.99.221.187','5005');
    }

}