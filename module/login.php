<?php
// login.php - Login logic

$login_error = '';

// 2. XỬ LÝ ĐĂNG NHẬP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $db->prepare("SELECT nv.*, pq.quyenxemsp, pq.quyenthemsp, pq.quyenthemdm, pq.quyensuasp, pq.quyenxoasp, pq.quyenxoadm, pq.quyenxembc, pq.quyenthembc, pq.quyensuabc, pq.quyenxoabc FROM nhanvien nv LEFT JOIN phanquyen pq ON nv.manv = pq.manv WHERE nv.email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['matkhauhash'])) {
        if ($user['trangthai'] === 'đang khóa') {
            $_SESSION['activation_user'] = $user;
            header('Location: module/change_password.php');
            exit;
        } else {
            $_SESSION['user'] = $user;
            ghiLichSu($db, $user['manv'], 'Đăng nhập hệ thống thành công');
            header('Location: index.php');
            exit;
        }
    } else {
        $login_error = 'Username hoặc Password không đúng.';
    }
}
?>