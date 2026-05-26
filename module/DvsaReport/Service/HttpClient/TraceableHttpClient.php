<?php

/**
 * Created by PhpStorm.
 * User: radoslawl
 * Date: 23/03/2018
 * Time: 13:36
 */

namespace DvsaReport\Service\HttpClient;

use DvsaReport\Service\Tracing\RequestTracingService;
use Laminas\Http\Client;
use Laminas\Stdlib;
use DvsaReport\Model\TracingEvents;

final class TraceableHttpClient extends Client
{
    /** @var RequestTracingService */
    protected $requestTracingService;

    /** @var string */
    protected $currentStageSpanId;

    /**
     * TraceableHttpClient constructor.
     */
    public function __construct(RequestTracingService $requestTracingService)
    {
        parent::__construct();

        $this->requestTracingService = $requestTracingService;
    }

    /**
     * Dispatch
     *
     * @param Stdlib\RequestInterface $request
     * @param Stdlib\ResponseInterface $response
     * @return Stdlib\ResponseInterface
     */
    #[\Override]
    public function dispatch(Stdlib\RequestInterface $request, Stdlib\ResponseInterface $response = null)
    {
        if (!($request instanceof \Laminas\Http\Request)) {
            throw new \Exception("Request is not instance of \Laminas\Http\Request");
        }
        $request = $this->requestTracingService->addAbsentTracingHeaders($request);

        if ($this->currentStageSpanId == false) {
            $this->currentStageSpanId = $this->requestTracingService->create64BitIdAsHex();
        }

        $request = $this->requestTracingService->updateTracingHeader(
            $request,
            RequestTracingService::PARENT_ID_HEADER,
            $this->currentStageSpanId
        );

        $request = $this->requestTracingService->updateTracingHeader(
            $request,
            RequestTracingService::SPAN_ID_HEADER,
            $this->requestTracingService->create64BitIdAsHex()
        );

        $this->requestTracingService->log($request, TracingEvents::CLIENT_SENT);
        $response = parent::dispatch($request);
        $this->requestTracingService->log($request, TracingEvents::CLIENT_RECEIVED);

        return $response;
    }
}
