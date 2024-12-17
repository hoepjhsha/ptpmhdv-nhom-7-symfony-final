<?php
/**
 * @project ptpmhdv-nhom-7-symfony-final
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 16/12/2024
 * @time 14:54
 */

namespace App\Controller\Account;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/account', name: 'account_')]
#[IsGranted('ROLE_USER')]
class SpayLaterController extends BaseController
{
    #[Route(path: '/spay-later', name: 'spay_later', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('account/spaylater.html.twig', [
            'page_title' => 'Spay Later',
        ]);
    }
}