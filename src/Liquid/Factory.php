<?php

namespace Liquid;

use Illuminate\View\Factory as IlluminateFactory;
use Liquid\Loader\FileContent;

class Factory extends IlluminateFactory
{

    /**
     * Get the extension used by the view file.
     *
     * @param  string  $path
     * @return string
     */
    protected function getExtension($path)
    {
        if($path instanceof FileContent) {
            return config('liquid.extension');
        }

        return parent::getExtension($path);
    }

}
