<?php
/**
 * @project ptpmhdv-nhom-7-symfony
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 06/12/2024
 * @time 05:46
 */

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\OrderHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends BaseController
{
    #[Route(path: '/', name: 'dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        $orderHistories = $em->getRepository(OrderHistory::class)
            ->createQueryBuilder('o')
            ->orderBy('o.created_at', 'ASC')
            ->getQuery()
            ->getResult();

        $chartData = [];
        foreach ($orderHistories as $orderHistory) {
            $chartData[] = [
                'date' => $orderHistory->getCreatedAt()->format('Y-m-d'),
                'value' => $orderHistory->getTotalPrice()
            ];
        }

        return $this->render('admin/index.html.twig', [
            'page_title' => 'Dashboard',
            'chartData' => json_encode($chartData)
        ]);
    }
}