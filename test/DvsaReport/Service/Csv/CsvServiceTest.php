<?php

/**
 * CsvService Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaReportModuleTest\DvsaReport\Service\Csv;

use DvsaReport\Service\Csv\CsvService;
use PHPUnit\Framework\TestCase;

/**
 * CsvService Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CsvServiceTest extends TestCase
{
    /**
     * Test generate content from setData
     *
     * @dataProvider dataProvider
     *
     * @param array $data
     * @param mixed $expected
     *
     * @return void
     */
    public function testGenerateCsvFromSetData($data, $expected)
    {
        $csv = new CsvService();

        $csv->setResponse(new \Laminas\Http\Response());

        $csv->setData($data);

        $this->assertEquals($data, $csv->getData());

        $content = $csv->generateDocument('test.csv');

        $this->assertEquals($expected, $content->getContent());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function dataProvider()
    {
        return array(
            array(
                array(
                    array(
                        'foo' => 'bar',
                        'cake' => 'bar',
                        'mmm' => 'cake'
                    )
                ),
                '"foo","cake","mmm"
"bar","bar","cake"'
            ),
            array(
                array(
                    array(
                        'foo' => 'bar',
                        'cake' => 'bar',
                        'mmm' => 'cake'
                    ),
                    array(
                        'foo' => 'a',
                        'cake' => 'b',
                        'mmm' => 'c'
                    )
                ),
                '"foo","cake","mmm"
"bar","bar","cake"
"a","b","c"'
            ),
            array(
                array(
                    array(
                        'foo' => '<a href="">bar</a>',
                        'cake' => '£bar',
                        'mmm' => 'cake'
                    ),
                    array(
                        'foo' => 'a',
                        'cake' => 'b',
                        'mmm' => 'c'
                    )
                ),
                '"foo","cake","mmm"
"bar","bar","cake"
"a","b","c"'
            )
        );
    }
}
