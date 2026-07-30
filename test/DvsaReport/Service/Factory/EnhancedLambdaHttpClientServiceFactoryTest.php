<?php

declare(strict_types=1);

namespace DvsaReportModuleTest\DvsaReport\Service\Factory;

use DvsaLogger\Logger\MotLogger;
use DvsaReport\Service\Factory\EnhancedLambdaHttpClientServiceFactory;
use DvsaReport\Service\HttpClient\EnhancedLambdaHttpClientService;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;

final class EnhancedLambdaHttpClientServiceFactoryTest extends TestCase
{
    private EnhancedLambdaHttpClientServiceFactory $factory;

    private ContainerInterface&MockObject $container;

    private MotLogger&MockObject $logger;

    /** @var array<string, array<string, mixed>> */
    private array $validConfig;

    #[\Override]
    public function setUp(): void
    {
        $this->factory = new EnhancedLambdaHttpClientServiceFactory();

        $this->logger = $this->getMockBuilder(MotLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();

        $this->validConfig = [
            'certificate_generation' => [
                'headers'                  => ['Content-Type' => 'application/json'],
                'uri'                      => 'https://example.com/api',
                'x-api-key'               => 'test-api-key',
                'max_request_attempt_count' => 3,
                'request_attempt_delay'    => 100,
                'adapter'                  => \Laminas\Http\Client\Adapter\Curl::class,
            ],
        ];
    }

    public function testInvokeReturnsEnhancedLambdaHttpClientService(): void
    {
        $this->container->method('get')
            ->willReturnMap([
                ['Config', $this->validConfig],
                ['Application\Logger', $this->logger],
            ]);

        $result = ($this->factory)($this->container, EnhancedLambdaHttpClientService::class);

        $this->assertInstanceOf(EnhancedLambdaHttpClientService::class, $result);
    }

    public function testInvokeWithClientOptionsCallsSetOptions(): void
    {
        $configWithOptions = $this->validConfig;
        $certGenConfig = $configWithOptions['certificate_generation'];
        $certGenConfig['client_options'] = ['timeout' => 30];
        $configWithOptions['certificate_generation'] = $certGenConfig;

        $this->container->method('get')
            ->willReturnMap([
                ['Config', $configWithOptions],
                ['Application\Logger', $this->logger],
            ]);

        $result = ($this->factory)($this->container, EnhancedLambdaHttpClientService::class);

        $this->assertInstanceOf(EnhancedLambdaHttpClientService::class, $result);
    }

    public function testInvokeThrowsExceptionWhenRootConfigKeyMissing(): void
    {
        $this->container->method('get')
            ->with('Config')
            ->willReturn([]);

        $this->expectException(RuntimeException::class);

        ($this->factory)($this->container, EnhancedLambdaHttpClientService::class);
    }

    /**
     * @dataProvider provideRequiredConfigKeys
     */
    public function testInvokeThrowsExceptionWhenRequiredConfigKeyMissing(string $missingKey): void
    {
        $config = $this->validConfig;
        $certGenConfig = $config['certificate_generation'];
        unset($certGenConfig[$missingKey]);
        $config['certificate_generation'] = $certGenConfig;

        $this->container->method('get')
            ->willReturnMap([
                ['Config', $config],
                ['Application\Logger', $this->logger],
            ]);

        $this->expectException(RuntimeException::class);

        ($this->factory)($this->container, EnhancedLambdaHttpClientService::class);
    }

    public function provideRequiredConfigKeys(): array
    {
        return array_map(
            fn(string $key) => [$key],
            EnhancedLambdaHttpClientServiceFactory::REQUIRED_CONFIG_KEYS
        );
    }

    public function testObtainLoggerThrowsExceptionWhenLoggerNotFoundInContainer(): void
    {
        $this->container->method('get')
            ->willReturnCallback(function (string $id) {
                if ($id === 'Config') {
                    return $this->validConfig;
                }
                throw new ServiceNotFoundException('Service not found: ' . $id);
            });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('\DvsaLogger\Logger\MotLogger instance expected in ServiceLocator under Application\Logger');

        ($this->factory)($this->container, EnhancedLambdaHttpClientService::class);
    }

    public function testObtainLoggerThrowsExceptionWhenLoggerIsNotMotLoggerInstance(): void
    {
        $invalidLogger = new \stdClass();

        $this->container->method('get')
            ->willReturnMap([
                ['Config', $this->validConfig],
                ['Application\Logger', $invalidLogger],
            ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('\DvsaLogger\Logger\MotLogger instance expected in ServiceLocator under Application\Logger');

        ($this->factory)($this->container, EnhancedLambdaHttpClientService::class);
    }

    public function testObtainLoggerThrowsExceptionWhenLoggerIsNull(): void
    {
        $this->container->method('get')
            ->willReturnMap([
                ['Config', $this->validConfig],
                ['Application\Logger', null],
            ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('\DvsaLogger\Logger\MotLogger instance expected in ServiceLocator under Application\Logger');

        ($this->factory)($this->container, EnhancedLambdaHttpClientService::class);
    }
}
