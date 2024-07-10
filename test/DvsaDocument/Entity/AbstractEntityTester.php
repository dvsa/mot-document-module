<?php

/**
 * Abstract entity tester
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaDocumentModuleTest\DvsaDocument\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * Abstract entity tester
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractEntityTester extends TestCase
{
    /**
     * Holds the entity
     *
     * @var object
     */
    protected $entity;

    /**
     * Holds the entity class name
     *
     * @var class-string
     */
    protected $entityClass;

    /**
     * @var array
     */
    protected $testMethods = [];

    /**
     * @return class-string
     */
    public function getClassToTestName()
    {
        return $this->entityClass;
    }

    /**
     * @dataProvider providerGettersAndSetters
     *
     * @param string $methodName
     * @param mixed $testValue
     * @param mixed $defValue
     *
     * @return void
     */
    public function testGettersAndSetters($methodName, $testValue, $defValue = null)
    {
        $classToTestName = $this->getClassToTestName();
        $entity = new $classToTestName();

        //  -- check initial value is null  --
        $actualDefValue = $entity->{'get' . $methodName}();

        if ($actualDefValue instanceof ArrayCollection) {
            $actualDefValue = $actualDefValue->toArray();
        }
        $this->assertSame($defValue, $actualDefValue);

        //  -- check set and get  --
        $entity->{'set' . $methodName}($testValue);
        $this->assertSame($testValue, $entity->{'get' . $methodName}());
    }

    /**
     * @return array
     */
    public function providerGettersAndSetters()
    {
        $classToTestName = $this->getClassToTestName();
        $reflection = new \ReflectionClass($classToTestName);

        $methods = $reflection->getMethods();

        //  --  common Entity methods  --
        $this->testMethods = [
            ['CreatedBy', 1],
            ['CreatedOn', new \DateTime()],
            ['LastUpdatedBy', 2],
            ['LastUpdatedOn', new \DateTime()],
            ['Version', rand(1, 100000), 1],
        ];

        //  --  class methods   --
        foreach ($methods as $method) {
            if (substr($method->getName(), 0, 3) == 'set') {
                $methodName = substr($method->getName(), 3);

                if (
                    (ltrim($method->getDeclaringClass()->getName(), "\\") == ltrim($classToTestName, "\\"))
                    && $method->isPublic()
                    && $reflection->hasProperty(lcfirst($methodName))
                    && $reflection->hasMethod('get' . $methodName)
                ) {
                    // If this $parameter->getClass() is not null, one of the methods is type-hinted.
                    foreach ($method->getParameters() as $parameter) {
                        if ($parameter->getType() !== null) {
                            continue 2;
                        }
                    }

                    if ($methodName == 'Id') {
                        $testValue = rand(10000, 200000);
                    } elseif ($methodName == 'IsDeleted') {
                        $testValue = 1;
                    } else {
                        $testValue = $methodName . '_test_' . rand(10000, 200000);
                    }

                    $this->testMethods[] = [$methodName, $testValue];
                }
            }
        }

        return $this->testMethods;
    }
}
