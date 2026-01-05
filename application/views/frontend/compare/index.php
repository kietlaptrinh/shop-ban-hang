<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>So sánh sản phẩm AI</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; }
        
        .compare-box { 
            margin-top: 50px; 
            background: #f9f9f9; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .compare-box h2 { margin-top: 0; color: #333; font-weight: 600; }
        .compare-box p { color: #666; font-size: 16px; }

        .vs-circle {
            width: 50px; height: 50px; background: #d9534f; color: #fff;
            border-radius: 50%; text-align: center; line-height: 50px;
            font-weight: bold; margin: 25px auto; font-size: 18px;
            box-shadow: 0 3px 10px rgba(217, 83, 79, 0.4);
        }

        #result-area { 
            margin-top: 30px; 
            background: #fff; 
            padding: 25px; 
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
            display: none; 
        }

        option:disabled { background-color: #f2f2f2; color: #bbb; cursor: not-allowed; font-style: italic; }

        #ai-content {
            width: 100%;
            overflow-x: auto; 
        }
        
        #ai-content table { width: 100%; max-width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        #ai-content th, #ai-content td { padding: 12px; border: 1px solid #ddd; }
        #ai-content th { background-color: #f8f8f8; }

        @media (max-width: 991px) {
            .compare-box { margin-top: 20px; padding: 20px; }
            
            .vs-circle { margin: 10px auto; width: 40px; height: 40px; line-height: 40px; font-size: 14px; }
            
            #btn-compare { width: 100%; margin-top: 10px; font-size: 16px; }
            
            .compare-box h2 { font-size: 24px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            
            <div class="compare-box text-center">
                <h2><i class="glyphicon glyphicon-scale"></i> So sánh Sản phẩm AI</h2>
                <p>Trợ lý ảo giúp bạn phân tích và chọn sản phẩm phù hợp nhất</p>
                <hr style="margin: 20px 0; border-top: 1px solid #eee;">

                <div class="row">
                    <div class="col-md-5 col-sm-12">
                        <div class="form-group">
                            <label class="hidden-md hidden-lg text-left" style="display:block">Sản phẩm 1:</label> <select id="product1" class="form-control input-lg" style="height: 50px;">
                                <option value="">-- Chọn sản phẩm A --</option>
                                <?php foreach($products as $p): ?>
                                    <option value="<?php echo $p['id'] ?>"><?php echo $p['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2 col-sm-12">
                        <div class="vs-circle">VS</div>
                    </div>

                    <div class="col-md-5 col-sm-12">
                        <div class="form-group">
                            <label class="hidden-md hidden-lg text-left" style="display:block">Sản phẩm 2:</label>
                            <select id="product2" class="form-control input-lg" style="height: 50px;">
                                <option value="">-- Chọn sản phẩm B --</option>
                                <?php foreach($products as $p): ?>
                                    <option value="<?php echo $p['id'] ?>"><?php echo $p['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <br>
                <button id="btn-compare" class="btn btn-primary btn-lg" style="box-shadow: 0 4px 6px rgba(51, 122, 183, 0.4);">
                    <i class="glyphicon glyphicon-flash"></i> PHÂN TÍCH & SO SÁNH NGAY
                </button>
            </div>

            <div id="result-area">
                <h3 class="text-center text-primary" style="margin-top:0"><i class="glyphicon glyphicon-list-alt"></i> Kết quả phân tích</h3>
                <hr>
                <div id="ai-content"></div>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#product1').change(function() {
        var id1 = $(this).val();
        $('#product2 option').prop('disabled', false);
        if(id1 != '') {
            $('#product2 option[value="' + id1 + '"]').prop('disabled', true);
            if($('#product2').val() == id1) {
                $('#product2').val('');
                alert('Sản phẩm này đã được chọn ở ô bên trái. Vui lòng chọn sản phẩm khác!');
            }
        }
    });

    $('#product2').change(function() {
        var id2 = $(this).val();
        $('#product1 option').prop('disabled', false);
        if(id2 != '') {
            $('#product1 option[value="' + id2 + '"]').prop('disabled', true);
            if($('#product1').val() == id2) {
                $('#product1').val('');
                alert('Sản phẩm này đã được chọn ở ô bên phải. Vui lòng chọn sản phẩm khác!');
            }
        }
    });

    $('#btn-compare').click(function() {
        var id1 = $('#product1').val();
        var id2 = $('#product2').val();

        if(!id1 || !id2) {
            alert("Vui lòng chọn đủ 2 sản phẩm để so sánh!");
            return;
        }
        if(id1 == id2) {
            alert("Vui lòng chọn 2 sản phẩm khác nhau!");
            return;
        }

        var $btn = $(this);
        var originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="glyphicon glyphicon-refresh fa-spin"></i> Đang phân tích dữ liệu...');
        
        $('#result-area').fadeOut(); 

        $.ajax({
            url: '<?php echo base_url("index.php/compare/ajax_compare"); ?>',
            type: 'POST',
            dataType: 'json',
            data: { id1: id1, id2: id2 },
            success: function(res) {
                if(res.status == 'success') {
                    $('#ai-content').html(res.content);
                  
                    $('#ai-content table').addClass('table table-bordered table-striped');
                    $('#result-area').fadeIn();
                    
                  
                    $('html, body').animate({
                        scrollTop: $("#result-area").offset().top - 20
                    }, 500);

                } else {
                    alert(res.message);
                }
            },
            error: function() {
                alert('Lỗi kết nối server!');
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>

</body>
</html>