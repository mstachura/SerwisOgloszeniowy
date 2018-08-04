<?php
/**
 * User repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Class UserRepository.
 */
class UserRepository
{
    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * UserRepository constructor.
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

    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select('id', 'login', 'password', 'email', 'role_id')
            ->from('user');
    }


    /**
     * Loads user by login.
     *
     * @param string $login User login
     * @throws UsernameNotFoundException
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function loadUserByLogin($login)
    {
        try {
            $user = $this->getUserByLogin($login);

            if (!$user || !count($user)) {
                throw new UsernameNotFoundException(
                    sprintf('Username "%s" does not exist.', $login)
                );
            }

            $roles = $this->getUserRoles($user['id']);

            if (!$roles || !count($roles)) {
                throw new UsernameNotFoundException(
                    sprintf('Username "%s" does not exist.', $login)
                );
            }

            return [
                'login' => $user['login'],
                'password' => $user['password'],
                'roles' => $roles,
            ];
        } catch (DBALException $exception) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $login)
            );
        } catch (UsernameNotFoundException $exception) {
            throw $exception;
        }
    }

    /**
     * Gets user data by login.
     *
     * @param string $login User login
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function getUserByLogin($login)
    {
        try {
            $queryBuilder = $this->db->createQueryBuilder();
            $queryBuilder->select('u.id', 'u.login', 'u.password')
                ->from('user', 'u')
                ->where('u.login = :login')
                ->setParameter(':login', $login, \PDO::PARAM_STR);

            return $queryBuilder->execute()->fetch();
        } catch (DBALException $exception) {
            return [];
        }
    }

    /**
     * Gets user roles by User ID.
     *
     * @param integer $userId User ID
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function getUserRoles($userId)
    {
        $roles = [];

        try {
            $queryBuilder = $this->db->createQueryBuilder();
            $queryBuilder->select('r.name')
                ->from('user', 'u')
                ->innerJoin('u', 'roles', 'r', 'u.role_id = r.id')
                ->where('u.id = :id')
                ->setParameter(':id', $userId, \PDO::PARAM_INT);
            $result = $queryBuilder->execute()->fetchAll();

            if ($result) {
                $roles = array_column($result, 'name');
            }

            return $roles;
        } catch (DBALException $exception) {
            return $roles;
        }
    }


    public function save($user, $app){

        $user_data= [];
            $user_data['firstname'] = $user['firstname'];
            $user_data['lastname'] = $user['lastname'];
            $user_data['phone_number'] = $user['phone_number'];
            unset($user['firstname']);
            unset($user['lastname']);
            unset($user['phone_number']);
            $user['password'] = $app['security.encoder.bcrypt']->encodePassword($user['password'], '');

        if (isset($user['id']) && ctype_digit((string) $user['id'])) {
            // update record
            $id = $user['id'];
            unset($user['id']);
//            dump($user);
//            dump($user_data);
            $this->db->update('user_data', $user_data, ['user_id' => $id]);
            return $this->db->update('user', $user, ['id' => $id]);
        } else {
            // add new record
//            dump($user);

            $user['role_id'] = 2;


 //           dump($user);
            $this->db->insert('user', $user);
            $user_data['user_id'] = $this->db->lastInsertId();
 //           dump($user_data);
            return $this->db->insert('user_data', $user_data);
        }
    }

    public function delete($user)
    {
        return $this->db->delete('user', ['id' => $user['id']]);
    }

    protected function queryAllExtra()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select('u.id', 'u.login', 'u.password', 'u.email', 'u.role_id', 'ud.firstname', 'ud.lastname', 'ud.phone_number')
            ->from('user', 'u')
            ->innerjoin('u', 'user_data', 'ud','u.id = ud.id');
//        dump($user);
//        return $queryBuilder->select('u.id', 'u.login', 'u.mail', 'u.password', 'ud.name', 'ud.surname')
//            ->from('user', 'u')
//            ->innerJoin('u', 'userdata', 'ud', 'u.id = ud.userId');
    }

    public function findOneByIdWithUserData($id){
        $queryBuilder = $this->queryAllExtra();
        $queryBuilder->where('id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    public function findForUniqueness($login, $id = null)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('id', 'login', 'password', 'email', 'role_id')
            ->from('user', 'u');
        $queryBuilder->where('u.login = :login')
            ->setParameter(':login', $login, \PDO::PARAM_STR);
        if ($id) {
            $queryBuilder->andWhere('u.id <> :id')
                ->setParameter(':id', $id, \PDO::PARAM_INT);
        }

//        dump($queryBuilder->execute()->fetchAll());
        return $queryBuilder->execute()->fetchAll();
    }
}


