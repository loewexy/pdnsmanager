<?php

namespace Plugins\Sessionstorage;

require '../vendor/autoload.php';

/**
 * Implements a session storage plugin for using PHPs APCu.
 */
class memcached implements InterfaceSessionstorage
{
    /** @var \Monolog\Logger */
    private $logger;

    /** @var \Memcached */
    private $memcached;

    /**
     * Construct the object
     * 
     * @param   $logger Monolog logger instance for error handling
     * @param   $config The configuration for the Plugin if any was provided
     */
    public function __construct(\Monolog\Logger $logger, array $config = null)
    {
        $this->logger = $logger;

        if (!class_exists('Memcached')) {
            $this->logger->critical('PHP Memcached extension is not available but configured as session storage backend exiting now');
            exit();
        }
        $this->memcached = new \Memcached();
        if (!array_key_exists('host', $config) || !array_key_exists('port', $config)) {
            $this->logger->critical('Memcached session configuration missing host or port value');
            exit();
        }
        $this->memcached->addServer($config['host'], $config['port']);
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
        $this->logger->debug('Storing data to Memcached', ['key' => $key, 'value' => $value, 'ttl' => $ttl]);

        $this->memcached->set($key, $value, $ttl);
    }

    /**
     * Queries the existence of some entry.
     * 
     * @param   $key    The key to query
     */
    public function exists(string $key) : bool
    {
        $this->logger->debug('Checking for Memcached key existence', ['key' => $key]);

        $this->memcached->get($key);
        return \Memcached::RES_NOTFOUND !== $this->memcached->getResultCode();
    }

    /**
     * Get the value for a given key. This should also reset the ttl to the given value.
     * 
     * @param   $key    The key for the entry to get
     * @param   $ttl    The new ttl for the entry
     */
    public function get(string $key, int $ttl) : string
    {
        $this->logger->debug('Getting data from Memcached', ['key' => $key, 'ttl' => $ttl]);

        $value = $this->memcached->get($key);

        if ($value == false) {
            $this->logger->error('Non existing key was queried from Memcached', ['key' => $key]);
            throw new \InvalidArgumentException('The requested key was not in the database!');
        }

        $this->memcached->touch($key, $ttl);

        return $value;
    }

    /**
     * Delete the value for a given key.
     * 
     * @param   $key    The key to delete
     */
    public function delete(string $key) : void
    {
        $this->logger->debug('Deleting key from Memcached', ['key' => $key]);

        $this->memcached->delete($key);
    }
}
