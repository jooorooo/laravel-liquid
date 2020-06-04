<?php

namespace Liquid\ViewFinders;

use Illuminate\Database\ConnectionInterface;
use InvalidArgumentException;
use Liquid\TemplateContent;

class DatabaseViewFinder extends AbstractViewFinder
{
    /**
     * The filesystem instance.
     *
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * Create a new file view loader instance.
     *
     * @param  ConnectionInterface $connection
     * @param  string $table
     * @param  array  $extensions
     * @return void
     */
    public function __construct(ConnectionInterface $connection, $table, array $extensions = null)
    {
        $this->connection = $connection;
        $this->paths = is_array($table) ? $table : [$table];

        if (isset($extensions)) {
            $this->extensions = $extensions;
        }
    }

    /**
     * Find the given view in the list of tables.
     *
     * @param  string  $name
     * @param  array   $tables
     * @return TemplateContent
     *
     * @throws \InvalidArgumentException
     */
    protected function findInDriver($name, $tables)
    {
        foreach ((array) $tables as $table) {
            foreach ($this->getPossibleViewFiles($name) as $file) {
                if ($template = $this->table($table)->where('path', $file)->first()) {
                    return new TemplateContent($template->content, strtotime($template->updated_at), $table . '::' . $file, $name);
                }
            }
        }

        throw new InvalidArgumentException("View [{$name}] not found.");
    }

    /**
     * Get the underlying database connection.
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get a query builder for the cache table.
     *
     * @param string $table
     * @return \Illuminate\Database\Query\Builder
     */
    protected function table($table)
    {
        return $this->connection->table($table);
    }
}
