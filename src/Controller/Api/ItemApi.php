<?php
/**
 * @project ptpmhdv-nhom-7-symfony
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 06/12/2024
 * @time 06:31
 */

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\Item;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/api', name: 'api_')]
#[IsGranted('PUBLIC_ACCESS')]
class ItemApi extends BaseController
{
    /**
     * @var EntityManagerInterface $em Entity manager interface instance
     */
    private EntityManagerInterface $em;
    /**
     * @var ItemRepository|EntityRepository $itemRepository Repository
     */
    private ItemRepository|EntityRepository $itemRepository;

    /**
     * Constructor.
     *
     * @param  EntityManagerInterface  $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->em = $manager;
        $this->itemRepository = $this->em->getRepository(Item::class);
    }


    #[Route(path: '/get-items', name: 'item_list', methods: ['GET'])]
    public function getItemList(): JsonResponse
    {
        $items = $this->itemRepository->findAll();
        $data = [];

        foreach ($items as $item) {
            $data[] = [
                'id'      => $item->getId(),
                'code' => $item->getItemCode(),
                'name' => $item->getItemName(),
                'price' => $item->getItemPrice(),
                'category' => $item->getCategory()->getCategoryName(),
                'image' => $item->getItemImage(),
                'description' => $item->getItemDescription() ?? "",
                'action' => [
                    'view'   => $this->generateUrl('admin_item_view', ['id' => $item->getId()]),
                    'update' => $this->generateUrl('admin_item_update', ['id' => $item->getId()]),
                    'delete' => [
                        'url' => $this->generateUrl('admin_item_delete', ['id' => $item->getId()]),
                        'id'  => $item->getId(),
                    ],

                ],
            ];
        }

        return new JsonResponse(['data' => $data]);
    }

    #[Route(path: '/get-item/{id}', name: 'item_view', methods: ['GET'])]
    public function getItem(int $id): JsonResponse
    {
        $item = $this->itemRepository->find($id);

        return new JsonResponse(['data' => [
            'id' => $item->getId(),
            'code' => $item->getItemCode(),
            'name' => $item->getItemName(),
            'price' => $item->getItemPrice(),
            'category' => $item->getCategory()->getCategoryName(),
            'image' => $item->getItemImage(),
            'description' => $item->getItemDescription() ?? "",
            'action' => [
                'view'   => $this->generateUrl('admin_item_view', ['id' => $item->getId()]),
                'update' => $this->generateUrl('admin_item_update', ['id' => $item->getId()]),
                'delete' => [
                    'url' => $this->generateUrl('admin_item_delete', ['id' => $item->getId()]),
                    'id'  => $item->getId(),
                ],

            ],
        ]]);
    }

    #[Route(path: '/get-items-shop', name: 'item_shop_list', methods: ['GET'])]
    public function getItemsForShop(): JsonResponse
    {
        $items = $this->itemRepository->findAll();
        $data = [];

        foreach ($items as $item) {
            $data[] = [
                'id'      => $item->getId(),
                'code' => $item->getItemCode(),
                'name' => $item->getItemName(),
                'price' => $item->getItemPrice(),
                'category' => $item->getCategory()->getCategoryName(),
                'image' => $item->getItemImage(),
                'description' => $item->getItemDescription() ?? "",
                'action' => [
                    'view' => $this->generateUrl('shop_view', ['id' => $item->getId()]),
                ],
            ];
        }

        return new JsonResponse(['data' => $data]);
    }

    #[Route(path: '/get-item-shop/{id}', name: 'item_shop_view', methods: ['GET'])]
    public function getItemShop(int $id): JsonResponse
    {
        $item = $this->itemRepository->find($id);

        return new JsonResponse(['data' => [
            'id' => $item->getId(),
            'code' => $item->getItemCode(),
            'name' => $item->getItemName(),
            'price' => $item->getItemPrice(),
            'category' => $item->getCategory()->getCategoryName(),
            'image' => $item->getItemImage(),
            'description' => $item->getItemDescription() ?? "",
            'action' => [
                'view' => $this->generateUrl('shop_view', ['id' => $item->getId()]),
            ],
        ]]);
    }
}