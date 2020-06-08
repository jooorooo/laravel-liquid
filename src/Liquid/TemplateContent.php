<?php

namespace Liquid;

class TemplateContent
{

    protected $content;

    protected $fileMtime;

    protected $path;

    protected $name;

    public function __construct($content, $fileMtime = null, $path = null, $name = null)
    {
        $this->content = $content;
        $this->fileMtime = $fileMtime;
        $this->path = $path;
        $this->name = $name;
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

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}
