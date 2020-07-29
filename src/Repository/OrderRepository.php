<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }
    /**
     * @return Order
     */
    public function getOrder( $email)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT DISTINCT 
                theOrder,
                delivery_order,
                products
            FROM 
                App\Entity\Order theOrder
            JOIN
            theOrder.DeliveryOrder delivery_order
            JOIN
            theOrder.product products
            WHERE
            theOrder.email = :email
            '
        )->setParameter('email', $email);;
        return $query->getResult();
    
    }
}
