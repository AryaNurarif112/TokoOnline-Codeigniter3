<?php

class Pembayaran extends CI_Controller
{
    public function index()
    {
        $apiKey       = 'DEV-m7hwgfuuDkuUYQ6oQRAPE5h7FyAVYPFy87m4DERv';
        $privateKey   = 'QldGG-ryeUM-aR6kS-Klls0-gi1q3';
        $merchantCode = 'T10724';
        $merchantRef  = 'id';
        $amount       = 1000000;

        $data = [
            'method'         => 'BRIVA',
            'merchant_ref'   => $merchantRef,
            'amount'         => $amount,
            'customer_name'  => 'Nama Pelanggan',
            'customer_email' => 'emailpelanggan@domain.com',
            'customer_phone' => '081234567890',
            'order_items'    => [
                [
                    'sku'         => 'FB-06',
                    'name'        => 'Nama Produk 1',
                    'price'       => 500000,
                    'quantity'    => 1,
                    'product_url' => 'https://tokokamu.com/product/nama-produk-1',
                    'image_url'   => 'https://tokokamu.com/product/nama-produk-1.jpg',
                ],
                [
                    'sku'         => 'FB-07',
                    'name'        => 'Nama Produk 2',
                    'price'       => 500000,
                    'quantity'    => 1,
                    'product_url' => 'https://tokokamu.com/product/nama-produk-2',
                    'image_url'   => 'https://tokokamu.com/product/nama-produk-2.jpg',
                ]
            ],
            'return_url'   => 'https://domainanda.com/redirect',
            'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
            'signature'    => hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey)
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => 'https://tripay.co.id/api-sandbox/transaction/create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data)
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);
        print_r(json_decode($response));
        //print empty($error) ? json_decode($response) : json_decode($error);
    }
}
