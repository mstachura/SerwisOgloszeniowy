<?php
/**
 * Category repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;
use Silex\Application;
use Utils\Paginator;

/**
 * Class CategoryRepository.
 */
class CategoryRepository
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
     * CategoryRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Find all
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
     * Find one by id
     * Find one record.
     *
     * @param string $id Element id
     *
     * @return array|mixed Result
     */
    public function findOneById($id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
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
        return $queryBuilder->select('id', 'name')
            ->from('category');
    }

    public function save(Application $app, $category)
    {

        if (isset($category['id']) && ctype_digit((string)$category['id'])) {
            // update record
            $id = $category['id'];
            unset($category['id']);


            return $this->db->update('category', $category, ['id' => $id]);
        } else {
            // add new record

            return $this->db->insert('category', $category);
        }
    }

    /**
     * Delete
     * @param $category
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function delete($category)
    {
        return $this->db->delete('category', ['id' => $category['id']]);
    }

}
