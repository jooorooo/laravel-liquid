<?php

namespace Liquid;

use Illuminate\Support\Facades\DB;
use Illuminate\View\FileViewFinder as IlluminateFileViewFinder;
use InvalidArgumentException;
use Liquid\Loader\FileContent;

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
                    if ($load = $this->loadFile($file)) {
                        return $load;
                    }
                }
                if ($this->files->exists($viewPath = $path.'/'.$file)) {
                    return $viewPath;
                }
            }
        }

        throw new InvalidArgumentException("View [{$name}] not found.");
    }

    protected function loadFile($file)
    {
        if($fileDb = DB::table('templates')->where('path', $file)->first()) {
            return new FileContent($fileDb->content, strtotime($fileDb->updated_at), $file);
        }

        return false;
    }
}
