<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cart>
 */
class CartRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Cart::class);
        $this->entityManager = $entityManager;
    }

    /**
     * Get or create an order for the user
     */
    public function getOrCreateCartForUser(User $user): Cart
    {
        // Check if the user already has an order
        $cart = $this->createQueryBuilder('o')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        // If no order exists, create a new one
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setCreatedAt(new DateTime());
            $cart->setUpdatedAt(new DateTime());

            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }


        return $this->createQueryBuilder('o')
            ->leftJoin('o.cartItems', 'oi')
            ->addSelect('oi')
            ->leftJoin('oi.item', 'item')
            ->addSelect('item')
            ->where('o.id = :id')
            ->setParameter('id', $cart->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Cart[] Returns an array of Cart objects
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

    //    public function findOneBySomeField($value): ?Cart
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
