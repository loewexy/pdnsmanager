<?php

namespace Plugins\UserAuth;

require '../vendor/autoload.php';

/**
 * This provides a simple user auth mechanism where users can be
 * stored in the config file. The config property therefore should
 * be a array mapping usernames to results of password_hash()
 */

class Config implements InterfaceUserAuth
{
    /** @var \Monolog\Logger */
    private $logger;

    /** @var \PDO */
    private $db;

    /** @var array */
    private $userList;

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
        $this->userList = $config ? $config : [];
    }

    /**
     * Authenticate user.
     * 
     * @param   $username   The username for authentication
     * @param   $password   The password for authentication
     * 
     * @return  true if valid false otherwise
     */
    public function authenticate(string $username, string $password) : bool
    {
        if (!array_key_exists($username, $this->userList)) {
            return false;
        }

        return password_verify($password, $this->userList[$username]);
    }
}
