pragma foreign_keys = on;
create table nhanvien (
    manv char(10) primary key,
    hoten nvarchar(100) not null,
    email varchar(100) unique not null,
    matkhauhash varchar(255) not null,
    chucvu nvarchar(20) not null check (chucvu in ('Admin', 'Warehouse', 'Sales')),
    trangthai nvarchar(20) default 'đang khóa' check (trangthai in ('hoạt động', 'đang khóa')),
    ngaytao date not null
);
create table phanquyen (
    manv char(10) primary key,
    quyenxemsp integer default 0 check (quyenxemsp in (0, 1)),
    quyenthemsp integer default 0 check (quyenthemsp in (0, 1)),
    quyensuasp integer default 0 check (quyensuasp in (0, 1)),
    quyenxoasp integer default 0 check (quyenxoasp in (0, 1)),
    quyenthemdm integer default 0 check (quyenthemdm in (0, 1)),
    quyenxoadm integer default 0 check (quyenxoadm in (0, 1)),
    quyenxembc integer default 0 check (quyenxembc in (0, 1)),
    quyenthembc integer default 0 check (quyenthembc in (0, 1)),
    quyensuabc integer default 0 check (quyensuabc in (0, 1)),
    quyenxoabc integer default 0 check (quyenxoabc in (0, 1)),
    foreign key (manv) references nhanvien(manv) on delete cascade
);
create table lichsuhoatdong (
    mals integer primary key autoincrement,
    manv char(10) not null,
    hanhdong nvarchar(1000) not null,
    thoigian datetime default current_timestamp,
    foreign key (manv) references nhanvien(manv) on delete cascade
);
create table if not exists danhmuc (
    madm char(10) primary key,
    tendm nvarchar(100) unique not null,
    hinhdang nvarchar(50) not null check (hinhdang in ('to', 'trung bình', 'nhỏ')),
    khoiluong nvarchar(50) not null check (khoiluong in ('Nhẹ', 'Trung bình', 'Nặng')),
    ngaytao date not null
);
create table if not exists sanpham (
    masp char(10) primary key,
    tensp nvarchar(150) not null,
    madm char(10) not null,
    dongia real not null check (dongia >= 0),
    soluong integer not null check (soluong >= 0),
    ngaytao date not null,
    foreign key (madm) references danhmuc(madm) on delete cascade
);
create table if not exists baocao (
    mabc char(10) primary key,
    tieude nvarchar(100) not null,
    ngaytao date not null,
    noidung text not null,
    manv char(10) null,
    constraint FK_baocao_nhanvien foreign key (manv) 
        references nhanvien(manv) on delete set null
);
create table if not exists chitietbaocao (
    mabc char(10) not null,
    masp char(10) not null,
    soluong int not null check (soluong > 0),
    primary key (mabc, masp),
    constraint FK_CTBC_baocao foreign key (mabc) 
        references baocao(mabc) on delete cascade,
    constraint FK_CTBC_sanpham foreign key (masp) 
        references sanpham(masp) on delete cascade
);

--admin
insert into nhanvien (manv, hoten, email, matkhauhash, chucvu, trangthai, ngaytao) values
('USR001', 'System Admin', 'trantiennghia.qngai@gmail.com', 
'$2y$10$DsDZQ4qO8.ANZa6ZgwmRjeZrSqsRLcuOSU2C7JV.nDgiMv6x8U7OC', 'Admin', 'hoạt động', current_date);

insert into phanquyen (manv, quyenxemsp, quyenthemsp, quyenthemdm, quyensuasp, 
quyenxoasp, quyenxoadm, quyenxembc, quyenthembc, quyensuabc, quyenxoabc) values
('USR001', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
