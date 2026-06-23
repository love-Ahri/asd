<?php
$dbFile = __DIR__ . '/db.sql';

try {
    $db = new PDO("sqlite:" . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Bắt buộc bật Foreign Key constraints trên SQLite
    $db->exec("PRAGMA foreign_keys = ON;");
} catch (PDOException $e) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage());
}
function initDatabase($db) {
    $db->exec("CREATE TABLE IF NOT EXISTS AQW_NhanVien (
        MaNV CHAR(10) PRIMARY KEY,
        HoTen NVARCHAR(100) NOT NULL,
        Email VARCHAR(100) UNIQUE NOT NULL,
        MatKhauHash VARCHAR(255) NOT NULL,
        ChucVu NVARCHAR(20) NOT NULL CHECK (ChucVu IN ('Admin', 'Warehouse', 'Sales')),
        TrangThai NVARCHAR(20) DEFAULT 'Inactive' CHECK (TrangThai IN ('Active', 'Inactive')),
        NgayTao DATE NOT NULL
    )");

    // 1.5. Bảng PhanQuyen liên kết 1-1 với NhanVien
    $db->exec("CREATE TABLE IF NOT EXISTS AQW_PhanQuyen (
        MaNV CHAR(10) PRIMARY KEY,
        QuyenXemSP INTEGER DEFAULT 1 CHECK (QuyenXemSP IN (0, 1)),
        QuyenThemSP INTEGER DEFAULT 0 CHECK (QuyenThemSP IN (0, 1)),
        QuyenThemDM INTEGER DEFAULT 0 CHECK (QuyenThemDM IN (0, 1)),
        QuyenSuaSP INTEGER DEFAULT 0 CHECK (QuyenSuaSP IN (0, 1)),
        QuyenXoaSP INTEGER DEFAULT 0 CHECK (QuyenXoaSP IN (0, 1)),
        QuyenXoaDM INTEGER DEFAULT 0 CHECK (QuyenXoaDM IN (0, 1)),
        QuyenXemBC INTEGER DEFAULT 1 CHECK (QuyenXemBC IN (0, 1)),
        QuyenThemBC INTEGER DEFAULT 1 CHECK (QuyenThemBC IN (0, 1)),
        QuyenSuaBC INTEGER DEFAULT 1 CHECK (QuyenSuaBC IN (0, 1)),
        QuyenXoaBC INTEGER DEFAULT 1 CHECK (QuyenXoaBC IN (0, 1)),
        FOREIGN KEY (MaNV) REFERENCES NhanVien(MaNV) ON DELETE CASCADE
    )");

    // 2. Bảng LichSuHoatDong
    $db->exec("CREATE TABLE IF NOT EXISTS AQW_LichSuHoatDong (
        MaLS INTEGER PRIMARY KEY AUTOINCREMENT,
        MaNV CHAR(10) NOT NULL,
        HanhDong NVARCHAR(1000) NOT NULL,
        ThoiGian DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (MaNV) REFERENCES NhanVien(MaNV) ON DELETE CASCADE
    )");

    // 3. Bảng DanhMuc
    $db->exec("CREATE TABLE IF NOT EXISTS AQW_DanhMuc (
        MaDM CHAR(10) PRIMARY KEY,
        TenDM NVARCHAR(100) UNIQUE NOT NULL,
        HinhDang NVARCHAR(50) NOT NULL CHECK (HinhDang IN ('Tròn', 'Vuông', 'Dẹt')),
        KhoiLuong NVARCHAR(50) NOT NULL CHECK (KhoiLuong IN ('Nhẹ', 'Trung bình', 'Nặng')),
        NgayTao DATE NOT NULL
    )");

    // 4. Bảng SanPham
    $db->exec("CREATE TABLE IF NOT EXISTS AQW_SanPham (
        MaSP CHAR(10) PRIMARY KEY,
        TenSP NVARCHAR(150) NOT NULL,
        MaDM CHAR(10) NOT NULL,
        DonGia REAL NOT NULL CHECK (DonGia >= 0),
        SoLuong INTEGER NOT NULL CHECK (SoLuong >= 0),
        NgayTao DATE NOT NULL,
        FOREIGN KEY (MaDM) REFERENCES DanhMuc(MaDM) ON DELETE CASCADE
    )");

    // 5. Bảng BaoCao
    $db->exec("CREATE TABLE IF NOT EXISTS AQW_BaoCao (
        MaBC CHAR(10) PRIMARY KEY,
        TieuDe NVARCHAR(100) NOT NULL,
        NgayTao DATE NOT NULL,
        NoiDung TEXT NOT NULL,
        MaNV CHAR(10) NULL,
        CONSTRAINT FK_BaoCao_NhanVien FOREIGN KEY (MaNV) 
            REFERENCES NhanVien(MaNV) ON DELETE SET NULL
    )");

    // 6. Bảng Chi Tiết Báo Cáo
    $db->exec("CREATE TABLE IF NOT EXISTS AQW_ChiTietBaoCao (
        MaBC CHAR(10) NOT NULL,
        MaSP CHAR(10) NOT NULL,
        SoLuong INT NOT NULL CHECK (SoLuong > 0),
        PRIMARY KEY (MaBC, MaSP),
        CONSTRAINT FK_CTBC_BaoCao FOREIGN KEY (MaBC) 
            REFERENCES BaoCao(MaBC) ON DELETE CASCADE,
        CONSTRAINT FK_CTBC_SanPham FOREIGN KEY (MaSP) 
            REFERENCES SanPham(MaSP) ON DELETE CASCADE
    )");
}
initDatabase($db);

?>