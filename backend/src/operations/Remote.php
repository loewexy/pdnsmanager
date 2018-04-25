<?php

namespace Operations;

require '../vendor/autoload.php';

/**
 * This class provides functions for the remote api.
 */
class Remote
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
     * Add new record
     * 
     * @param   $record     Name of the new record
     * @param   $content    Type of the new record
     * @param   $password   Content of the new record
     * 
     * @throws  NotFoundException   if the record does not exist
     * @throws  ForbiddenException  if the password is not valid for the record
     */
    public function updatePassword(int $record, string $content, string $password) : void
    {
        $query = $this->db->prepare('SELECT id FROM records WHERE id=:record');
        $query->bindValue(':record', $record, \PDO::PARAM_INT);
        $query->execute();

        if ($query->fetch() === false) {
            throw new \Exceptions\NotFoundException();
        }

        $query = $this->db->prepare('SELECT security FROM remote WHERE record=:record AND type=\'password\'');
        $query->bindValue(':record', $record, \PDO::PARAM_INT);
        $query->execute();

        $validPwFound = false;

        while ($row = $query->fetch()) {
            if (password_verify($password, $row['security'])) {
                $validPwFound = true;
                break;
            }
        }

        if (!$validPwFound) {
            throw new \Exceptions\ForbiddenException();
        }

        $records = new \Operations\Records($this->c);
        $records->updateRecord($record, null, null, $content, null, null);
    }
}
