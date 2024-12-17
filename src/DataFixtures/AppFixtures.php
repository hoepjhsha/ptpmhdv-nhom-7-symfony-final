<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Item;
use App\Entity\OrderHistory;
use App\Entity\Payment;
use App\Entity\Transaction;
use App\Entity\User;
use DateInvalidOperationException;
use DateMalformedStringException;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Random\RandomException;

class AppFixtures extends Fixture
{
    /**
     * @throws DateMalformedStringException
     * @throws DateInvalidOperationException
     * @throws RandomException
     */
    public function load(ObjectManager $manager): void
    {
        $filePath = __DIR__ . '/../../data/UV_Product2.xlsx';
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $dataCat = [];
        $first = true;

        foreach ($worksheet->getRowIterator() as $row) {
            if ($first) {
                $first = false;
                continue;
            }

            $columnE = $worksheet->getCell('E' . $row->getRowIndex())->getValue();
            $columnD = $worksheet->getCell('D' . $row->getRowIndex())->getValue();

            if ($columnE == 2) {
                continue;
            }

            $existing = array_column($dataCat, 0);

            if (!in_array($columnE, $existing, true)) {
                $dataCat[] = [$columnE, $columnD];
            }
        }

        foreach ($dataCat as $row) {
            if (in_array(null, $row, true)) {
                continue;
            }

            $category = new Category();
            $category->setId($row[0]);
            $category->setCategoryName($row[1]);

            $manager->persist($category);
        }

        $data = [];
        $first = true;

        foreach ($worksheet->getRowIterator() as $row) {
            if ($first) {
                $first = false;
                continue;
            }

            $rowData = [];
            $rowData[] = $worksheet->getCell('B' . $row->getRowIndex())->getValue();
            $rowData[] = $worksheet->getCell('C' . $row->getRowIndex())->getValue();
            $rowData[] = $worksheet->getCell('J' . $row->getRowIndex())->getValue();
            $rowData[] = $worksheet->getCell('E' . $row->getRowIndex())->getValue();
            $rowData[] = $worksheet->getCell('R' . $row->getRowIndex())->getValue();
            $rowData[] = $worksheet->getCell('S' . $row->getRowIndex())->getValue();

            $data[] = $rowData;
        }

        foreach ($data as $row) {
            if (in_array(null, $row, true)) {
                continue;
            }

            if ($row[3] == 2) {
                continue;
            }

            $item = new Item();
            $item->setItemCode($row[0]);
            $item->setItemName($row[1]);
            $item->setItemPrice($row[2]);
            $item->setCategory($manager->getRepository(Category::class)->find((int)$row[3]));
            $item->setItemImage($row[4]);
            $item->setItemDescription($row[5]);

            $manager->persist($item);
        }

        $user = new User();
        $user->setUsername('admin');
        $user->setPassword('$2y$13$tP0AkqVmDq/by3xXk8tViOXInr8fjNe/.o/.rxmKoeASXK.dnYLZ6');
        $user->setRoles(['ROLE_ADMIN']);

        $user2 = new User();
        $user2->setUsername('user');
        $user2->setPassword('$2y$13$iUrmZpZDMiS9u.Fy3GzwNeWs6CvYp052po8YkHlKssMdwVgmnceJa');
        $user2->setRoles(['ROLE_USER']);

        $manager->persist($user);
        $manager->persist($user2);

        for ($i = 0; $i < 20; $i++) {
            $orderHistory = new OrderHistory();

            $orderHistory->setUser($user2);

            $totalPrice = mt_rand(200000, 15000000) / 100;

            $orderHistory->setTotalAmount(number_format($totalPrice, 2, '.', ''));
            $createdAt = $this->getRandomDateWithinLast3Months();
            $orderHistory->setStatus(2);
            $orderHistory->setCreatedAt($createdAt);
            $orderHistory->setOrderItems(['item1', 'item2', 'item3']);

            $payment = new Payment();
            $payment->setOrderHistory($orderHistory);
            $payment->setPaymentMethod(1);
            $payment->setStatus(1);
            $payment->setPaidAt(new \DateTime());

            $transaction = new Transaction();
            $transaction->setTransactionFor(0);
            $transaction->setPayment($payment);
            $transaction->setAmount($orderHistory->getTotalAmount());
            $transaction->setBankCode('TEMP');
            $transaction->setBankTranNo('VNP' . mt_rand(10000000, 99999999));
            $transaction->setCardType(['ATM', 'Credit', 'Debit'][array_rand(['ATM', 'Credit', 'Debit'])]);
            $transaction->setOrderInfo('Thanh toan GD:' . mt_rand(1000, 9999));
            $transaction->setPayDate((new \DateTime())->format('YmdHis'));
            $transaction->setResponseCode('00');
            $transaction->setTmnCode('NED' . mt_rand(1000, 9999));
            $transaction->setTransactionNo((string)mt_rand(10000000, 99999999));
            $transaction->setTransactionStatus('00');
            $transaction->setTxnRef((string)mt_rand(1000, 9999));
            $transaction->setSecureHash(hash('sha256', $transaction->getAmount() . $transaction->getBankTranNo() . $transaction->getTxnRef() . random_bytes(16)));

            $manager->persist($transaction);

            $payment->setTransaction($transaction);

            $manager->persist($payment);

            $orderHistory->setPayment($payment);

            $manager->persist($orderHistory);
        }

        $manager->flush();
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateInvalidOperationException
     */
    private function getRandomDateWithinLast3Months(): \DateTime
    {
        $now = new \DateTime();

        $interval = new \DateInterval('P3M');
        $now->sub($interval);

        $randomDays = mt_rand(0, 90);

        $now->modify("-$randomDays days");

        return $now;
    }
}
