<?php

namespace Liquid;

use Illuminate\View\FileViewFinder as IlluminateFileViewFinder;
use InvalidArgumentException;

class FileViewFinder extends IlluminateFileViewFinder
{

    /**
     * Find the given view in the list of paths.
     *
     * @param  string  $name
     * @param  array   $paths
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function findInPaths($name, $paths)
    {
        foreach ((array) $paths as $path) {
            foreach ($this->getPossibleViewFiles($name) as $file) {
                if(pathinfo($file, PATHINFO_EXTENSION) == config('liquid.extension')) {
                    return app('liquid.view.manager')->store()->find($name);
                }

                if ($this->files->exists($viewPath = $path.'/'.$file)) {
                    return $viewPath;
                }
            }
        }

        throw new InvalidArgumentException("View [{$name}] not found.");
    }
}
