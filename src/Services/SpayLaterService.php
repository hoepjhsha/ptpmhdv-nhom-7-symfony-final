<?php
/**
 * @project ptpmhdv-nhom-7-symfony-final
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 17/12/2024
 * @time 10:20
 */

namespace App\Services;

use App\Entity\Installment;
use App\Entity\Payment;
use DateMalformedStringException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class SpayLaterService
{
    private float $SPayLater_installment_conversion_fee;
    private int $SpayLater_late_fee;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, float $SPayLater_installment_conversion_fee, int $SpayLater_late_fee)
    {
        $this->em = $em;
        $this->SPayLater_installment_conversion_fee = $SPayLater_installment_conversion_fee;
        $this->SpayLater_late_fee = $SpayLater_late_fee;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function createInstallments(Payment $order, int $installmentCount): void
    {
        $totalAmount = $order->getOrderHistory()->getTotalAmount();
        $installmentAmount = $totalAmount / $installmentCount;

        $currentDate = new DateTime();
        $currentMonth = (int)$currentDate->format('m');
        $currentYear = (int)$currentDate->format('Y');

        $dueDate = new DateTime("$currentYear-$currentMonth-10 23:59:59");
        if ($currentDate->format('d') >= 24) {
            $dueDate->modify('+2 month');
        } else {
            $dueDate->modify('+1 month');
        }

        for ($i = 1; $i <= $installmentCount; $i++) {
            $installment = new Installment();
            $installment->setPayment($order);
            $installment->setInstallmentNo($i);
            $installment->setAmount($installmentAmount);
            $installment->setLaterFee((int)($totalAmount * $this->SPayLater_installment_conversion_fee));
            $installment->setDueDate($dueDate);

            $this->em->persist($installment);
            $this->em->flush();

            $dueDate->modify('+1 month');
        }
    }

    public function applyLateFeeForOverdueInstallment(): void
    {
        $today = new DateTime('2025-01-11');

        if ($today->format('d') > 10) {
            $installments = $this->em->getRepository(Installment::class)->findAll();

            $overdueInstallments = [];
            foreach ($installments as $installment) {
                if ($installment->getDueDate() < $today && !$installment->isPaid()) {
                    $overdueInstallments[] = $installment;
                }
            }

            foreach ($overdueInstallments as $installment) {
                $installment->setLateFee($installment->getLateFee() + $this->SpayLater_late_fee);
                $installment->setDueDate((clone $installment->getDueDate())->modify('+1 month'));
                $this->em->flush();
            }
        }
    }
}