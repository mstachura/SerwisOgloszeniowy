<?php
/**
 * photo repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;

/**
 * Class photoRepository.
 */
class PhotoRepository
{
    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * photoRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }


    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select('c.id', 'c.date', 'c.text', 'c.user_id', 'c.ad_id', 'u.login')
            ->from('photo', 'c')
            ->innerjoin('c', 'user', 'u', 'u.id=c.user_id');
    }

    /**
     * Find one record.
     *
     * @param string $id Element id
     *
     * @return array|mixed Result
     */

    public function findOneById($id)
    {
        $queryBuilder = $this->queryAllOld();
        $queryBuilder
            ->where('id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    public function findAllFromAdvertisement($ad_id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder
            ->where('ad_id = :ad_id')
            ->setParameter(':ad_id', $ad_id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetchAll();

        return !$result ? [] : $result;
    }
}
