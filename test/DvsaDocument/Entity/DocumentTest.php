<?php

/**
 * Document Entity Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaDocumentModuleTest\DvsaDocument\Entity;

use DvsaDocument\Entity\Document;

/**
 * Document Entity Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DocumentTest extends AbstractEntityTester
{
    /**
     * Holds the entity class name
     *
     * @var class-string
     */
    protected $entityClass = Document::class;

    public function providerGettersAndSetters()
    {
        $testMethods = parent::providerGettersAndSetters();

        return $testMethods;
    }
}
