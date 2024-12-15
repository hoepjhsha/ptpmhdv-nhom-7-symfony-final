<?php
/**
 * @project ptpmhdv-nhom-7-symfony
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 07/12/2024
 * @time 05:10
 */

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class CategoryController extends BaseController
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
        $this->em                 = $manager;
        $this->categoryRepository = $this->em->getRepository(Category::class);
    }

    #[Route(path: '/category/list', name: 'category_list', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->getJsonArray('http://dastonehdv.local' . $this->generateUrl('api_category_list'));

        if (is_null($categories)) {
            $this->addFlash('error', 'Categories not found');

//            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/category/index.html.twig', [
            'page_title' => 'Category List',
            'categories' => $categories,
        ]);
    }

    #[Route(path: '/category/view/{id}', name: 'category_view', methods: ['GET'])]
    public function viewItem(int $id): Response
    {
        $category = $this->getJsonArray('http://dastonehdv.local' . $this->generateUrl('api_category_view', ['id' => $id]));

        if (is_null($category)) {
            $this->addFlash('error', 'Category not found');

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/category/detail.html.twig', [
            'page_title' => 'Category Detail',
            'category' => $category,
        ]);
    }

    #[Route(path: '/category/create', name: 'category_create', methods: ['GET', 'POST'])]
    public function createCategory(Request $request): Response
    {
        $task = new Category();
        $form = $this->createFormBuilder($task)
            ->add('category_name', TextType::class, [
                'label' => 'Category name',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Create',
                'attr' => ['class' => 'btn btn-primary'],
                'row_attr' => ['class' => 'mb-3 d-flex justify-content-end'],
            ])
            ->setAction($this->generateUrl('admin_category_create'))
            ->getForm();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $task->setId($this->em->getRepository(Category::class)->findBy([], ['id' => 'DESC'])[0]->getId() + 1);
                    $this->em->persist($task);
                    $this->em->flush();

                    $this->addFlash('success', 'Add new category successfully');
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                return $this->redirectToRoute('admin_category_create');
            }
        }

        return $this->render('admin/category/create.html.twig', [
            'page_title' => 'Create Category',
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/category/update/{id}', name: 'category_update', methods: ['GET', 'POST'])]
    public function updateItem(int $id, Request $request): Response
    {
        $task = $this->categoryRepository->find($id);

        if (is_null($task)) {
            $this->addFlash('error', 'Category not found');

            return $this->redirectToRoute('admin_category_list');
        }

        $form = $this->createFormBuilder($task)
            ->add('category_name', TextType::class, [
                'label' => 'Category name',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Create',
                'attr' => ['class' => 'btn btn-primary'],
                'row_attr' => ['class' => 'mb-3 d-flex justify-content-end'],
            ])
            ->setAction($this->generateUrl('admin_category_update', ['id' => $id]))
            ->getForm();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $this->em->flush();

                    $this->addFlash('success', 'Update category successfully');
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                return $this->redirectToRoute('admin_category_update', ['id' => $id]);
            }
        }

        return $this->render('admin/category/update.html.twig', [
            'page_title' => 'Update Category',
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/category/delete/{id}', name: 'category_delete', methods: ['GET', 'POST'])]
    public function deleteItem(int $id): Response
    {
        $task = $this->categoryRepository->find($id);

        if (is_null($task)) {
            $this->addFlash('error', 'Category not found');

            return $this->redirectToRoute('admin_category_list');
        }

        try {
            $this->em->remove($task);
            $this->em->flush();

            $this->addFlash('success', 'Delete category successfully');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_category_list');
    }

    /**
     * Function handle get data from API
     *
     * @param string $url
     *
     * @return mixed
     */
    private function getJsonArray(string $url): mixed
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: main_deauth_profile_token=74722a'
            ),
        ));

        $response = curl_exec($curl);

        $response = json_decode($response, true)['data'];

        curl_close($curl);

        return $response;
    }
}