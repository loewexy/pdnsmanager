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
     * Add new user
     * 
     * @param   $name       Name of the new zone
     * @param   $type       Type of the new zone
     * @param   $password   Password for the new user
     * 
     * @return  array       New user entry
     * 
     * @throws  AlreadyExistenException it the user exists already
     */
    public function addUser(string $name, string $type, string $password) : array
    {
        if (!in_array($type, ['admin', 'user'])) {
            throw new \Exceptions\SemanticException();
        }

        $this->db->beginTransaction();

        $query = $this->db->prepare('SELECT id FROM users WHERE name=:name AND backend=\'native\'');
        $query->bindValue(':name', $name, \PDO::PARAM_STR);
        $query->execute();

        $record = $query->fetch();

        if ($record !== false) { // Domain already exists
            $this->db->rollBack();
            throw new \Exceptions\AlreadyExistentException();
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $query = $this->db->prepare('INSERT INTO users (name, backend, type, password) VALUES(:name, \'native\', :type, :password)');
        $query->bindValue(':name', $name, \PDO::PARAM_STR);
        $query->bindValue(':type', $type, \PDO::PARAM_STR);
        $query->bindValue(':password', $passwordHash, \PDO::PARAM_STR);
        $query->execute();

        $query = $this->db->prepare('SELECT id,name,type FROM users WHERE name=:name AND backend=\'native\'');
        $query->bindValue(':name', $name, \PDO::PARAM_STR);
        $query->execute();

        $record = $query->fetch();
        $record['id'] = intval($record['id']);

        $this->db->commit();

        return $record;
    }

    /**
     * Delete user
     * 
     * @param   $id     Id of the user to delete
     * 
     * @return  void
     * 
     * @throws  NotFoundException   if user does not exist
     */
    public function deleteDomain(int $id) : void
    {
        $this->db->beginTransaction();

        $query = $this->db->prepare('SELECT id FROM users WHERE id=:id');
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();

        if ($query->fetch() === false) { //User does not exist
            $this->db->rollBack();
            throw new \Exceptions\NotFoundException();
        }

        $query = $this->db->prepare('DELETE FROM permissions WHERE user_id=:id');
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();

        $query = $this->db->prepare('DELETE FROM users WHERE id=:id');
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();

        $this->db->commit();
    }

    /**
     * Get user
     * 
     * @param   $id     Id of the user to get
     * 
     * @return  array   User data
     * 
     * @throws  NotFoundException   if user does not exist
     */
    public function getUser(int $id) : array
    {
        $config = $this->c['config']['authentication'];

        $query = $this->db->prepare('SELECT id,name,type,backend FROM users WHERE id=:id');
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();

        $record = $query->fetch();

        if ($record === false) {
            throw new \Exceptions\NotFoundException();
        }

        if (!array_key_exists($record['backend'], $config)) {
            throw new \Exceptions\NotFoundException();
        }
        if (!array_key_exists('prefix', $config[$record['backend']])) {
            throw new \Exceptions\NotFoundException();
        }

        $prefix = $config[$record['backend']]['prefix'];

        if ($prefix === 'default') {
            $name = $record['name'];
        } else {
            $name = $prefix . '/' . $record['name'];
        }

        return [
            'id' => intval($record['id']),
            'name' => $name,
            'type' => $record['type'],
            'native' => $record['backend'] === 'native'
        ];
    }

    /** Update user
     * 
     * If params are null do not change. If user is not native, name and password are ignored.
     * 
     * @param   $userId     User to update
     * @param   $name       New name
     * @param   $type       New type
     * @param   $password   New password
     * 
     * @return  void
     * 
     * @throws  NotFoundException           The given record does not exist
     * @throws  AlreadyExistentException    The given record name does already exist
     */
    public function updateUser(int $userId, ? string $name, ? string $type, ? string $password)
    {
        $this->db->beginTransaction();

        $query = $this->db->prepare('SELECT id,name,type,backend,password FROM users WHERE id=:userId');
        $query->bindValue(':userId', $userId);
        $query->execute();

        $record = $query->fetch();

        if ($record === false) {
            $this->db->rollBack();
            throw new \Exceptions\NotFoundException();
        }

        if ($record['backend'] !== 'native') {
            $name = null;
            $password = null;
        }

        if ($record['backend'] === 'native' && $name !== null) {
            //Check if user with new name already exists
            $query = $this->db->prepare('SELECT id FROM users WHERE name=:name AND backend=\'native\'');
            $query->bindValue(':name', $name);
            $query->execute();
            $recordTest = $query->fetch();
            if ($recordTest !== false && intval($recordTest['id']) !== $userId) {
                throw new \Exceptions\AlreadyExistentException();
            }
        }

        $name = $name === null ? $record['name'] : $name;
        $type = $type === null ? $record['type'] : $type;
        $password = $password === null ? $record['password'] : password_hash($password, PASSWORD_DEFAULT);

        $query = $this->db->prepare('UPDATE users SET name=:name,type=:type,password=:password WHERE id=:userId');
        $query->bindValue(':userId', $userId);
        $query->bindValue(':name', $name);
        $query->bindValue(':type', $type);
        $query->bindValue(':password', $password);
        $query->execute();

        $this->db->commit();
    }
}
