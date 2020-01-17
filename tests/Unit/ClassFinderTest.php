<?php

namespace Tests\Unit;

use Addworking\LaravelClassFinder\ClassFinder;
use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;

class ClassFinderTest extends TestCase
{
    public function testGetLoader()
    {
        $finder = ClassFinder::usingAutoload();

        $this->assertInstanceOf(ClassLoader::class, $finder->getLoader());
    }

    public function testSetLoader()
    {
        $finder = new ClassFinder($original = new ClassLoader);
        $finder->setLoader(new ClassLoader);

        $this->assertTrue($finder->getLoader() !== $original);
    }

    public function testClassToPath()
    {
        $finder = ClassFinder::usingAutoload();

        $this->assertEquals(
            realpath(__DIR__ . '/../../src/ClassFinder.php'),
            $finder->classToPath(ClassFinder::class)
        );
    }

    public function testPathToClass()
    {
        $finder = ClassFinder::usingAutoload();

        $this->assertEquals(
            ClassFinder::class,
            $finder->pathToClass(__DIR__ . '/../../src/ClassFinder.php')
        );
    }

    public function testClassesIn()
    {
        $finder = ClassFinder::usingAutoload();

        $this->assertCount(
            3,
            $finder->classesIn(__DIR__ . '/Classes')
        );
    }
}
