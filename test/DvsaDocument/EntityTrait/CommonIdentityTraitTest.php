<?php

namespace DvsaDocumentModuleTest\DvsaDocument\EntityTrait;

use DvsaDocument\EntityTrait\CommonIdentityTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for CommonIdentityTrait
 */
class CommonIdentityTraitTest extends TestCase
{
    /**
     * @return void
     *
     * @psalm-suppress UndefinedDocblockClass
     * The above is a trait not a class
    */
    public function testTrait()
    {
        // @phpstan-ignore-next-line
        /** @var CommonIdentityTrait&MockObject $mock */
        $mock = $this->getMockBuilder(CommonIdentityTrait::class)->disableOriginalConstructor()->getMockForTrait(); // @phpstan-ignore-line
        $mock->setId(9999);
        $this->assertEquals(9999, $mock->getId());
    }
}
