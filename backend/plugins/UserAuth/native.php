<?php

namespace Plugins\UserAuth;

require '../vendor/autoload.php';

/**
 * This interface provides the neccessary functions for
 * a user authentication backend.
 */
class Native implements InterfaceUserAuth
{
    /** @var \Monolog\Logger */
    private $logger;

    /** @var \PDO */
    private $db;

    /**
     * Construct the object
     * 
     * @param   $logger Monolog logger instance for error handling
     * @param   $db     Database connection
     * @param   $config The configuration for the Plugin if any was provided
     */
    public function __construct(\Monolog\Logger $logger, \PDO $db, array $config = null)
    {
        $this->logger = $logger;
        $this->db = $db;
    }

    /**
     * Authenticate user.
     * 
     * @param   $username   The key for the entry
     * @param   $password   The value for the entry
     * 
     * @return  true if valid false otherwise
     */
    public function authenticate(string $username, string $password) : bool
    {
        $query = $this->db->prepare('SELECT id, password FROM users WHERE name=:name AND backend=\'native\'');
        $query->bindValue(':name', $username, \PDO::PARAM_STR);
        $query->execute();

        $record = $query->fetch();

        if ($record === false) {
            return false;
        }

        return password_verify($password, $record['password']);
    }
}
