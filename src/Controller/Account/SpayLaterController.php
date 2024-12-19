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
use App\Entity\User;
use DateMalformedStringException;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/account', name: 'account_')]
#[IsGranted('ROLE_USER')]
class SpayLaterController extends BaseController
{
    private EntityManagerInterface $em;
    private Security $security;
    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
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

//        dd($this->splitGroupByMonthYear($installments));

        return $this->render('account/spaylater.html.twig', [
            'page_title' => 'Spay Later',
            'groupedInstallments' => $this->splitGroupByMonthYear($installments),
        ]);
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

        $currentDate = new DateTime();
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

                if ($currentKey == $previousMonthKey && $currentDate->format('d') >= 24) {
                    $group = &$groupedInstallments['current'];
                } else if ($currentDate->format('d') <= 10 && $currentKey == (clone $dueDate)->format('Y-m')) {
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