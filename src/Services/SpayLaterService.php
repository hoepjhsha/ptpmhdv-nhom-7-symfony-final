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
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class SpayLaterService
{
    private float $SPayLater_installment_conversion_fee;
    private int $SpayLater_late_fee;
    private EntityManagerInterface $em;
    private VNPayService $vnpService;

    public function __construct(EntityManagerInterface $em, VNPayService $vnpService, float $SPayLater_installment_conversion_fee, int $SpayLater_late_fee)
    {
        $this->em = $em;
        $this->vnpService = $vnpService;
        $this->SPayLater_installment_conversion_fee = $SPayLater_installment_conversion_fee;
        $this->SpayLater_late_fee = $SpayLater_late_fee;
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function createInstallments(Payment $order, int $installmentCount): void
    {
        $totalAmount = $order->getOrderHistory()->getTotalAmount();
        $installmentAmount = $totalAmount / $installmentCount;

        $currentDate = new \DateTime();
        $currentMonth = (int)$currentDate->format('m');
        $currentYear = (int)$currentDate->format('Y');

        $dueDate = new \DateTime("$currentYear-$currentMonth-10");
        if ($currentDate->format('d') >= 24) {
            $dueDate->modify('+2 month');
        } else {
            $dueDate->modify('+1 month');
        }

        for ($i = 1; $i <= $installmentCount; $i++) {
            $installment = new Installment();
            $installment->setPayment($order);
            $installment->setInstallmentNo($i);
            $installment->setAmount($totalAmount / $installmentCount);
            $installment->setLaterFee($totalAmount * $this->SPayLater_installment_conversion_fee);
            $installment->setDueDate($dueDate);

            $this->em->persist($installment);

            $dueDate->modify('+1 month');
        }

        $this->em->flush();
    }
}