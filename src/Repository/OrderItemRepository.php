<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderItem>
 */
class OrderItemRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, OrderItem::class);
        $this->em = $em;
    }

    public function addProductToOrder(Order $order, Item $item, int $quantity): OrderItem
    {
        $orderItem = $this->em->getRepository(OrderItem::class)
            ->findOneBy(['order' => $order, 'item' => $item]);

        if ($orderItem) {
            $orderItem->setQuantity($orderItem->getQuantity() + $quantity);
        } else {
            $orderItem = new OrderItem();
            $orderItem->setOrder($order);
            $orderItem->setItem($item);
            $orderItem->setQuantity($quantity);
            $this->em->persist($orderItem);
        }

        $this->em->flush();

        return $orderItem;
    }

    public function updateProductInOrder(Order $order, Item $item, int $quantity): ?OrderItem
    {
        $orderItem = $this->em->getRepository(OrderItem::class)
            ->findOneBy(['order' => $order, 'item' => $item]);

        if ($orderItem) {
            if ($quantity > 0) {
                $orderItem->setQuantity($quantity);
                $this->em->flush();
                return $orderItem;
            } else {
                $this->removeProductFromOrder($order, $item);
            }
        }

        return null;
    }

    public function removeProductFromOrder(Order $order, Item $item): void
    {
        $orderItem = $this->em->getRepository(OrderItem::class)
            ->findOneBy(['order_id' => $order, 'item_id' => $item]);

        if ($orderItem) {
            $this->em->remove($orderItem);
            $this->em->flush();
        }
    }

    //    /**
    //     * @return OrderItem[] Returns an array of OrderItem objects
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

    //    public function findOneBySomeField($value): ?OrderItem
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
