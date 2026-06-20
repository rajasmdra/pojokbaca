<?php
session_start();
// 1. Proteksi Halaman: Jika tidak ada session login_anggota, tendang kembali ke login (index.php)
if (!isset($_SESSION['login_anggota'])) {
    header("Location: index.php");
    exit;
}

// 2. Hubungkan ke database
include '../config/koneksi.php';

// PERBAIKAN: Menggunakan $_SESSION['nama_anggota'] sesuai dengan yang diset pada file login
$nama_lengkap = $_SESSION['nama_anggota'] ?? 'Anggota';
$id_anggota   = $_SESSION['id_anggota'];
$status_update = null; // Menyimpan status sukses/gagal proses update

// ====================================================================
// LOGIKA PROSES SIMPAN (Menyatu dalam satu file)
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data yang dikirimkan oleh form
    $nama_input = mysqli_real_escape_string($mysqli, $_POST['nama_lengkap']);
    $email      = mysqli_real_escape_string($mysqli, $_POST['email']);
    $notelp     = mysqli_real_escape_string($mysqli, $_POST['notelp']);
    $alamat     = mysqli_real_escape_string($mysqli, $_POST['alamat']);

    // Query untuk memperbarui data anggota di database
    $sql_update = "UPDATE anggota SET 
                    nama_lengkap = '$nama_input', 
                    email = '$email', 
                    no_telepon = '$notelp', 
                    alamat = '$alamat' 
                   WHERE id_anggota = '$id_anggota'";

    if (mysqli_query($mysqli, $sql_update)) {
        // PERBAIKAN: Perbarui $_SESSION['nama_anggota'] agar perubahan nama di navbar & sidebar langsung instan terlihat
        $_SESSION['nama_anggota'] = $nama_input;
        $nama_lengkap = $nama_input; // Perbarui variabel lokal untuk visualisasi komponen web
        
        $status_update = 'sukses';
    } else {
        $status_update = 'gagal';
    }
}
// ====================================================================

// 3. Ambil total denda aktif (belum_bayar) untuk kebutuhan indikator badge di sidebar
$query_denda = mysqli_query($mysqli, "SELECT COUNT(d.id_denda) AS jumlah_pelanggaran 
                                      FROM denda d 
                                      JOIN peminjaman p ON d.id_peminjaman = p.id_peminjaman 
                                      WHERE p.id_anggota = '$id_anggota' AND d.status_denda = 'belum_bayar'");
$data_denda = mysqli_fetch_assoc($query_denda);
$jumlah_denda_aktif = $data_denda['jumlah_pelanggaran'] ?? 0;

// 4. Ambil data profil terbaru milik anggota dari database
$query_profil = mysqli_query($mysqli, "SELECT * FROM anggota WHERE id_anggota = '$id_anggota'");
$user = mysqli_fetch_assoc($query_profil);

// Fallback data jika kolom di database belum terisi / berbeda nama
$email_user  = $user['email'] ?? '-';
$notelp_user = $user['no_telepon'] ?? $user['no_telp'] ?? $user['telepon'] ?? '-';
$alamat_user = $user['alamat'] ?? '-';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="PojokBaca Anggota Workspace">
  <title>PojokBaca | Profil Saya</title>

  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
  <div class="admin-shell">
    <div class="sidebar-backdrop" data-sidebar-close></div>

    <aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
      <div class="sidebar-header">
        <a class="brand-mark" href="dashboard.php" aria-label="PojokBaca dashboard">
          <span class="brand-icon"><i class="bi bi-book-half" aria-hidden="true"></i></span>
          <span class="brand-copy">
            <span class="brand-title">PojokBaca</span>
            <span class="brand-subtitle">Anggota</span>
          </span>
        </a>
      </div>

      <nav class="sidebar-nav">
        <a class="nav-link" href="dashboard.php">
          <span class="nav-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
          <span class="nav-text">Dashboard</span>
        </a>
        
        <a class="nav-link" href="katalog.php">
          <span class="nav-icon"><i class="bi bi-journal-text" aria-hidden="true"></i></span>
          <span class="nav-text">Katalog Buku</span>
        </a>
        
        <a class="nav-link" href="riwayat.php">
          <span class="nav-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
          <span class="nav-text">Riwayat Pinjam</span>
        </a>
        
        <a class="nav-link" href="denda.php">
          <span class="nav-icon"><i class="bi bi-cash-coin" aria-hidden="true"></i></span>
          <span class="nav-text">Denda Saya</span>
          <?php if($jumlah_denda_aktif > 0): ?>
            <span class="badge bg-danger ms-auto px-2 rounded-pill"><?= $jumlah_denda_aktif; ?></span>
          <?php endif; ?>
        </a>
        
        <a class="nav-link active" href="profil.php" aria-current="page">
          <span class="nav-icon"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
          <span class="nav-text">Profil Saya</span>
        </a>
        
        <hr class="mx-3 my-2 text-secondary opacity-25">
        <a class="nav-link text-danger" href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun anda?')">
          <span class="nav-icon"><i class="bi bi-box-arrow-left text-danger" aria-hidden="true"></i></span>
          <span class="nav-text fw-bold">Logout</span>
        </a>
      </nav>

      <div class="sidebar-user d-none">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_anggota); ?>">
        <strong>Admin Hasan</strong>
        <small>Anggota Aktif</small>
      </div>

      <div class="sidebar-user">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_lengkap); ?>">
        <strong><?= htmlspecialchars($nama_lengkap); ?></strong>
        <small>Anggota Aktif</small>
      </div>
    </aside>

    <div class="admin-main">
      <nav class="navbar admin-navbar navbar-expand bg-white">
        <div class="container-fluid px-3 px-lg-4">
          <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="true" aria-label="Toggle sidebar">
            <span></span><span></span><span></span>
          </button>

          <div class="navbar-actions ms-auto">
            <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
              <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
            </button>

            <div class="dropdown">
              <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="avatar-img avatar-sm" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_lengkap); ?>">
                <span class="d-none d-sm-inline"><?= htmlspecialchars($nama_lengkap); ?></span>
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
          <div class="page-heading">
            <div class="page-heading-copy">
              <span class="page-icon"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
              <div>
                <p class="eyebrow mb-1">Akun Saya</p>
                <h1 class="h3 mb-1">Profil Pengguna</h1>
                <p class="text-muted mb-0">Kelola informasi data diri, nomor kontak, beserta alamat tinggal Anda.</p>
              </div>
            </div>
          </div>

          <?php if ($status_update === 'sukses'): ?>
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle-fill me-2"></i> Profil berhasil diperbarui secara langsung!
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php elseif ($status_update === 'gagal'): ?>
            <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i> Gagal memperbarui profil. Silakan periksa kembali koneksi atau database Anda.
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <section class="row g-3">
            <div class="col-12 col-xl-4">
              <div class="panel h-100 text-center profile-card">
                <div class="profile-cover"><img src="../assets/images/png/dasher-ui-bootstrap-5.jpg" alt="PojokBaca workspace preview"></div>
                <img class="avatar-img avatar-xl profile-photo" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_lengkap); ?>">
                <h2 class="h5 mt-3 mb-1"><?= htmlspecialchars($nama_lengkap); ?></h2>
                <div class="d-flex justify-content-center gap-2">
                  <span class="badge text-bg-primary">Anggota</span>
                  <span class="badge text-bg-success">Verified</span>
                </div>
                
                <div class="info-list mt-4 text-start">
                  <div><span>Email</span><strong><?= htmlspecialchars($email_user); ?></strong></div>
                  <div><span>No. Telp</span><strong><?= htmlspecialchars($notelp_user); ?></strong></div>
                  <div><span>Alamat</span><strong><?= htmlspecialchars($alamat_user); ?></strong></div>
                </div>
              </div>
            </div>

            <div class="col-12 col-xl-8">
              <form class="panel needs-validation" method="POST" action="" novalidate>
                <div class="panel-header">
                  <div>
                    <h2 class="h5 mb-1 section-title"><i class="bi bi-person-gear" aria-hidden="true"></i><span>Pengaturan Profil</span></h2>
                    <p class="text-muted mb-0">Perbarui data kontak personal Anda untuk keperluan validasi sirkulasi buku.</p>
                  </div>
                </div>
                
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label" for="profileName">Nama Lengkap</label>
                    <input class="form-control" id="profileName" name="nama_lengkap" type="text" value="<?= htmlspecialchars($nama_lengkap); ?>" required>
                    <div class="invalid-feedback">Nama lengkap wajib diisi.</div>
                  </div>
                  
                  <div class="col-md-6">
                    <label class="form-label" for="profileEmail">Email</label>
                    <input class="form-control" id="profileEmail" name="email" type="email" value="<?= htmlspecialchars($email_user); ?>" required>
                    <div class="invalid-feedback">Masukkan alamat email yang valid.</div>
                  </div>

                  <div class="col-12">
                    <label class="form-label" for="profilePhone">No. Telp</label>
                    <input class="form-control" id="profilePhone" name="notelp" type="tel" value="<?= htmlspecialchars($notelp_user); ?>" required>
                    <div class="invalid-feedback">Nomor telepon/WhatsApp aktif wajib diisi.</div>
                  </div>

                  <div class="col-12">
                    <label class="form-label" for="profileAddress">Alamat</label>
                    <input class="form-control" id="profileAddress" name="alamat" type="text" value="<?= htmlspecialchars($alamat_user); ?>" required></input>
                    <div class="invalid-feedback">Alamat rumah domisili saat ini wajib diisi.</div>
                  </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                  <button class="btn btn-primary" type="submit" onclick="return confirm('Apakah Anda yakin ingin mengedit data anda?')">
                    <i class="bi bi-check2-circle" aria-hidden="true"></i> Simpan Perubahan
                  </button>
                </div>
              </form>
            </div>
          </section>
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