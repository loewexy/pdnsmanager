<?php

namespace Plugins\Sessionstorage;

require '../vendor/autoload.php';

/**
 * This interface provides the neccessary functions for a session storage backend
 */
interface InterfaceSessionstorage
{
    /**
     * Construct the object
     * 
     * @param   $logger Monolog logger instance for error handling
     * @param   $config The configuration for the Plugin if any was provided
     */
    public function __construct(\Monolog\Logger $logger, array $config = null);

    /**
     * Save new entry.
     * 
     * @param   $key    The key for the entry
     * @param   $value  The value for the entry
     * @param   $ttl    The time (in s) for which this item should be available
     */
    public function set(string $key, string $value, int $ttl) : void;

    /**
     * Queries the existence of some entry.
     * 
     * @param   $key    The key to query
     */
    public function exists(string $key) : bool;

    /**
     * Get the value for a given key. This should also reset the ttl to the given value.
     * 
     * @param   $key    The key for the entry to get
     * @param   $ttl    The new ttl for the entry
     */
    public function get(string $key, int $ttl) : string;

    /**
     * Delete the value for a given key.
     * 
     * @param   $key    The key to delete
     */
    public function delete(string $key) : void;
}
