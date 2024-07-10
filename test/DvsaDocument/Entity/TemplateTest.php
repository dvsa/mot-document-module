<?php

/**
 * Template Entity Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaDocumentModuleTest\DvsaDocument\Entity;

use DvsaDocument\Entity\Template;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Template Entity Test
 *
 */
class TemplateTest extends AbstractEntityTester
{
    /**
     * Holds the entity class name
     *
     * @var class-string
     */
    protected $entityClass = Template::class;

    public function providerGettersAndSetters()
    {
        $testMethods = parent::providerGettersAndSetters();

        $testMethods[] = array('Documents', new ArrayCollection(), []);
        $testMethods[] = array('Variations', new ArrayCollection());

        return $testMethods;
    }
}
