<?php

namespace Liquid\ViewFinders;

use InvalidArgumentException;
use Illuminate\Filesystem\Filesystem;
use Liquid\TemplateContent;

class FileViewFinder extends AbstractViewFinder
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new file view loader instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  array  $paths
     * @param  array  $extensions
     * @return void
     */
    public function __construct(Filesystem $files, array $paths, array $extensions = null)
    {
        $this->files = $files;
        $this->paths = $paths;

        if (isset($extensions)) {
            $this->extensions = $extensions;
        }
    }

    /**
     * Find the given view in the list of paths.
     *
     * @param  string  $name
     * @param  array   $paths
     * @return TemplateContent
     *
     * @throws \InvalidArgumentException
     */
    protected function findInDriver($name, $paths)
    {
        foreach ((array) $paths as $path) {
            foreach ($this->getPossibleViewFiles($name) as $file) {
                if ($this->files->exists($viewPath = $path.'/'.$file)) {
                    return new TemplateContent($this->files->get($viewPath), $this->files->lastModified($viewPath), $viewPath, $name);
                }
            }
        }

        throw new InvalidArgumentException("View [{$name}] not found.");
    }

    /**
     * Get the filesystem instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }
}
