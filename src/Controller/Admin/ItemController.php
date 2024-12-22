<?php
/**
 * @project ptpmhdv-nhom-7-symfony
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 06/12/2024
 * @time 06:28
 */

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Item;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class ItemController extends BaseController
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
        $this->em                 = $manager;
        $this->itemRepository = $this->em->getRepository(Item::class);
    }

    #[Route(path: '/item/list', name: 'item_list', methods: ['GET'])]
    public function index(): Response
    {
        $items = $this->getJsonArray('http://dastonehdv.local' . $this->generateUrl('api_item_list'));

        if (is_null($items)) {
            $this->addFlash('error', 'Items not found');
        }

        return $this->render('admin/item/index.html.twig', [
            'page_title' => 'Item List',
            'items' => $items,
        ]);
    }

    #[Route(path: '/item/view/{id}', name: 'item_view', methods: ['GET'])]
    public function viewItem(int $id): Response
    {
        $item = $this->getJsonArray('http://dastonehdv.local' . $this->generateUrl('api_item_view', ['id' => $id]));

        if (is_null($item)) {
            $this->addFlash('error', 'Item not found');

            return $this->redirectToRoute('admin_item_list');
        }

        return $this->render('admin/item/detail.html.twig', [
            'page_title' => 'Item Detail',
            'item' => $item,
        ]);
    }

    #[Route(path: '/item/create', name: 'item_create', methods: ['GET', 'POST'])]
    public function createItem(CategoryRepository $categoryRepository, Request $request): Response
    {
        $task = new Item();
        $form = $this->createFormBuilder($task)
            ->add('item_code', TextType::class, [
                'label' => 'Code',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('item_name', TextType::class, [
                'label' => 'Name',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('item_price', MoneyType::class, [
                'label' => 'Price',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Category',
                'attr' => ['class' => 'form-select'],
                'label_attr' => ['class' => 'form-label'],
                'row_attr' => ['class' => 'mb-3'],
                'choices' => $categoryRepository->findAll(),
                'choice_label' => function ($category) {
                    return $category->getCategoryName();
                },
                'choice_value' => function ($category) {
                    return $category ? $category->getId() : '';
                },
            ])
            ->add('item_image', TextType::class, [
                'label' => 'Image (enter as link)',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('item_description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Create',
                'attr' => ['class' => 'btn btn-primary'],
                'row_attr' => ['class' => 'mb-3 d-flex justify-content-end'],
            ])
            ->setAction($this->generateUrl('admin_item_create'))
            ->getForm();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $this->em->persist($task);
                    $this->em->flush();

                    $this->addFlash('success', 'Add new item successfully');
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                return $this->redirectToRoute('admin_item_create');
            }
        }

        return $this->render('admin/item/create.html.twig', [
            'page_title' => 'Create Item',
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/item/update/{id}', name: 'item_update', methods: ['GET', 'POST'])]
    public function updateItem(int $id, Request $request, CategoryRepository $categoryRepository): Response
    {
        $task = $this->itemRepository->find($id);

        if (is_null($task)) {
            $this->addFlash('error', 'Item not found');

            return $this->redirectToRoute('admin_item_list');
        }

        $form = $this->createFormBuilder($task)
            ->add('item_code', TextType::class, [
                'label' => 'Code',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('item_name', TextType::class, [
                'label' => 'Name',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('item_price', MoneyType::class, [
                'label' => 'Price',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Category',
                'attr' => ['class' => 'form-select'],
                'label_attr' => ['class' => 'form-label'],
                'row_attr' => ['class' => 'mb-3'],
                'choices' => $categoryRepository->findAll(),
                'choice_label' => function ($category) {
                    return $category->getCategoryName();
                },
                'choice_value' => function ($category) {
                    return $category ? $category->getId() : '';
                },
            ])
            ->add('item_image', TextType::class, [
                'label' => 'Image (enter as link)',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('item_description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Update',
                'attr' => ['class' => 'btn btn-primary'],
                'row_attr' => ['class' => 'mb-3 d-flex justify-content-end'],
            ])
            ->setAction($this->generateUrl('admin_item_update', ['id' => $id]))
            ->getForm();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $this->em->flush();

                    $this->addFlash('success', 'Update item successfully');
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                return $this->redirectToRoute('admin_item_update', ['id' => $id]);
            }
        }

        return $this->render('admin/item/update.html.twig', [
            'page_title' => 'Update Item',
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/item/delete/{id}', name: 'item_delete', methods: ['GET', 'POST'])]
    public function deleteItem(int $id): Response
    {
        $task = $this->itemRepository->find($id);

        if (is_null($task)) {
            $this->addFlash('error', 'Item not found');

            return $this->redirectToRoute('admin_item_list');
        }

        try {
            $this->em->remove($task);
            $this->em->flush();

            $this->addFlash('success', 'Delete item successfully');
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_item_list');
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