<?php
/**
 * @project ptpmhdv-nhom-7-symfony-final
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 25/12/2024
 * @time 23:40
 */

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/user', name: 'admin_user_')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends BaseController
{
    /**
     * @var EntityManagerInterface $em Entity manager interface instance
     */
    private EntityManagerInterface $em;

    /**
     * @var EntityRepository|UserRepository $userRepository Repository
     */
    private UserRepository|EntityRepository $userRepository;

    /**
     * Constructor.
     *
     * @param  EntityManagerInterface  $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->em                 = $manager;
        $this->userRepository = $this->em->getRepository(User::class);
    }

    #[Route(path: '/list', name: 'list', methods: ['GET'])]
    public function index(): Response
    {
        $users = $this->userRepository->findAll();

        return $this->render('admin/user/index.html.twig', [
            'page_title' => 'User Account List',
            'users' => $users,
        ]);
    }

    #[Route(path: '/view/{id}', name: 'view', methods: ['GET'])]
    public function viewUser(int $id): Response
    {
        $item = $this->userRepository->find($id);

        if (is_null($item)) {
            $this->addFlash('error', 'User not found');

            return $this->redirectToRoute('admin_user_list');
        }

        return $this->render('admin/user/detail.html.twig', [
            'page_title' => 'User Detail',
            'item' => $item,
        ]);
    }

    #[Route(path: '/create', name: 'create', methods: ['GET', 'POST'])]
    public function createUser(Request $request): Response
    {
        $task = new User();
        $form = $this->createFormBuilder($task)
            ->add('username', TextType::class, [
                'label' => 'Username',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('password', TextType::class, [
                'label' => 'Password',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Create',
                'attr' => ['class' => 'btn btn-primary'],
                'row_attr' => ['class' => 'mb-3 d-flex justify-content-end'],
            ])
            ->setAction($this->generateUrl('admin_user_create'))
            ->getForm();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $this->em->persist($task);
                    $this->em->flush();

                    $this->addFlash('success', 'Add new user successfully');
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                return $this->redirectToRoute('admin_user_create');
            }
        }

        return $this->render('admin/user/create.html.twig', [
            'page_title' => 'Create User',
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/update/{id}', name: 'update', methods: ['GET', 'POST'])]
    public function updateUser(int $id, Request $request): Response
    {
        $task = $this->userRepository->find($id);

        if (is_null($task)) {
            $this->addFlash('error', 'User not found');

            return $this->redirectToRoute('admin_user_list');
        }

        $form = $this->createFormBuilder($task)
            ->add('username', TextType::class, [
                'label' => 'Username',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('password', TextType::class, [
                'label' => 'Password',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Update',
                'attr' => ['class' => 'btn btn-primary'],
                'row_attr' => ['class' => 'mb-3 d-flex justify-content-end'],
            ])
            ->setAction($this->generateUrl('admin_user_update', ['id' => $id]))
            ->getForm();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $this->em->flush();

                    $this->addFlash('success', 'Update user successfully');
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                return $this->redirectToRoute('admin_user_update', ['id' => $id]);
            }
        }

        return $this->render('admin/user/update.html.twig', [
            'page_title' => 'Update User',
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/delete/{id}', name: 'delete', methods: ['GET', 'POST'])]
    public function deleteUser(int $id): Response
    {
        $task = $this->userRepository->find($id);

        if (is_null($task)) {
            $this->addFlash('error', 'User not found');

            return $this->redirectToRoute('admin_user_list');
        }

        try {
            $this->em->remove($task);
            $this->em->flush();

            $this->addFlash('success', 'Delete user successfully');
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_user_list');
    }
}