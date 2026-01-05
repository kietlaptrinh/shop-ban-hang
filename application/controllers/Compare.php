<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Compare extends CI_Controller {
 
    private $api_keys = [
        'AIzaSyAMH2RZpiLX_P_bu9qqh-RSAcn3bmetrZc',
        'KEY_2_CUA_BAN', 
        'KEY_3_CUA_BAN', 
    ];

    public function __construct() {
        parent::__construct();
        $this->load->model('frontend/Mproduct'); 
        set_time_limit(0); 
        ini_set('max_execution_time', 0);
    }

    public function index() {
        $data['products'] = $this->db->select('id, name')->where('status', 1)->get('db_product')->result_array();
        $this->load->view('frontend/compare/index', $data);
    }

    public function ajax_compare() {
        $id1 = $this->input->post('id1');
        $id2 = $this->input->post('id2');

        $p1 = $this->db->where('id', $id1)->get('db_product')->row_array();
        $p2 = $this->db->where('id', $id2)->get('db_product')->row_array();

        if (!$p1 || !$p2) {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy thông tin sản phẩm!']);
            return;
        }

        $price1 = $p1['price']; 
        if ($p1['sale'] > 0) {
            $price1 = $p1['price'] * (1 - $p1['sale'] / 100);
        }

        $price2 = $p2['price']; 
        if ($p2['sale'] > 0) {
            $price2 = $p2['price'] * (1 - $p2['sale'] / 100);
        }

         $info_p1 = "Sản phẩm A: " . $p1['name'] . ". Giá bán thực tế: " . number_format($price1) . " VNĐ (Đã giảm " . $p1['sale'] . "%). Thông số: " . strip_tags($p1['sortDesc']);
        
        $info_p2 = "Sản phẩm B: " . $p2['name'] . ". Giá bán thực tế: " . number_format($price2) . " VNĐ (Đã giảm " . $p2['sale'] . "%). Thông số: " . strip_tags($p2['sortDesc']);
        $prompt = "Bạn là trợ lý tư vấn mua hàng công nghệ. Khách hàng đang phân vân giữa 2 sản phẩm sau:\n" .
                  "1. " . $info_p1 . "\n" .
                  "2. " . $info_p2 . "\n\n" .
                  "YÊU CẦU:\n" .
                  "- Tạo một bảng so sánh ngắn gọn về các điểm chính (Hiệu năng, Giá, Đặc điểm).\n" .
                  "- Đưa ra LỜI KHUYÊN: Ai nên mua máy A? Ai nên mua máy B? (Ví dụ: Mua A nếu thích chụp ảnh, Mua B nếu thích chơi game).\n" .
                  "- Giọng văn khách quan, hữu ích, ngắn gọn. Trình bày bằng HTML đẹp mắt (dùng thẻ table class='table table-bordered', thẻ p, thẻ strong).";

        $content = $this->call_gemini($prompt);

        echo json_encode(['status' => 'success', 'content' => $content]);
    }

    private function call_gemini($prompt) {
        $last_error = '';
        foreach ($this->api_keys as $index => $current_key) {
            
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $current_key;
            
            $data = [
                "contents" => [["parts" => [["text" => $prompt]]]],
                "generationConfig" => ["temperature" => 0.7, "maxOutputTokens" => 8192]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
            
            $response = curl_exec($ch);
            $curl_errno = curl_errno($ch);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($curl_errno) {
                $last_error = "Key " . ($index + 1) . " lỗi mạng: " . $curl_error;
                continue; 
            }

            $result = json_decode($response, true);

            if (isset($result['error'])) {
                $last_error = "Key " . ($index + 1) . " lỗi API: " . $result['error']['message'];
                continue; 
            }
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                return $result['candidates'][0]['content']['parts'][0]['text'];
            }
        }

        return "Hệ thống đang quá tải (Tất cả API Key đều bận). Chi tiết lỗi cuối cùng: " . $last_error;
    }
}