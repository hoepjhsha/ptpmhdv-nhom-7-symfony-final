<?php
/**
 * @project ptpmhdv-nhom-7-symfony-final
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 16/12/2024
 * @time 14:54
 */

namespace App\Controller\Account;

use App\Controller\BaseController;
use App\Entity\Installment;
use App\Entity\OrderHistory;
use App\Entity\Payment;
use App\Entity\Transaction;
use App\Entity\User;
use App\Services\VNPayService;
use DateMalformedStringException;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/account', name: 'account_')]
#[IsGranted('ROLE_USER')]
class SpayLaterController extends BaseController
{
    private EntityManagerInterface $em;
    private Security $security;
    private VNPayService $vnpService;

    public function __construct(EntityManagerInterface $em, Security $security, VNPayService $vnpService)
    {
        $this->em = $em;
        $this->security = $security;
        $this->vnpService = $vnpService;
    }

    /**
     * @throws DateMalformedStringException
     */
    #[Route(path: '/spay-later', name: 'spay_later', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $installments = [];
        foreach ($this->em->getRepository(OrderHistory::class)->findBy(['user' => $user]) as $order) {
            if ($order->getPayment()->getPaymentMethod() == 2) {
                foreach ($order->getPayment()->getInstallments() as $installment) {
                    $installments[] = $installment;
                }
            }
        }

        return $this->render('account/spaylater.html.twig', [
            'page_title' => 'Spay Later',
            'groupedInstallments' => $this->splitGroupByMonthYear($installments),
        ]);
    }

    #[Route('/payment/create', name: 'payment_create', methods: ['POST'])]
    public function createPayment(Request $request): Response
    {
        $data = [
            'amount' => $request->request->get('amount'),
            'language' => 'en',
            'bankCode' => '',
        ];

        $this->vnpService->setVnpReturnurl('http://dastonehdv.local' . $this->generateUrl('account_payment_callback'));

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

                $this->em->flush();

                $currentDate = new DateTime('2024-12-24');
                $currentKey = (clone $currentDate)->format('Y-m');

                $total_amount = 0;
                foreach ($this->em->getRepository(OrderHistory::class)->findBy(['user' => $user]) as $order) {
                    if ($order->getPayment()->getPaymentMethod() == 2) {
                        foreach ($order->getPayment()->getInstallments() as $installment) {
                            if (! $installment instanceof Installment) {
                                continue;
                            }

                            $dueDate = $installment->getDueDate();
                            $dueDateKey = (clone $dueDate)->modify('-1 month')->format('Y-m');

                            $found = false;
                            if ($currentKey == $dueDateKey && $currentDate->format('d') >= 24) {
                                $found = true;
                            } else if ($currentDate->format('d') <= 10 && $currentKey == (clone $dueDate)->format('Y-m')) {
                                $found = true;
                            }

                            if ($found) {
                                $installment->setPaid(true);
                                $installment->setTransact($transaction);
                                $total_amount += $installment->getAmount();

                                $this->em->flush();
                            }
                        }
                    }
                }

                $user->setCreditLimit($user->getCreditLimit() + $total_amount);

                $this->addFlash('success', 'Paid successful! Your installment has been paid.');
            } else {
                $this->addFlash('error', 'Paid failed!');
            }
        } else {
            $this->addFlash('error', 'Invalid signature');
        }

        return $this->redirectToRoute('account_spay_later');
    }

    /**
     * @throws DateMalformedStringException
     */
    private function splitGroupByMonthYear(array $installments): array
    {
        if (empty($installments)) {
            return [
                'current' => [],
                'unpaid' => [],
                'paid' => []
            ];
        }

        $currentDate = new DateTime('2024-12-24');
        $currentKey = (clone $currentDate)->format('Y-m');
        $currentLabel = (clone $currentDate)->format('F Y');

        $groupedInstallments = [
            'current' => [
                'label' => $currentLabel,
                'installments' => [],
                'total_amount' => 0,
                'total_fee' => 0,
                'total_late_fee' => 0
            ],
            'unpaid' => [],
            'paid' => []
        ];

        foreach ($installments as $installment) {
            if (! $installment instanceof Installment) {
                continue;
            }

            if ($installment->isPaid()) {
                $baseGroup = &$groupedInstallments['paid'];
            } else {
                $baseGroup = &$groupedInstallments['unpaid'];
            }

            $dueDate = $installment->getDueDate();

            if ($dueDate instanceof DateTimeInterface) {
                $previousMonthKey = (clone $dueDate)->modify('-1 month')->format('Y-m');
                $previousMonthLabel = (clone $dueDate)->modify('-1 month')->format('F Y');

                if (!$installment->isPaid() && $currentKey == $previousMonthKey && $currentDate->format('d') >= 24) {
                    $group = &$groupedInstallments['current'];
                } else if (!$installment->isPaid() && $currentDate->format('d') <= 10 && $currentKey == (clone $dueDate)->format('Y-m')) {
                    $group = &$groupedInstallments['current'];
                } else {
                    if (!isset($baseGroup[$previousMonthKey])) {
                        $baseGroup[$previousMonthKey] = [
                            'label' => $previousMonthLabel,
                            'installments' => [],
                            'total_amount' => 0,
                            'total_fee' => 0,
                            'total_late_fee' => 0
                        ];
                    }

                    $group = &$baseGroup[$previousMonthKey];
                }

                $group['installments'][] = $installment;
                $group['total_amount'] += $installment->getAmount();
                $group['total_fee'] += $installment->getLaterFee();
                $group['total_late_fee'] += $installment->getLateFee();
            }
        }

        return $groupedInstallments;
    }
}