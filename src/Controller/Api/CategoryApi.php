<?php
/**
 * @project ptpmhdv-nhom-7-symfony
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 07/12/2024
 * @time 05:10
 */

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/api', name: 'api_')]
#[IsGranted('PUBLIC_ACCESS')]
class CategoryApi extends BaseController
{
    /**
     * @var EntityManagerInterface $em Entity manager interface instance
     */
    private EntityManagerInterface $em;
    /**
     * @var CategoryRepository|EntityRepository $categoryRepository Repository
     */
    private CategoryRepository|EntityRepository $categoryRepository;

    /**
     * Constructor.
     *
     * @param  EntityManagerInterface  $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->em = $manager;
        $this->categoryRepository = $this->em->getRepository(Category::class);
    }


    #[Route(path: '/get-categories', name: 'category_list', methods: ['GET'])]
    public function getCategoryList(): JsonResponse
    {
        $categories = $this->categoryRepository->findAll();
        $data = [];

        foreach ($categories as $key => $category) {
            $data[] = [
                'id'      => $category->getId(),
                'name' => $category->getCategoryName(),
                'action' => [
                    'view'   => $this->generateUrl('admin_category_view', ['id' => $category->getId()]),
                    'update' => $this->generateUrl('admin_category_update', ['id' => $category->getId()]),
                    'delete' => [
                        'url' => $this->generateUrl('admin_category_delete', ['id' => $category->getId()]),
                        'id'  => $category->getId(),
                    ],

                ],
            ];
        }

        return new JsonResponse(['data' => $data]);
    }

    #[Route(path: '/get-category/{id}', name: 'category_view', methods: ['GET'])]
    public function getCategory(int $id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);

        return new JsonResponse(['data' => [
            'id'      => $category->getId(),
            'name' => $category->getCategoryName(),
            'action' => [
                'view'   => $this->generateUrl('admin_category_view', ['id' => $category->getId()]),
                'update' => $this->generateUrl('admin_category_update', ['id' => $category->getId()]),
                'delete' => [
                    'url' => $this->generateUrl('admin_category_delete', ['id' => $category->getId()]),
                    'id'  => $category->getId(),
                ],

            ],
        ]]);
    }
}