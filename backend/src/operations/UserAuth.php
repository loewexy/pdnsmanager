<?php

namespace Operations;

require '../vendor/autoload.php';

use \Exceptions\PluginNotFoundException as PluginNotFoundException;

/**
 * This class provides user authentication for the application.
 * Its main purpose is to find the apropriate authentication
 * plugin. It also ensures that a user entry for that user is
 * in the database.
 */
class UserAuth
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
     * Authenticates a user with username/password combination.
     * 
     * @param   $username   Username
     * @param   $password   Password
     * 
     * @return  int -1 if authentication failed, the user id otherwise
     * 
     * @throws \Exceptions\PluginNotFoundExecption if no matching backend can be found
     */
    public function authenticate(string $username, string $password) : int
    {
        if (strpos($username, '/') === false) { // no explicit backend specification
            $prefix = 'default';
            $name = $username;
        } else {
            $parts = preg_split('/\//', $username, 2);
            $prefix = $parts[0];
            $name = $parts[1];
        }

        $this->logger->debug('Trying to authenticate with info', ['prefix' => $prefix, 'name' => $name]);

        try {
            $backend = '';
            if ($this->authenticateBackend($prefix, $name, $password, $backend)) {
                return $this->localUser($backend, $name, $password);
            } else {
                return -1;
            }
        } catch (\Exceptions\PluginNotFoundException $e) {
            throw $e;
        }
    }

    /**
     * This function searches for an apropriate backend and calls it
     * to authenticate the user.
     * 
     * @param   $backend    The name of the backend to use
     * @param   $username   The username to use
     * @param   $password   The password to use
     * @param   $backendId  Output to return the backend id used
     * 
     * @return  bool true if authentication successfull false otherwise
     * 
     * @throws \Exceptions\PluginNotFoundExecption if no matching backend can be found
     */
    private function authenticateBackend(string $backend, string $username, string $password, string &$backendId) : bool
    {
        $config = $this->c['config']['authentication'];

        $configForPrefix = array_filter($config, function ($v, $k) use ($backend) {
            return $backend === $v['prefix'];
        }, ARRAY_FILTER_USE_BOTH);

        if (count($configForPrefix) === 0) { // Check if backend is configured for prefix
            $this->logger->warning('No authentication backend configured for prefix', ['prefix' => $backend]);
            throw new PluginNotFoundException('No authentication backend configured for this user.');
        } elseif (count($configForPrefix) > 1) {
            $this->logger->error('Two authentication backends configured for prefix.', ['prefix' => $backend]);
        }

        $backendId = array_keys($configForPrefix)[0];

        $plugin = $config[$backendId]['plugin'];
        $pluginClass = '\\Plugins\\UserAuth\\' . $plugin;
        $pluginConfig = $config[$backendId]['config'];

        if (!class_exists($pluginClass)) { // Check if given backend class exists
            $this->logger->error('The configured UserAuth plugin does not exist', ['prefix' => $backend, 'plugin' => $plugin]);
            throw new PluginNotFoundException('The authentication request can not be processed.');
        }

        //Try to create class with given name
        $backendObj = new $pluginClass($this->logger, $this->db, $pluginConfig);

        if (!$backendObj instanceof \Plugins\UserAuth\InterfaceUserAuth) { // Check if class implements interface
            $this->logger->error('The configured plugin does not implement InterfaceUserAuth', ['plugin' => $plugin, 'prefix' => $backend]);
            throw new PluginNotFoundException('The authentication request can not be processed.');
        }

        $this->logger->debug("UserAuth plugin was loaded", ['plugin' => $plugin, 'prefix' => $backend, 'backend' => $backendId]);

        return $backendObj->authenticate($username, $password);
    }

    /**
     * Ensures the user from the given backend has a entry in the local database,
     * then returns the user id.
     * 
     * @param   $backend    The name of the backend to use
     * @param   $username   The username to use
     * @param   $password   The password to use
     * 
     * @return  int The local user id
     */
    private function localUser(string $backend, string $username, string $password) : int
    {
        $config = $this->c['config']['authentication'];
        $backendId = $config[$backend]['plugin'];

        $this->db->beginTransaction();

        $query = $this->db->prepare('SELECT id FROM users WHERE name=:name AND backend=:backend');
        $query->bindValue(':name', $username, \PDO::PARAM_STR);
        $query->bindValue(':backend', $backendId, \PDO::PARAM_STR);
        $query->execute();

        $record = $query->fetch();

        if ($record === false) {
            $insert = $this->db->prepare('INSERT INTO users (name,backend,type) VALUES (:name, :backend, \'user\')');
            $insert->bindValue(':name', $username, \PDO::PARAM_STR);
            $insert->bindValue(':backend', $backendId, \PDO::PARAM_STR);
            $insert->execute();

            $query->execute();

            $record = $query->fetch();

            $this->logger->info('Non existing user created', ['username' => $username, 'backendId' => $backendId, 'newId' => $record['id']]);
        } else {
            $this->logger->debug('User was found in database', ['username' => $username, 'backendId' => $backendId, 'id' => $record['id']]);
        }

        $this->db->commit();

        return $record['id'];
    }
}
