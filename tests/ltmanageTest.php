<?php
declare(strict_types=1);
namespace Jefs42;
use PHPUnit\Framework\TestCase;

final class ltmanageTest extends TestCase
{
    public function testCanLtManage():void
    {
        $translate = new LibreTranslate('translate.fortytwo-it.com', 5000);
        $this->assertTrue($translate->canManage);
    }
}