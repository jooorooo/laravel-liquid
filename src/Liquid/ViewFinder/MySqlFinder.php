<?php

namespace Liquid\ViewFinder;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\View\FileViewFinder;
use InvalidArgumentException;
use Liquid\Loader\FileContent;

class MySqlFinder extends FileViewFinder
{

    protected $connection;

    protected $table;

    protected $extensions = [
        'liquid'
    ];

    public function __construct(ConnectionInterface $connection, $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * @inheritDoc
     */
    public function find($name)
    {
        return parent::find($name instanceof FileContent ? $name->getPath() : $name);
    }

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
        foreach ($this->getPossibleViewFiles($name) as $file) {
            if ($template = $this->getTable()->where('path', $file)->orWhere('path', $name)->first()) {
                return new FileContent($template->content, strtotime($template->updated_at), $template->path);
            }
        }

        throw new InvalidArgumentException("View [{$name}] not found.");
    }

    /**
     * @return Builder
     */
    protected function getTable()
    {
        $table = $this->connection->table($this->table);
        if(($where = config('liquid.view_store.where')) && is_array($where)) {
            array_walk($where, function($value, $key) use ($table) {
                $table->where($key, $value);
            });
        }

        return $table;
    }

}
