<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Order::class);
        $this->entityManager = $entityManager;
    }

    /**
     * Get or create an order for the user
     */
    public function getOrCreateOrderForUser(User $user): Order
    {
        // Check if the user already has an order
        $order = $this->createQueryBuilder('o')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        // If no order exists, create a new one
        if (!$order) {
            $order = new Order();
            $order->setUser($user);
            $order->setCreatedAt(new \DateTime());
            $order->setUpdatedAt(new \DateTime());

            // Persist the new order
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        }

//        return $order;

        return $this->createQueryBuilder('o')
            ->leftJoin('o.orderItems', 'oi')
            ->addSelect('oi')
            ->leftJoin('oi.item', 'item')
            ->addSelect('item')
            ->where('o.id = :id')
            ->setParameter('id', $order->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
