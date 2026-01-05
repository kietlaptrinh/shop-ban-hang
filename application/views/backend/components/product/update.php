<?php if (!isset($row) || $row === null): ?>
    <div class="content-wrapper">
        <section class="content-header">
            <h1><i class="glyphicon glyphicon-cd"></i> Cập nhật sản phẩm</h1>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box" id="view">
                        <div class="box-body">
                            <div class="alert alert-danger">Sản phẩm không tồn tại hoặc đã bị xóa vào thùng rác!</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
<?php else: ?>
<?php echo form_open_multipart('admin/product/update/'.$row['id']); ?>
<?php  
$list=$this->Mcategory->category_list();
$option_parentid="";
foreach ($list as $r) {
	if($r['id']==$row['catid']){
		$option_parentid.="<option selected value='".$r['id']."'>".$r['name']."</option>";
	}else{
		$option_parentid.="<option value='".$r['id']."'>".$r['name']."</option>";
	}
}
$listProducer=$this->Mproducer->producer_list();
$option="";
foreach ($listProducer as $r) {
	if($r['id']==$row['producer']){
		$option.="<option selected value='".$r['id']."'>".$r['name']."</option>";
	}else{
		$option.="<option value='".$r['id']."'>".$r['name']."</option>";
	}
}
?>
<div class="content-wrapper">
	<form action="<?php echo base_url() ?>admin/product/update.html" enctype="multipart/form-data" method="POST" accept-charset="utf-8">
		<section class="content-header">
			<h1><i class="glyphicon glyphicon-cd"></i> Cập nhật sản phẩm</h1>
			<div class="breadcrumb">
				<button type = "submit" class="btn btn-primary btn-sm">
					<span class="glyphicon glyphicon-floppy-save"></span>
					Lưu[Cập nhật]
				</button>
				<a class="btn btn-primary btn-sm" href="admin/product" role="button">
					<span class="glyphicon glyphicon-remove do_nos"></span> Thoát
				</a>
			</div>
		</section>
		<!-- Main content -->
		<section class="content">
			<div class="row">
				<div class="col-md-12">
					<div class="box" id="view">
						<div class="box-body">
							<div class="col-md-9">
								<div class="form-group">
									<label>Tên sản phẩm <span class = "maudo">(*)</span></label>
									<input type="text" class="form-control" name="name" style="width:100%" placeholder="Tên sản phẩm" value="<?php echo $row['name'] ?>">
									<div class="error" id="password_error"><?php echo form_error('name')?></div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<div class="col-md-6" style="padding-left: 0px;">
											<div class="form-group">
												<label>Loại sản phẩm<span class = "maudo">(*)</span></label>
												<select name="catid" class="form-control">
													<option value = "">[--Chọn loại sản phẩm--]</option>
													<option value = "0">No Parent</option>
													<?php  
													echo $option_parentid;
													?>
												</select>
												<div class="error" id="password_error"><?php echo form_error('catid')?></div>
											</div>
										</div>
										<div class="col-md-6" style="padding-right: 0px;">
											<div class="form-group">
												<label>Nhà cung cấp<span class = "maudo">(*)</span></label>
												<select name="producer" class="form-control">
													<option value = "">[--Chọn nhà cung cấp--]</option>
													<?php echo $option;?>
												</select>
												<div class="error" id="password_error"><?php echo form_error('catid')?></div>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label>Mô tả ngắn (Thông số/Gợi ý cho AI)</label>
									<textarea name="sortDesc" class="form-control"><?php echo $row['sortDesc'] ?></textarea>
								</div>
								<div class="form-group">
									<label>Chi tiết sản phẩm</label>
									
									<div style="margin-bottom: 5px;">
										<button type="button" class="btn btn-success btn-xs" id="btn-ai-generate">
											<i class="glyphicon glyphicon-flash"></i> Viết nội dung chuẩn SEO bằng AI
										</button>
										<span style="font-style: italic; color: #d9534f; font-size: 13px; margin-left: 10px;">
											<i class="glyphicon glyphicon-time"></i> Lưu ý: AI cần khoảng 30 giây đến 2 phút để viết bài.
										</span>
									</div>
									
									<textarea name="detail" id="detail" class="form-control"><?php echo $row['detail'] ?></textarea>
									<script>CKEDITOR.replace('detail');</script>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Giá gốc</label>
									<input name="price_root" class="form-control" type="number" value="<?php echo $row['price'] ?>" min="0" max="1000000000">
								</div>
								<div class="form-group">
									<label>Khuyến mãi (%)</label>
									<input name="sale_of" class="form-control" type="number" value="<?php echo $row['sale'] ?>"min="0" max="100">
								</div>

								<?php $giaban = $row['price'] *(1-$row['sale'] /100);?>
								<div class="form-group">
									<label>Giá bán</label>
									<input name="price_buy" class="form-control"  type="number" value=<?php echo $giaban ?> min="0" max="1000000000" readonly>
									<div class="error" id="password_error"><?php echo form_error('price_buy')?></div>
								</div>
								<div class="form-group">
									<label>Số lượng tồn kho</label>
									<input name="number" class="form-control" type="number" value="<?php echo $row['number'] - $row['number_buy'] ?>" min="1" max="1000" disabled>
								</div>
								<div class="form-group">
									<label>Số lượng đã bán</label>
									<input name="number" class="form-control" type="number" value="<?php echo $row['number_buy'] ?>" min="1"  max="1000" disabled>
								</div>
								<div class="form-group">
									<label>Trạng thái</label>
									<select name="status" class="form-control">
										<option value="1" <?php if($row['status'] == 1) {echo 'selected';}?> >Đang kinh doanh</option>
										<option value="0" <?php if($row['status'] == 0) {echo 'selected';}?>>Ngừn kinh doanh</option>
									</select>
								</div>
							</div>
						</div>
					</div><!-- /.box -->
				</div>
				<!-- /.col -->
			</div>
			<!-- /.row -->
		</section>
	</form>
	<!-- /.content -->
</div><!-- /.content-wrapper -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

const priceRootInput = document.querySelector('input[name="price_root"]');
const saleOfInput = document.querySelector('input[name="sale_of"]');
const priceBuyInput = document.querySelector('input[name="price_buy"]');

function calculatePriceBuy() {
  const priceRoot = parseFloat(priceRootInput.value);
  const saleOf = parseFloat(saleOfInput.value);
  const priceBuy = (priceRoot * (100 - saleOf)) / 100;
  priceBuyInput.value = priceBuy.toFixed(2);
}

priceRootInput.addEventListener('input', calculatePriceBuy);
saleOfInput.addEventListener('input', calculatePriceBuy);

$(document).ready(function() {
        $('#btn-ai-generate').click(function(e) {
            e.preventDefault();

            // Lấy tên và mô tả ngắn hiện tại
            var name = $('input[name="name"]').val();
            var specs = $('textarea[name="sortDesc"]').val();

            if (!name) {
                alert('Cần có tên sản phẩm!');
                return;
            }

            if(!confirm('Nội dung cũ trong khung "Chi tiết" sẽ bị thay thế bởi nội dung mới từ AI. Bạn có chắc chắn không?')) {
                return;
            }

            var $btn = $(this);
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="glyphicon glyphicon-refresh"></i> Đang viết lại...');

            $.ajax({
                url: '<?php echo base_url("index.php/admin/aicontent/generate_description"); ?>',
                type: 'POST',
                dataType: 'json',
                data: { name: name, specs: specs },
                success: function(response) {
                    if (response.status === 'success') {
                        CKEDITOR.instances['detail'].setData(response.content);
                    } else {
                        alert('Lỗi: ' + response.message);
                    }
                },
                error: function() {
                    alert('Lỗi kết nối API');
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
</script>
<?php endif; ?>