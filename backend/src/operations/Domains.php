<?php

namespace Operations;

require '../vendor/autoload.php';

/**
 * This class provides functions for retrieving and modifying domains.
 */
class Domains
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
     * Get a list of domains according to filter criteria
     * 
     * @param   $pi         PageInfo object, which is also updated with total page number
     * @param   $userId     Id of the user for which the table should be retrieved
     * @param   $query      Search query to search in the domain name, null for no filter
     * @param   $sorting    Sort string in format 'field-asc,field2-desc', null for default
     * @param   $type       Type to filter for, null for no filter
     */
    public function getDomains(\Utils\PagingInfo &$pi, int $userId, ? string $query, ? string $sorting, ? string $type) : array
    {
        $ac = new \Operations\AccessControl($this->c);
        $userIsAdmin = $ac->isAdmin($userId);

        $queryStr = $query === null ? '%' : '%' . $query . '%';

        //Count elements
        if ($pi->pageSize === null) {
            $pi->totalPages = 1;
        } else {
            $query = $this->db->prepare('
                SELECT COUNT(*) AS total
                FROM domains D
                LEFT OUTER JOIN permissions P ON D.id = P.domain_id
                WHERE (P.user_id=:userId OR :userIsAdmin) AND
                (D.name LIKE :nameQuery) AND
                (D.type = :domainType OR :noTypeFilter)
            ');

            $query->bindValue(':userId', $userId, \PDO::PARAM_INT);
            $query->bindValue(':userIsAdmin', intval($userIsAdmin), \PDO::PARAM_INT);
            $query->bindValue(':nameQuery', $queryStr, \PDO::PARAM_STR);
            $query->bindValue(':domainType', (string)$type, \PDO::PARAM_STR);
            $query->bindValue(':noTypeFilter', intval($type === null), \PDO::PARAM_INT);

            $query->execute();
            $record = $query->fetch();

            $pi->totalPages = ceil($record['total'] / $pi->pageSize);
        }
        
        //Query and return result
        $ordStr = \Services\Database::makeSortingString($sorting, [
            'id' => 'D.id',
            'name' => 'D.name',
            'type' => 'D.type',
            'records' => 'records'
        ]);
        $pageStr = \Services\Database::makePagingString($pi);

        $query = $this->db->prepare('
            SELECT D.id,D.name,D.type,D.master,count(R.domain_id) AS records
            FROM domains D
            LEFT OUTER JOIN records R ON D.id = R.domain_id
            LEFT OUTER JOIN permissions P ON D.id = P.domain_id
            WHERE (P.user_id=:userId OR :userIsAdmin)
            GROUP BY D.id
            HAVING
            (D.name LIKE :nameQuery) AND
            (D.type=:domainType OR :noTypeFilter)'
            . $ordStr . $pageStr);

        $query->bindValue(':userId', $userId, \PDO::PARAM_INT);
        $query->bindValue(':userIsAdmin', intval($userIsAdmin), \PDO::PARAM_INT);
        $query->bindValue(':nameQuery', $queryStr, \PDO::PARAM_STR);
        $query->bindValue(':domainType', (string)$type, \PDO::PARAM_STR);
        $query->bindValue(':noTypeFilter', intval($type === null), \PDO::PARAM_INT);

        $query->execute();

        $data = $query->fetchAll();

        return array_map(function ($item) {
            if ($item['type'] != 'SLAVE') {
                unset($item['master']);
            }
            return $item;
        }, $data);
    }
}
