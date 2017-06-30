<?php

namespace Bezhanov\Silex\Routing;

use Symfony\Component\Config\FileLocatorInterface;

/**
 * AnnotationDirectoryLoader loads routing information from annotations set on PHP classes and methods.
 *
 * @see \Symfony\Component\Routing\Loader\AnnotationDirectoryLoader
 */
class AnnotationDirectoryLoader
{
    protected $locator;

    protected $loader;

    public function __construct(FileLocatorInterface $locator, AnnotationClassLoader $loader)
    {
        if (!function_exists('token_get_all')) {
            throw new \RuntimeException('The Tokenizer extension is required for the routing annotation loaders.');
        }

        $this->locator = $locator;
        $this->loader = $loader;
    }

    /**
     * @param string $path
     * @return AnnotationClassData[]
     */
    public function load($path): array
    {
        $annotationClassDataCollection = [];

        $dir = $this->locator->locate($path);

        $files = iterator_to_array(new \RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS),
                function (\SplFileInfo $current) {
                    return '.' !== substr($current->getBasename(), 0, 1);
                }
            ),
            \RecursiveIteratorIterator::LEAVES_ONLY
        ));

        usort($files, function (\SplFileInfo $a, \SplFileInfo $b) {
            return (string) $a > (string) $b ? 1 : -1;
        });

        foreach ($files as $file) {
            if (!$file->isFile() || '.php' !== substr($file->getFilename(), -4)) {
                continue;
            }

            if ($class = $this->findClass($file)) {
                $reflectionClass = new \ReflectionClass($class);

                if ($reflectionClass->isAbstract()) {
                    continue;
                }

                $annotationClassDataCollection[] = $this->loader->load($class);
            }
        }

        return $annotationClassDataCollection;
    }

    /**
     * @see \Symfony\Component\Routing\Loader\AnnotationFileLoader::findClass()
     *
     * @param string $file A PHP file path
     *
     * @return string|false Full class name if found, false otherwise
     */
    protected function findClass(string $file)
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));

        if (1 === count($tokens) && T_INLINE_HTML === $tokens[0][0]) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain PHP code. Did you forgot to add the "<?php" start tag at the beginning of the file?', $file));
        }

        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];

            if (!isset($token[1])) {
                continue;
            }

            if (true === $class && T_STRING === $token[0]) {
                return $namespace.'\\'.$token[1];
            }

            if (true === $namespace && T_STRING === $token[0]) {
                $namespace = $token[1];
                while (isset($tokens[++$i][1]) && in_array($tokens[$i][0], [T_NS_SEPARATOR, T_STRING])) {
                    $namespace .= $tokens[$i][1];
                }
                $token = $tokens[$i];
            }

            if (T_CLASS === $token[0]) {
                // Skip usage of ::class constant
                $isClassConstant = false;
                for ($j = $i - 1; $j > 0; --$j) {
                    if (!isset($tokens[$j][1])) {
                        break;
                    }

                    if (T_DOUBLE_COLON === $tokens[$j][0]) {
                        $isClassConstant = true;
                        break;
                    } elseif (!in_array($tokens[$j][0], [T_WHITESPACE, T_DOC_COMMENT, T_COMMENT])) {
                        break;
                    }
                }

                if (!$isClassConstant) {
                    $class = true;
                }
            }

            if (T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return false;
    }
}
