<?php
/**
 * @project ptpmhdv-nhom-7-symfony
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 07/12/2024
 * @time 10:26
 */

namespace App\Controller\Shop;

use App\Controller\BaseController;
use App\Entity\Item;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/shop', name: 'shop_')]
class CartController extends BaseController
{
    private EntityManagerInterface $em;
    private OrderRepository $orderRepository;
    private OrderItemRepository $orderItemRepository;
    private Security $security;

    public function __construct(OrderRepository $orderRepository, OrderItemRepository $orderItemRepository, Security $security, EntityManagerInterface $em)
    {
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->security = $security;
        $this->em = $em;
    }

    #[Route('/cart/add', name: 'cart_add', methods: ['POST'])]
    public function addToCart(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $itemId = $request->request->get('item_id');
        $quantity = (int)$request->request->get('quantity');

        $item = $this->em->getRepository(Item::class)->find($itemId);
        if (!$item) {
            $this->addFlash('error', 'Item not found');

            return $this->redirectToRoute('shop_list');
        }

        $order = $this->orderRepository->getOrCreateOrderForUser($user);
        $orderItem = $this->orderItemRepository->findOneBy(['order' => $order, 'item' => $item]);

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

        $this->addFlash('success', 'Item added to cart');

        return $this->redirectToRoute('shop_list');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove', methods: ['GET', 'POST'])]
    public function removeFromCart(int $id, Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $itemId = $request->request->get($id);
        $item = $this->em->getRepository(Item::class)->find($id);

        $order = $this->orderRepository->getOrCreateOrderForUser($user);
        $orderItem = $this->orderItemRepository->findOneBy(['order' => $order, 'item' => $item]);

        if ($orderItem) {
            $this->em->remove($orderItem);
            $this->em->flush();
        }

        return $this->redirectToRoute('shop_cart');
    }

    #[Route('/cart/update', name: 'cart_update', methods: ['GET', 'POST'])]
    public function updateCart(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $order = $this->orderRepository->getOrCreateOrderForUser($user);

        $quantities = $request->request->all('quantity');

        foreach ($quantities as $orderItemId => $quantity) {
            $orderItem = $this->orderItemRepository->find($orderItemId);
            if ($orderItem) {
                $orderItem->setQuantity((int)$quantity);
                $this->em->persist($orderItem);
            }
        }

        $this->em->flush();

        return $this->redirectToRoute('shop_cart');
    }

    #[Route('/cart', name: 'cart', methods: ['GET'])]
    public function viewCart(): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $order = $this->orderRepository->getOrCreateOrderForUser($user);
        $orderItems = $order->getOrderItems();

        $data = [];
        foreach ($orderItems as $orderItem) {
            $data[] = $orderItem;
        }

        $totalMoney = 0;
        foreach ($data as $item) {
            $totalMoney += $item->getItem()->getItemPrice() * $item->getQuantity();
        }

        return $this->render('shop/cart.html.twig', [
            'page_title' => 'Cart',
            'orderItems' => $data,
            'totalMoney' => $totalMoney,
        ]);
    }

    private function getOrder(): Order|Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user) {
            return new Response('User not logged in', Response::HTTP_UNAUTHORIZED);
        }

        return $this->orderRepository->getOrCreateOrderForUser($user);
    }
}