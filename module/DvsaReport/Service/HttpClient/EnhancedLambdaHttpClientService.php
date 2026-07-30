<?php

namespace DvsaReport\Service\HttpClient;

use Laminas\Http\Response;
use DvsaLogger\Logger\MotLogger;

/**
 * Created by PhpStorm.
 * User: radoslawl
 * Date: 16/02/2018
 * Time: 12:54
 * @psalm-suppress ClassMustBeFinal cannot be final or tests would need overhall
 */
class EnhancedLambdaHttpClientService extends LambdaHttpClientService
{
    public const RETRIABLE_STATUS_CODES = array(
        Response::STATUS_CODE_429,
        Response::STATUS_CODE_503,
        Response::STATUS_CODE_504);

    protected int $MAX_ATTEMPT_COUNT;

    protected int $RETRY_DELAY_IN_SECONDS;

    protected MotLogger $logger;

    /**
     * LambdaHttpClientWrapper constructor.
     */
    public function __construct(int $maxAttemptCount, int $retryDelayInSeconds)
    {
        $this->MAX_ATTEMPT_COUNT = $maxAttemptCount;
        $this->RETRY_DELAY_IN_SECONDS = $retryDelayInSeconds;
    }

    #[\Override]
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

    private function delayNextRequest(int $attempt): void
    {
        $secondsToSleep = $attempt * $this->RETRY_DELAY_IN_SECONDS;
        set_time_limit(15);
        sleep(max(0, $secondsToSleep));
    }
}
