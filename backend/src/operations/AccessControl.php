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
}
