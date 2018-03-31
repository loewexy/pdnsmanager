<?php

namespace Operations;

require '../vendor/autoload.php';

/**
 * This class provides functions for retrieving and modifying credentials.
 */
class Credentials
{
    /** @var \Monolog\Logger */
    private $logger;

    /** @var \PDO */
    private $db;

    /** @var \Slim\Container */
    private $c;

    public function __construct(\Slim\Container $c)
    {
        $this->logger = $c->logger;
        $this->db = $c->db;
        $this->c = $c;
    }

    /**
     * Get a list of credentials
     * 
     * @param   $pi         PageInfo object, which is also updated with total page number
     * @param   $recordId   Id of the record for which the table should be retrieved
     * 
     * @return  array       Array with credentials
     */
    public function getCredentials(\Utils\PagingInfo &$pi, int $recordId) : array
    {
        //Count elements
        if ($pi->pageSize === null) {
            $pi->totalPages = 1;
        } else {
            $query = $this->db->prepare('
                SELECT COUNT(*) AS total
                FROM remote
                WHERE record=:recordId
            ');

            $query->bindValue(':recordId', $recordId, \PDO::PARAM_INT);
            $query->execute();
            $record = $query->fetch();

            $pi->totalPages = ceil($record['total'] / $pi->pageSize);
        }

        $pageStr = \Services\Database::makePagingString($pi);

        $query = $this->db->prepare('SELECT id,description,type FROM remote WHERE record=:recordId ORDER BY id ASC' . $pageStr);
        $query->bindValue(':recordId', $recordId, \PDO::PARAM_INT);
        $query->execute();

        $data = $query->fetchAll();

        return array_map(function ($item) {
            $item['id'] = intval($item['id']);
            return $item;
        }, $data);
    }
}
