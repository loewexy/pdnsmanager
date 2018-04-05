<?php

namespace Operations;

require '../vendor/autoload.php';

/**
 * This class provides functions for retrieving and modifying users.
 */
class Users
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
     * Get a list of users according to filter criteria
     * 
     * @param   $pi         PageInfo object, which is also updated with total page number
     * @param   $nameQuery  Search query, may be null
     * @param   $type       Type of the user, comma separated, null for no filter
     * @param   $sorting    Sort string in format 'field-asc,field2-desc', null for default
     * 
     * @return  array       Array with matching users
     */
    public function getUsers(\Utils\PagingInfo &$pi, ? string $nameQuery, ? string $type, ? string $sorting) : array
    {
        $config = $this->c['config']['authentication'];

        $this->db->beginTransaction();

        $nameQuery = $nameQuery !== null ? '%' . $nameQuery . '%' : '%';

        //Count elements
        if ($pi->pageSize === null) {
            $pi->totalPages = 1;
        } else {
            $query = $this->db->prepare('
                SELECT COUNT(*) AS total
                FROM users U
                WHERE (U.name LIKE :nameQuery) AND
                (U.type IN ' . \Services\Database::makeSetString($this->db, $type) . ' OR :noTypeFilter)
            ');

            $query->bindValue(':nameQuery', $nameQuery, \PDO::PARAM_STR);
            $query->bindValue(':noTypeFilter', intval($type === null), \PDO::PARAM_INT);

            $query->execute();
            $record = $query->fetch();

            $pi->totalPages = ceil($record['total'] / $pi->pageSize);
        }
        
        //Query and return result
        $ordStr = \Services\Database::makeSortingString($sorting, [
            'id' => 'U.id',
            'name' => 'U.name',
            'type' => 'U.type'
        ]);
        $pageStr = \Services\Database::makePagingString($pi);

        $query = $this->db->prepare('
            SELECT id, name, type, backend
            FROM users U
            WHERE (U.name LIKE :nameQuery) AND
            (U.type IN ' . \Services\Database::makeSetString($this->db, $type) . ' OR :noTypeFilter)'
            . $ordStr . $pageStr);

        $query->bindValue(':nameQuery', $nameQuery, \PDO::PARAM_STR);
        $query->bindValue(':noTypeFilter', intval($type === null), \PDO::PARAM_INT);

        $query->execute();

        $data = $query->fetchAll();

        $this->db->commit();

        $dataTransformed = array_map(
            function ($item) use ($config) {
                if (!array_key_exists($item['backend'], $config)) {
                    return null;
                }
                if (!array_key_exists('prefix', $config[$item['backend']])) {
                    return null;
                }

                $prefix = $config[$item['backend']]['prefix'];

                if ($prefix === 'default') {
                    $name = $item['name'];
                } else {
                    $name = $prefix . '/' . $item['name'];
                }

                return [
                    'id' => intval($item['id']),
                    'name' => $name,
                    'type' => $item['type'],
                    'native' => $item['backend'] === 'native'
                ];
            },
            $data
        );

        return array_filter($dataTransformed, function ($v) {
            return $v !== null;
        });
    }

    /**
     * Add new domain
     * 
     * @param   $name       Name of the new zone
     * @param   $type       Type of the new zone
     * @param   $master     Master for slave zones, otherwise null
     * 
     * @return  array       New domain entry
     * 
     * @throws  AlreadyExistenException it the domain exists already
     */
    public function addDomain(string $name, string $type, ? string $master) : array
    {
        if (!in_array($type, [' MASTER ', ' SLAVE ', ' NATIVE '])) {
            throw new \Exceptions\SemanticException();
        }

        $this->db->beginTransaction();

        $query = $this->db->prepare(' SELECT id FROM domains WHERE name = : name ');
        $query->bindValue(' : name ', $name, \PDO::PARAM_STR);
        $query->execute();

        $record = $query->fetch();

        if ($record !== false) { // Domain already exists
            $this->db->rollBack();
            throw new \Exceptions\AlreadyExistentException();
        }

        if ($type === ' SLAVE ') {
            $query = $this->db->prepare(' INSERT INTO domains (name, type, master) VALUES(: name, : type, : master) ');
            $query->bindValue(' : master ', $master, \PDO::PARAM_STR);
        } else {
            $query = $this->db->prepare(' INSERT INTO domains (name, type) VALUES (: name, : type) ');
        }
        $query->bindValue(' : name ', $name, \PDO::PARAM_STR);
        $query->bindValue(' : type ', $type, \PDO::PARAM_STR);
        $query->execute();


        $query = $this->db->prepare(' SELECT id, name, type, master FROM domains WHERE name = : name ');
        $query->bindValue(' : name ', $name, \PDO::PARAM_STR);
        $query->execute();

        $record = $query->fetch();
        $record[' id '] = intval($record[' id ']);
        if ($type !== ' SLAVE ') {
            unset($record[' master ']);
        }

        $this->db->commit();

        return $record;
    }

    /**
     * Delete domain
     * 
     * @param   $id     Id of the domain to delete
     * 
     * @return  void
     * 
     * @throws  NotFoundException   if domain does not exist
     */
    public function deleteDomain(int $id) : void
    {
        $this->db->beginTransaction();

        $query = $this->db->prepare(' SELECT id FROM domains WHERE id = : id ');
        $query->bindValue(' : id ', $id, \PDO::PARAM_INT);
        $query->execute();

        if ($query->fetch() === false) { //Domain does not exist
            $this->db->rollBack();
            throw new \Exceptions\NotFoundException();
        }

        $query = $this->db->prepare('
            DELETE E FROM remote E
            LEFT OUTER JOIN records R ON R . id = E . record
            WHERE R . domain_id = : id ');
        $query->bindValue(' : id ', $id, \PDO::PARAM_INT);
        $query->execute();

        $query = $this->db->prepare(' DELETE FROM records WHERE domain_id = : id ');
        $query->bindValue(' : id ', $id, \PDO::PARAM_INT);
        $query->execute();

        $query = $this->db->prepare(' DELETE FROM domains WHERE id = : id ');
        $query->bindValue(' : id ', $id, \PDO::PARAM_INT);
        $query->execute();

        $this->db->commit();
    }

    /**
     * Get domain
     * 
     * @param   $id     Id of the domain to get
     * 
     * @return  array   Domain data
     * 
     * @throws  NotFoundException   if domain does not exist
     */
    public function getDomain(int $id) : array
    {
        $query = $this->db->prepare('
            SELECT D . id, D . name, D . type, D . master, COUNT (R . domain_id) as records FROM domains D
            LEFT OUTER JOIN records R ON D . id = R . domain_id
            WHERE D . id = : id
            GROUP BY D . id, D . name, D . type, D . master
            ');
        $query->bindValue(' : id ', $id, \PDO::PARAM_INT);
        $query->execute();

        $record = $query->fetch();

        if ($record === false) {
            throw new \Exceptions\NotFoundException();
        }

        $record[' id '] = intval($record[' id ']);
        $record[' records '] = intval($record[' records ']);
        if ($record[' type '] !== ' SLAVE ') {
            unset($record[' master ']);
        }

        return $record;
    }

    /**
     * Get type of given domain
     * 
     * @param   int     Domain id
     * 
     * @return  string  Domain type
     * 
     * @throws  NotFoundException   if domain does not exist
     */
    public function getDomainType(int $id) : string
    {
        $query = $this->db->prepare(' SELECT type FROM domains WHERE id = : id ');
        $query->bindValue(' : id ', $id, \PDO::PARAM_INT);
        $query->execute();
        $record = $query->fetch();

        if ($record === false) {
            throw new \Exceptions\NotFoundException();
        }

        return $record[' type '];
    }

    /**
     * Update master for slave zone
     * 
     * @param   int     Domain id
     * @param   string  New master
     * 
     * @return  void
     * 
     * @throws  NotFoundException   if domain does not exist
     * @throws  SemanticException   if domain is no slave zone
     */
    public function updateSlave(int $id, string $master)
    {
        if ($this->getDomainType($id) !== ' SLAVE ') {
            throw new \Exceptions\SemanticException();
        }

        $query = $this->db->prepare(' UPDATE domains SET master = : master WHERE id = : id ');
        $query->bindValue(' : id ', $id, \PDO::PARAM_INT);
        $query->bindValue(' : master', $master, \PDO::PARAM_STR);
        $query->execute();
    }
}
