<?php
/**
 * @project ptpmhdv-nhom-7-symfony-final
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 23/12/2024
 * @time 11:42
 */

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('PUBLIC_ACCESS')]
class HomeController extends BaseController
{
    #[Route(path: '', name: 'home')]
    public function index(): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->redirectToRoute('shop_list');
    }

    #[Route(path: '/404', name: '404')]
    public function route_404(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('404.html.twig');
    }
}