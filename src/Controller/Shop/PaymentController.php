<?php
/**
 * @project ptpmhdv-nhom-7-symfony-final
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 14/12/2024
 * @time 13:38
 */

namespace App\Controller\Shop;

use App\Controller\BaseController;
use App\Entity\OrderHistory;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Services\VNPayService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('PUBLIC_ACCESS')]
class PaymentController extends BaseController
{
    private VNPayService $vnpService;
    private Security $security;
    private EntityManagerInterface $em;
    private OrderRepository $orderRepository;

    public function __construct(VNPayService $vnpService, Security $security, EntityManagerInterface $em, OrderRepository $orderRepository)
    {
        $this->vnpService = $vnpService;
        $this->security = $security;
        $this->em = $em;
        $this->orderRepository = $orderRepository;
    }

    #[Route('/payment/create', name: 'payment_create', methods: ['POST'])]
    public function createPayment(Request $request): Response
    {
        $data = [
            'amount' => $request->request->get('amount'),
            'language' => 'en',
            'bankCode' => '',
        ];

        $paymentUrl = $this->vnpService->createPaymentUrl($data);

        return $this->redirect($paymentUrl);
    }

    #[Route('/payment/callback', name: 'payment_callback', methods: ['GET'])]
    public function paymentCallback(Request $request): Response
    {
        $data = $request->query->all();

        $vnpSecureHash = $data['vnp_SecureHash'] ?? '';

        unset($data['vnp_SecureHash']);
        ksort($data);
        $i = 0;
        $hashData = "";
        foreach ($data as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $this->vnpService->getVnpHashSecret());

        if ($secureHash == $vnpSecureHash) {
            if ($_GET['vnp_ResponseCode'] == '00') {

                $user = $this->security->getUser();
                if (!$user instanceof User) {
                    return $this->redirectToRoute('app_login');
                }

                $order = $this->orderRepository->getOrCreateOrderForUser($user);

                $order = $this->orderRepository->findOneBy(['user' => $user]);
                if (!$order) {
                    $this->addFlash('error', 'Your cart is empty.');
                    return $this->redirectToRoute('shop_cart');
                }

                $totalPrice = 0;
                $orderItems = $order->getOrderItems();

                $itemsSummary = [];
                foreach ($orderItems as $orderItem) {
                    $item = $orderItem->getItem();
                    $quantity = $orderItem->getQuantity();
                    $price = $item->getItemPrice();
                    $totalPrice += $price * $quantity;

                    $itemsSummary[] = [
                        'item_name' => $item->getItemName(),
                        'quantity' => $quantity,
                        'price' => $price,
                    ];
                }

                $orderHistory = new OrderHistory();
                $orderHistory->setUser($user);
                $orderHistory->setOrderItems($itemsSummary);
                $orderHistory->setTotalPrice($totalPrice);
                $orderHistory->setPaymentType(1);
                $orderHistory->setCreatedAt(new \DateTime());

                $transaction = new Transaction();
                $transaction->setAmount($data['vnp_Amount']);
                $transaction->setBankCode($data['vnp_BankCode']);
                $transaction->setBankTranNo($data['vnp_BankTranNo']);
                $transaction->setCardType($data['vnp_CardType']);
                $transaction->setOrderInfo($data['vnp_OrderInfo']);
                $transaction->setPayDate($data['vnp_PayDate']);
                $transaction->setResponseCode($data['vnp_ResponseCode']);
                $transaction->setTmnCode($data['vnp_TmnCode']);
                $transaction->setTransactionNo($data['vnp_TransactionNo']);
                $transaction->setTransactionStatus($data['vnp_TransactionStatus']);
                $transaction->setTxnRef($data['vnp_TxnRef']);
                $transaction->setSecureHash($vnpSecureHash);

                $this->em->persist($transaction);
                $orderHistory->setTransact($transaction);

                $this->em->persist($orderHistory);

                foreach ($orderItems as $orderItem) {
                    $this->em->remove($orderItem);
                }

                $this->em->persist($order);

                $this->em->flush();

                $this->addFlash('success', 'Checkout successful! Your order has been placed.');
            } else {
                $this->addFlash('error', 'Checkout failed!');
            }
        } else {
            $this->addFlash('error', 'Invalid signature');
        }

        return $this->redirectToRoute('shop_cart');
    }
}