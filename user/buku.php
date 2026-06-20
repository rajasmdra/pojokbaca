<?php
session_start();
// 1. Proteksi Halaman
if (!isset($_SESSION['login_anggota'])) {
    header("Location: index.php");
    exit;
}

// 2. Hubungkan ke database
include '../config/koneksi.php';

$nama_anggota = $_SESSION['nama_anggota'] ?? 'Anggota';
$id_anggota   = $_SESSION['id_anggota'];

// 3. Ambil ID Buku dari URL parameter ?id=
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: katalog.php");
    exit;
}

$id_buku = mysqli_real_escape_string($mysqli, $_GET['id']);

// 4. Query Relasi (JOIN) untuk mengambil nama teks asli dari Kategori, Penerbit, dan Rak
$sql_detail = "SELECT b.*, 
                      k.nama_kategori, 
                      p.nama_penerbit, 
                      r.nama_rak 
               FROM buku b
               LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
               LEFT JOIN penerbit p ON b.id_penerbit = p.id_penerbit
               LEFT JOIN rak r ON b.id_rak = r.id_rak
               WHERE b.id_buku = '$id_buku'";

$query_detail = mysqli_query($mysqli, $sql_detail);
$buku = mysqli_fetch_assoc($query_detail);

// Jika ID buku tidak ditemukan di database, kembalikan ke katalog
if (!$buku) {
    header("Location: katalog.php");
    exit;
}

// 5. Ambil total denda aktif (belum_bayar) untuk kebutuhan indikator badge di sidebar
$query_denda = mysqli_query($mysqli, "SELECT COUNT(d.id_denda) AS jumlah_pelanggaran 
                                      FROM denda d 
                                      JOIN peminjaman p ON d.id_peminjaman = p.id_peminjaman 
                                      WHERE p.id_anggota = '$id_anggota' AND d.status_denda = 'belum_bayar'");
$data_denda = mysqli_fetch_assoc($query_denda);
$jumlah_denda_aktif = $data_denda['jumlah_pelanggaran'] ?? 0;

// Penentu ketersediaan berdasarkan kolom 'stok_tersedia'
$stok_tersedia = $buku['stok_tersedia'] ?? 0;
$is_available  = $stok_tersedia > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PojokBaca | Detail Buku</title>
  
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <div class="admin-shell">
    <div class="sidebar-backdrop" data-sidebar-close></div>

    <aside class="admin-sidebar" id="adminSidebar">
      <div class="sidebar-header">
        <a class="brand-mark" href="dashboard.php">
          <span class="brand-icon"><i class="bi bi-book-half"></i></span>
          <span class="brand-copy">
            <span class="brand-title">PojokBaca</span>
            <span class="brand-subtitle">Anggota</span>
          </span>
        </a>
      </div>
      <nav class="sidebar-nav">
        <a class="nav-link" href="dashboard.php"><span class="nav-icon"><i class="bi bi-speedometer2"></i></span><span class="nav-text">Dashboard</span></a>
        <a class="nav-link active" href="katalog.php"><span class="nav-icon"><i class="bi bi-journal-text"></i></span><span class="nav-text">Katalog Buku</span></a>
        <a class="nav-link" href="riwayat.php"><span class="nav-icon"><i class="bi bi-arrow-left-right"></i></span><span class="nav-text">Riwayat Pinjam</span></a>
        <a class="nav-link" href="denda.php">
          <span class="nav-icon"><i class="bi bi-cash-coin"></i></span><span class="nav-text">Denda Saya</span>
          <?php if($jumlah_denda_aktif > 0): ?>
            <span class="badge bg-danger ms-auto px-2 rounded-pill"><?= $jumlah_denda_aktif; ?></span>
          <?php endif; ?>
        </a>
        <a class="nav-link" href="profil.php"><span class="nav-icon"><i class="bi bi-person-badge"></i></span><span class="nav-text">Profil Saya</span></a>
        <hr class="mx-3 my-2 text-secondary opacity-25">
        <a class="nav-link text-danger" href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun anda?')"><span class="nav-icon"><i class="bi bi-box-arrow-left text-danger"></i></span><span class="nav-text fw-bold">Logout</span></a>
      </nav>
      <div class="sidebar-user d-none">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_anggota); ?>">
        <strong>Admin Hasan</strong>
        <small>Anggota Aktif</small>
      </div>
      <div class="sidebar-user">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="User">
        <strong><?= htmlspecialchars($nama_anggota); ?></strong>
        <small>Anggota Aktif</small>
      </div>
    </aside>

    <div class="admin-main">
      <nav class="navbar admin-navbar navbar-expand bg-white">
        <div class="container-fluid px-3 px-lg-4">
          <button class="sidebar-toggle" type="button" data-sidebar-toggle><span></span><span></span><span></span></button>
          <div class="navbar-actions ms-auto">
            <div class="dropdown">
              <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <img class="avatar-img avatar-sm" src="../assets/images/avatar/avatar.jpg" alt="User">
                <span class="d-none d-sm-inline"><?= htmlspecialchars($nama_anggota); ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun anda?')"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
              </ul>
            </div>
          </div>
        </div>
      </nav>

      <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
          
          <div class="mb-3">
            <a href="katalog.php" class="btn btn-sm btn-outline-secondary px-3 rounded-pill">
              <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
            </a>
          </div>

          <div class="page-heading">
            <div class="page-heading-copy">
              <span class="page-icon"><i class="bi bi-info-circle"></i></span>
              <div>
                <p class="eyebrow mb-1">E-Katalog</p>
                <h1 class="h3 mb-1">Detail Informasi Buku</h1>
                <p class="text-muted mb-0">Rincian spesifikasi data buku berdasarkan database utama perpustakaan.</p>
              </div>
            </div>
          </div>

          <div class="panel border-0 shadow-sm overflow-hidden p-4">
            <div class="row g-4">
              
              <div class="col-12 col-md-4 col-lg-3 text-center text-md-start">
                <div class="bg-light p-3 rounded d-inline-block shadow-sm">
                  <?php if (!empty($buku['cover'])): ?>
                    <img src="../assets/images/cover/<?= $buku['cover']; ?>" class="img-fluid rounded" alt="Cover Buku" style="max-height: 280px; object-fit: cover;">
                  <?php else: ?>
                    <div class="text-center py-5 px-4 text-secondary">
                      <i class="bi bi-book display-1 d-block mb-2"></i>
                      <span class="small text-muted">Tidak Ada Cover</span>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <div class="col-12 col-md-8 col-lg-9">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                  <span class="badge <?= $is_available ? 'bg-success text-white border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'; ?> px-3 py-2">
                    <?= $is_available ? 'Tersedia Di Perpustakaan' : 'Sedang Kosong'; ?>
                  </span>
                  <span class="badge bg-white text-dark border border-secondary-subtle px-3 py-2">
                    <i class="bi bi-bookshelf me-1"></i> Lokasi: <?= htmlspecialchars($buku['nama_rak'] ?? 'Belum Ditentukan'); ?>
                  </span>
                </div>

                <h2 class="h3 fw-bold mb-1"><?= htmlspecialchars($buku['judul']); ?></h2>
                <p class="text-primary fw-medium mb-4 fs-5">Oleh: <?= htmlspecialchars($buku['penulis'] ?? 'Tidak Diketahui'); ?></p>

                <div class="table-responsive">
                  <table class="table table-striped align-middle text-dark">
                    <tbody>
                      <tr>
                        <td style="width: 200px;">Nomor ISBN</td>
                        <td class="fw-semibold">: <?= htmlspecialchars($buku['isbn'] ?? '-'); ?></td>
                      </tr>
                      <tr>
                        <td>Kategori / Genre</td>
                        <td class="fw-semibold">: <?= htmlspecialchars($buku['nama_kategori'] ?? 'Umum'); ?></td>
                      </tr>
                      <tr>
                        <td>Penerbit Buku</td>
                        <td class="fw-semibold">: <?= htmlspecialchars($buku['nama_penerbit'] ?? '-'); ?></td>
                      </tr>
                      <tr>
                        <td>Tahun Terbit</td>
                        <td class="fw-semibold">: <?= htmlspecialchars($buku['tahun_terbit'] ?? '-'); ?></td>
                      </tr>
                      <tr>
                        <td>Jumlah Halaman</td>
                        <td class="fw-semibold">: <?= htmlspecialchars($buku['jumlah_halaman'] ?? '-'); ?> Halaman</td>
                      </tr>
                      <tr>
                        <td>Stok Total Koleksi</td>
                        <td class="fw-semibold">: <?= htmlspecialchars($buku['stok_total'] ?? '0'); ?> Buku</td>
                      </tr>
                      <tr>
                        <td>Stok Tersedia di Rak</td>
                        <td class="fw-semibold text-success">: <?= htmlspecialchars($buku['stok_tersedia'] ?? '0'); ?> Buku</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <?php if (!empty($buku['sinopsis'])): ?>
                  <div class="mt-4 pt-3 border-top">
                    <h5 class="fw-bold mb-2">Sinopsis Buku</h5>
                    <p class="medium lh-base" style="text-align: justify;">
                      <?= nl2br(htmlspecialchars($buku['sinopsis'])); ?>
                    </p>
                  </div>
                <?php endif; ?>

                <div class="alert alert-warning border-0 small d-flex mt-4 mb-0" role="alert">
                  <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                  <div>
                    <strong>Pemberitahuan:</strong> Fitur peminjaman online dinonaktifkan. Silakan langsung menuju ke lokasi <strong><?= htmlspecialchars($buku['nama_rak'] ?? 'Rak terkait'); ?></strong> untuk membaca buku ini di tempat.
                  </div>
                </div>

              </div>
            </div>
          </div>

        </div>
      </main>

      <footer class="admin-footer">
        <div class="container-fluid px-3 px-lg-4">
          <span>Copyright 2026 PojokBaca. All rights reserved.</span>
        </div>
      </footer>
    </div>
  </div>
  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
</body>
</html>