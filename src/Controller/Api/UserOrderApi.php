<?php
/**
 * @project ptpmhdv-nhom-7-symfony-final
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 14/12/2024
 * @time 18:18
 */

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\OrderHistory;
use App\Entity\User;
use App\Repository\OrderHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use http\Env\Request;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/api', name: 'api_')]
#[IsGranted('PUBLIC_ACCESS')]
class UserOrderApi extends BaseController
{
    /**
     * @var EntityManagerInterface $em Entity manager interface instance
     */
    private EntityManagerInterface $em;

    /**
     * @var OrderHistoryRepository|EntityRepository $itemRepository Repository
     */
    private OrderHistoryRepository|EntityRepository $orderHistoryRepository;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->em = $manager;
        $this->orderHistoryRepository = $this->em->getRepository(OrderHistory::class);
    }

    #[Route(path: '/get-user-orders', name: 'user_order_list', methods: ['GET'])]
    public function getUserOrders(\Symfony\Component\HttpFoundation\Request $request): RedirectResponse|JsonResponse
    {
        $user = $this->em->getRepository(User::class)->find($request->get('user_id'));

        $orders = $this->orderHistoryRepository->findBy(['user' => $user]);
        $data = [];

        foreach ($orders as $key => $order) {
            $data[] = [
                'id' => $order->getId(),
                'order_items' => $order->getOrderItems(),
                'status' => $order->getStatus(),
                'total_amount' => $order->getTotalAmount(),
                'action' => [
                    'refund' => '',
                    'cancel' => $this->generateUrl('account_order_cancel', ['id' => $order->getId()]),
                ],
            ];
        }

        return new JsonResponse(['data' => $data]);
    }
}