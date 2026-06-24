<?php
session_start();

$dbFile = __DIR__ . '/../database.sqlite';
try {
    $db = new PDO("sqlite:" . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec("PRAGMA foreign_keys = ON;");
} catch (PDOException $e) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage());
}

function ghiLichSu($db, $maNV, $hanhDong) {
    try {
        $stmt = $db->prepare("insert into lichsuhoatdong (manv, hanhdong) values (:manv, :hanhdong)");
        $stmt->execute([
            ':manv' => $maNV,
            ':hanhdong' => $hanhDong
        ]);
    } catch (PDOException $e) {
        error_log("Lỗi ghi lịch sử: " . $e->getMessage());
    }
}

if (!isset($_SESSION['activation_user'])) {
    header('Location: ../index.php');
    exit;
}

$user = $_SESSION['activation_user'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (strlen($new_password) < 6) {
        $error = 'Mật khẩu mới phải có tối thiểu từ 6 ký tự trở lên.';
    } else {
        try {
            $db->beginTransaction();
            
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE nhanvien SET matkhauhash = :hash, trangthai = 'hoạt động' WHERE manv = :manv");
            $stmt->execute([
                ':hash' => $hash,
                ':manv' => $user['manv']
            ]);
            
            ghiLichSu($db, $user['manv'], 'Kích hoạt tài khoản và thay đổi mật khẩu lần đầu thành công');
            
            $db->commit();
            
            $stmt = $db->prepare("SELECT nv.*, pq.quyenxemsp, pq.quyenthemsp, pq.quyenthemdm, pq.quyensuasp, pq.quyenxoasp, pq.quyenxoadm, pq.quyenxembc, pq.quyenthembc, pq.quyensuabc, pq.quyenxoabc FROM nhanvien nv LEFT JOIN phanquyen pq ON nv.manv = pq.manv WHERE nv.manv = :manv");
            $stmt->execute([':manv' => $user['manv']]);
            $updatedUser = $stmt->fetch();
            
            $_SESSION['user'] = $updatedUser;
            unset($_SESSION['activation_user']);
            header('Location: ../index.php');
            exit;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $error = 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../view/change_password.tpl.html';
?>