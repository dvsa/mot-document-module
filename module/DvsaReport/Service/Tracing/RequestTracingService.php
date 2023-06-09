<?php

namespace DvsaReport\Service\Tracing;

use \Laminas\Http\Request;
use \Laminas\Http\Headers;
use \DvsaApplicationLogger\Log\Logger;
/**
 * Created by PhpStorm.
 * User: radoslawl
 * Date: 20/03/2018
 * Time: 15:15
 */
class RequestTracingService
{
    const TRACE_ID_HEADER = "X-B3-TraceId";
    const PARENT_ID_HEADER = "X-B3-ParentSpanId";
    const SPAN_ID_HEADER = "X-B3-SpanId";

    const TRACING_HEADERS = [self::TRACE_ID_HEADER, self::PARENT_ID_HEADER, self::SPAN_ID_HEADER];

    private $logger;

    /**
     * RequestTracingService constructor.
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }


    /**
     * The method adds all headers necessary for tracing. At this point (27.03.18) the API is the first stage of tracing
     * it must generate traceId on its own. In the futuer, if any external client calls API the traceId will not be
     * regenerated, but used the same throughout entire request chain.
     */
    public function addAbsentTracingHeaders(Request $request) {
        foreach(self::TRACING_HEADERS as $header) {
            if(!$this->headerExists($request, $header)) {
                $request = $this->addHeader($request, $header, $this->create64BitIdAsHex());
            }
        }

        return $request;
    }

    public function addHeader(Request $request, String $headerName, String $headerValue) {
        $currentHeaders = $request->getHeaders()->addHeaders(array($headerName => $headerValue));

        $request->setHeaders($currentHeaders);

        return $request;
    }

    public function headerExists(Request $request, String $headerName) {
        return !($request->getHeader($headerName) === false);
    }

    public function updateTracingHeader(Request $request, String $headerName, String $value) {
        $headers = $request->getHeaders()->toArray();
        $headers[$headerName] = $value;

        $updatedHeaders = (new Headers())->addHeaders($headers);

        $request->setHeaders($updatedHeaders);

        return $request;
    }

    public function log(Request $request, String $tracingEvents) {
        $traceIdHeader = $request->getHeader(self::TRACE_ID_HEADER);
        $parentIdHeader = $request->getHeader(self::PARENT_ID_HEADER);
        $spanIdHeader = $request->getHeader(self::SPAN_ID_HEADER);

        $this->logger->info(sprintf("%s: %s %s: %s %s: %s Event: %s",
            $traceIdHeader->getFieldName(),
            $traceIdHeader->getFieldValue(),
            $parentIdHeader->getFieldName(),
            $parentIdHeader->getFieldValue(),
            $spanIdHeader->getFieldName(),
            $spanIdHeader->getFieldValue(),
            $tracingEvents));
    }

    public function create64BitIdAsHex()
    {
        return bin2hex(openssl_random_pseudo_bytes(8));
    }
}