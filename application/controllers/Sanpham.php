<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sanpham extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('frontend/Mproduct');
        $this->load->model('frontend/Mcategory');
        $this->data['com']='sanpham';
        $this->load->library('session');
        $this->load->library('phantrang');
    }
    
    public function index(){
        if(isset($_POST['sapxep'])){
            $dksx=$_POST['sapxep'];
            $char = explode('-', $dksx);
            $f=$char[0];
            $od=$char[1];
            $data = array('0' => $f, '1' =>$od);
            $this->session->set_userdata('sortby', $data);
        }else{
            if($this->session->userdata('sortby')){
                $data = $this->session->userdata('sortby');
                $f=$data[0];
                $od=$data[1];
            }else{
                $f='created';
                $od='desc';
            }
        }
        $this->load->library('phantrang');
        $limit=12;
        $current=$this->phantrang->PageCurrent();
        $first=$this->phantrang->PageFirst($limit, $current);
        $total=$this->Mproduct->product_sanpham_count();
        $this->data['strphantrang']=$this->phantrang->PagePer($total, $current, $limit, $url='san-pham');
        $this->data['list']=$this->Mproduct->product_sanpham($limit,$first,$f,$od);
        $this->data['title']='Website - Tất cả sản phẩm';
        $this->data['view']='index';
        if(isset($_POST['sapxep'])){
            $result=$this->load->view('frontend/components/sanpham/index_order',$this->data,true);
            echo json_encode($result);
        }else{
            $this->load->view('frontend/layout',$this->data);
        }
    }

    public function category(){
        if(isset($_POST['sapxep-category'])){
            $dksx=$_POST['sapxep-category'];
            $char = explode('-', $dksx);
            $f=$char[0];
            $od=$char[1];
            $data = array('0' => $f, '1' =>$od);
            $this->session->set_userdata('sortby-category', $data);
        }else{
            if($this->session->userdata('sortby-category')){
                $data = $this->session->userdata('sortby-category');
                $f=$data[0];
                $od=$data[1];
            }else{
                $f='created';
                $od='desc';
            }
        }
        $aurl= explode('/',uri_string());
        $link=$aurl[1];
        $catid=$this->Mcategory->category_id($link);
        $listcat=$this->Mcategory->category_listcat($catid);
        $this->data['categoryname']=$this->Mcategory->category_name($catid);
        
        $this->load->library('phantrang');
        $limit=12;
        $current=$this->phantrang->PageCurrent();
        $first=$this->phantrang->PageFirst($limit, $current);
        $total=$this->Mproduct->product_chude_count($listcat);
        $this->data['strphantrang']=$this->phantrang->PagePer($total, $current, $limit, $url='san-pham/'.$link);
        $this->data['list']=$this->Mproduct->product_list_cat_limit($listcat, $limit,$first,$f,$od);
        $this->data['title']='Website - Sản phẩm theo từng danh mục';  
        $this->data['view']='category';
        if(isset($_POST['sapxep-category'])){

            // $result=$this->load->view('frontend/components/sanpham/index_order2',$this->data,true);
            // echo json_encode($result);
            $html='<script>document.location.reload(true);</script>';
            echo json_encode($html);
            
        }else{
            $this->load->view('frontend/layout',$this->data);
        }
    }
   public function detail($link) {   
    log_message('debug', 'Product alias: ' . $link);
    $row = $this->Mproduct->product_detail($link);
    if ($row === null) {
        log_message('error', 'Product not found for alias: ' . $link);
        show_404();
    }
    $this->data['row'] = $row;
    $this->data['title'] = 'Website - ' . $row['name'];
    $this->data['view'] = 'detail';
    $this->load->view('frontend/layout', $this->data);
}
    public function addcart(){
        $this->load->library('session');
        $id=$_POST['id'];
        if($this->session->userdata('cart')){
            $cart=$this->session->userdata('cart');
            if(array_key_exists($id, $cart)){
                $cart[$id]++;
            }else{
                $cart[$id] = 1;
            }
        }else{
            $cart[$id]=1;
        }
        $this->session->set_userdata('cart',$cart);
        echo json_encode( $cart );
    }

    public function update()
{
    $id = $this->input->post('id');
    $sl = $this->input->post('sl');
    
    log_message('debug', 'Update cart: id=' . $id . ', quantity=' . $sl . ', Cart before: ' . json_encode($this->session->userdata('cart')));
    
    if ($sl <= 0) {
        $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Số lượng phải lớn hơn 0']));
        return;
    }
    
    $product = $this->Mproduct->product_detail_id($id);
    if ($product && $product['number'] >= $sl) {
        $cart = $this->session->userdata('cart');
        $cart[$id] = $sl;
        $this->session->set_userdata('cart', $cart);
        $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'cart' => $cart]));
    } else {
        $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Số lượng tồn kho không đủ']));
    }
    
    log_message('debug', 'Cart after update: ' . json_encode($this->session->userdata('cart')));
}
    public function remove()
{
    $this->load->library('session');
    $id = $this->input->post('id');
    
    // Log dữ liệu đầu vào
    log_message('debug', 'Remove product: id=' . $id . ', Cart before: ' . json_encode($this->session->userdata('cart')));
    
    if (!$id) {
        $response = ['status' => 'error', 'message' => 'ID sản phẩm không hợp lệ'];
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
        return;
    }

    $cart = $this->session->userdata('cart');
    if ($cart && isset($cart[$id])) {
        unset($cart[$id]);
        $this->session->set_userdata('cart', $cart);
        // Xóa coupon_price nếu cần
        $this->session->unset_userdata('coupon_price');
        $response = ['status' => 'success', 'message' => 'Xóa sản phẩm thành công', 'cart' => $cart];
    } else {
        $response = ['status' => 'error', 'message' => 'Sản phẩm không tồn tại trong giỏ hàng'];
    }

    // Log dữ liệu sau khi xóa
    log_message('debug', 'Cart after remove: ' . json_encode($this->session->userdata('cart')));
    $this->output->set_content_type('application/json')->set_output(json_encode($response));
}
}
