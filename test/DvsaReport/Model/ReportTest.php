<?php

/**
 * Report Model Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaDocumentModuleTest\DvsaReport\Model;

use DvsaReport\Model\Report;

/**
 * Report Model Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ReportTest extends AbstractModelTester
{
    protected $modelClass = Report::class;

    /**
     * Test setName replaces slashes
     *
     * @return void
     */
    public function testSetNameReplacesSlashes(): void
    {
        $model = new Report();
        $model->setName('Name/With/Slashes.pdf');
        $this->assertEquals('Name-With-Slashes.pdf', $model->getName());
    }
}
