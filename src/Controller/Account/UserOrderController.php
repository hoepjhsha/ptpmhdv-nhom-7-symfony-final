<?php
/**
 * @project ptpmhdv-nhom-7-symfony-final
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 14/12/2024
 * @time 18:08
 */

namespace App\Controller\Account;

use App\Controller\BaseController;
use App\Entity\OrderHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/account', name: 'account_')]
#[IsGranted('ROLE_USER')]
class UserOrderController extends BaseController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route(path: '/order', name: 'order')]
    public function index(): Response
    {
        $orders = $this->getJsonArray('http://dastonehdv.local' . $this->generateUrl('api_user_order_list') . '?user_id=' . $this->getUser()->getId());

        return $this->render('account/user_order.html.twig', [
            'page_title' => "User's Orders",
            'orderItems' => $orders,
        ]);
    }

    #[Route(path: '/order-cancel/{id}', name: 'order_cancel')]
    public function cancelOrder(int $id): Response
    {
        $order = $this->em->getRepository(OrderHistory::class)->find($id);

        if ($order->getStatus() != 1 && $order->getStatus() != 0) {
            $this->addFlash('error', 'Cart can not be canceled!');
            return $this->redirectToRoute('account_order');
        }

        $order->setStatus(3);
        $this->em->persist($order);
        $this->em->flush();

        $this->addFlash('success', 'Cart has been canceled successfully!');
        return $this->redirectToRoute('account_order');
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