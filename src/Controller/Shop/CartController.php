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
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\User;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/shop', name: 'shop_')]
class CartController extends BaseController
{
    private EntityManagerInterface $em;
    private CartRepository $cartRepository;
    private CartItemRepository $cartItemRepository;
    private Security $security;

    public function __construct(CartRepository $cartRepository, CartItemRepository $cartItemRepository, Security $security, EntityManagerInterface $em)
    {
        $this->cartRepository = $cartRepository;
        $this->cartItemRepository = $cartItemRepository;
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

        $cart = $this->cartRepository->getOrCreateCartForUser($user);
        $cartItem = $this->cartItemRepository->findOneBy(['cart' => $cart, 'item' => $item]);

        if ($cartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setItem($item);
            $cartItem->setQuantity($quantity);

            $this->em->persist($cartItem);
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

        $cart = $this->cartRepository->getOrCreateCartForUser($user);
        $cartItem = $this->cartItemRepository->findOneBy(['cart' => $cart, 'item' => $item]);

        if ($cartItem) {
            $this->em->remove($cartItem);
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

        $cart = $this->cartRepository->getOrCreateCartForUser($user);

        $quantities = $request->request->all('quantity');

        foreach ($quantities as $cartItemId => $quantity) {
            $cartItem = $this->cartItemRepository->find($cartItemId);
            if ($cartItem) {
                $cartItem->setQuantity((int)$quantity);

                $this->em->persist($cartItem);
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

        $cart = $this->cartRepository->getOrCreateCartForUser($user);
        $cartItems = $cart->getCartItems();

        $data = [];
        foreach ($cartItems as $cartItem) {
            $data[] = $cartItem;
        }

        $totalMoney = 0;
        foreach ($data as $item) {
            $totalMoney += $item->getItem()->getItemPrice() * $item->getQuantity();
        }

        return $this->render('shop/cart.html.twig', [
            'page_title' => 'Cart',
            'cartItems' => $data,
            'totalMoney' => $totalMoney,
        ]);
    }
}