<?php
// login.php - Login logic
session_start();
require_once __DIR__ . '/db.php';

$login_error = '';

// 2. XỬ LÝ ĐĂNG NHẬP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $db->prepare("SELECT nv.*, pq.QuyenXemSP, pq.QuyenThemSP, pq.QuyenThemDM, pq.QuyenSuaSP, pq.QuyenXoaSP, pq.QuyenXoaDM, pq.QuyenXemBC, pq.QuyenThemBC, pq.QuyenSuaBC, pq.QuyenXoaBC FROM NhanVien nv LEFT JOIN PhanQuyen pq ON nv.MaNV = pq.MaNV WHERE nv.Email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['MatKhauHash'])) {
        if ($user['TrangThai'] === 'Inactive') {
            $_SESSION['activation_user'] = $user;
            header('Location: change_password.php');
            exit;
        } else {
            $_SESSION['user'] = $user;
            ghiLichSu($db, $user['MaNV'], 'Đăng nhập hệ thống thành công');
            header('Location: index.php');
            exit;
        }
    } else {
        $login_error = 'Username hoặc Password không đúng.';
    }
}
?>