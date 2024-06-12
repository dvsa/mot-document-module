<?php

/**
 * Abstract Model Tester
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaDocumentModuleTest\DvsaReport\Model;

use PHPUnit\Framework\TestCase;

/**
 * Abstract Model Tester
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractModelTester extends TestCase
{
    /**
     * Holds the model
     *
     * @var object
     */
    protected $model;

    /**
     * Holds the model class name
     *
     * @var class-string
     */
    protected $modelClass;

    /**
     * @var array
     */
    protected $testMethods = [];

    /**
     * @return class-string
     */
    public function getClassToTestName()
    {
        return $this->modelClass;
    }

    /**
     * @dataProvider providerGettersAndSetters
     *
     * @param string $methodName
     * @param mixed  $testValue
     *
     * @return void
     */
    public function testGettersAndSetters($methodName, $testValue)
    {
        $classToTestName = $this->getClassToTestName();
        $model = new $classToTestName();

        $model->{'set' . $methodName}($testValue);
        $this->assertSame($testValue, $model->{'get' . $methodName}());
    }

    /**
     * @return array
     */
    public function providerGettersAndSetters()
    {
        $classToTestName = $this->getClassToTestName();
        $reflection = new \ReflectionClass($classToTestName);

        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            if (substr($method->getName(), 0, 3) == 'set') {
                $methodName = substr($method->getName(), 3);

                if (
                    (ltrim($method->getDeclaringClass()->getName(), "\\") == ltrim($classToTestName, "\\")) &&
                    $method->isPublic() &&
                    $reflection->hasProperty(lcfirst($methodName)) &&
                    $reflection->hasMethod('get' . $methodName)
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

                    $this->testMethods[] = array($methodName, $testValue);
                }
            }
        }
        return $this->testMethods;
    }
}
