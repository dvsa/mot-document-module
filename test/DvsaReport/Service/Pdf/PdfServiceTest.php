<?php

/**
 * PdfService Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaReportModuleTest\DvsaReport\Service\Pdf;

use DvsaReport\Service\Pdf\PdfService;
use Exception;
use Laminas\Http\Response;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * PdfService Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PdfServiceTest extends TestCase
{
    /**
     * Test generate document using dompdf
     *
     * @return void
     */
    public function testGenerateDocumentUsingDompdf()
    {
        $pdf = $this->getMockBuilder(PdfService::class)->disableOriginalConstructor()->onlyMethods(array('generateUsingDompdf'))->getMock();

        $pdf->expects($this->once())
            ->method('generateUsingDompdf')
            ->will($this->returnValue('PDF CONTENT'));

        $pdf->setResponse(new Response());

        $pdf->setHtml('<h1>Test</h1>');

        $this->assertEquals('<h1>Test</h1>', $pdf->getHtml());

        $response = $pdf->generateDocument('test.pdf');

        $this->assertInstanceOf(Response::class, $response);

        $this->assertEquals('PDF CONTENT', $response->getContent());
    }

    /**
     * Test generate document using dompdf
     *
     * @return void
     */
    public function testGenerateDocumentExceptionUsingDompdf()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to generate PDF');

        $pdf = $this->getMockBuilder(PdfService::class)->disableOriginalConstructor()->onlyMethods(array('generateUsingDompdf'))->getMock();

        $pdf->expects($this->once())
            ->method('generateUsingDompdf')
            ->will($this->returnValue(null));

        $pdf->setResponse(new Response());

        $pdf->setHtml('<h1>Test</h1>');

        $this->assertEquals('<h1>Test</h1>', $pdf->getHtml());

        $pdf->generateDocument('test.pdf');
    }

    /**
     * Test replaceWebRoot
     *
     * @dataProvider dataProviderForReplaceWebRoot
     *
     * @param null|string $input
     * @param string $base
     * @param mixed $expected
     *
     * @return void
     */
    public function testReplaceWebRoot($input, $base, $expected)
    {
        $pdfService = new PdfService();

        $output = $pdfService->replaceWebRoot($input, $base);

        $this->assertEquals($expected, $output);
    }

    /**
     * @return array
     */
    public function dataProviderForReplaceWebRoot()
    {
        return array(
            array(
                '<img blah="foo" src="/something.png" foo="blah"/>',
                'http://somewhere.com/',
                '<img blah="foo" src="http://somewhere.com/something.png" foo="blah"/>'
            ),
            array(
                '<link blah="foo" href="/something/somewhere-else.css" foo="blah"/>',
                'http://somewhere.com/',
                '<link blah="foo" href="http://somewhere.com/something/somewhere-else.css" foo="blah"/>'
            ),
            array(
                '<script blah="foo" src="/something-else.js" foo="blah"/>',
                'http://somewhere.com/',
                '<script blah="foo" src="http://somewhere.com/something-else.js" foo="blah"/>'
            ),
            array(
                '<script blah="foo" src="/something-else.js" foo="blah"/>
<link blah="foo" href="/something/somewhere-else.css" foo="blah"/>
<img blah="foo" src="/something.png" foo="blah"/>',
                'http://somewhere.com/',
                '<script blah="foo" src="http://somewhere.com/something-else.js" foo="blah"/>
<link blah="foo" href="http://somewhere.com/something/somewhere-else.css" foo="blah"/>
<img blah="foo" src="http://somewhere.com/something.png" foo="blah"/>'
            ),
            array(
                '<img src="http://leave-me-along" />
<script blah="foo" src="/something-else.js" foo="blah"/>
<link blah="foo" href="/something/somewhere-else.css" foo="blah"/>
<img blah="foo" src="/something.png" foo="blah"/>',
                'http://somewhere.com/',
                '<img src="http://leave-me-along" />
<script blah="foo" src="http://somewhere.com/something-else.js" foo="blah"/>
<link blah="foo" href="http://somewhere.com/something/somewhere-else.css" foo="blah"/>
<img blah="foo" src="http://somewhere.com/something.png" foo="blah"/>'
            ),
            array(
                '<img src="http://leave-me-along" />',
                'http://somewhere.com/',
                '<img src="http://leave-me-along" />'
            )
        );
    }
}
