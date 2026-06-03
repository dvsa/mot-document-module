<?php

declare(strict_types=1);

namespace DvsaDocumentModuleTest\DvsaDocument\Entity;

use DvsaDocument\Entity\Document;

/**
 * Document Entity Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class DocumentTest extends AbstractEntityTester
{
    protected $entityClass = Document::class;

    #[\Override]
    public function providerGettersAndSetters(): array
    {
        return parent::providerGettersAndSetters();
    }
}
