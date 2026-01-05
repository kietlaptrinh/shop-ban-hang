<style>
    #gemini-chat-btn {
        position: fixed; bottom: 120px; right: 20px; width: 60px; height: 60px;
        background: linear-gradient(135deg, #ffa500, #ff8c00);
        border-radius: 50%; box-shadow: 0 4px 15px rgba(255, 165, 0, 0.4);
        cursor: pointer; z-index: 99999; display: flex; justify-content: center; align-items: center;
        animation: pulse 2s infinite;
    }
    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(255, 165, 0, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(255, 165, 0, 0); } 100% { box-shadow: 0 0 0 0 rgba(255, 165, 0, 0); } }

    #gemini-chat-window {
        display: none; position: fixed; bottom: 90px; right: 20px; width: 360px; height: 500px;
        background: #fff; border-radius: 12px; box-shadow: 0 5px 30px rgba(0,0,0,0.15); z-index: 99999;
        flex-direction: column; overflow: hidden; border: 1px solid #ffa500; font-family: 'Roboto', sans-serif;
    }

    .chat-header {
        background: #ffa500; color: white; padding: 15px; font-weight: bold;
        display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;
    }
    .chat-close { cursor: pointer; font-size: 24px; }

    .chat-body { flex: 1; padding: 15px; overflow-y: auto; background: #fdfdfd; position: relative; }
    
    .message { margin-bottom: 15px; display: flex; flex-direction: column; }
    .message.bot { align-items: flex-start; }
    .message.user { align-items: flex-end; }
    .msg-content { padding: 10px 14px; border-radius: 15px; max-width: 85%; font-size: 14px; line-height: 1.5; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
    .message.bot .msg-content { background: #f1f1f1; color: #333; border-bottom-left-radius: 2px; }
    .message.user .msg-content { background: #ffa500; color: white; border-bottom-right-radius: 2px; }
    .msg-content img { border-radius: 8px; margin-top: 8px; border: 1px solid #ddd; max-width: 100%; display: block; }
    .msg-content a { color: #d35400; font-weight: bold; text-decoration: underline; display: block; margin-top: 5px; }
    .message.user .msg-content a { color: #fff; }

    #welcome-screen {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: white; z-index: 10; padding: 20px;
        display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;
    }
    #welcome-screen h3 { color: #ffa500; margin-bottom: 10px; font-size: 18px; text-transform: uppercase; }
    #welcome-screen p { color: #666; margin-bottom: 25px; font-size: 14px; }
    .role-options { display: flex; gap: 10px; justify-content: center; width: 100%; }
    .role-btn {
        padding: 10px 20px; border: 1px solid #ffa500; background: white; color: #ffa500;
        border-radius: 20px; cursor: pointer; transition: 0.3s; font-weight: bold;
    }
    .role-btn:hover { background: #ffa500; color: white; }

    .suggestion-chips {
        display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 15px; margin-top: 10px;
    }
    .chip {
        background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2;
        padding: 6px 12px; border-radius: 15px; font-size: 12px; cursor: pointer; transition: 0.2s;
    }
    .chip:hover { background: #ffe0b2; }

    .chat-footer { padding: 12px; background: #fff; border-top: 1px solid #eee; display: flex; align-items: center; flex-shrink: 0; }
    #chat-input { flex: 1; padding: 10px 15px; border: 1px solid #ddd; border-radius: 25px; outline: none; }
    #chat-input:focus { border-color: #ffa500; }
    #chat-send { background: #ffa500; color: white; border: none; width: 40px; height: 40px; margin-left: 10px; border-radius: 50%; cursor: pointer; display: flex; justify-content: center; align-items: center; }
    .typing-indicator { font-style: italic; color: #888; font-size: 12px; display: none; margin-bottom: 10px; margin-left: 5px; }
</style>

<div id="gemini-chat-btn">
    <i class="fas fa-comment-dots" style="color: white; font-size: 28px;"></i>
</div>

<div id="gemini-chat-window">
    <div class="chat-header">
        <span><i class="fas fa-robot"></i> Trợ lý ảo AI</span>
        <span class="chat-close" title="Đóng">&times;</span>
    </div>

    <div id="welcome-screen">
        <i class="fas fa-smile-beam" style="font-size: 50px; color: #ffa500; margin-bottom: 20px;"></i>
        <h3>Chào bạn!</h3>
        <p>Để tiện xưng hô và hỗ trợ tốt nhất,<br>bạn muốn mình gọi bạn là gì ạ?</p>
        <div class="role-options">
            <button class="role-btn" onclick="selectRole('Anh')">Anh</button>
            <button class="role-btn" onclick="selectRole('Chị')">Chị</button>
            <button class="role-btn" onclick="selectRole('Bạn')">Bạn</button>
        </div>
    </div>
    
    <div class="chat-body" id="chat-box" style="display: none;">
        </div>

    <div class="chat-footer">
        <input type="text" id="chat-input" placeholder="Nhập câu hỏi..." autocomplete="off">
        <button id="chat-send"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<script>
var currentUserRole = ''; 
const sampleQuestions = [
    "Điện thoại iPhone nào chụp ảnh đẹp?",
    "Có laptop nào dưới 15 triệu không?",
    "Shop ở đâu vậy ạ?",
    "Samsung S24 Ultra giá bao nhiêu?",
    "Tư vấn cho mình macbook làm văn phòng",
    "Shop có bán trả góp không?",
    "Tai nghe bluetooth nào nghe hay?",
    "Pin dự phòng loại nào tốt?"
];

function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}

function selectRole(role) {
    currentUserRole = role;
    $('#welcome-screen').fadeOut(300, function() {
        $('#chat-box').fadeIn();
        initChatInterface(role);
    });
}

function initChatInterface(role) {
    var greeting = `Dạ chào ${role}! Em có thể giúp gì cho ${role} hôm nay ạ?`;
    
    var shuffledQ = shuffleArray([...sampleQuestions]).slice(0, 3);
    var chipsHtml = '<div class="suggestion-chips">';
    shuffledQ.forEach(q => {
        chipsHtml += `<div class="chip" onclick="sendChip('${q}')">${q}</div>`;
    });
    chipsHtml += '</div>';

    if ($('#chat-box').children().length === 0) {
        var html = `
            <div class="message bot">
                <div class="msg-content">
                    ${greeting}
                    <br>🏠 Địa chỉ shop: <b>470 Trần Đại Nghĩa, Đà Nẵng</b>
                </div>
            </div>
            ${chipsHtml}
        `;
        $('#chat-box').append(html);
    }
}

function sendChip(text) {
    $('#chat-input').val(text);
    sendMessage();
}

$(document).ready(function() {
    $('#gemini-chat-btn, .chat-close').click(function() {
        var win = $('#gemini-chat-window');
        if (win.css('display') === 'none') {
            win.css('display', 'flex').hide().fadeIn();
            if($('#welcome-screen').is(':hidden')) {
                $('#chat-input').focus();
            }
        } else {
            win.fadeOut();
        }
    });

    window.sendMessage = function() {
        var message = $('#chat-input').val().trim();
        if(message == '') return;

        $('.suggestion-chips').remove();

        $('#chat-box').append('<div class="message user"><div class="msg-content">' + $('<div>').text(message).html() + '</div></div>');
        $('#chat-input').val('');
        scrollToBottom();

        var typingDiv = $('<div class="typing-indicator" id="typing"><i class="fas fa-circle-notch fa-spin"></i> Em đang trả lời ' + currentUserRole + '...</div>');
        $('#chat-box').append(typingDiv);
        typingDiv.show();
        scrollToBottom();

        $.ajax({
            url: '<?php echo base_url("chatbot/ask"); ?>', 
            type: 'POST',
            dataType: 'json',
            data: { 
                message: message,
                role: currentUserRole 
            },
            success: function(response) {
                $('#typing').remove();
                $('#chat-box').append('<div class="message bot"><div class="msg-content">' + response.reply + '</div></div>');
                scrollToBottom();
            },
            error: function() {
                $('#typing').remove();
                $('#chat-box').append('<div class="message bot"><div class="msg-content">⚠️ Mất kết nối. Vui lòng thử lại.</div></div>');
                scrollToBottom();
            }
        });
    }

    $('#chat-send').click(function() { sendMessage(); });
    $('#chat-input').keypress(function(e) { if(e.which == 13) sendMessage(); });

    function scrollToBottom() {
        var chatBox = document.getElementById("chat-box");
        chatBox.scrollTop = chatBox.scrollHeight;
    }
});
</script>