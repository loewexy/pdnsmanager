<?php

namespace Plugins\Sessionstorage;

require '../vendor/autoload.php';

/**
 * Implements a session storage plugin for using PHPs APCu.
 */
class apcu implements InterfaceSessionstorage
{
    /** @var \Monolog\Logger */
    private $logger;

    /**
     * Construct the object
     * 
     * @param   $logger Monolog logger instance for error handling
     * @param   $config The configuration for the Plugin if any was provided
     */
    public function __construct(\Monolog\Logger $logger, array $config = null)
    {
        $this->logger = $logger;

        if (!function_exists('apcu_store')) {
            $this->logger->critical('PHP APCu extension is not available but configured as session storage backend exiting now');
            exit();
        }
    }

    /**
     * Save new entry.
     * 
     * @param   $key    The key for the entry
     * @param   $value  The value for the entry
     * @param   $ttl    The time (in s) for which this item should be available
     */
    public function set(string $key, string $value, int $ttl) : void
    {
        $this->logger->debug('Storing data to APCu', ['key' => $key, 'value' => $value, 'ttl' => $ttl]);

        apcu_store($key, $value, $ttl);
    }

    /**
     * Queries the existence of some entry.
     * 
     * @param   $key    The key to query
     */
    public function exists(string $key) : bool
    {
        $this->logger->debug('Checking for APCu key existence', ['key' => $key]);

        return apcu_exists($key);
    }

    /**
     * Get the value for a given key. This should also reset the ttl to the given value.
     * 
     * @param   $key    The key for the entry to get
     * @param   $ttl    The new ttl for the entry
     */
    public function get(string $key, int $ttl) : string
    {
        $this->logger->debug('Getting data from APCu', ['key' => $key, 'ttl' => $ttl]);

        $value = apcu_fetch($key);

        if ($value == false) {
            $this->logger->error('Non existing key was queried from APCu', ['key' => $key]);
            throw new \InvalidArgumentException('The requested key was not in the database!');
        }

        apcu_store($key, $value, $ttl);

        return $value;
    }

    /**
     * Delete the value for a given key.
     * 
     * @param   $key    The key to delete
     */
    public function delete(string $key) : void
    {
        $this->logger->debug('Deleting key from APCu', ['key' => $key]);

        apcu_delete($key);
    }
}
