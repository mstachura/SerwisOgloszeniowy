<?php
///**
// * comment repository.
// */
//namespace Repository;
//
//use Doctrine\DBAL\Connection;
//
///**
// * Class commentRepository.
// */
//class CommentRepository
//{
//    /**
//     * Doctrine DBAL connection.
//     *
//     * @var \Doctrine\DBAL\Connection $db
//     */
//    protected $db;
//
//    /**
//     * commentRepository constructor.
//     *
//     * @param \Doctrine\DBAL\Connection $db
//     */
//    public function __construct(Connection $db)
//    {
//        $this->db = $db;
//    }
//
//    /**
//     * Fetch all records.
//     *
//     * @return array Result
//     */
//    public function findAll()
//    {
//        $queryBuilder = $this->queryAll();
//        return $queryBuilder->execute()->fetchAll();
//    }
//
//
//    protected function queryAllOld()
//    {
//        $queryBuilder = $this->db->createQueryBuilder();
//        return $queryBuilder->select('id', 'date', 'text', 'user_id', 'ad_id')
//            ->from('comment')
//            ;
//    }
//
//    protected function queryAll()
//    {
//        $queryBuilder = $this->db->createQueryBuilder();
//        return $queryBuilder->select('c.id', 'c.date', 'c.text', 'c.user_id', 'c.ad_id', 'u.login')
//            ->from('comment', 'c')
//            ->innerjoin('c', 'user', 'u', 'u.id=c.user_id');
//    }
//
//    /**
//     * Find one record.
//     *
//     * @param string $id Element id
//     *
//     * @return array|mixed Result
//     */
//
//    public function findOneById($id)
//    {
//        $queryBuilder = $this->queryAllOld();
//        $queryBuilder
//            ->where('id = :id')
//            ->setParameter(':id', $id, \PDO::PARAM_INT);
//        $result = $queryBuilder->execute()->fetch();
//
//        return !$result ? [] : $result;
//    }
//
//    public function findAllFromAdvertisement($ad_id)
//    {
//        $queryBuilder = $this->queryAll();
//        $queryBuilder
//            ->where('ad_id = :ad_id')
//            ->setParameter(':ad_id', $ad_id, \PDO::PARAM_INT);
//        $result = $queryBuilder->execute()->fetchAll();
//
//        return !$result ? [] : $result;
//    }
//
//    /**
//     * Find one record.
//     *
//     * @param string $id Element id
//     *
//     * @return array|mixed Result
//     */
//
////    public function findAllByUser($user_id)
////    {
////        $queryBuilder = $this->queryAll();
////        $queryBuilder
////            ->where('user_id = :user_id')
////            ->setParameter(':user_id', $user_id, \PDO::PARAM_INT);
////        $result = $queryBuilder->execute()->fetchAll();
////
////        return !$result ? [] : $result;
////    }
//
//
//    /**
//     * Save record.
//     *
//     *
//     *
//     */
//    public function save($comment)
//    {
//        if (isset($comment['id']) && ctype_digit((string) $comment['id'])) {
//            // update record
//            $id = $comment['id'];
//            unset($comment['id']);
//
//
//            return $this->db->update('comment', $comment, ['id' => $id]);
//        } else {
//            // add new record
//
//            $comment['date'] = date('Y-m-d H:i:s');
//            unset($comment['login']);
//            $this->db->insert('comment', $comment);
//            return $this->db->lastInsertId();
//        }
//    }
//
//    public function delete($comment)
//    {
//        return $this->db->delete('comment', ['id' => $comment['id']]);
//    }
//}
