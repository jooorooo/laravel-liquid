<?php

namespace Liquid\Loader;

class FileContent
{

    protected $content;

    protected $fileMtime;

    protected $path;

    public function __construct($content, $fileMtime, $path)
    {
        $this->content = $content;
        $this->fileMtime = $fileMtime;
        $this->path = $path;
    }

    public function getFileMTime()
    {
        return $this->fileMtime;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getPath()
    {
        return $this->path;
    }

}
