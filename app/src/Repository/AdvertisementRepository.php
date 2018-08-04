<?php
/**
 * Advertisement repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;
use Utils\Paginator;

/**
 * Class AdvertisementRepository.
 */
class AdvertisementRepository
{

    /**
     * Number of items per page.
     *
     * const int NUM_ITEMS
     */
    const NUM_ITEMS = 3;

    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * AdvertisementRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Fetch all records.
     *
     * @return array Result
     */
    public function findAll()
    {
        $queryBuilder = $this->queryAll();
        return $queryBuilder->execute()->fetchAll();
    }

    /**
     * Get records paginated.
     *
     * @param int $page Current page number
     *
     * @return array Result
     */
    public function findAllPaginated($page = 1)
    {
        $countQueryBuilder = $this->queryAll()
            ->select('COUNT(DISTINCT ad.id) AS total_results')
            ->setMaxResults(1);

        $paginator = new Paginator($this->queryAll(), $countQueryBuilder);
        $paginator->setCurrentPage($page);
        $paginator->setMaxPerPage(static::NUM_ITEMS);

        return $paginator->getCurrentPageResults();
    }


    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select('id', 'name', 'price', 'description', 'user_id', 'type', 'location', 'province')
            ->from('ad');
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
     * Find one record.
     *
     * @param string $id Element id
     *
     * @return array|mixed Result
     */

    public function findAllByUser($user_id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder
            ->where('user_id = :user_id')
            ->setParameter(':user_id', $user_id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetchAll();

        return !$result ? [] : $result;
    }


    public function findAllByCategory($category_id){
        $queryBuilder = $this->queryAll();
        $queryBuilder
            ->where('category_id = :category_id')
            ->setParameter(':category_id', $category_id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetchAll();

        return !$result ? [] : $result;
    }

    /**
     * Save record.
     *
     * @param array $ad Ad
     *
     */
    public function save($ad)
    {
        if (isset($ad['id']) && ctype_digit((string) $ad['id'])) {
            // update record
            $id = $ad['id'];
            unset($ad['id']);

            return $this->db->update('ad', $ad, ['id' => $id]);
        } else {
            // add new record

            unset($ad['photo']);
            $this->db->insert('ad', $ad);
            $id = $this->db->lastInsertId();
          }            return $id;

    }

    public function delete($ad)
    {
        return $this->db->delete('ad', ['id' => $ad['id']]);
    }
}
