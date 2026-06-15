<?php

declare(strict_types=1);

namespace DvsaDocumentModuleTest\DvsaDocument\EntityTrait;

use DvsaDocument\EntityTrait\CommonIdentityTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for CommonIdentityTrait
 */
final class CommonIdentityTraitTest extends TestCase
{
    /**
     * The above is a trait not a class
     * @throws \Exception
     * @psalm-suppress UndefinedDocblockClass
     */
    public function testTrait(): void
    {
        // @phpstan-ignore-next-line
        /** @var CommonIdentityTrait&MockObject $mock */
        $mock = $this->getMockBuilder(CommonIdentityTrait::class)->disableOriginalConstructor()->getMockForTrait(); // @phpstan-ignore-line
        $mock->setId(9999);
        $this->assertEquals(9999, $mock->getId());
    }
}
