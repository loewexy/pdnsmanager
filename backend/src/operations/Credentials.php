<?php

namespace Operations;

require '../vendor/autoload.php';

/**
 * This class provides functions for retrieving and modifying credentials.
 */
class Credentials
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
     * Get a list of credentials
     * 
     * @param   $pi         PageInfo object, which is also updated with total page number
     * @param   $recordId   Id of the record for which the table should be retrieved
     * 
     * @return  array       Array with credentials
     */
    public function getCredentials(\Utils\PagingInfo &$pi, int $recordId) : array
    {
        //Count elements
        if ($pi->pageSize === null) {
            $pi->totalPages = 1;
        } else {
            $query = $this->db->prepare('
                SELECT COUNT(*) AS total
                FROM remote
                WHERE record=:recordId
            ');

            $query->bindValue(':recordId', $recordId, \PDO::PARAM_INT);
            $query->execute();
            $record = $query->fetch();

            $pi->totalPages = ceil($record['total'] / $pi->pageSize);
        }

        $pageStr = \Services\Database::makePagingString($pi);

        $query = $this->db->prepare('SELECT id,description,type FROM remote WHERE record=:recordId ORDER BY id ASC' . $pageStr);
        $query->bindValue(':recordId', $recordId, \PDO::PARAM_INT);
        $query->execute();

        $data = $query->fetchAll();

        return array_map(function ($item) {
            $item['id'] = intval($item['id']);
            return $item;
        }, $data);
    }

    /**
     * Add a new credential
     * 
     * @param   $record         Record for which this credential should be valid
     * @param   $description    Description for this credential
     * @param   $type           Type of the credential, can bei key or password
     * @param   $key            Key if type is key, null otherwise
     * @param   $password       Password if type was password, null otherwise
     * 
     * @return  array           The new credential entry.
     */
    public function addCredential(int $record, string $description, string $type, ? string $key, ? string $password) : array
    {
        if ($type === 'key') {
            if (openssl_pkey_get_public($key) === false) {
                throw new \Exceptions\InvalidKeyException();
            }
            $secret = $key;
        } elseif ($type === 'password') {
            $secret = password_hash($password, PASSWORD_DEFAULT);
        } else {
            throw new \Exceptions\SemanticException();
        }

        $this->db->beginTransaction();

        $query = $this->db->prepare('INSERT INTO remote (record, description, type, security) VALUES (:record, :description, :type, :security)');
        $query->bindValue(':record', $record, \PDO::PARAM_INT);
        $query->bindValue(':description', $description, \PDO::PARAM_STR);
        $query->bindValue(':type', $type, \PDO::PARAM_STR);
        $query->bindValue(':security', $secret, \PDO::PARAM_STR);
        $query->execute();

        $query = $this->db->prepare('SELECT id, description, type, security FROM remote ORDER BY id DESC LIMIT 1');
        $query->execute();
        $record = $query->fetch();

        $record['id'] = intval($record['id']);
        if ($record['type'] === 'key') {
            $record['key'] = $record['security'];
            unset($record['security']);
        } else {
            unset($record['security']);
        }

        $this->db->commit();

        return $record;
    }

    /**
     * Delete credential
     * 
     * @param   $recordId       Id of the record
     * @param   $credentialId   Id of the credential to delete
     * 
     * @return  void
     * 
     * @throws  NotFoundException   if credential does not exist
     */
    public function deleteCredential(int $recordId, int $credentialId) : void
    {
        $this->db->beginTransaction();

        $query = $this->db->prepare('SELECT id FROM remote WHERE id=:id AND record=:record');
        $query->bindValue(':id', $credentialId, \PDO::PARAM_INT);
        $query->bindValue(':record', $recordId, \PDO::PARAM_INT);
        $query->execute();

        if ($query->fetch() === false) { //Credential does not exist
            $this->db->rollBack();
            throw new \Exceptions\NotFoundException();
        }

        $query = $this->db->prepare('DELETE FROM remote WHERE id=:id');
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();

        $this->db->commit();
    }
}
