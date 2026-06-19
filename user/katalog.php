<?php
session_start();
// 1. Proteksi Halaman: Jika tidak ada session login_anggota, tendang kembali ke login (index.php)
if (!isset($_SESSION['login_anggota'])) {
    header("Location: index.php");
    exit;
}

// 2. Hubungkan ke database
include '../config/koneksi.php';

$nama_anggota = $_SESSION['nama_anggota'] ?? 'Anggota';
$id_anggota   = $_SESSION['id_anggota'];

// 3. Ambil total denda aktif (belum lunas) untuk kebutuhan indikator badge di sidebar
$query_denda = mysqli_query($mysqli, "SELECT COUNT(d.id_denda) AS jumlah_pelanggaran 
                                      FROM denda d 
                                      JOIN peminjaman p ON d.id_peminjaman = p.id_peminjaman 
                                      WHERE p.id_anggota = '$id_anggota' AND d.status_denda = 'belum_bayar'");
$data_denda = mysqli_fetch_assoc($query_denda);
$jumlah_denda_aktif = $data_denda['jumlah_pelanggaran'] ?? 0;

// 4. Logika Sorting via Header Tabel
$sort_by   = $_GET['by'] ?? 'judul'; // Kolom target default
$sort_order = $_GET['order'] ?? 'ASC'; // Arah default

// Ambil arah sebaliknya untuk link klik berikutnya (Toggle ASC <=> DESC)
$next_order = ($sort_order == 'ASC') ? 'DESC' : 'ASC';

// Validasi kolom whitelist agar aman dari SQL Injection
$allowed_columns = ['judul', 'nama_kategori', 'penulis', 'nama_rak', 'stok_tersedia'];
$allowed_orders  = ['ASC', 'DESC'];

if (!in_array($sort_by, $allowed_columns)) { $sort_by = 'judul'; }
if (!in_array($sort_order, $allowed_orders)) { $sort_order = 'ASC'; }

// Pemetaan query pengurutan berdasarkan kolom yang dipilih
switch ($sort_by) {
    case 'nama_kategori':
        $orderby_sql = "ORDER BY k.nama_kategori $sort_order";
        break;
    case 'nama_rak':
        $orderby_sql = "ORDER BY r.nama_rak $sort_order";
        break;
    case 'penulis':
        $orderby_sql = "ORDER BY b.penulis $sort_order, b.tahun_terbit $sort_order";
        break;
    case 'stok_tersedia':
        // Mengurutkan berdasarkan jumlah stok fisik buku
        $orderby_sql = "ORDER BY b.stok_tersedia $sort_order";
        break;
    default:
        $orderby_sql = "ORDER BY b.judul $sort_order";
        break;
}

// 5. Ambil Data Buku disesuaikan dengan struktur tabel (Menggunakan urutan dinamis)
$query_buku = mysqli_query($mysqli, "SELECT b.*, k.nama_kategori, r.nama_rak 
                                     FROM buku b
                                     LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                                     LEFT JOIN rak r ON b.id_rak = r.id_rak
                                     $orderby_sql");

// Fungsi bantu untuk menampilkan icon panah sort yang aktif di header
function getSortIcon($column, $current_by, $current_order) {
    if ($column === $current_by) {
        return ($current_order === 'ASC') ? ' <i class="bi bi-caret-up-fill small"></i>' : ' <i class="bi bi-caret-down-fill small"></i>';
    }
    return ' <i class="bi bi-arrow-down-up text-muted opacity-50 small"></i>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="PojokBaca Anggota Workspace">
  <title>PojokBaca | Katalog Buku</title>

  <link class="main-stylesheet" rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    /* Styling agar header th yang bisa di-klik terlihat interaktif */
    th.sortable-header {
      cursor: pointer;
      white-space: nowrap;
      transition: background-color 0.2s;
    }
    th.sortable-header:hover {
      background-color: rgba(0, 0, 0, 0.04) !important;
    }
    th.sortable-header a {
      text-decoration: none;
      color: inherit;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
    }
  </style>
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
        
        <a class="nav-link active" href="katalog.php" aria-current="page">
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
        
        <a class="nav-link" href="profil.php">
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
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_anggota); ?>">
        <strong><?= htmlspecialchars($nama_anggota); ?></strong>
        <small>Anggota Aktif</small>
      </div>

      <div class="sidebar-footer">
        <span class="status-dot"></span>
        <span class="sidebar-footer-text">Sistem berjalan lancar</span>
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
                <img class="avatar-img avatar-sm" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_anggota); ?>">
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
          <div class="page-heading">
            <div class="page-heading-copy">
              <span class="page-icon"><i class="bi bi-journal-text" aria-hidden="true"></i></span>
              <div>
                <p class="eyebrow mb-1">Eksplorasi</p>
                <h1 class="h3 mb-1">Katalog Buku</h1>
                <p class="text-muted mb-0">Cari koleksi referensi bacaan berkualitas yang tersedia di PojokBaca.</p>
              </div>
            </div>
          </div>

          <section class="panel">
            <div class="panel-header">
              <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-table" aria-hidden="true"></i><span>Daftar Koleksi Buku</span></h2>
                <p class="text-muted mb-0">Silahkan klik pada nama kolom tabel untuk mengurutkan koleksi buku.</p>
              </div>
              <input class="form-control form-control-sm table-search" type="search" placeholder="Cari judul, penulis, rak..." data-table-search="katalogBukuTable" aria-label="Search books">
            </div>
            
            <div class="table-responsive">
              <table class="table align-middle mb-0 table-hover" id="katalogBukuTable" data-searchable-table>
                <thead>
                  <tr>
                    <th class="sortable-header">
                      <a href="katalog.php?by=judul&order=<?= ($sort_by == 'judul') ? $next_order : 'ASC'; ?>">
                        Judul Buku <?= getSortIcon('judul', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="katalog.php?by=nama_kategori&order=<?= ($sort_by == 'nama_kategori') ? $next_order : 'ASC'; ?>">
                        Kategori <?= getSortIcon('nama_kategori', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="katalog.php?by=penulis&order=<?= ($sort_by == 'penulis') ? $next_order : 'ASC'; ?>">
                        Penulis / Tahun <?= getSortIcon('penulis', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="katalog.php?by=nama_rak&order=<?= ($sort_by == 'nama_rak') ? $next_order : 'ASC'; ?>">
                        Lokasi Rak <?= getSortIcon('nama_rak', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="katalog.php?by=stok_tersedia&order=<?= ($sort_by == 'stok_tersedia') ? $next_order : 'DESC'; ?>">
                        Status Stok <?= getSortIcon('stok_tersedia', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="text-end" style="min-width: 100px;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  if (mysqli_num_rows($query_buku) > 0): 
                    while($buku = mysqli_fetch_assoc($query_buku)):
                      
                      $stok_total     = $buku['stok_total'] ?? 0;
                      $stok_tersedia  = $buku['stok_tersedia'] ?? 0; 

                      // Format stok murni angka ringkas Total/Tersedia (contoh: 5/4 atau 3/3)
                      if($stok_tersedia > 0) {
                          $status_badge = '<span class="badge bg-success text-white border border-success px-3 py-1.5 fw-bold">' . $stok_total . '/' . $stok_tersedia . '</span>';
                      } else {
                          $status_badge = '<span class="badge bg-danger text-white border border-danger px-3 py-1.5 fw-bold">' . $stok_total . '/' . $stok_tersedia . '</span>';
                      }
                  ?>
                    <tr>
                      <td>
                        <div class="table-media">
                          <span class="brand-icon"><i class="bi bi-book-half" aria-hidden="true"></i></span>
                          <span class="fw-semibold text-truncate" style="max-width: 230px;"><?= htmlspecialchars($buku['judul']); ?></span>
                        </div>
                      </td>
                      <td>
                        <span class="badge bg-light text-dark border px-3 py-2 fw-medium">
                          <?= htmlspecialchars($buku['nama_kategori'] ?? 'Umum'); ?>
                        </span>
                      </td>
                      <td>
                        <div class="d-flex flex-column">
                          <span><?= htmlspecialchars($buku['penulis']); ?></span>
                          <small class="text-muted fs-8"><?= htmlspecialchars($buku['tahun_terbit']); ?></small>
                        </div>
                      </td>
                      <td>
                        <span class="badge bg-light text-dark border px-3 py-2">
                          <i class="bi bi-bookshelf text-secondary me-1"></i><?= htmlspecialchars($buku['nama_rak'] ?? 'Tanpa Rak'); ?>
                        </span>
                      </td>
                      <td><?= $status_badge; ?></td>
                      <td class="text-end">
                        <a href="buku.php?id=<?= $buku['id_buku']; ?>" class="btn btn-light btn-sm fw-medium border shadow-sm px-3">
                          <i class="bi bi-eye me-1"></i> Detail
                        </a>
                      </td>
                    </tr>
                  <?php 
                    endwhile; 
                  else: 
                  ?>
                    <tr>
                      <td colspan="6" class="text-center py-4 text-muted">
                        <i class="bi bi-inboxes display-6 d-block mb-2"></i> Belum ada data koleksi buku di sistem.
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
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