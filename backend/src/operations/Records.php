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
     * Get a list of records according to filter criteria
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
        $this->db->beginTransaction();

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
                (R.content LIKE :queryContent) AND
                R.type <> \'SOA\'
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
            (R.content LIKE :queryContent)  AND
            R.type <> \'SOA\'
            GROUP BY R.id' . $ordStr . $pageStr);

        $query->bindValue(':userId', $userId, \PDO::PARAM_INT);
        $query->bindValue(':userIsAdmin', intval($userIsAdmin), \PDO::PARAM_INT);
        $query->bindValue(':queryName', $queryName, \PDO::PARAM_STR);
        $query->bindValue(':queryContent', $queryContent, \PDO::PARAM_STR);
        $query->bindValue(':noDomainFilter', intval($domain === null), \PDO::PARAM_INT);
        $query->bindValue(':noTypeFilter', intval($type === null), \PDO::PARAM_INT);

        $query->execute();

        $data = $query->fetchAll();

        $this->db->commit();

        return array_map(function ($item) {
            $item['id'] = intval($item['id']);
            $item['priority'] = intval($item['priority']);
            $item['ttl'] = intval($item['ttl']);
            $item['domain'] = intval($item['domain']);
            return $item;
        }, $data);
    }

    /**
     * Add new record
     * 
     * @param   $name       Name of the new record
     * @param   $type       Type of the new record
     * @param   $content    Content of the new record
     * @param   $priority   Priority of the new record
     * @param   $ttl        TTL of the new record
     * @param   $domain     Domain id of the domain to add the record
     * 
     * @return  array       New record entry
     * 
     * @throws  NotFoundException   if the domain does not exist
     * @throws  SemanticException   if the record type is invalid
     */
    public function addRecord(string $name, string $type, string $content, int $priority, int $ttl, int $domain) : array
    {
        if (!in_array($type, $this->c['config']['records']['allowedTypes'])) {
            throw new \Exceptions\SemanticException();
        }

        $this->db->beginTransaction();

        $query = $this->db->prepare('SELECT id FROM domains WHERE id=:id AND type IN (\'MASTER\',\'NATIVE\')');
        $query->bindValue(':id', $domain, \PDO::PARAM_INT);
        $query->execute();

        $record = $query->fetch();

        if ($record === false) { // Domain does not exist
            $this->db->rollBack();
            throw new \Exceptions\NotFoundException();
        }

        $query = $this->db->prepare('INSERT INTO records (domain_id, name, type, content, ttl, prio, change_date)
                                    VALUES (:domainId, :name, :type, :content, :ttl, :prio, :changeDate)');
        $query->bindValue(':domainId', $domain, \PDO::PARAM_INT);
        $query->bindValue(':name', $name, \PDO::PARAM_STR);
        $query->bindValue(':type', $type, \PDO::PARAM_STR);
        $query->bindValue(':content', $content, \PDO::PARAM_STR);
        $query->bindValue(':ttl', $ttl, \PDO::PARAM_INT);
        $query->bindValue(':prio', $priority, \PDO::PARAM_INT);
        $query->bindValue(':changeDate', time(), \PDO::PARAM_INT);
        $query->execute();

        $query = $this->db->prepare('SELECT id,name,type,content,prio AS priority,ttl,domain_id AS domain FROM records
                                    ORDER BY id DESC LIMIT 1');
        $query->execute();

        $record = $query->fetch();

        $record['id'] = intval($record['id']);
        $record['priority'] = intval($record['priority']);
        $record['ttl'] = intval($record['ttl']);
        $record['domain'] = intval($record['domain']);

        $soa = new \Operations\Soa($this->c);
        $soa->updateSerial($domain);

        $this->db->commit();

        return $record;
    }

    /**
     * Delete record
     * 
     * @param   $id     Id of the record to delete
     * 
     * @return  void
     * 
     * @throws  NotFoundException   if record does not exist
     */
    public function deleteRecord(int $id) : void
    {
        $this->db->beginTransaction();

        $query = $this->db->prepare('SELECT id,domain_id FROM records WHERE id=:id');
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();

        $record = $query->fetch();

        if ($record === false) { //Domain does not exist
            $this->db->rollBack();
            throw new \Exceptions\NotFoundException();
        }

        $domainId = intval($record['domain_id']);

        $query = $this->db->prepare('DELETE FROM remote WHERE record=:id');
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();

        $query = $this->db->prepare('DELETE FROM records WHERE id=:id');
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();

        $soa = new \Operations\Soa($this->c);
        $soa->updateSerial($domainId);

        $this->db->commit();
    }

    /**
     * Get record
     * 
     * @param   $recordId   Name of the record
     * 
     * @return  array       Record entry
     * 
     * @throws  NotFoundException   if the record does not exist
     */
    public function getRecord(int $recordId) : array
    {
        $query = $this->db->prepare('SELECT id,name,type,content,prio AS priority,ttl,domain_id AS domain FROM records
                                     WHERE id=:recordId');
        $query->bindValue(':recordId', $recordId, \PDO::PARAM_INT);
        $query->execute();

        $record = $query->fetch();

        if ($record === false) {
            throw new \Exceptions\NotFoundException();
        }

        $record['id'] = intval($record['id']);
        $record['priority'] = intval($record['priority']);
        $record['ttl'] = intval($record['ttl']);
        $record['domain'] = intval($record['domain']);

        return $record;
    }

    /** Update Record
     * 
     * If params are null do not change
     * 
     * @param   $recordId   Record to update
     * @param   $name       New name
     * @param   $type       New type
     * @param   $content    New content
     * @param   $priority   New priority
     * @param   $ttl        New ttl
     * 
     * @return  void
     * 
     * @throws  NotFoundException   The given record does not exist
     * @throws  SemanticException   The given record type is invalid
     */
    public function updateRecord(int $recordId, ? string $name, ? string $type, ? string $content, ? int $priority, ? int $ttl)
    {
        $this->db->beginTransaction();

        $query = $this->db->prepare('SELECT id,domain_id,name,type,content,prio,ttl FROM records WHERE id=:recordId');
        $query->bindValue(':recordId', $recordId);
        $query->execute();

        $record = $query->fetch();

        if ($record === false) {
            $this->db->rollBack();
            throw new \Exceptions\NotFoundException();
        }

        if ($type !== null && !in_array($type, $this->c['config']['records']['allowedTypes'])) {
            throw new \Exceptions\SemanticException();
        }

        $domainId = intval($record['domain_id']);

        $name = $name === null ? $record['name'] : $name;
        $type = $type === null ? $record['type'] : $type;
        $content = $content === null ? $record['content'] : $content;
        $priority = $priority === null ? intval($record['prio']) : $priority;
        $ttl = $ttl === null ? intval($record['ttl']) : $ttl;

        $query = $this->db->prepare('UPDATE records SET name=:name,type=:type,content=:content,prio=:priority,ttl=:ttl WHERE id=:recordId');
        $query->bindValue('recordId', $recordId);
        $query->bindValue(':name', $name);
        $query->bindValue(':type', $type);
        $query->bindValue(':content', $content);
        $query->bindValue(':priority', $priority);
        $query->bindValue(':ttl', $ttl);
        $query->execute();

        $soa = new \Operations\Soa($this->c);
        $soa->updateSerial($domainId);

        $this->db->commit();
    }
}
