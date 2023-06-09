<?php

/**
 * PdfService Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace DvsaReportModuleTest\DvsaReport\Service\Pdf;

use DvsaReport\Service\Pdf\PdfService;
use PHPUnit\Framework\TestCase;

/**
 * PdfService Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PdfServiceTest extends TestCase
{

    /**
     * Test generate document
     */
    public function testGenerateDocument()
    {
        $pdf = $this->getMockBuilder('\DvsaReport\Service\Pdf\PdfService')->disableOriginalConstructor()->setMethods(array('generateUsingWkHtmlToPdf'))->getMock();

        $pdf->setTmpDir(__DIR__);

        $this->assertEquals(__DIR__, $pdf->getTmpDir());

        $pdf->expects($this->once())
            ->method('generateUsingWkHtmlToPdf')
            ->will($this->returnValue('PDF CONTENT'));

        $pdf->setResponse(new \Laminas\Http\Response());

        $pdf->setHtml('<h1>Test</h1>');

        $this->assertEquals('<h1>Test</h1>', $pdf->getHtml());

        $response = $pdf->generateDocument('test.pdf');

        $this->assertInstanceOf('\Laminas\Http\Response', $response);

        $this->assertEquals('PDF CONTENT', $response->getContent());
    }

    public function testGenerateDocumentCantWrite()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Failed to create temporary html file");
        $pdf = new PdfService();

        $pdf->setTmpDir('/some/fake/dir');

        $pdf->setResponse(new \Laminas\Http\Response());

        $pdf->setHtml('<h1>Test</h1>');

        $pdf->generateDocument('test.pdf');
    }

    /**
     * Test replaceWebRoot
     *
     * @dataProvider dataProviderForReplaceWebRoot
     */
    public function testReplaceWebRoot($input, $base, $expected)
    {
        $pdfService = new PdfService();

        $output = $pdfService->replaceWebRoot($input, $base);

        $this->assertEquals($expected, $output);
    }

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
