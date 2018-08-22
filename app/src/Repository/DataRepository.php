<?php
/**
 * Data repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;

/**
 * Class DataRepository
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
     * Find one by user id
     * @param $user_id
     * @return array|mixed
     */
    public function findOneByUserId($user_id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('user_id = :user_id')
            ->setParameter(':user_id', $user_id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    /**
     * Query all
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select('id', 'firstname', 'lastname', 'user_id', 'phone_number')
            ->from('user_data');
    }
}
