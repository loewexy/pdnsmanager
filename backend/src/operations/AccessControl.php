<?php

namespace Operations;

require '../vendor/autoload.php';

/**
 * This class provides access control for the application.
 */
class AccessControl
{
    /** @var \Monolog\Logger */
    private $logger;

    /** @var \PDO */
    private $db;

    public function __construct(\Slim\Container $c)
    {
        $this->logger = $c->logger;
        $this->db = $c->db;
    }

    /**
     * Determines if the given user has admin privileges.
     * 
     * @param   $userId User id of the user
     * 
     * @return bool true if admin, false otherwise
     */
    public function isAdmin(int $userId) : bool
    {
        $query = $this->db->prepare('SELECT type FROM users WHERE id=:id');
        $query->bindValue(':id', $userId, \PDO::PARAM_STR);
        $query->execute();

        $record = $query->fetch();

        if ($record === false) {
            $this->logger->error('Queried record for non existing user id, this should not happen.', ['userId' => $userId]);
            return false;
        }

        return $record['type'] == 'admin';
    }

    /**
     * Check if a given user has permissons for a given domain.
     * 
     * @param   $userId     User id of the user
     * @param   $domainId   Domain to check
     * 
     * @return bool true if access is granted, false otherwise
     */
    public function canAccessDomain(int $userId, int $domainId) : bool
    {
        if ($this->isAdmin($userId)) {
            return true;
        }

        $query = $this->db->prepare('SELECT user_id,domain_id FROM permissions WHERE user_id=:userId AND domain_id=:domainId');
        $query->bindValue(':userId', $userId, \PDO::PARAM_INT);
        $query->bindValue(':domainId', $domainId, \PDO::PARAM_INT);
        $query->execute();

        $record = $query->fetch();

        if ($record === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check if a given user has permissons for a given record.
     * 
     * @param   $userId     User id of the user
     * @param   $recordId   Record to check
     * 
     * @return bool true if access is granted, false otherwise
     */
    public function canAccessRecord(int $userId, int $recordId) : bool
    {
        if ($this->isAdmin($userId)) {
            return true;
        }

        $query = $this->db->prepare('
            SELECT * FROM records R
            LEFT OUTER JOIN permissions P ON P.domain_id=R.domain_id
            WHERE R.id=:recordId AND P.user_id=:userId
        ');
        $query->bindValue(':userId', $userId, \PDO::PARAM_INT);
        $query->bindValue(':recordId', $recordId, \PDO::PARAM_INT);
        $query->execute();

        $record = $query->fetch();

        if ($record === false) {
            return false;
        } else {
            return true;
        }
    }
}
