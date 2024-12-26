<?php
/**
 * @project ptpmhdv-nhom-7-symfony-final
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 26/12/2024
 * @time 10:26
 */

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\OrderHistory;
use App\Repository\OrderHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/admin/order', name: 'admin_order_')]
class OrderController extends BaseController
{
    /**
     * @var EntityManagerInterface $em Entity manager interface instance
     */
    private EntityManagerInterface $em;

    private OrderHistoryRepository|EntityRepository $orderHistoryRepository;

    /**
     * Constructor.
     *
     * @param  EntityManagerInterface  $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->em                 = $manager;
        $this->orderHistoryRepository = $this->em->getRepository(OrderHistory::class);
    }

    #[Route(path: '/list', name: 'list', methods: ['GET'])]
    public function index(): Response
    {
        $orderHistories = $this->orderHistoryRepository->findAll();

        return $this->render('admin/order/index.html.twig', [
            'page_title' => 'Order List',
            'orders' => $orderHistories,
        ]);
    }

    #[Route(path: '/view/{id}', name: 'view', methods: ['GET'])]
    public function viewOrder(int $id): Response
    {
        $order = $this->orderHistoryRepository->find($id);

        return $this->render('admin/order/detail.html.twig', [
            'page_title' => 'Order Detail',
            'order' => $order,
        ]);
    }

    #[Route(path: '/update/{id}', name: 'update', methods: ['GET', 'POST'])]
    public function updateOrder(int $id, Request $request): Response
    {
        $task = $this->orderHistoryRepository->find($id);

        if (is_null($task)) {
            $this->addFlash('error', 'Order not found');

            return $this->redirectToRoute('admin_order_list');
        }

        $form = $this->createFormBuilder($task)
            ->add('status', TextType::class, [
                'label' => 'Status',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Update',
                'attr' => ['class' => 'btn btn-primary'],
                'row_attr' => ['class' => 'mb-3 d-flex justify-content-end'],
            ])
            ->setAction($this->generateUrl('admin_order_update', ['id' => $id]))
            ->getForm();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $this->em->flush();

                    $this->addFlash('success', 'Update order successfully');
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                return $this->redirectToRoute('admin_order_update', ['id' => $id]);
            }
        }

        return $this->render('admin/order/update.html.twig', [
            'page_title' => 'Update Order',
            'form' => $form->createView(),
        ]);
    }
}