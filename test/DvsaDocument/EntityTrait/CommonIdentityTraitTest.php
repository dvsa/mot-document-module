<?php

namespace DvsaDocumentModuleTest\DvsaDocument\EntityTrait;

use DvsaDocument\EntityTrait\CommonIdentityTrait;
use PHPUnit\Framework\TestCase;

/**
 * Test for CommonIdentityTrait
 */
class CommonIdentityTraitTest extends TestCase
{
    public function testTrait()
    {
        /** @var CommonIdentityTrait $mock */
        $mock = $this->getMockBuilder('DvsaDocument\EntityTrait\CommonIdentityTrait')->disableOriginalConstructor()->getMockForTrait();
        $mock->setId(9999);
        $this->assertEquals(9999, $mock->getId());
    }
}
