<?php

/**
 * Template Entity Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaDocumentModuleTest\DvsaDocument\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaDocument\Entity\Template;

/**
 * Template Entity Test
 *
 */
final class TemplateTest extends AbstractEntityTester
{
    protected $entityClass = Template::class;
    #[\Override]
    public function providerGettersAndSetters(): array
    {
        $testMethods = parent::providerGettersAndSetters();

        $testMethods[] = array('Documents', new ArrayCollection(), []);
        $testMethods[] = array('Variations', new ArrayCollection());

        return $testMethods;
    }
}
