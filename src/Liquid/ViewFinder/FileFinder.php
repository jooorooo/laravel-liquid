<?php

namespace Liquid\ViewFinder;

use Illuminate\View\FileViewFinder;
use InvalidArgumentException;
use Liquid\Loader\FileContent;

class FileFinder extends FileViewFinder
{

    /**
     * @inheritDoc
     */
    public function find($name)
    {
        return parent::find($name instanceof FileContent ? $name->getPath() : $name);
    }

    /**
     * @inheritDoc
     */
    protected function findInPaths($name, $paths)
    {
        $path = parent::findInPaths($name, $paths);
        return new FileContent($this->files->get($path), $this->files->lastModified($path), $path);
    }

}
