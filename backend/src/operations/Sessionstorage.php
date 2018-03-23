<?php

namespace Operations;

require '../vendor/autoload.php';

/**
 * This is a proxy class which load the configured plugin as
 * backend and proxies queries to it.
 */
class Sessionstorage
{
    /** @var \Monolog\Logger */
    private $logger;

    /** @var InterfaceSessionstorage */
    private $backend;

    public function __construct(\Slim\Container $c)
    {
        $this->logger = $c->logger;

        $config = $c['config']['sessionstorage'];

        $plugin = $config['plugin'];
        $pluginConfig = $config['config'];

        $pluginClass = '\\Plugins\\Sessionstorage\\' . $plugin;

        //Check if plugin is available
        if (!class_exists($pluginClass)) {
            $this->logger->critical('The configured session storage plugin does not exist', ['plugin' => $plugin]);
            exit();
        }

        //Try to create class with given name
        $this->backend = new $pluginClass($this->logger, $pluginConfig);

        if (!$this->backend instanceof \Plugins\Sessionstorage\InterfaceSessionstorage) {
            $this->logger->critical('The configured plugin does not implement InterfaceSessionstorage', ['pluginname' => $plugin]);
            exit();
        }

        $this->logger->debug("Session storage plugin was loaded", ['plugin' => $plugin]);
    }

    public function set(string $key, string $value, int $ttl) : void
    {
        $this->backend->set($key, $value, $ttl);
    }

    public function exists(string $key) : bool
    {
        return $this->backend->exists($key);
    }

    public function get(string $key, int $ttl) : string
    {
        return $this->backend->get($key, $ttl);
    }

    public function delete(string $key) : void
    {
        $this->backend->delete($key);
    }
}
