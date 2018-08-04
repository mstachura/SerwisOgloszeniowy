<?php
/**
 * User repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;

/**
 * Class UserDataRepository
 */
class DataRepository
{
    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * UserDataRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Find one record.
     *
     * @param string $id Element id
     *
     * @return array|mixed Result
     */

    public function findOneByUserId($user_id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('user_id = :user_id')
            ->setParameter(':user_id', $user_id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select('id', 'firstname', 'lastname', 'user_id', 'phone_number')
            ->from('user_data');
    }


}