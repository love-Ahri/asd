<?php
if ($chucVu === 'Admin') {
    //Thêm nhân viên mới
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['themnv'])) {
        $hoTen = trim($_POST['hoten'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'Sales';
        
        if (empty($hoTen) || empty($email)) {
            $error_msg = 'Vui lòng điền đầy đủ họ tên và email.';
        } else { 
            $stmt = $db->prepare("select count(*) from nhanvien where email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                $error_msg = 'Email đã tồn tại trong hệ thống.';
            } else {
                try {
                    $db->beginTransaction();
                $newMaNV = sinhMaTuDong($db, 'nhanvien', 'manv', 'USR');
                    $tempPass = 123456; 
                    $tempHash = password_hash($tempPass, PASSWORD_DEFAULT);
                    $today = date('Y-m-d');
                    
                    $stmt = $db->prepare("insert into nhanvien (manv, hoten, email, matkhauhash, chucvu, trangthai, ngaytao) 
                                          values (:manv, :hoten, :email, :pass, :role, 'Inactive', :today)");
                    $stmt->execute([
                        ':manv' => $newMaNV,
                        ':hoten' => $hoTen,
                        ':email' => $email,
                        ':pass' => $tempHash,
                        ':role' => $role,
                        ':today' => $today
                    ]);
                    
                    $qXemSP = 1;
                    $qThemSP = ($role === 'Warehouse') ? 1 : 0;
                    $qThemDM = ($role === 'Warehouse') ? 1 : 0;
                    $qSuaSP = ($role === 'Warehouse') ? 1 : 0;
                    $qXoaSP = ($role === 'Warehouse') ? 1 : 0;
                    $qXoaDM = ($role === 'Warehouse') ? 1 : 0;
                    $qXemBC = 1;
                    $qThemBC = 1;
                    $qSuaBC = 1;
                    $qXoaBC = 1;
                    
                    $stmtPQ = $db->prepare("insert into phanquyen (manv, quyenxemsp, quyenthemsp, quyenthemdm, quyensuasp, quyenxoasp, quyenxoadm, quyenxembc, quyenthembc, quyensuabc, quyenxoabc) 
                                            values (:manv, :quyenxemsp, :quyenthemsp, :quyenthemdm, :quyensuasp, :quyenxoasp, :quyenxoadm, :quyenxembc, :quyenthembc, :quyensuabc, :quyenxoabc)");
                    $stmtPQ->execute([
                        ':manv' => $newMaNV,
                        ':quyenxemsp' => $qXemSP,
                        ':quyenthemsp' => $qThemSP,
                        ':quyenthemdm' => $qThemDM,
                        ':quyensuasp' => $qSuaSP,
                        ':quyenxoasp' => $qXoaSP,
                        ':quyenxoadm' => $qXoaDM,
                        ':quyenxembc' => $qXemBC,
                        ':quyenthembc' => $qThemBC,
                        ':quyensuabc' => $qSuaBC,
                        ':quyenxoabc' => $qXoaBC
                    ]);
                    
                    $db->commit();
                    ghiLichSu($db, $maNV, "Tạo nhân viên mới: $hoTen ($newMaNV) - Chức vụ: $role - Trạng thái: Inactive");
                    header('Location: index.php?tab=users');
                    exit;
                } catch (Exception $e) {
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    $error_msg = 'Lỗi hệ thống: ' . $e->getMessage();
                }
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['capnhatnv'])) {
        $maNVCapNhat = $_POST['manv'] ?? '';
        $hoTen = trim($_POST['hoten'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'Sales';
        $trangThai = $_POST['trangthai'] ?? 'Inactive';

        $qXemSP = isset($_POST['quyenxemsp']) ? 1 : 0;
        $qThemSP = isset($_POST['quyenthemsp']) ? 1 : 0;
        $qThemDM = isset($_POST['quyenthemdm']) ? 1 : 0;
        $qSuaSP = isset($_POST['quyensuasp']) ? 1 : 0;
        $qXoaSP = isset($_POST['quyenxoasp']) ? 1 : 0;
        $qXoaDM = isset($_POST['quyenxoadm']) ? 1 : 0;
        $qXemBC = isset($_POST['quyenxembc']) ? 1 : 0;
        $qThemBC = isset($_POST['quyenthembc']) ? 1 : 0;
        $qSuaBC = isset($_POST['quyensuabc']) ? 1 : 0;
        $qXoaBC = isset($_POST['quyenxoabc']) ? 1 : 0;
        if ($role === 'Admin') {
        $error_msg = 'Không thể thay đổi quyền của Admin.';
        }
        else if (empty($hoTen) || empty($email)) {
            $error_msg = 'Vui lòng điền đầy đủ họ tên và email.';
        } else {
            $stmt = $db->prepare("select count(*) from nhanvien where email = :email and manv != :manv");
            $stmt->execute([':email' => $email, ':manv' => $maNVCapNhat]);
            if ($stmt->fetchColumn() > 0) {
                $error_msg = 'Email đã tồn tại trong hệ thống.';
            } else {
                try {
                    $db->beginTransaction();
                    $stmt = $db->prepare("update nhanvien set hoten = :hoten, email = :email, chucvu = :role, trangthai = :trangthai where manv = :manv");
                    $stmt->execute([
                        ':hoten' => $hoTen,
                        ':email' => $email,
                        ':role' => $role,
                        ':trangthai' => $trangThai,
                        ':manv' => $maNVCapNhat
                    ]);
                    $stmtPQ = $db->prepare("update phanquyen set quyenxemsp = :quyenxemsp, quyenthemsp = :quyenthemsp, quyenthemdm = :quyenthemdm, 
                                          quyensuasp = :quyensuasp, quyenxoasp = :quyenxoasp, quyenxoadm = :quyenxoadm, 
                                          quyenxembc = :quyenxembc, quyenthembc = :quyenthembc, quyensuabc = :quyensuabc, 
                                          quyenxoabc = :quyenxoabc where manv = :manv");
                    $stmtPQ->execute([
                        ':quyenxemsp' => $qXemSP,
                        ':quyenthemsp' => $qThemSP,
                        ':quyenthemdm' => $qThemDM,
                        ':quyensuasp' => $qSuaSP,
                        ':quyenxoasp' => $qXoaSP,
                        ':quyenxoadm' => $qXoaDM,
                        ':quyenxembc' => $qXemBC,
                        ':quyenthembc' => $qThemBC,
                        ':quyensuabc' => $qSuaBC,
                        ':quyenxoabc' => $qXoaBC,
                        ':manv' => $maNVCapNhat
                    ]);
                    $db->commit();
                    ghiLichSu($db, $maNV, "Cập nhật thông tin nhân viên: $hoTen ($maNVCapNhat) - Chức vụ: $role - Trạng thái: $trangThai");
                    $success_msg = 'Thông tin nhân viên đã được cập nhật thành công.';
                } catch (PDOException $e) {
                    $db->rollback();
                    $error_msg = 'Có lỗi xảy ra khi cập nhật thông tin nhân viên.';
                }
            }
        }
    }
    //tạo lại mật khẩu cho nhân viên
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resetpassnv'])) {
        $maNVReset = $_POST['manv'] ?? '';
        $tempPass = 123456; 
        $tempHash = password_hash($tempPass, PASSWORD_DEFAULT);
        try {
            $stmt = $db->prepare("update nhanvien set matkhauhash = :pass, trangthai = 'Inactive' where manv = :manv");
            $stmt->execute([
                ':pass' => $tempHash,
                ':manv' => $maNVReset
            ]);
            $stmt = $db->prepare("select hoten from nhanvien where manv = :manv");
            $stmt->execute([':manv' => $maNVReset]);
            $hoTenReset = $stmt->fetch();
            ghiLichSu($db, $maNV, "Đặt lại mật khẩu cho nhân viên: $hoTenReset[hoten] ($maNVReset)");
            $success_msg = 'Mật khẩu đã được đặt lại thành công. Mật khẩu tạm thời là: 123456';
            $_SESSION['reset_pass_success'] = [
                'message' => $success_msg,
                'hoTen' => $hoTenReset['hoten'],
                'maNV' => $maNVReset,
                'tempPass' => $tempPass
            ];
            header('Location: index.php?tab=users');
            exit;
        } catch (PDOException $e) {
            $error_msg = 'Có lỗi xảy ra khi đặt lại mật khẩu cho nhân viên.'.$e->getMessage();
        }            

    }
    //xóa nhân viên
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xoanv'])) {
        $maNVXoa = $_POST['manv'] ?? '';
        try {
            $stmt = $db->prepare("SELECT hoten, chucvu FROM nhanvien WHERE manv = :manv");
            $stmt->execute([':manv' => $maNVXoa]);
            $nvXoa = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$nvXoa) {
                $error_msg = 'Nhân viên không tồn tại.';
            } elseif ($nvXoa['chucvu'] === 'Admin') {
                $error_msg = 'Không thể xóa Admin.';
            } else {
                $stmt = $db->prepare("DELETE FROM nhanvien WHERE manv = :manv");
                $stmt->execute([':manv' => $maNVXoa]);
                ghiLichSu($db, $maNV, "Xóa nhân viên: {$nvXoa['hoten']} ($maNVXoa)");
                $success_msg = 'Nhân viên đã được xóa thành công.';
            }
        } catch (PDOException $e) {
            $error_msg = 'Có lỗi xảy ra khi xóa nhân viên: ' . $e->getMessage();
        }
    }
    // xoa nhieu nhan vien
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xoanhieunv'])) {
        $chonnv = $_POST['selected_nhanvien'] ?? [];
        if (!is_array($chonnv)) {
            $chonnv = [$chonnv];
        }
        $chonnv = array_diff($chonnv, ['USR001']);
        if (empty($chonnv)) {
            $error_msg = 'Vui lòng chọn ít nhất một nhân viên để xóa (không thể xóa Admin).';
        } else {
            try {
                $db->beginTransaction();
                $placeholders = implode(',', array_fill(0, count($chonnv), '?'));
                $stmt = $db->prepare("DELETE FROM nhanvien WHERE manv IN ($placeholders)");
                $stmt->execute(array_values($chonnv));
                ghiLichSu($db, $maNV, "Xóa nhiều nhân viên: " . implode(',', $chonnv));
                $success_msg = 'Nhân viên đã được xóa thành công.';
                $db->commit();
            } catch (PDOException $e) {
                $db->rollBack();
                $error_msg = 'Có lỗi xảy ra khi xóa nhân viên: ' . $e->getMessage();
            }
        }
    }
    // xem nhân viên
    $xemnv = null;
    $lichSuHoatDong = [];
    if (isset($_GET['view_nv'])) {
        $maNVXem = $_GET['view_nv'];
        $stmt = $db->prepare("SELECT * FROM nhanvien WHERE manv = :manv");
        $stmt->execute([':manv' => $maNVXem]);
        $xemnv = $stmt->fetch();
        if ($xemnv) {
            $stmtLS = $db->prepare("SELECT * FROM lichsuhoatdong WHERE manv = :manv ORDER BY thoigian DESC");
            $stmtLS->execute([':manv' => $maNVXem]);
            $lichSuHoatDong = $stmtLS->fetchAll();
        }   
 
    }
    $capnhatnv = null;
    if (isset($_GET['edit_nv'])) {
        $maNVCN = $_GET['edit_nv'];
        $stmt = $db->prepare("SELECT nv.*, pq.* FROM nhanvien nv LEFT JOIN phanquyen pq ON nv.manv = pq.manv WHERE nv.manv = :manv");
        $stmt->execute([':manv' => $maNVCN]);
        $capnhatnv = $stmt->fetch();
    }
}


?>