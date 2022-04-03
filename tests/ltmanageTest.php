<?php
declare(strict_types=1);
namespace Jefs42;
use PHPUnit\Framework\TestCase;

final class ltmanageTest extends TestCase
{
    public function testCanLtManage():void
    {
        $translate = new LibreTranslate('http://167.99.221.187', 5000);
        $this->assertTrue($translate->canManage);
    }
    public function testCannotLtManage():void
    {
        $translate = new LibreTranslate('http://167.99.221.187', 5000);
        $this->assertFalse($translate->canManage);
    }
}