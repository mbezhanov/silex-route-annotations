<?php

namespace Bezhanov\Silex\Routing
{
    use Bezhanov\Silex\Routing\Tests\AnnotationDirectoryLoaderTest;

    function function_exists($function)
    {
        if ($function === 'token_get_all') {
            return AnnotationDirectoryLoaderTest::$tokenizerExtensionLoaded;
        }
        return \function_exists($function);
    }
}

namespace Bezhanov\Silex\Routing\Tests
{
    use Bezhanov\Silex\Routing\AnnotationClassLoader;
    use Bezhanov\Silex\Routing\AnnotationDirectoryLoader;
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\Config\FileLocator;

    class AnnotationDirectoryLoaderTest extends TestCase
    {
        public static $tokenizerExtensionLoaded = true;

        protected function tearDown()
        {
            self::$tokenizerExtensionLoaded = true;
        }

        public function testLoad()
        {
            $directoryLoader = $this->createDirectoryLoader();
            $annotationClassDataCollection = $directoryLoader->load(__DIR__ . '/Fixtures/AnnotatedClasses');
            $this->assertCount(2, $annotationClassDataCollection);
        }

        public function testLoadInvalidPhpFile()
        {
            $this->expectException(\InvalidArgumentException::class);
            $directoryLoader = $this->createDirectoryLoader();
            $directoryLoader->load(__DIR__ . '/Fixtures/EmptyPhpFile');
        }

        public function testLoadWithDisabledTokenizer()
        {
            $this->expectException(\RuntimeException::class);
            self::$tokenizerExtensionLoaded = false;
            $directoryLoader = $this->createDirectoryLoader();
            $directoryLoader->load(__DIR__ . '/Fixtures/AnnotatedClasses');
        }

        private function createDirectoryLoader()
        {
            $fileLocator = new FileLocator();
            $classLoader = $this->prophesize(AnnotationClassLoader::class);
            return new AnnotationDirectoryLoader($fileLocator, $classLoader->reveal());
        }
    }
}
