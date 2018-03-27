<?php

namespace Operations;

require '../vendor/autoload.php';

/**
 * This class provides functions for retrieving and modifying soa records.
 */
class Soa
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
     * Get a list of domains according to filter criteria
     * 
     * @param   $domainId   Domain to update soa
     * @param   $mail       Mail of zone master
     * @param   $primary    The primary nameserver
     * @param   $refresh    The refresh interval
     * @param   $retry      The retry interval
     * @param   $expire     The expire timeframe
     * @param   $ttl        The zone ttl
     * 
     * @return  void
     * 
     * @throws  NotFoundException   If the given domain does not exist
     */
    public function setSoa(int $domainId, string $mail, string $primary, int $refresh, int $retry, int $expire, int $ttl)
    {
        $this->db->beginTransaction();

        $query = $this->db->prepare('SELECT id,name,type FROM domains WHERE id=:id');
        $query->bindValue(':id', $domainId, \PDO::PARAM_INT);
        $query->execute();
        $record = $query->fetch();

        if ($record === false) {
            $this->db->rollBack();
            throw new \Exceptions\NotFoundException();
        } elseif ($record['type'] === 'SLAVE') {
            $this->db->rollBack();
            throw new \Exceptions\SemanticException();
        } else {
            $domainName = $record['name'];
        }

        //Generate soa content string without serial
        $soaArray = [
            $primary,
            $this->fromEmail($mail),
            'serial',
            $refresh,
            $retry,
            $expire,
            $ttl
        ];

        $query = $this->db->prepare('SELECT content FROM records WHERE domain_id=:id AND type=\'SOA\'');
        $query->bindValue(':id', $domainId, \PDO::PARAM_INT);
        $query->execute();

        $content = $query->fetch();

        if ($content === false) { //No soa exists yet
            $soaArray[2] = strval($this->calculateSerial(0));
            $soaString = implode(' ', $soaArray);
            $changeDate = strval(time());

            $query = $this->db->prepare('
                INSERT INTO records (domain_id, name, type, content, ttl, change_date)
                VALUES (:domainId, :name, \'SOA\', :content, :ttl, :changeDate)
            ');
            $query->bindValue(':domainId', $domainId, \PDO::PARAM_INT);
            $query->bindValue(':name', $domainName, \PDO::PARAM_STR);
            $query->bindValue(':content', $soaString, \PDO::PARAM_STR);
            $query->bindValue(':ttl', $ttl, \PDO::PARAM_STR);
            $query->bindValue(':changeDate', $changeDate, \PDO::PARAM_INT);
            $query->execute();
        } else {
            $oldSerial = intval(explode(' ', $content['content'])[2]);

            $soaArray[2] = strval($this->calculateSerial($oldSerial));
            $soaString = implode(' ', $soaArray);
            $changeDate = strval(time());

            $query = $this->db->prepare('UPDATE records SET content=:content, ttl=:ttl,
                change_date=:changeDate WHERE domain_id=:domainId AND type=\'SOA\'');
            $query->bindValue(':domainId', $domainId, \PDO::PARAM_INT);
            $query->bindValue(':content', $soaString, \PDO::PARAM_STR);
            $query->bindValue(':ttl', $ttl, \PDO::PARAM_STR);
            $query->bindValue(':changeDate', $changeDate, \PDO::PARAM_INT);
            $query->execute();
        }

        $this->db->commit();
    }

    /**
     * Get soa record for domain
     * 
     * @param   $domainId   Domain to get soa from
     * 
     * @return  array       Soa data as associative array
     */
    public function getSoa(int $domainId)
    {
        $query = $this->db->prepare('SELECT content FROM records WHERE domain_id=:domainId AND type=\'SOA\'');
        $query->bindValue(':domainId', $domainId, \PDO::PARAM_INT);
        $query->execute();

        $record = $query->fetch();

        if ($record === false) {
            throw new \Exceptions\NotFoundException();
        }

        $soaArray = explode(' ', $record['content']);

        return [
            'primary' => $soaArray[0],
            'email' => $this->toEmail($soaArray[1]),
            'serial' => intval($soaArray[2]),
            'refresh' => intval($soaArray[3]),
            'retry' => intval($soaArray[4]),
            'expire' => intval($soaArray[5]),
            'ttl' => intval($soaArray[6])
        ];
    }

    /**
     * Increases the serial number of the given domain to the next required.
     * 
     * If domain has no present soa record this method does nothing.
     * 
     * @param   $domainId   Domain to update
     * 
     * @return  void
     */
    public function updateSerial(int $domainId) : void
    {
        $query = $this->db->prepare('SELECT content FROM records WHERE domain_id=:id AND type=\'SOA\'');
        $query->bindValue(':id', $domainId, \PDO::PARAM_INT);
        $query->execute();
        $content = $query->fetch();

        if ($content === false) {
            $this->logger->warning('Trying to update serial of domain without soa set it first', ['domainId' => $domainId]);
            return;
        }

        $soaArray = explode(' ', $content['content']);
        $soaArray[2] = strval($this->calculateSerial(intval($soaArray[2])));
        $soaString = implode(' ', $soaArray);

        $query = $this->db->prepare('UPDATE records SET content=:content WHERE domain_id=:domainId AND type=\'SOA\'');
        $query->bindValue(':content', $soaString, \PDO::PARAM_STR);
        $query->bindValue(':domainId', $domainId, \PDO::PARAM_INT);
        $query->execute();
    }

    /**
     * Calculate new serial from old
     * 
     * @param   $oldSerial  Old serial number
     * 
     * @return  int     New serial number
     */
    private function calculateSerial(int $oldSerial) : int
    {
        $time = new \DateTime(null, new \DateTimeZone('UTC'));
        $currentTime = intval($time->format('Ymd')) * 100;

        return \max($oldSerial + 1, $currentTime);
    }

    /**
     * Convert email to soa mail string
     * 
     * @param   $email  Email address
     * 
     * @return  string  Soa email address
     */
    private function fromEmail(string $email)
    {
        $parts = explode('@', $email);
        $parts[0] = str_replace('.', '\.', $parts[0]);
        $parts[] = '';
        return rtrim(implode(".", $parts), ".");
    }

    /**
     * Convert soa mail to mail string
     * 
     * @param   $soaMail    Soa email address
     * 
     * @return  string      Email address
     */
    private function toEmail(string $soaEmail)
    {
        $tmp = preg_replace('/([^\\\\])\\./', '\\1@', $soaEmail, 1);
        $tmp = preg_replace('/\\\\\\./', ".", $tmp);
        $tmp = preg_replace('/\\.$/', "", $tmp);
        return $tmp;
    }
}
