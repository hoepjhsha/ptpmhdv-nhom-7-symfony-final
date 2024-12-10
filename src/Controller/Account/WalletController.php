<?php
/**
 * @project ptpmhdv-nhom-7-symfony
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 07/12/2024
 * @time 19:12
 */

namespace App\Controller\Account;

use App\Controller\BaseController;
use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/account', name: 'account_')]
#[IsGranted('ROLE_USER')]
class WalletController extends BaseController
{
    private Security $security;
    private EntityManagerInterface $em;

    public function __construct(Security $security, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->security = $security;
    }

    #[Route(path: '/wallet', name: 'wallet', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $wallet = $user->getWallet();

        if (!$wallet) {
            $wallet = new Wallet();
            $wallet->setUser($user);

            $this->em->persist($wallet);

            $user->setWallet($wallet);
            $this->em->persist($user);
            $this->em->flush();
        }


        return $this->render('account/wallet.html.twig', [
            'page_title' => 'Wallet',
            'wallet' => $wallet
        ]);
    }

    #[Route(path: '/wallet/deposit', name: 'wallet_deposit', methods: ['GET', 'POST'])]
    public function deposit(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $wallet = $user->getWallet();

        $money = $request->request->get('_money');

        if ($money <= 0) {
            $this->addFlash('error', 'Invalid money amount');

            return $this->redirectToRoute('account_wallet');
        }

        $wallet->setCurrency($wallet->getCurrency() + $money);
        $this->em->persist($wallet);
        $this->em->flush();

        $this->addFlash('success', 'Deposit successfully');

        return $this->redirectToRoute('account_wallet');
    }
}