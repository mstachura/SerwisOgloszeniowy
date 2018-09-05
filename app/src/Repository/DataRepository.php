<?php
/**
 * Data repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;
use Silex\Application;
use Repository\UserRepository;
use Repository\LocationRepository;

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
     * @param Application $app
     * @param $user_data
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function save(Application $app, $user_data)
    {
        $userRepository = new UserRepository($app['db']);
        $user = $userRepository->findOneById($user_data['user_id']);

        $user['email'] = $user_data['email'];
        unset($user_data['email']);


        //lokalizacja
        if ($user_data['location_name']) {
            $locationRepository = new LocationRepository($app['db']);
            $location = $locationRepository->findOneByName($user_data['location_name']);

            if ($location) {
                $user['location_id'] = $location['id'];
            } else {
                $location['name'] = $user_data['location_name'];
                $this->db->insert('location', $location);
                $user['location_id'] = $this->db->lastInsertId();
            }
        }

        unset($user_data['location_name']);

//        if (isset($user_data['id']) && ctype_digit((string)$user_data['id'])) {
        // update record
        $id = $user_data['id'];
        unset($user_data['id']);

        $this->db->update('user_data', $user_data, ['id' => $id]);
        return $this->db->update('user', $user, ['id' => $user_data['user_id']]);
//        }
// else {
//            // add new record
//            dump($user_data);
//
//            $user['role_id'] = 2;
//
////            $this->db->insert('user', $user);
//            $user_data['user_id'] = $this->db->lastInsertId();
//        }
//        return $this->db->insert('user_data', $user_data);
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
