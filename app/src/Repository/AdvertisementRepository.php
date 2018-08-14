<?php
/**
 * Advertisement repository.
 */

namespace Repository;

use Doctrine\DBAL\Connection;
use Utils\Paginator;
use Silex\Application;

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

    /**
     * Query all
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select('id', 'name', 'price', 'description', 'user_id', 'type_id', 'location_id', 'province', 'category_id')
            ->from('ad');
    }

    /**
     * Query All Extra
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function queryAllExtra()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select('ad.id', 'ad.name', 'ad.price', 'ad.description', 'ad.user_id', 'ad.category_id', 'ad.province', 'ad.type_id', 'ad.location_id', 'l.name')
            ->from('ad', 'ad')
            ->innerjoin('ad', 'location', 'l', 'ad.location_id = l.id');
    }

    /**
     * Query all extra 2
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function queryAllExtra2()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select('ad.id', 'ad.name', 'ad.price', 'ad.description', 'ad.user_id', 'ad.category_id', 'ad.province', 'ad.type_id', 'ad.location_id', 'c.name')
            ->from('ad', 'ad')
            ->innerjoin('ad', 'category', 'c', 'ad.category_id = c.id');
    }

    protected function queryAllExtra3()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select('ad.id', 'ad.name', 'ad.price', 'ad.description', 'ad.user_id', 'ad.category_id', 'ad.province', 'ad.type_id', 'ad.location_id', 't.name')
            ->from('ad', 'ad')
            ->innerjoin('ad', 'type', 't', 'ad.type_id = t.id');
    }

//    /**
//     * Query all extra 2
//     * @return \Doctrine\DBAL\Query\QueryBuilder
//     */
//    protected function queryAllExtra2()
//    {
//        $queryBuilder = $this->db->createQueryBuilder();
//        return $queryBuilder->select('ad.id', 'ad.name', 'ad.price', 'ad.description', 'ad.user_id', 'ad.category_id', 'ad.province', 'ad.type_id', 'ad.location_id', 'c.name')
//            ->from('ad', 'ad')
//            ->innerjoin('ad', 'category', 'c', 'ad.category_id = c.id');
//    }

    protected function queryAllFiltered($phrase)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select('ad.id', 'ad.name', 'ad.price', 'ad.description')
            ->from('ad', 'ad')
            ->where('ad.name LIKE :phrase')
            ->setParameter(':phrase', '%' . $phrase . '%');
            }





//
//            /**
//             * Query all extra 2
//             * @return \Doctrine\DBAL\Query\QueryBuilder
//             */
//    protected function queryAllExtra2()
//    {
//        $queryBuilder = $this->db->createQueryBuilder();
//        return $queryBuilder->select('ad.id', 'ad.name', 'ad.price', 'ad.description', 'ad.user_id', 'ad.category_id', 'ad.province', 'ad.type_id', 'ad.location_id', 'c.name')
//            ->from('ad', 'ad')
//            ->innerjoin('ad', 'category', 'c', 'ad.category_id = c.id');
//    }

//    protected function queryAllExtra3()
//    {
//        $queryBuilder = $this->db->createQueryBuilder();
//        return $queryBuilder->select('ad.id', 'ad.name', 'ad.price', 'ad.description', 'ad.user_id', 'ad.category_id', 'ad.province', 'ad.type_id', 'ad.location_id', 't.name')
//            ->from('ad', 'ad')
//            ->innerjoin('ad', 'type', 't', 'ad.type_id = t.id');
//    }
//
//
//
//        /**
//     * Query all extra 2
//     * @return \Doctrine\DBAL\Query\QueryBuilder
//     */
//    protected function queryAllExtra2()
//    {
//        $queryBuilder = $this->db->createQueryBuilder();
//        return $queryBuilder->select('ad.id', 'ad.name', 'ad.price', 'ad.description', 'ad.user_id', 'ad.category_id', 'ad.province', 'ad.type_id', 'ad.location_id', 'c.name')
//            ->from('ad', 'ad');
//
//    }
//
//    protected function queryAllExtra3()
//    {
//        $queryBuilder = $this->db->createQueryBuilder();
//        return $queryBuilder->select('ad.id', 'ad.name', 'ad.price', 'ad.description', 'ad.user_id', 'ad.category_id', 'ad.province', 'ad.type_id', 'ad.location_id', 't.name')
//            ->from('ad', 'ad')
//            ->innerjoin('ad', 'type', 't', 'ad.type_id = t.id');
//    }

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

    /**
     * Find all by category
     * @param $category_id
     * @return array
     */
    public function findAllByCategory($category_id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder
            ->where('category_id = :category_id')
            ->setParameter(':category_id', $category_id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetchAll();

        return !$result ? [] : $result;
    }

    /**
     * Find all by phrase of name
     * @param $phrase
     * @return array
     */
    public function findAllByPhraseOfName($phrase)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder
            ->where('ad.name LIKE :phrase')
            ->setParameter(':phrase', '%' . $phrase . '%');
        $result = $queryBuilder->execute()->fetchAll();

        return !$result ? [] : $result;
    }

    public function findByPhrasePaginated($phrase, $page = 1)
    {

        $countQueryBuilder = $this->queryAllFiltered($phrase)
        ->select('COUNT(DISTINCT ad.id) AS total_results')
//            ->where('ad.name LIKE :phrase')
//            ->setParameter(':phrase', '%' . $phrase . '%')
            ->setMaxResults(1);
        dump($countQueryBuilder);
        $paginator = new Paginator($this->queryAllFiltered($phrase), $countQueryBuilder);
        $paginator->setCurrentPage($page);
        $paginator->setMaxPerPage(static::NUM_ITEMS);

        return $paginator->getCurrentPageResults();
    }


    /**
     * Save record.
     *
     * @param array $ad Ad
     *
     */
    public function save($ad)
    {
        $this->db->beginTransaction();
        try {
            unset($ad['photo']);
            unset($ad['photo_source']);
            $photo = [];
            $photo['name'] = $ad['photo_title'];
            $photo['source'] = $ad['source'];
            unset($ad['source']);
            unset($ad['photo_title']);

            if (isset($ad['id']) && ctype_digit((string)$ad['id'])) {
                // update record
                $id = $ad['id'];
                unset($ad['id']);

                $this->db->update('photo', $photo, ['ad_id' => $id]);
                return $this->db->update('ad', $ad, ['id' => $id]);
            } else {
                // add new record


//                dump($ad);
                $this->db->insert('ad', $ad);
                $id = $this->db->lastInsertId();


                if ($photo['source']) {
                    $photo['ad_id'] = $id;
                    $this->db->insert('photo', $photo);
                }
            }

            $this->db->commit();

        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
        return $id;
    }
    /*
     *
     */

    /**
     * Delete
     * @param $ad
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function delete($ad)
    {
        $this->db->beginTransaction();
        try {
            if (isset($ad['id']) && ctype_digit((string)$ad['id'])) {
                $this->db->delete('ad', ['id' => $ad['id']]);


            } else {
                throw new \InvalidArgumentException('Invalid parameter type');
            }
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
