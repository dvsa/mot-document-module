<?php

declare(strict_types=1);

namespace DvsaDocumentModuleTest\Logger;

use DvsaDocumentModuleTest\TestBootstrap;
use DvsaLogger\Logger\MotLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @phpstan-type LoggerWriter array{
 *     type: string,
 *     path?: string,
 *     formatter?: string,
 *     level?: string,
 *     enabled?: bool
 * }
 * @phpstan-type LoggerDefinition array{
 *     channel: string,
 *     environment_levels?: array<string, string>,
 *     writers: list<LoggerWriter>
 * }
 * @phpstan-type LoggerConfig array{
 *     loggers?: array<string, LoggerDefinition>,
 *     writers?: list<LoggerWriter>,
 *     channel?: string
 * }
 */
final class LoggerOutputTest extends TestCase
{
    private const LOGGER_NAME = 'mot-document-module';

    /**
     * @return array{mot_logger: array{loggers: array<string, LoggerDefinition>}}
     */
    private function getProductionConfig(): array
    {
        /** @var array{mot_logger: array{loggers: array<string, LoggerDefinition>}} */
        return require __DIR__ . '/../../config/autoload/mot-logger.local.php';
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMotLoggerConfigurationIsLoaded(): void
    {
        $serviceManager = TestBootstrap::getServiceManager();
        /** @var array{mot_logger: LoggerConfig} $config */
        $config = $serviceManager->get('Config');

        $this->assertArrayHasKey(
            'mot_logger',
            $config,
            'mot_logger configuration must be present in merged config'
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMotLoggerHasContent(): void
    {
        $serviceManager = TestBootstrap::getServiceManager();
        /** @var array{mot_logger: LoggerConfig} $config */
        $config = $serviceManager->get('Config');
        $motLoggerConfig = $config['mot_logger'];

        $hasLoggers = isset($motLoggerConfig['loggers']) && $motLoggerConfig['loggers'] !== [];
        $hasLegacyFormat = (isset($motLoggerConfig['writers']) || isset($motLoggerConfig['channel']));

        $this->assertTrue(
            $hasLoggers || $hasLegacyFormat,
            'mot_logger must have either loggers (new) or writers/channel (legacy) configured'
        );
    }

    public function testMotDocumentModuleLoggerConfiguredInProduction(): void
    {
        $productionConfig = $this->getProductionConfig();
        $motLoggerConfig = $productionConfig['mot_logger'];

        $this->assertArrayHasKey(
            'loggers',
            $motLoggerConfig,
            'Production config must have loggers section (new format)'
        );

        $loggers = $motLoggerConfig['loggers'];
        $this->assertArrayHasKey(
            self::LOGGER_NAME,
            $loggers,
            'mot-document-module logger must be configured in production'
        );
    }

    public function testMotDocumentModuleLoggerHasCorrectChannel(): void
    {
        $productionConfig = $this->getProductionConfig();
        $logger = $productionConfig['mot_logger']['loggers'][self::LOGGER_NAME];

        $this->assertArrayHasKey(
            'channel',
            $logger,
            'mot-document-module logger must have channel defined'
        );

        $this->assertEquals(
            self::LOGGER_NAME,
            $logger['channel'],
            'mot-document-module logger channel must be "mot-document-module"'
        );
    }

    public function testMotDocumentModuleLoggerHasWritersConfigured(): void
    {
        $productionConfig = $this->getProductionConfig();
        $logger = $productionConfig['mot_logger']['loggers'][self::LOGGER_NAME];

        $this->assertArrayHasKey(
            'writers',
            $logger,
            'mot-document-module logger must have writers configured'
        );

        $writers = $logger['writers'];
        $this->assertNotEmpty(
            $writers,
            'mot-document-module logger must have at least one writer'
        );
    }

    public function testMotDocumentModuleLoggerWritersHaveCorrectStructure(): void
    {
        $productionConfig = $this->getProductionConfig();
        $writers = $productionConfig['mot_logger']['loggers'][self::LOGGER_NAME]['writers'];

        foreach ($writers as $idx => $writer) {
            $this->assertArrayHasKey(
                'type',
                $writer,
                "Writer [$idx] must have 'type' field"
            );

            $this->assertContains(
                $writer['type'],
                ['stream', 'database'],
                "Writer [$idx] type must be 'stream' or 'database', got: {$writer['type']}"
            );

            if ($writer['type'] === 'stream') {
                $this->assertArrayHasKey(
                    'path',
                    $writer,
                    "Stream writer [$idx] must have 'path' configured"
                );

                if (isset($writer['formatter'])) {
                    $this->assertContains(
                        $writer['formatter'],
                        ['pipe', 'json'],
                        "Stream writer [$idx] formatter must be 'pipe' or 'json'"
                    );
                }
            }
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testNamedLoggerServiceCanBeRetrieved(): void
    {
        $serviceManager = TestBootstrap::getServiceManager();

        $this->assertTrue(
            $serviceManager->has(self::LOGGER_NAME),
            'mot-document-module named logger service must be resolvable from the container'
        );

        /** @var MotLogger $logger */
        $logger = $serviceManager->get(self::LOGGER_NAME);
        $this->assertInstanceOf(MotLogger::class, $logger);
    }
}
