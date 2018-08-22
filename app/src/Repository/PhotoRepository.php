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

    /**
     * Query all
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select('p.id', 'p.name', 'p.source', 'p.ad_id')
            ->from('photo', 'p');
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
        $queryBuilder = $this->queryAll();
        $queryBuilder
            ->where('id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    /**
     * Find one by advertisement id
     * @param $ad_id
     * @return array|mixed
     */
    public function findOneByAdvertisementId($ad_id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder
            ->where('ad_id = :ad_id')
            ->setParameter(':ad_id', $ad_id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    /**
     * Delete
     * @param $photo
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function delete($photo)
    {
//        $this->db->beginTransaction();
        try {
            return $this->db->delete('photo', ['id' => $photo['id']]);
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
