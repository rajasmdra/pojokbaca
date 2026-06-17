<?php
session_start();
// 1. Proteksi Halaman: Pastikan yang masuk adalah admin
if (!isset($_SESSION['login_admin'])) {
    header("Location: ../index.php");
    exit;
}

// 2. Hubungkan ke database
include '../config/koneksi.php';

// Pastikan parameter ID peminjaman ada di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: peminjaman.php");
    exit;
}

$id_peminjaman   = $_GET['id'];
$id_admin_kembali = $_SESSION['id_admin']; // Mengambil ID Admin yang memproses pengembalian
$tgl_kembali     = date('Y-m-d'); // Tanggal pengembalian adalah hari ini

// 3. Ambil data peminjaman yang bersangkutan untuk divalidasi dan dihitung dendanya
$query_cek = mysqli_query($mysqli, "SELECT * FROM peminjaman WHERE id_peminjaman = '$id_peminjaman'");
$data_pinjam = mysqli_fetch_assoc($query_cek);

if (!$data_pinjam) {
    $_SESSION['gagal_kembali'] = "Data transaksi peminjaman tidak ditemukan.";
    header("Location: peminjaman.php");
    exit;
}

// Jika statusnya sudah kembali, jangan diproses lagi
if ($data_pinjam['status'] == 'kembali') {
    $_SESSION['gagal_kembali'] = "Buku ini sudah berstatus dikembalikan sebelumnya.";
    header("Location: peminjaman.php");
    exit;
}

$id_buku          = $data_pinjam['id_buku'];
$tgl_jatuh_tempo  = $data_pinjam['tgl_jatuh_tempo'];

// 4. Logika Perhitungan Denda Keterlambatan
$denda_per_hari = 1000; // Menggunakan tarif default Rp 1.000 per hari (silakan sesuaikan jika berbeda)
$total_denda    = 0;
$keterlambatan  = 0;

if ($tgl_kembali > $tgl_jatuh_tempo) {
    // Hitung selisih hari
    $selisih = strtotime($tgl_kembali) - strtotime($tgl_jatuh_tempo);
    $keterlambatan = floor($selisih / (60 * 60 * 24)); // Konversi ke jumlah hari
    $total_denda = $keterlambatan * $denda_per_hari;
}

// 5. Mulai Database Transaction untuk menjaga konsistensi data
mysqli_begin_transaction($mysqli);

try {
    // A. Update status peminjaman, tanggal kembali, dan admin yang memproses pengembalian
    // Sesuaikan nama kolom id_admin jika di database Anda bernama 'id_admin_kembali'
    $sql_update_pinjam = "UPDATE peminjaman 
                          SET status = 'kembali', 
                              tgl_kembali = '$tgl_kembali', 
                              id_admin_kembali = '$id_admin_kembali' 
                          WHERE id_peminjaman = '$id_peminjaman'";
    $proses_update_pinjam = mysqli_query($mysqli, $sql_update_pinjam);

    // B. Kembalikan stok buku (+1)
    $sql_update_stok = "UPDATE buku SET stok_tersedia = stok_tersedia + 1 WHERE id_buku = '$id_buku'";
    $proses_update_stok = mysqli_query($mysqli, $sql_update_stok);

    // C. Jika ada denda, masukkan ke dalam tabel denda
    $proses_insert_denda = true; // default true jika tidak ada denda
    if ($total_denda > 0) {
        $sql_insert_denda = "INSERT INTO denda (id_peminjaman, jumlah_denda, status_denda) 
                             VALUES ('$id_peminjaman', '$total_denda', 'belum_bayar')";
        $proses_insert_denda = mysqli_query($mysqli, $sql_insert_denda);
    }

    // 6. Validasi hasil eksekusi query
    if ($proses_update_pinjam && $proses_update_stok && $proses_insert_denda) {
        mysqli_commit($mysqli); // Sukses, simpan semua perubahan ke database

        // Siapkan pesan notifikasi untuk halaman peminjaman
        if ($total_denda > 0) {
            $_SESSION['sukses_kembali'] = "Buku berhasil dikembalikan! Anggota terlambat <strong>$keterlambatan hari</strong> dan dikenakan denda sebesar <strong>Rp " . number_format($total_denda, 0, ',', '.') . "</strong>.";
        } else {
            $_SESSION['sukses_kembali'] = "Buku berhasil dikembalikan tepat waktu. Transaksi selesai!";
        }
    } else {
        throw new Exception("Gagal memperbarui status sirkulasi atau denda di database.");
    }

} catch (Exception $e) {
    mysqli_rollback($mysqli); // Batalkan semua jika ada salah satu proses yang gagal
    $_SESSION['gagal_kembali'] = "Terjadi kesalahan sistem saat memproses pengembalian: " . $e->getMessage();
}

// Kembali ke halaman log peminjaman setelah memproses
header("Location: peminjaman.php");
exit;