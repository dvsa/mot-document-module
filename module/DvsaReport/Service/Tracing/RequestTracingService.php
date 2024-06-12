<?php

namespace DvsaReport\Service\Tracing;

use Laminas\Http\Request;
use Laminas\Http\Headers;

/**
 * Created by PhpStorm.
 * User: radoslawl
 * Date: 20/03/2018
 * Time: 15:15
 */
class RequestTracingService
{
    public const TRACE_ID_HEADER = "X-B3-TraceId";
    public const PARENT_ID_HEADER = "X-B3-ParentSpanId";
    public const SPAN_ID_HEADER = "X-B3-SpanId";

    public const TRACING_HEADERS = [self::TRACE_ID_HEADER, self::PARENT_ID_HEADER, self::SPAN_ID_HEADER];

    /**
     * @var \Laminas\Log\Logger
     */
    private $logger;

    /**
     * RequestTracingService constructor.
     *
     * @param \Laminas\Log\Logger $logger
     */
    public function __construct($logger)
    {
        $this->logger = $logger;
    }


    /**
     * The method adds all headers necessary for tracing. At this point (27.03.18) the API is the first stage of tracing
     * it must generate traceId on its own. In the futuer, if any external client calls API the traceId will not be
     * regenerated, but used the same throughout entire request chain.
     *
     * @return Request
     */
    public function addAbsentTracingHeaders(Request $request)
    {
        foreach (self::TRACING_HEADERS as $header) {
            if (!$this->headerExists($request, $header)) {
                $request = $this->addHeader($request, $header, $this->create64BitIdAsHex());
            }
        }

        return $request;
    }

    /**
     * @return Request
     */
    public function addHeader(Request $request, string $headerName, string $headerValue)
    {
        /** @var Headers $currentHeaders */
        $currentHeaders = $request->getHeaders();
        $currentHeaders = $currentHeaders->addHeaders(array($headerName => $headerValue));

        $request->setHeaders($currentHeaders);

        return $request;
    }

    /**
     * @return bool
     */
    public function headerExists(Request $request, string $headerName)
    {
        return !($request->getHeader($headerName) === false);
    }

    /**
     * @return Request
     */
    public function updateTracingHeader(Request $request, string $headerName, string $value)
    {
        /** @var Headers */
        $headers = $request->getHeaders();
        $headersArr = $headers->toArray();
        $headersArr[$headerName] = $value;

        $updatedHeaders = (new Headers())->addHeaders($headersArr);

        $request->setHeaders($updatedHeaders);

        return $request;
    }

    public function log(Request $request, string $tracingEvents): void
    {
        /** @var \Laminas\Http\Header\HeaderInterface */
        $traceIdHeader = $request->getHeader(self::TRACE_ID_HEADER);
        /** @var \Laminas\Http\Header\HeaderInterface */
        $parentIdHeader = $request->getHeader(self::PARENT_ID_HEADER);
        /** @var \Laminas\Http\Header\HeaderInterface */
        $spanIdHeader = $request->getHeader(self::SPAN_ID_HEADER);

        $this->logger->info(sprintf(
            "%s: %s %s: %s %s: %s Event: %s",
            $traceIdHeader->getFieldName(),
            $traceIdHeader->getFieldValue(),
            $parentIdHeader->getFieldName(),
            $parentIdHeader->getFieldValue(),
            $spanIdHeader->getFieldName(),
            $spanIdHeader->getFieldValue(),
            $tracingEvents
        ));
    }

    public function create64BitIdAsHex(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(8));
    }
}
