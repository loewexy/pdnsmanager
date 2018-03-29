<?php

namespace Operations;

use function Monolog\Handler\error_log;

require '../vendor/autoload.php';

/**
 * This class provides functions for retrieving and modifying domains.
 */
class Records
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
     * @param   $pi             PageInfo object, which is also updated with total page number
     * @param   $userId         Id of the user for which the table should be retrieved
     * @param   $domain         Comma separated list of domain ids
     * @param   $queryName      Search query to search in the record name, null for no filter
     * @param   $type           Comma separated list of types
     * @param   $queryContent   Search query to search in the record content, null for no filter
     * @param   $sort           Sort string in format 'field-asc,field2-desc', null for default
     * 
     * @return  array           Array with matching records
     */
    public function getRecords(
        \Utils\PagingInfo &$pi,
        int $userId,
        ? string $domain,
        ? string $queryName,
        ? string $type,
        ? string $queryContent,
        ? string $sort
    ) : array {
        $ac = new \Operations\AccessControl($this->c);
        $userIsAdmin = $ac->isAdmin($userId);

        $queryName = $queryName === null ? '%' : '%' . $queryName . '%';
        $queryContent = $queryContent === null ? '%' : '%' . $queryContent . '%';

        $setDomains = \Services\Database::makeSetString($this->db, $domain);
        $setTypes = \Services\Database::makeSetString($this->db, $type);

        //Count elements
        if ($pi->pageSize === null) {
            $pi->totalPages = 1;
        } else {
            $query = $this->db->prepare('
                SELECT COUNT(*) AS total FROM records R
                LEFT OUTER JOIN domains D ON R.domain_id = D.id
                LEFT OUTER JOIN permissions P ON P.domain_id = R.domain_id
                WHERE (P.user_id=:userId OR :userIsAdmin) AND
                (R.domain_id IN ' . $setDomains . ' OR :noDomainFilter) AND
                (R.name LIKE :queryName) AND
                (R.type IN ' . $setTypes . ' OR :noTypeFilter) AND
                (R.content LIKE :queryContent)
            ');

            $query->bindValue(':userId', $userId, \PDO::PARAM_INT);
            $query->bindValue(':userIsAdmin', intval($userIsAdmin), \PDO::PARAM_INT);
            $query->bindValue(':queryName', $queryName, \PDO::PARAM_STR);
            $query->bindValue(':queryContent', $queryContent, \PDO::PARAM_STR);
            $query->bindValue(':noDomainFilter', intval($domain === null), \PDO::PARAM_INT);
            $query->bindValue(':noTypeFilter', intval($type === null), \PDO::PARAM_INT);

            $query->execute();
            $record = $query->fetch();

            $pi->totalPages = ceil($record['total'] / $pi->pageSize);
        }

        //Query and return result
        $ordStr = \Services\Database::makeSortingString($sort, [
            'id' => 'R.id',
            'name' => 'R.name',
            'type' => 'R.type',
            'content' => 'R.content',
            'priority' => 'R.prio',
            'ttl' => 'R.ttl'
        ]);
        $pageStr = \Services\Database::makePagingString($pi);

        $query = $this->db->prepare('
            SELECT R.id,R.name,R.type,R.content,R.prio as priority,R.ttl,R.domain_id as domain FROM records R
            LEFT OUTER JOIN domains D ON R.domain_id = D.id
            LEFT OUTER JOIN permissions P ON P.domain_id = R.domain_id
            WHERE (P.user_id=:userId OR :userIsAdmin) AND
            (R.domain_id IN ' . $setDomains . ' OR :noDomainFilter) AND
            (R.name LIKE :queryName) AND
            (R.type IN ' . $setTypes . ' OR :noTypeFilter) AND
            (R.content LIKE :queryContent)
            GROUP BY R.id' . $ordStr . $pageStr);

        $query->bindValue(':userId', $userId, \PDO::PARAM_INT);
        $query->bindValue(':userIsAdmin', intval($userIsAdmin), \PDO::PARAM_INT);
        $query->bindValue(':queryName', $queryName, \PDO::PARAM_STR);
        $query->bindValue(':queryContent', $queryContent, \PDO::PARAM_STR);
        $query->bindValue(':noDomainFilter', intval($domain === null), \PDO::PARAM_INT);
        $query->bindValue(':noTypeFilter', intval($type === null), \PDO::PARAM_INT);

        $query->execute();

        $data = $query->fetchAll();

        return array_map(function ($item) {
            $item['id'] = intval($item['id']);
            $item['priority'] = intval($item['priority']);
            $item['ttl'] = intval($item['ttl']);
            $item['domain'] = intval($item['domain']);
            return $item;
        }, $data);
    }
}
