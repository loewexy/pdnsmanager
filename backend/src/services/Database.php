<?php

namespace Services;

require '../vendor/autoload.php';

class Database
{
    public function __invoke(\Slim\Container $c)
    {
        $config = $c['config']['db'];

        try {
            $pdo = new \PDO(
                'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['dbname'],
                $config['user'],
                $config['password']
            );
        } catch (\PDOException $e) {
            $c->logger->critical("SQL Connect Error: " . $e->getMessage());
            $c->logger->critical("DB Config was", $config);
            exit();
        }

        try {
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $c->logger->critical("SQL Parameter Error: " . $e->getMessage());
            exit();
        }

        $c->logger->debug("Database setup successfull");

        return $pdo;
    }

    /**
     * Makes a SQL LIMIT string from paging information
     * 
     * @param   $pi     PagingInfo object to use
     * 
     * @return  string  SQL string to use
     */
    public static function makePagingString(\Utils\PagingInfo $pi) : string
    {
        if ($pi->pageSize === null) {
            return '';
        }

        if ($pi->page === null) {
            $pi->page = 1;
        }

        $offset = ($pi->page - 1) * $pi->pageSize;

        return ' LIMIT ' . intval($pi->pageSize) . ' OFFSET ' . intval($offset);
    }

    /**
     * Makes a SQL ORDER BY string from order information.
     * 
     * This is done from a string with format 'field-asc,field2-desc'
     * where fields are mapped to columns in param $colMap. This also
     * should prevent SQL injections.
     * 
     * @param   $sort       Sort string
     * @param   $colMap     Map which assigns to each field name a column to use
     * 
     * @return  string  SQL string to use
     */
    public static function makeSortingString(? string $sort, array $colMap) : string
    {
        if ($sort === null) {
            return '';
        }

        $orderStrings = [];

        foreach (explode(',', $sort) as $value) {
            $parts = explode('-', $value);

            if (array_key_exists($parts[0], $colMap) && count($parts) == 2) { // is valid known field
                if ($parts[1] == 'asc') {
                    $orderStrings[] = $colMap[$parts[0]] . ' ASC';
                } elseif ($parts[1] == 'desc') {
                    $orderStrings[] = $colMap[$parts[0]] . ' DESC';
                }
            }
        }

        if (count($orderStrings) == 0) { // none was valid
            return '';
        }

        return ' ORDER BY ' . implode(', ', $orderStrings);
    }

    /**
     * Makes a string which works to use with an IN SQL clause.
     * 
     * Input is a comma separated list, all items are escaped and joint
     * to the form ('a','b')
     * 
     * @param   $db         PDO object used for escaping
     * @param   $input      Comma separated list of items
     * 
     * @return  string  SQL string to use
     */
    public static function makeSetString(\PDO $db, ? string $input) : string
    {
        if ($input === null || $input === '') {
            return '(\'\')';
        }

        $parts = explode(',', $input);

        $partsEscaped = array_map(function ($item) use ($db) {
            return $db->quote($item, \PDO::PARAM_STR);
        }, $parts);

        return '(' . implode(', ', $partsEscaped) . ')';
    }
}
