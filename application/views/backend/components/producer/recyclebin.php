<!-- application/views/backend/components/producer/recyclebin.php -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="glyphicon glyphicon-trash"></i> Thùng rác nhà cung cấp</h1>
        <div class="breadcrumb">
            <a class="btn btn-primary btn-sm" href="admin/producer" role="button">
                <span class="glyphicon glyphicon-remove do_nos"></span> Thoát
            </a>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box" id="view">
                    <div class="box-body">
                        <?php if ($this->session->flashdata('producer_success')): ?>
                            <div class="alert alert-success">
                                <?php echo $this->session->flashdata('producer_success'); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            </div>
                        <?php endif; ?>
                        <?php if ($this->session->flashdata('error')): ?>
                            <div class="alert alert-danger">
                                <?php echo $this->session->flashdata('error'); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            </div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center">ID</th>
                                        <th>Tên nhà cung cấp</th>
                                        <th>Code</th>
                                        <th>Người đăng</th>
                                        <th class="text-center">Khôi phục</th>
                                        <th class="text-center">Xóa VV</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list as $row): ?>
                                        <tr>
                                            <td class="text-center"><?php echo $row['id'] ?></td>
                                            <td><?php echo $row['name'] ?></td>
                                            <td><?php echo $row['code'] ?></td>
                                            <td><?php echo $this->Muser->user_name($row['created_by']); ?></td>
                                            <td class="text-center">
                                                <a class="btn btn-success btn-xs" href="admin/producer/restore/<?php echo $row['id'] ?>" role="button">
                                                    <span class="glyphicon glyphicon-edit"></span> Khôi phục
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($user['role'] == 1): ?>
                                                    <a class="btn btn-danger btn-xs" href="admin/producer/delete/<?php echo $row['id'] ?>" onclick="return confirm('Xác nhận xóa vĩnh viễn nhà cung cấp này?')" role="button">
                                                        <span class="glyphicon glyphicon-trash"></span> Xóa VV
                                                    </a>
                                                <?php else: ?>
                                                    <p class="fa fa-lock" style="color:red"> Không đủ quyền</p>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <ul class="pagination">
                                    <?php echo $strphantrang ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>