<?php

declare(strict_types=1);

namespace DvsaReportModuleTest\DvsaReport\Service\Csv;

use DvsaReport\Service\Encoder\ParamsEncoder;
use PHPUnit\Framework\TestCase;

final class ParamsEncoderTest extends TestCase
{
    /**
     * Test ParamsEncoder data handling
     *
     * @dataProvider dataProvider
     */
    public function testEncodingProvidedData(array $data, mixed $expected): void
    {
        $paramsEncoder = new ParamsEncoder();

        $encodedData = $paramsEncoder->arrayToJson($data);

        $this->assertEquals($expected, $encodedData);
    }

    /**
     * Data provider
     */
    public function dataProvider(): array
    {
        return [
            [
                //Data
                ["person" => '{"name":"Bob"}'],
                //Expected
                '{"person":{"name":"Bob"}}'
            ],
            [
                //Data
                [
                    "person" => [
                        "name" => "Bob"
                    ]
                ],
                //Expected
                '{"person":{"name":"Bob"}}'
            ],
            [
                //Data
                [
                    "person" => 1
                ],
                //Expected
                '{"person":1}'
            ],
            [
                //Data
                [
                    "person" => true
                ],
                //Expected
                '{"person":true}'
            ],
            [
                //Data
                [
                    "person" => "yes"
                ],
                //Expected
                '{"person":"yes"}'
            ],
            [
                // Ensure that a string that looks like a number isn't treated as such.
                [
                    "notANumber" => '2E12345'
                ],
                //Expected
                '{"notANumber":"2E12345"}'
            ],
        ];
    }
}
