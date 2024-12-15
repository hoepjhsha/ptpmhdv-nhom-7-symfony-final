<?php
/**
 * @project ptpmhdv-nhom-7-symfony-final
 * @author hoepjhsha
 * @email hiepnguyen3624@gmail.com
 * @date 14/12/2024
 * @time 00:44
 */

namespace App\Services;

class VNPayService
{
    private string $vnp_TmnCode;
    private string $vnp_HashSecret;
    private string $vnp_Url;
    private  string $vnp_Returnurl;

    public function __construct(string $vnp_TmnCode, string $vnp_HashSecret, string $vnp_Url, string $vnp_Returnurl)
    {
        $this->vnp_TmnCode = $vnp_TmnCode;
        $this->vnp_HashSecret = $vnp_HashSecret;
        $this->vnp_Url = $vnp_Url;
        $this->vnp_Returnurl = $vnp_Returnurl;
    }

    public function createPaymentUrl(array $data): string
    {
        $vnp_TxnRef = rand(1,10000);
        $vnp_Amount = $data['amount'];
        $vnp_Locale = $data['language'];
        $vnp_BankCode = $data['bankCode'];
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
        $startTime = date("YmdHis");
        $expire = date('YmdHis',strtotime('+15 minutes',strtotime($startTime)));

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount* 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => "Thanh toan GD:" . $vnp_TxnRef,
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => $this->vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate"=>$expire
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);

        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $this->vnp_Url . "?" . $query;
        if (isset($this->vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);//
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    public function getVnpHashSecret(): string
    {
        return $this->vnp_HashSecret;
    }
}