<?php
// Inventory module code here
// them danh muc
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['them_danh_muc'])) {
    if ($quyen_them_dm == 0 ) {
        $error_msg = 'khong có quyên thêm danh mục.';

    } else {
        $hinhdang = $_POST['hinhdang'] ?? 'to';
        $khoiluong = $_POST['khoiluong'] ?? 'nhẹ';
    $ten_dm = trim($_POST['ten_dm'] ?? '');
        if ($ten_dm === '') {
            $error_msg = 'Tên danh mục không được để trống.';
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM DanhMuc WHERE TenDM = :ten_dm");
            $stmt->execute([':ten_dm' => $ten_dm]);
            if ($stmt->fetchColumn() > 0) {
                $error_msg = 'Tên danh mục đã tồn tại.';
            } else {
                try {
                    $dmmoi = sinhMaDM($db,'DM','MaDM','cat');
                    $today = date('Y-m-d');

                    $stmt = $db->prepare("INSERT INTO DanhMuc (MaDM, TenDM, HinhDang, KhoiLuong, NgayTao) 
                    VALUES (:ma_dm, :ten_dm, :hinhdang, :khoiluong, :ngaytao)");
                    $stmt->execute([
                        ':ma_dm' => $dmmoi,
                        ':ten_dm' => $ten_dm,
                        ':hinhdang' => $hinhdang,
                        ':khoiluong' => $khoiluong,
                        ':ngaytao' => $today
                    ]);
                    ghiLichSu($db, $_SESSION['user']['MaNV'], "Thêm danh mục mới: $ten_dm (Mã: $dmmoi) - Hình dạng: $hinhdang, Khối lượng: $khoiluong");
                    $success_msg = 'Danh mục mới đã được thêm thành công.';
                    header('Location: index.php?tab=inventory');
                    exit;
                } catch (PDOException $e) {
                    $error_msg = 'Lỗi DM ' . $e->getMessage();
                }
            header('Location: index.php?tab=inventory');
            exit;
        }
    }
}
    // xoa danh muc
}
if (isset($_POST['xoa_danh_muc'])) {
    if ($quyen_xoa_dm == 0) {
        $error_msg = 'Bạn không có quyền xóa danh mục.';
    } else {
        $dmchon = $_GET['dmxoa'];
        $
    }
}
?>