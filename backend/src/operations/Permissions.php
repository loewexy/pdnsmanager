<?php

namespace Operations;

require '../vendor/autoload.php';

/**
 * This class provides functions for retrieving and modifying permissions.
 */
class Permissions
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
     * Get a list of permissions
     * 
     * @param   $pi         PageInfo object, which is also updated with total page number
     * @param   $userId     Id of the user for which the permissions should be retrieved
     * 
     * @return  array       Array with matching permissions
     */
    public function getPermissions(\Utils\PagingInfo &$pi, int $userId) : array
    {
        $this->db->beginTransaction();

        //Count elements
        if ($pi->pageSize === null) {
            $pi->totalPages = 1;
        } else {
            $query = $this->db->prepare('SELECT COUNT(*) AS total FROM permissions WHERE user_id=:userId');

            $query->bindValue(':userId', $userId, \PDO::PARAM_INT);

            $query->execute();
            $record = $query->fetch();

            $pi->totalPages = ceil($record['total'] / $pi->pageSize);
        }

        $pageStr = \Services\Database::makePagingString($pi);

        $query = $this->db->prepare('
            SELECT P.domain_id as domainId,D.name as domainName FROM permissions P
            LEFT OUTER JOIN domains D ON D.id=P.domain_id
            WHERE P.user_id=:userId'
            . $pageStr);

        $query->bindValue(':userId', $userId, \PDO::PARAM_INT);

        $query->execute();

        $data = $query->fetchAll();

        $this->db->commit();

        return $data;
    }

    /**
     * Add a new permission
     * 
     * @param   $userId     User id
     * @param   $domainId   Domain for which access should be granted
     * 
     * @return  void
     * 
     * @throws  NotFoundException If domain or user was not found
     */
    public function addPermission(int $userId, int $domainId) : void
    {
        $this->db->beginTransaction();

        $query = $this->db->prepare('SELECT id FROM users WHERE id=:userId');
        $query->bindValue(':userId', $userId, \PDO::PARAM_INT);
        $query->execute();
        if ($query->fetch() === false) {
            $this->db->rollBack();
            throw new \Exceptions\NotFoundException();
        }

        $query = $this->db->prepare('SELECT id FROM domains WHERE id=:domainId');
        $query->bindValue(':domainId', $domainId, \PDO::PARAM_INT);
        $query->execute();
        if ($query->fetch() === false) {
            $this->db->rollBack();
            throw new \Exceptions\NotFoundException();
        }

        $query = $this->db->prepare('SELECT * FROM permissions WHERE domain_id=:domainId AND user_id=:userId');
        $query->bindValue(':domainId', $domainId, \PDO::PARAM_INT);
        $query->bindValue(':userId', $userId, \PDO::PARAM_INT);
        $query->execute();
        if ($query->fetch() === false) {
            $query = $this->db->prepare('INSERT INTO permissions (domain_id,user_id) VALUES (:domainId, :userId)');
            $query->bindValue(':domainId', $domainId, \PDO::PARAM_INT);
            $query->bindValue(':userId', $userId, \PDO::PARAM_INT);
            $query->execute();
        }

        $this->db->commit();
    }

    /**
     * Delete a permission
     * 
     * @param   $userId     User id
     * @param   $domainId   Domain for which access should be revoked
     * 
     * @return  void
     * 
     * @throws  NotFoundException   if the entry was not found
     */
    public function deletePermission(int $userId, int $domainId) : void
    {
        $this->db->beginTransaction();

        $query = $this->db->prepare('SELECT * FROM permissions WHERE domain_id=:domainId AND user_id=:userId');
        $query->bindValue(':domainId', $domainId, \PDO::PARAM_INT);
        $query->bindValue(':userId', $userId, \PDO::PARAM_INT);
        $query->execute();
        if ($query->fetch() === false) {
            $this->db->rollBack();
            throw new \Exceptions\NotFoundException();
        }

        $query = $this->db->prepare('DELETE FROM permissions WHERE domain_id=:domainId AND user_id=:userId');
        $query->bindValue(':domainId', $domainId, \PDO::PARAM_INT);
        $query->bindValue(':userId', $userId, \PDO::PARAM_INT);
        $query->execute();

        $this->db->commit();
    }
}
