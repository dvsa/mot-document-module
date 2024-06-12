<?php

/**
 * Report Model Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaDocumentModuleTest\DvsaReport\Model;

/**
 * Report Model Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReportTest extends AbstractModelTester
{
    /**
     * Holds the model class name
     *
     * @var class-string
     */
    protected $modelClass = \DvsaReport\Model\Report::class;

    /**
     * Test setName replaces slashes
     *
     * @return void
     */
    public function testSetNameReplacesSlashes()
    {
        $model = new \DvsaReport\Model\Report();
        $model->setName('Name/With/Slashes.pdf');
        $this->assertEquals('Name-With-Slashes.pdf', $model->getName());
    }
}
