<!DOCTYPE html>
<html lang="en">
  <head>
    <base href="<?php echo base_url(); ?>"></base>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>
        <?php 
            if(isset($title))
                echo $title;
            else
                echo "Website - Điện thoại, Laptop, Link kiện chính hãng";
        ?>
    </title>
    <link rel="icon" type="image/x-icon" href="public/images/logofix.jpg">
    <link href="public/css/bootstrap.css" rel="stylesheet">
    <link href="public/css/fontawesome.css" rel="stylesheet">
    <link href="public/css/lte.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <link href="public/css/owl.carousel.min.css" rel="stylesheet">
    <link href="public/css/AdminLTE.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style-jc.css">
    <link href="public/css/menu-tab.css" rel="stylesheet">
    <link href="public/css/style.css" rel="stylesheet">
    <link href="public/css/jquery.bxslider.css" rel="stylesheet">
    <link href="public/css/flexslider.css" rel="stylesheet">

    
    
        <script src="public/js/jquery-2.2.3.min.js"></script>
    </head>
    <body>
        <div class='thetop'></div>
        <div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v16.0&appId=955538068812240&autoLogAppEvents=1" nonce="MOZVozY5"></script>
        <!-- TOPBAR -->
        <?php 
            $this->load->view('frontend/modules/topbar');
        ?>
        <!-- HEADER LOGO + SEARCH -->
         <div id="my-sticky-header">
        <?php 
            $this->load->view('frontend/modules/logo-search');
        ?>
        <!-- <?php 
            $this->load->view('frontend/modules/category');
        ?> -->
        </div>
        <section id="menu-slider">
            <?php 
                $this->load->view('frontend/modules/panel-left');
            ?>
        </section>
        <!--CONTENT-->
        <?php 
            if(isset($com,$view)){
                $this->load->view('frontend/components/'.$com.'/'.$view);
            }
            else
                $this->load->view('frontend/components/Error404/index');
        ?>
        <!--FOOTER-->
        <?php 
            $this->load->view('frontend/modules/footer');
        ?>
        <script src="public/js/bootstrap.js"></script>
        <script src="public/js/app.min.js"></script>
        <script src="public/js/owl.carousel.js"></script>
        <script src="public/js/jquery.jcarousel.js"></script>
        <script src="public/js/jcarousel.connected-carousels.js"></script>
        <script src="public/js/scroll.js"></script>
        <script src="public/js/search-quick.js"></script>
        <script src="public/js/custom-owl.js"></script>
        <script src="public/js/jquery.flexslider.js"></script>
        <?php $this->load->view('frontend/modules/compare_btn'); ?>
        <?php $this->load->view('frontend/modules/chatbot'); ?>
        <div class='scrolltop'>
        <div class='scroll icon'><i class="fa fa-4x fa-angle-up"></i></div>
        </div>

        <script type="text/javascript">
$(document).ready(function() {
    // 1. Chọn phần tử cần dính
    var stickyHeader = $('#my-sticky-header');
    
    // 2. Lấy vị trí ban đầu của nó so với đỉnh trang
    // Nếu stickyHeader không tồn tại thì không làm gì cả để tránh lỗi
    if (stickyHeader.length) {
        var stickyOffset = stickyHeader.offset().top;
        
        // 3. Lắng nghe sự kiện cuộn chuột
        $(window).scroll(function() {
            var scrollPos = $(window).scrollTop();

            // Nếu vị trí cuộn > vị trí của header => Thêm class dính
            if (scrollPos >= stickyOffset) {
                stickyHeader.addClass('sticky-active');
                
                // Mẹo quan trọng: Thêm padding-top cho body để tránh nội dung bên dưới bị giật lên
                // khi header chuyển sang position: fixed
                $('body').css('padding-top', stickyHeader.outerHeight() + 'px');
            } else {
                // Nếu cuộn ngược lên trên => Gỡ class dính
                stickyHeader.removeClass('sticky-active');
                
                // Trả lại padding cho body
                $('body').css('padding-top', '0');
            }
        });
    }
});
</script>
    </body>
</html>
