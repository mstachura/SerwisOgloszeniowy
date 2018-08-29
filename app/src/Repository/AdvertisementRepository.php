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
    const NUM_ITEMS = 4;

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
        $countQueryBuilder = $this->queryAllFullExtra()
            ->select('COUNT(DISTINCT ad.id) AS total_results')
            ->setMaxResults(1);

        $paginator = new Paginator($this->queryAllFullExtra(), $countQueryBuilder);
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

        return $queryBuilder->select(
            'id',
            'name',
            'price',
            'description',
            'user_id',
            'type_id',
            'location_id',
            'province',
            'category_id'
        )
            ->from('ad');
    }

//    /**
//     * Query All Extra
//     * @return \Doctrine\DBAL\Query\QueryBuilder
//     */
//    protected function queryAllExtra()
//    {
//        $queryBuilder = $this->db->createQueryBuilder();
//        return $queryBuilder->select(
//            'ad.id',
//            'ad.name',
//            'ad.price',
//            'ad.description',
//            'ad.user_id',
//            'ad.category_id',
//            'ad.province',
//            'ad.type_id',
//            'ad.location_id',
//            'l.name'
//        )
//            ->from('ad', 'ad')
//            ->innerjoin('ad', 'location', 'l', 'ad.location_id = l.id');
//    }


    /**
     * Query all extra add photo
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function queryAllFullExtra()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select(
            'p.source',
            'ad.id',
            'ad.name',
            'ad.price',
            'ad.description',
            'u.login',
            'u.id AS user_id',
            'c.id AS category_id',
            'c.name AS category_name',
            'ad.province',
            't.name AS type_name',
            'l.name AS location_name'
        )
            ->from('ad')
            ->innerjoin('ad', 'type', 't', 'ad.type_id = t.id')
            ->innerjoin('ad', 'category', 'c', 'ad.category_id = c.id')
            ->innerjoin('ad', 'location', 'l', 'ad.location_id = l.id')
            ->innerjoin('ad', 'user', 'u', 'ad.user_id = u.id')
            ->leftjoin('ad', 'photo', 'p', 'ad.id = p.ad_id');
    }

    /**
     * Query All Filtered
     * @param $phrase
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function queryAllFiltered($phrase)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select(
            'p.source',
            'ad.id',
            'ad.name',
            'ad.price',
            'ad.description',
            'u.login',
            'u.id AS user_id',
            'c.id AS category_id',
            'c.name AS category_name',
            'ad.province',
            't.name AS type_name',
            'l.name AS location_name'
        )
            ->from('ad')
            ->innerjoin('ad', 'type', 't', 'ad.type_id = t.id')
            ->innerjoin('ad', 'category', 'c', 'ad.category_id = c.id')
            ->innerjoin('ad', 'location', 'l', 'ad.location_id = l.id')
            ->innerjoin('ad', 'user', 'u', 'ad.user_id = u.id')
            ->leftjoin('ad', 'photo', 'p', 'ad.id = p.ad_id')
            ->where('ad.name LIKE :phrase')
            ->setParameter(':phrase', '%' . $phrase . '%');
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
    public function findOneByIdExtra($id)
    {
        $queryBuilder = $this->queryAllFullExtra();
        $queryBuilder
            ->where('ad.id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    /**
     * Find all by user
     * @param $user_id
     * @return array
     */
    public function findAllByUserPaginated($user_id, $page)
    {

        $countQueryBuilder = $this->queryAllFilteredByUserId($user_id)
            ->select('COUNT(DISTINCT ad.id) AS total_results')
            ->setMaxResults(1);
        $paginator = new Paginator($this->queryAllFilteredByUserId($user_id), $countQueryBuilder);
        $paginator->setCurrentPage($page);
        $paginator->setMaxPerPage(static::NUM_ITEMS);

        return $paginator->getCurrentPageResults();
    }


    /**
     * Query All Filtered
     * @param $phrase
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function queryAllFilteredByUserId($user_id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select(
            'p.source',
            'ad.id',
            'ad.name',
            'ad.price',
            'ad.description',
            'u.login',
            'u.id AS user_id',
            'c.id AS category_id',
            'c.name AS category_name',
            'ad.province',
            't.name AS type_name',
            'l.name AS location_name'
        )
            ->from('ad')
            ->innerjoin('ad', 'type', 't', 'ad.type_id = t.id')
            ->innerjoin('ad', 'category', 'c', 'ad.category_id = c.id')
            ->innerjoin('ad', 'location', 'l', 'ad.location_id = l.id')
            ->innerjoin('ad', 'user', 'u', 'ad.user_id = u.id')
            ->leftjoin('ad', 'photo', 'p', 'ad.id = p.ad_id')
            ->where('ad.user_id LIKE :user_id')
            ->setParameter(':user_id', $user_id);
    }


    /**
     * @param $category_id
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function queryAllFilteredCategory($category_id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select('ad.id', 'ad.name')
            ->from('ad', 'ad')
            ->where('ad.category_id = :category_id')
            ->setParameter(':category_id', $category_id);
    }

    /**
     * Find all by category
     * @param $category_id
     * @return array
     */
    public function findAllByCategoryPaginated($category_id, $page = 1)
    {
        $countQueryBuilder = $this->queryAllFilteredCategory($category_id)
            ->select('COUNT(DISTINCT ad.id) AS total_results')
            ->setMaxResults(1);
        $paginator = new Paginator($this->queryAllFilteredCategory($category_id), $countQueryBuilder);
        $paginator->setCurrentPage($page);
        $paginator->setMaxPerPage(static::NUM_ITEMS);

        return $paginator->getCurrentPageResults();
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

    /**
     * Find By Phrase Paginated
     * @param $phrase
     * @param int $page
     * @return array
     */
    public function findByPhrasePaginated($phrase, $page = 1)
    {

        $countQueryBuilder = $this->queryAllFiltered($phrase)
            ->select('COUNT(DISTINCT ad.id) AS total_results')
//            ->where('ad.name LIKE :phrase')
//            ->setParameter(':phrase', '%' . $phrase . '%')
            ->setMaxResults(1);

        $paginator = new Paginator($this->queryAllFiltered($phrase), $countQueryBuilder);
        $paginator->setCurrentPage($page);
        $paginator->setMaxPerPage(static::NUM_ITEMS);

        return $paginator->getCurrentPageResults();
    }


    /**
     * Save
     * @param $ad
     * @return int|string
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function save(Application $app, $ad)
    {

//        $this->db->beginTransaction();
        // try {
        unset($ad['photo']);
//            unset($ad['photo_source']);

        if ($ad['source']) { //jeśli w formularzu dodano plik ze zdjęciem
            $photo = [];

            $photo['name'] = $ad['photo_title'];
            $photo['source'] = $ad['source'];
        }

        unset($ad['source']);
        unset($ad['photo_title']);


        //lokalizacja
        $locationRepository = new LocationRepository($app['db']);
        $location = $locationRepository->findOneByName($ad['location_name']);

        if ($location) {
            $ad['location_id'] = $location['id'];
        } else {
            $location['name'] = $ad['location_name'];
            $this->db->insert('location', $location);
            $ad['location_id'] = $this->db->lastInsertId();
        }
        unset($ad['location_name']);


        if (isset($ad['id']) && ctype_digit((string)$ad['id'])) {
            // update record
            $id = $ad['id'];
            unset($ad['id']);

            $photoRepository = new PhotoRepository($app['db']);

            if ($photo) { //sprawdzamy, czy w formularzu przesyłamy zdjęcie
                if ($photoRepository->findOneByAdvertisementId($id)) { //sprawdzamy, czy ogłoszenie posiadało zdjęcie
                    $this->db->update('photo', $photo, ['ad_id' => $id]);
                } else {
                    $photo['ad_id'] = $id; //zdjęcie dostaje numer ogłoszenia
                    $this->db->insert('photo', $photo); //dodajemy zdjęcie do istniejącego już ogłoszenia
                }
                return $this->db->update('ad', $ad, ['id' => $id]);
            }
        } else {
            // add new record

            $this->db->insert('ad', $ad);
            $id = $this->db->lastInsertId();


            if ($photo['source']) {
                $photo['ad_id'] = $id;
                $this->db->insert('photo', $photo);
            }
        }
//            $this->db->commit();
//        } catch (DBALException $e) {
//            $this->db->rollBack();
//            throw $e;
//        }
        return $id;
    }


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
