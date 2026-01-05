<div class="row content-cart">
	<div class="container">
		<?php if($this->session->userdata('cart')):
			$cart = $this->session->userdata('cart');
			?>
			<form action="" method="post" id="cartformpage">
				<div class="cart-index">
				<h2>Chi tiết giỏ hàng</h2>
					<div class="tbody text-center">
						<div class="col-xs-12 col-12 col-sm-12 col-md-8 col-lg-8">

							<table class="table table-list-product">

								<thead>
									<tr style="background: #f3f3f3;">
										<th>Hình ảnh</th>
										<th>Tên sản phẩm</th>
										<th class="text-center">Đơn giá</th>
										<th class="text-center">Số lượng</th>
										<th class="text-center">Thành tiền</th>
										<th class="text-center">Xóa</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($cart as $key => $value) :
										$row = $this->Mproduct->product_detail_id($key);
										?>
										<tr>
											<td class="img-product-cart">
												<a href="<?php echo $row['alias'] ?>">
													<img src="public/images/products/<?php echo $row['avatar'] ?>" alt="<?php echo $row['name'] ?>">
												</a>
											</td>
											<td>
												<a href="<?php echo $row['alias'] ?>" class="pull-left"><?php echo $row['name'] ?></a>
											</td>
											<td>
												<span class="amount">
													<?php 
													if($row['price_sale'] > 0){
														echo (number_format($row['price_sale'])).' VNĐ';
													}else{
														echo (number_format($row['price'])).' VNĐ';
													}
													?>
												</span>
											</td>
											<td>
												<div class="quantity clearfix" style='width:160px;'>
												<?php
if($row['number'] <= $value) {
    echo '<input name="quantity" id="' . $row['id'] . '" class="form-control" type="number" value="' . $value . '" min="1" max="1000" onchange="onChangeSL(' . $row['id'] . ')">';
    echo '<p style="min-with: 150px; color: red">Hàng trong kho không đủ</p>';
} else {
    echo '<input name="quantity" id="' . $row['id'] . '" class="form-control" type="number" value="' . $value . '" min="1" max="1000" onchange="onChangeSL(' . $row['id'] . ')">';
}


?>

												</div>
											</td>
											<td>
												<span class="amount">
													<?php 
													if($row['price_sale'] > 0){
														echo (number_format($row['price_sale']*$value)).' VNĐ';
													}else{
														echo (number_format($row['price'] * $value)).' VNĐ';
													}
													?>
													
												</span>
											</td>
											<td>
												<a class="remove" title="Xóa" onclick="onRemoveProduct(<?php echo $row['id']; ?>)"><i class="fas fa-trash-alt"></i></a>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<button class="btn" onclick="window.location.href='san-pham'"> <a href="<?php echo base_url() ?>san-pham">Tiếp tục mua hàng</a></button>
						</div>
						<?php $total = 0; ?>
						<?php foreach ($cart as $key => $value) : 
							$row = $this->Mproduct->product_detail_id($key);?>
							<?php
							if($row['price_sale'] > 0)
								$sum = $row['price_sale'] * $value;
							else
								$sum = $row['price'] * $value;
							$total += $sum;
							?>	
						<?php endforeach; ?>
						<div class="col-xs-12 col-sm-12 col-md-4">
							<div class="clearfix btn-submit" style="padding-left: 10px;margin-top: 20px;">
								<table class="table total-price" style="border: 1px solid #ececec;">
									<tbody>
										<tr style="background: #f4f4f4;">
											<td>Tổng tiền</td>
											<td><strong><?php echo (number_format($total)).' VNĐ'; ?></strong></td>
										</tr>
										<tr>
											<td colspan="2"><h5>Mua hàng trực tiếp tại cửa hàng giảm giá 5%</h5></td>
										</tr>
										<tr>
											<td colspan="2"><h5>Nếu đặt online Bạn hãy đồng ý với điều khoản sử dụng & hướng dẫn hoàn trả.</h5></td>
										</tr>
										 
										<tr>
											<?php
$canOrder = true; // Biến kiểm tra xem có thể đặt hàng không
foreach ($cart as $key => $value) :
    $row = $this->Mproduct->product_detail_id($key);
    if ($row['number'] <= $value || $value <= 0) { // Kiểm tra số lượng tồn kho và số lượng trong giỏ
        $canOrder = false;
        break;
    }
endforeach;
?>
<tr>
    <?php if (!$canOrder) : ?>
        <td colspan="2">
            <button disabled style="background-color:#c2d2cb" type="button" class="btn-next-checkout">Đặt hàng</button>
        </td>
    <?php else : ?>
        <td colspan="2">
            <button type="button" onclick="window.location.href='<?php echo base_url('gio-hang/info-order'); ?>'" class="btn-next-checkout">Đặt hàng</button>
        </td>
    <?php endif; ?>
</tr>
										
										</tr>
									</tbody>
								</table>

							</div>
						</div>
					</div>

				</div>

			</form>
			<?php else: ?>
				<div class="cart-info">
					Chưa có sản phẩm nào trong giỏ hàng !
					<br>	
					<button class="btn" onclick="window.location.href='san-pham'"> Tiếp tục mua hàng</button>
				</div>

			<?php endif;?>
		</div>
	</div>
	<script>
		function onChangeSL(id){
    var value = $('#'+id).val(); // Lấy giá trị của input
    var price = <?php echo ($row['price_sale'] > 0) ? $row['price_sale'] : $row['price']; ?>; // Lấy giá của sản phẩm
    var amount = value * price; // Tính amount
    $('#amount_'+id).text(numberWithCommas(amount) + ' VNĐ'); // Hiển thị amount
}

		function onChangeSL(id) {
    var sl = document.getElementById(id).value;
    if (sl <= 0) {
        alert('Số lượng phải lớn hơn 0');
        document.getElementById(id).value = 1; // Đặt lại số lượng tối thiểu
        return;
    }
    var strurl = "<?php echo base_url(); ?>" + '/sanpham/update';
    jQuery.ajax({
        url: strurl,
        type: 'POST',
        dataType: 'json',
        data: { id: id, sl: sl },
        success: function(data) {
            if (data.status === 'success') {
                document.location.reload(true);
            } else {
                alert('Cập nhật số lượng thất bại: ' + (data.message || 'Lỗi không xác định'));
            }
        },
        error: function() {
            alert('Lỗi kết nối đến server khi cập nhật số lượng');
        }
    });
}
		function onRemoveProduct(id) {
    var strurl = "<?php echo base_url(); ?>" + '/sanpham/remove';
    jQuery.ajax({
        url: strurl,
        type: 'POST',
        dataType: 'json',
        data: { id: id },
        success: function(data) {
            if (data.status === 'success') {
                document.location.reload(true);
                alert('Xóa sản phẩm thành công !!');
            } else {
                alert('Xóa sản phẩm thất bại: ' + (data.message || 'Lỗi không xác định'));
            }
        },
        error: function() {
            alert('Lỗi kết nối đến server khi xóa sản phẩm');
        }
    });
}
	</script>