<?php

namespace DvsaReport\Service\HttpClient;

use Laminas\Http\Response;

/**
 * Created by PhpStorm.
 * User: radoslawl
 * Date: 16/02/2018
 * Time: 12:54
 */
class EnhancedLambdaHttpClientService extends LambdaHttpClientService
{

    const RETRIABLE_STATUS_CODES = array(
        Response::STATUS_CODE_429,
        Response::STATUS_CODE_503,
        Response::STATUS_CODE_504);

    protected $MAX_ATTEMPT_COUNT;
    protected $RETRY_DELAY_IN_SECONDS;

    protected $logger;

    /**
     * LambdaHttpClientWrapper constructor.
     */
    public function __construct($maxAttemptCount, $retryDelayInSeconds)
    {
        $this->MAX_ATTEMPT_COUNT = $maxAttemptCount;
        $this->RETRY_DELAY_IN_SECONDS = $retryDelayInSeconds;
    }

    /**
     * @param HttpClientServiceInterface $httpClient
     */
    public function dispatch() : Response
    {
        $attempt = 0;

        $response = null;
        while($attempt < $this->MAX_ATTEMPT_COUNT) {

            $this->delayNextRequest($attempt);

            $response = $this->client->dispatch($this->request);

            $statusCode = $response->getStatusCode();
            if ($statusCode == Response::STATUS_CODE_200) {
                $this->logger->info("Lambda service call successful!");
                return $response;
            } elseif (in_array($statusCode, self::RETRIABLE_STATUS_CODES)) {
                $this->logger->warn(sprintf("Attempt nr: %s of Lambda service call failed with response: \n %s",
                    ($attempt + 1), $response));
                $attempt++;
            } else {
                throw new \Exception($response);
            }
        }

        throw new \Exception(sprintf("Getting report failed after 3 attempts:\n %s", $response));
    }

    private function delayNextRequest($attempt) {
        $secondsToSleep = $attempt * $this->RETRY_DELAY_IN_SECONDS;
        set_time_limit(15);
        sleep($secondsToSleep);
    }
}