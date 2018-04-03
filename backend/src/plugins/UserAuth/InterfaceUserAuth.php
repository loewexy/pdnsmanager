<?php

namespace Plugins\UserAuth;

require '../vendor/autoload.php';

/**
 * This interface provides the neccessary functions for
 * a user authentication backend.
 */
interface InterfaceUserAuth
{
    /**
     * Construct the object
     * 
     * @param   $logger Monolog logger instance for error handling
     * @param   $db     Database connection
     * @param   $config The configuration for the Plugin if any was provided
     */
    public function __construct(\Monolog\Logger $logger, \PDO $db, array $config = null);

    /**
     * Authenticate user.
     * 
     * @param   $username   The username for authentication
     * @param   $password   The password for authentication
     * 
     * @return  true if valid false otherwise
     */
    public function authenticate(string $username, string $password) : bool;
}
