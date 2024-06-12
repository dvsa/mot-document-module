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
    public const RETRIABLE_STATUS_CODES = array(
        Response::STATUS_CODE_429,
        Response::STATUS_CODE_503,
        Response::STATUS_CODE_504);

    /** @var integer */
    protected $MAX_ATTEMPT_COUNT;
    /** @var integer */
    protected $RETRY_DELAY_IN_SECONDS;

    /** @var \Laminas\Log\Logger */
    protected $logger;

    /**
     * LambdaHttpClientWrapper constructor.
     *
     * @param int $maxAttemptCount
     * @param int $retryDelayInSeconds
     */
    public function __construct($maxAttemptCount, $retryDelayInSeconds)
    {
        $this->MAX_ATTEMPT_COUNT = $maxAttemptCount;
        $this->RETRY_DELAY_IN_SECONDS = $retryDelayInSeconds;
    }

    public function dispatch(): Response
    {
        $attempt = 0;

        $response = null;
        while ($attempt < $this->MAX_ATTEMPT_COUNT) {
            $this->delayNextRequest($attempt);

            /** @var Response */
            $response = $this->client->dispatch($this->request);

            $statusCode = $response->getStatusCode();
            if ($statusCode == Response::STATUS_CODE_200) {
                $this->logger->info("Lambda service call successful!");
                return $response;
            } elseif (in_array($statusCode, self::RETRIABLE_STATUS_CODES)) {
                $this->logger->warn(sprintf(
                    "Attempt nr: %s of Lambda service call failed with response: \n %s",
                    ($attempt + 1),
                    $response
                ));
                $attempt++;
            } else {
                throw new \Exception((string) $response);
            }
        }

        if (is_null($response)) {
            throw new \Exception("Getting report failed after 3 attempts");
        }

        throw new \Exception(sprintf("Getting report failed after 3 attempts:\n %s", $response));
    }

    /**
     * @param int $attempt
     */
    private function delayNextRequest($attempt): void
    {
        $secondsToSleep = $attempt * $this->RETRY_DELAY_IN_SECONDS;
        set_time_limit(15);
        sleep(max(0, $secondsToSleep));
    }
}
