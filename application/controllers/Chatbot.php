<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chatbot extends CI_Controller {

    private $api_keys = [
        'AIzaSyBN-cl0sXtkjP6kTuNo6ty5dM7mVUidbMs',
        'KEY_DU_PHONG_1',
        'KEY_DU_PHONG_2',
    ];
    private $shop_info = "Địa chỉ shop: 470 Trần Đại Nghĩa, Hoà Hải, Ngũ Hành Sơn, Đà Nẵng 550000, Việt Nam.";

    public function __construct() {
        parent::__construct();
        $this->load->model('frontend/Mproduct');
        $this->load->library('session');
        set_time_limit(0);
        ini_set('max_execution_time', 0);
    }

    public function reset_chat() {
        $this->session->unset_userdata('chat_history');
        $this->session->unset_userdata('user_role'); 
        echo json_encode(['status' => 'success']);
    }

    public function ask() {
        if (!$this->input->is_ajax_request()) exit('No direct script access allowed');

        $user_message = $this->input->post('message');
        $user_role = $this->input->post('role');

        if ($user_role && !$this->session->userdata('user_role')) {
            $this->session->set_userdata('user_role', $user_role);
        }
        if (!$user_role) {
            $user_role = $this->session->userdata('user_role') ? $this->session->userdata('user_role') : 'Bạn';
        }

        if (empty($user_message)) {
            echo json_encode(['reply' => 'Bạn cần nhập nội dung câu hỏi.']);
            return;
        }

        $history = $this->session->userdata('chat_history');
        if (!$history) {
            $history = [];
        }

        $search_results = [];
        $words = explode(' ', $user_message);
        $keywords_to_search = [];
        
        foreach ($words as $word) {
            if (strlen($word) >= 3) { 
                $keywords_to_search[] = $word;
            }
        }

        foreach ($keywords_to_search as $key) {
            $items = $this->Mproduct->search_product_for_chat($key);
            if (!empty($items)) {
                $search_results = array_merge($search_results, $items);
            }
        }
       
        $search_results = array_unique($search_results, SORT_REGULAR);
        
        $is_fallback = false; 
        if (empty($search_results)) {
            $search_results = $this->Mproduct->product_selling(5); 
            $is_fallback = true;
        } else {
            $search_results = array_slice($search_results, 0, 6);
        }

        $product_context = "";
        if (!empty($search_results)) {
            $product_context = "Dữ liệu sản phẩm thực tế từ Database:\n";
            foreach ($search_results as $p) {
                $price = number_format($p['price'], 0, ',', '.') . 'đ';
                $sale_price = ($p['sale'] > 0) ? number_format($p['price_sale'], 0, ',', '.') . 'đ' : $price;
                $img = base_url('public/images/products/' . $p['avatar']);
                $link = base_url($p['alias']);
                
                $product_context .= "- ID:{$p['id']} | Tên: {$p['name']} | Giá: $sale_price | Ảnh: $img | Link: $link\n";
            }
        }

        $instruction_fallback = $is_fallback 
            ? "Hiện tại không tìm thấy sản phẩm nào khớp chính xác tên khách yêu cầu. Hãy nói khéo là 'Hiện bên em chưa thấy mẫu đó, nhưng em có mấy mẫu HOT này anh/chị xem thử nhé:' rồi liệt kê danh sách." 
            : "Trả lời dựa trên danh sách sản phẩm tìm được.";

        $system_instruction = "Bạn là trợ lý ảo shop công nghệ. " .
        "Thông tin shop: " . $this->shop_info . ". " .
        "Xưng hô: 'Em' gọi khách là '" . $user_role . "'. " .
        "Quy tắc trả lời:\n" .
        "1. " . $instruction_fallback . "\n" .
        "2. CHỈ được giới thiệu sản phẩm có trong 'Dữ liệu sản phẩm thực tế'. KHÔNG ĐƯỢC BỊA RA TÊN SẢN PHẨM KHÁC.\n" .
        "3. Nếu 'Dữ liệu sản phẩm thực tế' trống, hãy xin lỗi và hỏi khách muốn tìm dòng nào khác.\n" .
        "4. BẮT BUỘC format hiển thị sản phẩm như sau (không dùng markdown list, dùng thẻ br để xuống dòng):\n" .
        "<div style='margin-bottom:10px; border-bottom:1px solid #eee; padding-bottom:5px;'>" .
        "<img src='LINK_ANH' style='width:100%; max-width:120px; border-radius:5px;'><br>" .
        "<b><a href='LINK_SP' target='_blank'>TÊN_SP</a></b><br>" .
        "<span style='color:#d35400; font-weight:bold;'>GIÁ_BÁN</span>" .
        "</div>";

        $full_prompt = $system_instruction . "\n\n" . 
                       "Lịch sử chat:\n" . json_encode($history, JSON_UNESCAPED_UNICODE) . "\n\n" .
                       $product_context . "\n\n" .
                       "Khách hàng hỏi: " . $user_message;

        $reply = $this->call_gemini_api($full_prompt);

        $history[] = ['role' => 'user', 'content' => $user_message];
        $history[] = ['role' => 'model', 'content' => strip_tags($reply)];
        if (count($history) > 6) $history = array_slice($history, -6);
        $this->session->set_userdata('chat_history', $history);

        echo json_encode(['reply' => $reply]);
    }

    private function call_gemini_api($prompt) {
        $last_error = '';

        foreach ($this->api_keys as $index => $current_key) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $current_key;

        $data = [
            "contents" => [
                ["parts" => [["text" => $prompt]]]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "maxOutputTokens" => 1000,
            ]
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

        return "Dạ hiện tại hệ thống em đang quá tải một chút. " . ($this->session->userdata('user_role') ?? 'Anh/Chị') . " vui lòng hỏi lại sau vài phút nhé! (Lỗi kỹ thuật: " . $last_error . ")";
    }
}