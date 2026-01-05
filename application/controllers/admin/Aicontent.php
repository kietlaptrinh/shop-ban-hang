<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aicontent extends CI_Controller {
 
    private $api_keys = [
        'AIzaSyAwSAB_K50R3ZyKr8aDxlpPFaPzlUIF9Bo', 
        'KEY_DU_PHONG_1_CUA_BAN',                 
        'KEY_DU_PHONG_2_CUA_BAN',                
    ];

    public function __construct() {
        parent::__construct();
        set_time_limit(0); 
        ini_set('max_execution_time', 0);
    }

    public function generate_description() {
        if (!$this->input->is_ajax_request()) exit('No direct script access allowed');

        $product_name = $this->input->post('name');
        $product_specs = $this->input->post('specs'); 

        if (empty($product_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập tên sản phẩm trước!']);
            return;
        }

        // 1. Tạo Prompt chuyên gia SEO
        $prompt = "Bạn là một chuyên gia Copywriter và SEO Marketing chuyên nghiệp cho các sản phẩm công nghệ. \n" .
                  "Nhiệm vụ: Viết một bài mô tả sản phẩm chi tiết, hấp dẫn để bán hàng trên website cho sản phẩm: " . $product_name . ".\n" .
                  "Thông số kỹ thuật/Đặc điểm: " . $product_specs . ".\n\n" .
                  "Yêu cầu định dạng bài viết:\n" .
                  "1. TUYỆT ĐỐI KHÔNG có lời chào hỏi, không có câu dẫn nhập (như 'Chào bạn', 'Dưới đây là', 'Tuyệt vời'...). Bắt đầu ngay vào nội dung.\n" .
                  "2. Viết bằng tiếng Việt, giọng văn chuyên nghiệp, thôi thúc mua hàng.\n" .
                  "3. Bắt đầu ngay lập tức bằng việc sử dụng các thẻ HTML để trình bày đẹp mắt:\n" .
                  "   - Dùng thẻ <h3> cho các tiêu đề mục (Ví dụ: Thiết kế sang trọng, Hiệu năng mạnh mẽ...).\n" .
                  "   - Dùng thẻ <p> cho các đoạn văn.\n" .
                  "   - Dùng thẻ <ul> và <li> cho các danh sách tính năng nổi bật.\n" .
                  "   - Dùng thẻ <strong> hoặc <b> để bôi đậm các từ khóa quan trọng (tên sản phẩm, thông số khủng).\n" .
                  "4. Độ dài khoảng 800 - 1000 từ.\n" .
                  "5. Nội dung cần chuẩn SEO: lặp lại tên sản phẩm khéo léo, tập trung vào lợi ích người dùng.\n" .
                  "6. Không cần viết phần kết bài kiểu 'Hãy mua ngay', chỉ cần tập trung phân tích sản phẩm.\n" .
                  "7. Viết trọn vẹn, không được dừng giữa chừng.";

        // 2. Gọi hàm xử lý đa key
        $content = $this->call_gemini_multikey($prompt);

        echo json_encode(['status' => 'success', 'content' => $content]);
    }

    private function call_gemini_multikey($prompt) {
        $last_error = '';

        foreach ($this->api_keys as $index => $current_key) {
            
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $current_key;
            
            $data = [
                "contents" => [["parts" => [["text" => $prompt]]]],
                "generationConfig" => [
                    "temperature" => 0.7, 
                    "maxOutputTokens" => 8192 
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            curl_setopt($ch, CURLOPT_TIMEOUT, 40); 

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
                $text = $result['candidates'][0]['content']['parts'][0]['text'];
                
                $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
                
                $garbage_phrases = ['/^Tuyệt vời(.*?)[\r\n]+/', '/^Dưới đây là(.*?)[\r\n]+/'];
                foreach ($garbage_phrases as $pattern) {
                    $text = preg_replace($pattern, '', $text);
                }

                return trim($text);
            }
        }
        return "Hệ thống đang bận (Tất cả API Key đều lỗi). Lỗi cuối cùng: " . $last_error;
    }
}