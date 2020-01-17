<?php

namespace Addworking\LaravelClassFinder;

use Composer\Autoload\ClassLoader;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use SplFileInfo;
use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class ClassFinder
{
    protected $loader;

    public function __construct(ClassLoader $loader)
    {
        $this->setLoader($loader);
    }

    public static function usingAutoload(string $cwd = '.'): self
    {
        return new self(require "{$cwd}/vendor/autoload.php");
    }

    public function getLoader(): ClassLoader
    {
        return $this->loader;
    }

    public function setLoader(ClassLoader $loader): self
    {
        $this->loader = $loader;

        return $this;
    }

    public function classToPath(string $class): string
    {
        if (! $path = $this->getLoader()->findFile($class)) {
            throw new RuntimeException("unable to locate declaration file for class '{$class}'");
        }

        return realpath($path);
    }

    public function pathToClass($path): string
    {
        if ($path instanceof SplFileInfo) {
            $path = $path->getPathname();
        }

        if (! is_string($path)) {
            throw new InvalidArgumentException("\$path is not a string");
        }

        $loader = $this->getLoader();
        $path = realpath($path);

        // PSR-4 paths lookup
        foreach ($loader->getPrefixesPsr4() as $namespace => $directories) {
            foreach ($directories as $directory) {
                if (! $directory = realpath($directory)) {
                    continue; // that directory probably doesn't exist...
                }

                if (Str::startsWith($path, $directory = realpath($directory))) {
                    return $namespace . strtr(substr($path, strlen($directory) +1, -4), DIRECTORY_SEPARATOR, '\\');
                }
            }
        }

        // PSR-0 paths lookup
        foreach ($loader->getPrefixes() as $directories) {
            foreach ($directories as $directory) {
                if (! $directory = realpath($directory)) {
                    continue; // that directory probably doesn't exist...
                }

                if (Str::startsWith($path, $directory = realpath($directory))) {
                    return strtr(substr($path, strlen($directory) +1, -4), DIRECTORY_SEPARATOR, '\\');
                }
            }
        }

        // classmap lookup
        foreach ($loader->getClassmap() as $class => $file) {
            if ($path == realpath($file)) {
                return $class;
            }
        }

        throw new RuntimeException("unable to find a suitable class for path '{$path}'");
    }

    public function classesIn($path): Generator
    {
        if ($path instanceof SplFileInfo) {
            $path = $path->getPathname();
        }

        if (! is_string($path)) {
            throw new InvalidArgumentException("\$path is not a string");
        }

        if (! is_dir($path)) {
            throw new RuntimeException("{$path} is not a directory ");
        }

        $dir   = new RecursiveDirectoryIterator($path);
        $it    = new RecursiveIteratorIterator($dir);
        $files = new RegexIterator($it, '/\.php$/', RegexIterator::MATCH);

        foreach ($files as $file) {
            try {
                yield $this->pathToClass($file->getPathName());
            } catch (RuntimeException $e) {
                continue;
            }
        }
    }
}
