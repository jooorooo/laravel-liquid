<?php

namespace Liquid;

class TemplateContent
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

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getFileMtime()
    {
        return $this->fileMtime;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

}
