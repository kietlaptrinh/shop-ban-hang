<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Giohang extends CI_Controller {
	// Hàm khởi tạo
    function __construct() {
        parent::__construct();
        $this->load->model('frontend/Morder');
        $this->load->model('frontend/Mproduct');
        $this->load->model('frontend/Morderdetail');
        $this->load->model('frontend/Mcustomer');
        $this->load->model('frontend/Mcategory');
        $this->load->model('frontend/Mconfig');
        $this->load->model('frontend/Mdistrict');
        $this->load->model('frontend/Mprovince');
        $this->load->library('paypal_lib');
        $this->data['com']='giohang';

    }
    
    public function index(){



        $this->data['title']='Website - Giỏ hàng của bạn';
        $this->data['view']='index';
        $this->load->view('frontend/layout',$this->data);

    }
    function check_mail(){
        $email = $this->input->post('email');
        if($this->Mcustomer->customer_detail_email($email))
        {
            $this->form_validation->set_message(__FUNCTION__, 'Email đã đã là thành viên, Vui lòng đăng nhập hoặc nhập Email khác !');
            return FALSE;
        }
        return TRUE;
    }
    public function info_order()
{
    $this->load->library('session');
    $this->load->helper('string');
    $this->load->library('form_validation');

    // Validation rules
    if (!$this->session->userdata('sessionKhachHang')) {
        $this->form_validation->set_rules('email', 'Địa chỉ email', 'required|valid_email|is_unique[db_customer.email]');
    } else {
        $this->form_validation->set_rules('tv', 'Email', 'required|valid_email');
    }
    $this->form_validation->set_rules('phone', 'Số điện thoại', 'required|numeric');
    $this->form_validation->set_rules('name', 'Họ và tên', 'required|min_length[3]');
    $this->form_validation->set_rules('address', 'Địa chỉ', 'required');
    $this->form_validation->set_rules('city', 'Tỉnh thành', 'required|numeric');
    $this->form_validation->set_rules('DistrictId', 'Quận huyện', 'required|numeric');
    $this->form_validation->set_rules('payment_method', 'Phương thức thanh toán', 'required|in_list[cod,paypal]');

    // Debug form data and session
    log_message('debug', 'Form data: ' . json_encode($this->input->post()));
    log_message('debug', 'SessionKhachHang: ' . json_encode($this->session->userdata('sessionKhachHang')));

    if ($this->form_validation->run() == TRUE) {
        // Tính toán tổng tiền
        $money = 0;
        $cartData = $this->session->userdata('cart');
        log_message('debug', 'Cart data: ' . json_encode($cartData));
        if (empty($cartData)) {
            log_message('error', 'Cart is empty');
            $this->session->set_flashdata('error', 'Giỏ hàng trống. Vui lòng thêm sản phẩm.');
            redirect('gio-hang');
        }

        foreach ($cartData as $key => $value) {
            $row = $this->Mproduct->product_detail_id($key);
            if (!$row) {
                log_message('error', 'Product not found for ID: ' . $key);
                $this->session->set_flashdata('error', 'Sản phẩm không tồn tại trong giỏ hàng.');
                redirect('gio-hang/info-order');
            }
            $price = ($row['price_sale'] > 0) ? $row['price_sale'] : $row['price'];
            if ($price <= 0) {
                log_message('error', 'Invalid product price for ID: ' . $key);
                $this->session->set_flashdata('error', 'Giá sản phẩm không hợp lệ.');
                redirect('gio-hang/info-order');
            }
            $total = $price * $value;
            $money += $total;
        }

        $priceShip = $this->Mconfig->config_price_ship();
        $coupon = $this->session->userdata('coupon_price') ?: 0;
        $money_vnd = $money + $priceShip - $coupon;

        log_message('debug', 'Money: ' . $money . ', PriceShip: ' . $priceShip . ', Coupon: ' . $coupon . ', Money VND: ' . $money_vnd);

        if ($money_vnd <= 0) {
            log_message('error', 'Invalid order amount: ' . $money_vnd);
            $this->session->set_flashdata('error', 'Số tiền đơn hàng không hợp lệ.');
            redirect('gio-hang/info-order');
        }

        // Xử lý thông tin khách hàng
        $idCustomer = null;
        if ($this->session->userdata('sessionKhachHang')) {
            $info = $this->session->userdata('sessionKhachHang');
            $idCustomer = $info['id'];
            $email = $this->input->post('tv');
        } else {
            $email = $this->input->post('email');
            $datacustomer = array(
                'fullname' => $this->input->post('name'),
                'phone' => $this->input->post('phone'),
                'email' => $email,
                'created' => date('Y-m-d H:i:s'),
                'status' => 1,
                'trash' => 1
            );
            $this->Mcustomer->customer_insert($datacustomer);
            $new_customer_info = $this->Mcustomer->customer_detail_email($email);
            $idCustomer = $new_customer_info['id'];
            $this->session->set_userdata('info-customer', $new_customer_info);
        }

        // Chuẩn bị dữ liệu đơn hàng
        $mydata = array(
            'orderCode' => random_string('alnum', 8),
            'customerid' => $idCustomer,
            'orderdate' => date('Y-m-d H:i:s'),
            'fullname' => $this->input->post('name'),
            'phone' => $this->input->post('phone'),
            'address' => $this->input->post('address'),
            'money' => $money_vnd,
            'price_ship' => $priceShip,
            'coupon' => $coupon,
            'province' => $this->input->post('city'),
            'district' => $this->input->post('DistrictId'),
            'trash' => 1,
            'status' => 0
        );

        // Rẽ nhánh phương thức thanh toán
        $payment_method = $this->input->post('payment_method');
        if ($payment_method == 'paypal') {
            $this->session->set_userdata('paypal_order_data', $mydata);
            $this->config->load('paypal', TRUE);
            $exchange_rate = $this->config->item('exchange_rate', 'paypal');
log_message('debug', 'Exchange rate loaded: ' . var_export($exchange_rate, true));
if (!is_numeric($exchange_rate) || $exchange_rate <= 0) {
    log_message('error', 'Invalid exchange rate: ' . var_export($exchange_rate, true));
    $this->session->set_flashdata('error', 'Lỗi cấu hình tỷ giá hối đoái.');
    redirect('gio-hang/info-order');
}
            $total_usd = round($money_vnd / $exchange_rate, 2);
            if ($total_usd <= 0 || !is_numeric($total_usd)) {
                log_message('error', 'Invalid USD amount: ' . $total_usd . ', money_vnd: ' . $money_vnd . ', exchange_rate: ' . $exchange_rate);
                $this->session->set_flashdata('error', 'Số tiền thanh toán không hợp lệ.');
                redirect('gio-hang/info-order');
            }
            $description = "Thanh toán cho đơn hàng: " . $mydata['orderCode'];
            log_message('debug', 'PayPal create_payment: total=' . $total_usd . ', orderCode=' . $mydata['orderCode']);
            $payment = $this->paypal_lib->create_payment($total_usd, $mydata['orderCode'], $description);
            if ($payment && $payment->getApprovalLink()) {
                log_message('debug', 'PayPal approval link: ' . $payment->getApprovalLink());
                redirect($payment->getApprovalLink());
            } else {
                log_message('error', 'PayPal create_payment failed: ' . json_encode($payment));
                $this->session->set_flashdata('error', 'Không thể kết nối với PayPal. Vui lòng thử lại.');
                redirect('gio-hang/info-order');
            }
        } else {
            $this->_save_order_to_db($mydata);
            redirect('/thankyou', 'refresh');
        }
    } else {
        log_message('error', 'Form validation failed: ' . json_encode($this->form_validation->error_array()));
        $this->data['title'] = 'Website - Thông tin đơn hàng';
        $this->data['view'] = 'info-order';
        $this->data['validation_errors'] = validation_errors(); // Thêm để debug lỗi validation
        $this->load->view('frontend/layout', $this->data);
    }
}

    public function paypal_success()
    {
        if (isset($_GET['paymentId']) && isset($_GET['PayerID'])) {
            $payment_id = $_GET['paymentId'];
            $payer_id = $_GET['PayerID'];
            $result = $this->paypal_lib->execute_payment($payment_id, $payer_id);

            if ($result && $result->getState() == 'approved') {
                $mydata = $this->session->userdata('paypal_order_data');
                if ($mydata) {
                    $mydata['status'] = 1; // Trạng thái: Đã thanh toán, chờ xử lý
                    $this->_save_order_to_db($mydata);
                    $this->session->unset_userdata('paypal_order_data');
                    redirect('/thankyou', 'refresh');
                }
            }
        }
        $this->session->set_flashdata('error', 'Thanh toán PayPal không thành công hoặc có lỗi xảy ra.');
        redirect('gio-hang');
    }

    public function paypal_cancel()
    {
        $this->session->set_flashdata('error', 'Bạn đã hủy giao dịch thanh toán qua PayPal.');
        redirect('gio-hang');
    }

    private function _save_order_to_db($mydata)
    {
        $this->Morder->order_insert($mydata);
        $order_info = $this->Morder->order_detail_by_code($mydata['orderCode']);
        $orderid = $order_info['id'];

        if ($this->session->userdata('cart')) {
            $val = $this->session->userdata('cart');
            foreach ($val as $key => $value) {
                $row = $this->Mproduct->product_detail_id($key);
                $price = ($row['price_sale'] > 0) ? $row['price_sale'] : $row['price'];
                $data_detail = array(
                    'orderid' => $orderid,
                    'productid' => $key,
                    'price' => $price,
                    'count' => $value,
                    'trash' => 1,
                    'status' => 1
                );
                $this->Morderdetail->orderdetail_insert($data_detail);
            }
        }

        if ($this->session->userdata('coupon_price')) {
            $idcoupon = $this->session->userdata('id_coupon_price');
            $amount_number_used = $this->Mconfig->get_amount_number_used($idcoupon);
            $mycoupon = array('number_used' => $amount_number_used + 1);
            $this->Mconfig->coupon_update($mycoupon, $idcoupon);
        }

        $this->session->unset_userdata('cart');
        $this->session->unset_userdata('coupon_price');
        $this->session->unset_userdata('id_coupon_price');
        return true;
    }

    

    public function thankyou(){
        if($this->session->userdata('info-customer')||$this->session->userdata('sessionKhachHang')){
            if($this->session->userdata('sessionKhachHang')){
                $val = $this->session->userdata('sessionKhachHang');
            }else{
                $val = $this->session->userdata('info-customer');
            }
            $list = $this->Morder->order_detail_customerid($val['id']);
            $data = array(
                'order' => $list,
                'customer' => $val,
                'orderDetail' => $this->Morderdetail->orderdetail_order_join_product($list['id']),
                'province' => $this->Mprovince->province_name($list['province']),
                'district' => $this->Mdistrict->district_name($list['district']),
                'priceShip' => $this->Mconfig->config_price_ship(),
                'coupon' => $list['coupon'],

            );
            $this->data['customer']=$val;
            $this->data['get']=$list;
            $this->load->library('email');
            $this->load->library('parser');
            $this->email->clear();
            $config['protocol']    = 'smtp';
            $config['smtp_host']    = 'ssl://smtp.gmail.com';
            $config['smtp_port']    = '465';
            $config['smtp_timeout'] = '7';
            $config['smtp_user']    = 'hmai.my03@gmail.com';
            $config['smtp_pass']    = 'wyxqqggdgahvoyrd';
            // mk trên la mat khau dung dung cua gmail, có thể dùng gmail hoac mat khau. Tao mat khau ung dung de bao mat tai khoan
            $config['charset']    = 'utf-8';
            $config['newline']    = "\r\n";
            $config['wordwrap'] = TRUE;
            $config['mailtype'] = 'html';
            $config['validation'] = TRUE;   
            $this->email->initialize($config);
            $this->email->from('hmai.my03@gmail.com', 'Website');
            $list = array($val['email']);
            $this->email->to($list);
            $this->email->subject('Website');
            $body = $this->load->view('frontend/modules/email',$data,TRUE);
            $this->email->message($body); 
            $this->email->send();

            $datax = array('email' => '');
            $idx= $this->session->userdata('id-info-customer');
            $this->Mcustomer->customer_update($datax,$idx);
            $this->session->unset_userdata('id-info-customer','money_check_coupon');
        }   
        $this->data['title']='Website - Kết quả đơn hàng';
        $this->data['view']='thankyou';
        $this->load->view('frontend/layout',$this->data);
    }

    public function district(){
        $this->load->library('session');
        $id=$_POST['provinceid'];
        $list = $this->Mdistrict->district_provinceid($id);
        $html="<option value =''>--- Chọn quận huyện ---</option>";
        foreach ($list as $row) 
        {
            $html.='<option value = '.$row["id"].'>'.$row["name"].'</option>';
        }
        echo json_encode($html);
    }
    public function coupon(){
        $d=getdate();
        $today=$d['year']."-".$d['mon']."-".$d['mday'];
        $html='';
        if($this->session->userdata('coupon_price')){
         $html.='<p>Mỗi đơn hàng chỉ áp dụng 1 Mã giảm giá !!</p>';
     }else{
        if(empty($_POST['code']))
        {
            $html.='<p>Vui lòng nhập Mã giảm giá nếu có !!</p>';
        }
        else
        {
            // KIỂM TRA SỐ TIỀN TRONG GIỎ HÀNG
            $money=0;
            if($this->session->userdata('cart')){
                $data=$this->session->userdata('cart');
                foreach ($data as $key => $value) {
                    $row = $this->Mproduct->product_detail_id($key);
                    $total=0;
                    if($row['price_sale'] > 0){
                        $total=$row['price_sale']*$value;
                    }else{
                        $total=$row['price'] * $value;
                    }
                    $money+=$total;
                }
            }
            //
            // KIỂM TRA MÃ GIẢM GIÁ CÓ TỒN TẠI KO
            $coupon = $_POST['code'];
            $getcoupon = $this->Mconfig->get_config_coupon_discount($coupon);
            if(empty($getcoupon)) {
               $html.='<p>Mã giảm giá không tồn tại!</p>';
           }
           foreach ($getcoupon as $value) {
            if($value['code'] == $coupon)
            {
                if (strtotime($value['expiration_date']) <= strtotime($today)){
                    $html.='<p>Mã giảm giá '.$value['code'].' đã hết hạn sử dụng từ ngày '.$value['expiration_date'].' !</p>';
                }else if($value['limit_number'] -$value['number_used'] == 0){
                    $html.='<p>Mã giảm giá '.$value['code'].' đã hết số lần nhập !</p>';
                }else if($value['payment_limit'] >= $money ){
                    $html.='<p> Mã giảm giá này chỉ áp dụng cho đơn hàng từ '.number_format($value['payment_limit']).' đ trở lên !</p>';
                }else{
                    $html.='<script>document.location.reload(true);</script> <p>Mã giảm giá '.$value['code'].' đã được kích hoạt !</p>';
                    $this->session->set_userdata('coupon_price',$value['discount']);
                    $this->session->set_userdata('id_coupon_price',$value['id']);
                }
            }
        }
    }

}
echo json_encode($html);
}
public function removecoupon(){
    $html='<script>document.location.reload(true);</script>';
    $this->session->unset_userdata('coupon_price');
    $this->session->unset_userdata('id_coupon_price');
    echo json_encode($html);
}
}
// email trang thankyou bị sai
