<?php

namespace Tms\Bundle\ThemeBundle\Tests\Model;

abstract class AbstractModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get the entity class name to test.
     */
    abstract protected function getClassName();

    /**
     * Attributes whose accessors must be tested.
     *
     * @return array
     */
    public function getterAndSetterProvider()
    {
        return array(
            array(null, null),
        );
    }

    /**
     * Test that the value set with setter method is the same as the value returned with the getter method.
     *
     * @param string $attribute Attribute name
     * @param mixed  $value     Attribute value
     *
     * @dataProvider getterAndSetterProvider
     */
    public function testGettersAndSetters($attribute, $value)
    {
        if ($className = $this->getClassName()) {
            // Create a new entity
            $entity = new $className();

            // generate methods name
            $getter = sprintf('get%s', ucfirst($attribute));
            $setter = sprintf('set%s', ucfirst($attribute));

            // Use setter method
            $entity->$setter($value);

            // Check getter method result
            $this->assertSame(
                $value,
                $entity->$getter(),
                sprintf(
                    'The %s::%s() method does not return the same value set with %s::%s() method',
                    $className,
                    $getter,
                    $className,
                    $setter
                )
            );
        }
    }

    /**
     * Attributes whose is method must be tested.
     *
     * @return array
     */
    public function booleanProvider()
    {
        return array(
            array(null),
        );
    }

    /**
     * Test the value returned by a is method is a boolean.
     *
     * @param string $attribute Attribute name
     *
     * @depends      testGettersAndSetters
     * @dataProvider booleanProvider
     */
    public function testBoolean($attribute)
    {
        if (($className = $this->getClassName()) && $attribute) {
            // Create a new entity
            $entity = new $className();

            // generate methods name
            $setter = sprintf('set%s', ucfirst($attribute));
            $is = sprintf('is%s', ucfirst($attribute));

            // Tests
            $entity->$setter('NotABoolean');
            $this->assertInternalType(
                'bool',
                $entity->$is(),
                sprintf(
                    'The %s::%s() method must return a boolean value',
                    $className,
                    $is
                )
            );

            $entity->$setter(0);
            $this->assertInternalType(
                'bool',
                $entity->$is(),
                sprintf(
                    'The %s::%s() method must return a boolean value',
                    $className,
                    $is
                )
            );

            $entity->$setter(true);
            $this->assertTrue(
                $entity->$is(),
                sprintf(
                    'The %s::%s() method must return true when true is setted',
                    $className,
                    $is
                )
            );

            $entity->$setter(false);
            $this->assertFalse(
                $entity->$is(),
                sprintf(
                    'The %s::%s() method must return false when false is setted',
                    $className,
                    $is
                )
            );
        }
    }

    /**
     * Attributes whose accessors must be tested.
     *
     * @return array
     */
    public function collectionsProvider()
    {
        return array(
            array(null, null, null, null),
        );
    }

    /**
     * Test the add and remove methods for Collection attributes.
     *
     * @param string $attribute    Entity attribute name
     * @param string $name         Name used with the add and remove methods
     * @param mixed  $firstObject  Fist instance of an Object
     * @param mixed  $secondObject Another instance of an Object
     *
     * @dataProvider collectionsProvider
     */
    public function testCollections($attribute, $name, $firstObject, $secondObject)
    {
        if ($attribute && ($className = $this->getClassName())) {
            // Create a new entity
            $entity = new $className();

            // generate methods name
            $add = sprintf('add%s', ucfirst($name));
            $getter = sprintf('get%s', ucfirst($attribute));
            $has = sprintf('has%s', ucfirst($name));
            $remove = sprintf('remove%s', ucfirst($name));

            // Object setted empty
            $this->assertCount(
                0,
                $entity->$getter(),
                sprintf(
                    'The %s::%s() method return an non empty collection when no object was added',
                    $className,
                    $add
                )
            );

            // Add the first object
            $entity->$add($firstObject);
            $this->assertContains(
                $firstObject,
                $entity->$getter(),
                sprintf(
                    'The %s::%s() method does not add correctly the first object',
                    $className,
                    $add
                )
            );

            if (method_exists($entity, $has)) {
                $this->assertInternalType(
                    'boolean',
                    $entity->$has($firstObject),
                    sprintf('"%s" method must return a boolean', $has)
                );
                $this->assertTrue($entity->$has($firstObject));
                $this->assertInternalType(
                    'boolean',
                    $entity->$has($secondObject),
                    sprintf('"%s" method must return a boolean', $has)
                );
                $this->assertFalse($entity->$has($secondObject));
            }

            // Remove the second object (not added)
            $entity->$remove($secondObject);
            $this->assertCount(
                1,
                $entity->$getter(),
                sprintf(
                    'The %s::%s() method must not remove an unexistant object',
                    $className,
                    $remove
                )
            );

            if (method_exists($entity, $has)) {
                $this->assertTrue($entity->$has($firstObject));
                $this->assertFalse($entity->$has($secondObject));
            }

            // Add the second object
            $entity->$add($secondObject);
            $this->assertContains(
                $secondObject,
                $entity->$getter(),
                sprintf(
                    'The %s::%s() method does not add correctly the second object',
                    $className,
                    $add
                )
            );

            if (method_exists($entity, $has)) {
                $this->assertTrue($entity->$has($firstObject));
                $this->assertTrue($entity->$has($secondObject));
            }

            // Check if the collection contains two more object
            $this->assertCount(
                2,
                $entity->$getter(),
                sprintf(
                    'The %s::%s() method has added more objects than the two added',
                    $className,
                    $add
                )
            );

            // Try to re-add the first object
            $entity->$add($firstObject);
            $this->assertCount(
                2,
                $entity->$getter(),
                sprintf(
                    'The %s::%s() method must ignore an object already added',
                    $className,
                    $add
                )
            );

            if (method_exists($entity, $has)) {
                $this->assertTrue($entity->$has($firstObject));
                $this->assertTrue($entity->$has($secondObject));
            }

            // Remove the second object
            $entity->$remove($secondObject);
            $this->assertCount(
                1,
                $entity->$getter(),
                sprintf(
                    'The %s::%s() method has not remove the given object',
                    $className,
                    $remove
                )
            );

            if (method_exists($entity, $has)) {
                $this->assertTrue($entity->$has($firstObject));
                $this->assertFalse($entity->$has($secondObject));
            }
        }
    }

    /**
     * Test the __toString method.
     */
    public function testToStringMethod()
    {
        if ($className = $this->getClassName()) {
            // Create a new entity
            $entity = new $className();

            // Test __toString method
            if (method_exists($entity, '__toString')) {
                // Test this method allways return a string
                $this->assertInternalType(
                    'string',
                    $entity->__toString(),
                    'The __toString method doesn\'t return a string value'
                );

                // fill all the fields
                $fields = $this->getterAndSetterProvider();
                foreach ($fields as $fieldParameters) {
                    // generate setter name
                    $setter = sprintf('set%s', ucfirst($fieldParameters[0]));

                    // Use setter method
                    $entity->$setter($fieldParameters[1]);
                }

                // Test this method allways return a string
                $this->assertInternalType(
                    'string',
                    $entity->__toString(),
                    'The __toString method doesn\'t return a string value'
                );
                $this->assertNotEquals(
                    '',
                    $entity->__toString(),
                    'The __toString method must not return an empty value'
                );
            }
        }
    }
}
