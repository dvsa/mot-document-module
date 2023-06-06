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
     * @var string
     */
    protected $modelClass = '\DvsaReport\Model\Report';

    /**
     * Test setName replaces slashes
     */
    public function testSetNameReplacesSlashes()
    {
        $model = new \DvsaReport\Model\Report();
        $model->setName('Name/With/Slashes.pdf');
        $this->assertEquals('Name-With-Slashes.pdf', $model->getName());
    }
}
