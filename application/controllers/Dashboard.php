<?php

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('role_id') != '2') {
            $this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            Anda Belum Login!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
          </div>');
            redirect('auth/login');
        }
    }
    public function tambah_ke_keranjang($id)
    {
        $barang = $this->Model_barang->find($id);
        $data = array(
            'id'      => $barang->id_brg,
            'qty'     => 1,
            'price'   => $barang->harga,
            'name'    => $barang->nama_brg,
        );

        $this->cart->insert($data);
        redirect('welcome');
    }

    public function detail_keranjang()
    {
        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('keranjang');
        $this->load->view('templates/footer');
    }

    public function hapus_keranjang()
    {
        $this->cart->destroy();
        redirect('welcome');
    }

    public function pembayaran()
    {
        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('pembayaran');
        $this->load->view('templates/footer');
    }

    public function proses_pesanan()
    {
        date_default_timezone_set('Asia/Jakarta');
        $id_user = $this->session->userdata('id');
        $nama = $this->input->post('nama');
        $alamat = $this->input->post('alamat');
        $no_hape = $this->input->post('no_telp');
        $metodebayar = $this->input->post('metode_pembayaran');
        $no_invoice = date('YmdHis');
        $returnurl = base_url();
        $invoice = array(
            'nama' => $nama,
            'alamat' => $alamat,
            'tgl_pesan' => date('Y-m-d H:i:s'),
            'batas_bayar' => date('Y-m-d H:i:s', mktime(
                date('H'),
                date('i'),
                date('s'),
                date('m'),
                date('d') + 1,
                date('Y')
            )),
            'no_hape' => $no_hape,
            'id_user' => $id_user,
            'no_invoice' => $no_invoice,
        );
        $this->Model_invoice->index($invoice); //save invoice

        // tripay
        $apiKey       = 'DEV-m7hwgfuuDkuUYQ6oQRAPE5h7FyAVYPFy87m4DERv';
        $privateKey   = 'QldGG-ryeUM-aR6kS-Klls0-gi1q3';
        $merchantCode = 'T10724';
        $merchantRef  = $no_invoice;
        $amount       = preg_replace('/\D/', '', $this->cart->total());
        foreach ($this->cart->contents() as $item) {
            $keranjang[] = [
                'sku' => $item['id'],
                'name' => $item['name'],
                'quantity' => $item['qty'],
                'price' => $item['price'],
            ];
        }
        $datatripay = [
            'method'         => $metodebayar,
            'merchant_ref'   => $merchantRef,
            'amount'         => $amount,
            'customer_name'  => $nama,
            'customer_email' => 'emailpelanggan@domain.com',
            'customer_phone' => $no_hape,
            'order_items'    => $keranjang,
            'return_url'   => $returnurl,
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
            CURLOPT_POSTFIELDS     => http_build_query($datatripay)
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);
        $respon = json_decode($response);
        $url_checkout = $respon->data->checkout_url;
        // var_dump($url_checkout);
        // die;
        $this->cart->destroy();
        redirect($url_checkout);
    }
    public function detail($id_brg)
    {
        $data['barang'] = $this->Model_barang->detail_brg($id_brg);
        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('detail_barang', $data);
        $this->load->view('templates/footer');
    }
}
