<?php
session_start();
// 1. Proteksi Halaman: Pastikan yang masuk adalah admin
if (!isset($_SESSION['login_admin'])) {
    header("Location: ../index.php");
    exit;
}

// 2. Hubungkan ke database
include '../config/koneksi.php';

$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';

// 3. Ambil total pendaftaran anggota baru yang berstatus 'pending' untuk badge sidebar
$query_pending = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM anggota WHERE status_akun = 'pending'");
$data_pending  = mysqli_fetch_assoc($query_pending);
$anggota_pending = $data_pending['total'] ?? 0;

// ==========================================================
// LOGIKA PROSES PENGEMBALIAN BUKU DAN PENGUNCIAN DENDA FINAL
// ==========================================================
if (isset($_GET['aksi']) && $_GET['aksi'] == 'kembali' && isset($_GET['id'])) {
    $id_peminjaman    = mysqli_real_escape_string($mysqli, $_GET['id']);
    $tanggal_sekarang = date('Y-m-d'); 

    // Ambil data peminjaman untuk memeriksa tgl_jatuh_tempo
    $query_cek = mysqli_query($mysqli, "SELECT * FROM peminjaman WHERE id_peminjaman = '$id_peminjaman'");
    $data_pinjam = mysqli_fetch_assoc($query_cek);

    if ($data_pinjam) {
        $tgl_jatuh_tempo = $data_pinjam['tgl_jatuh_tempo'];

        // 1. Update status peminjaman menjadi 'kembali' dan set tanggal kembali
        $sql_kembali = "UPDATE peminjaman SET status = 'kembali', tgl_kembali = '$tanggal_sekarang' WHERE id_peminjaman = '$id_peminjaman'";
        mysqli_query($mysqli, $sql_kembali);

        // 2. Hitung denda final saat pengembalian dilakukan jika terlambat
        if ($tanggal_sekarang > $tgl_jatuh_tempo) {
            $tgl_sekarang_obj = new DateTime($tanggal_sekarang);
            $tenggat_obj      = new DateTime($tgl_jatuh_tempo);
            $selisih          = $tgl_sekarang_obj->diff($tenggat_obj);
            $jumlah_hari      = $selisih->days;

            $tarif_denda_per_hari = 5000; 
            $total_denda          = $jumlah_hari * $tarif_denda_per_hari;

            // Masukkan data ke tabel denda, kunci nominalnya, dan set status menjadi 'lunas'
            $cek_denda = mysqli_query($mysqli, "SELECT * FROM denda WHERE id_peminjaman = '$id_peminjaman'");
            if (mysqli_num_rows($cek_denda) > 0) {
                $sql_denda = "UPDATE denda SET jumlah_denda = '$total_denda', status_denda = 'lunas', tgl_bayar = '$tanggal_sekarang' WHERE id_peminjaman = '$id_peminjaman'";
            } else {
                $sql_denda = "INSERT INTO denda (id_peminjaman, jumlah_denda, status_denda, tgl_bayar) 
                              VALUES ('$id_peminjaman', '$total_denda', 'lunas', '$tanggal_sekarang')";
            }
            mysqli_query($mysqli, $sql_denda);
        }
    }
    
    header("Location: peminjaman.php");
    exit;
}

// 4. Logika Sorting via Header Tabel
$sort_by    = $_GET['by'] ?? 'tgl_pinjam';
$sort_order = $_GET['order'] ?? 'DESC';
$next_order = ($sort_order == 'ASC') ? 'DESC' : 'ASC';

// Tambahkan 'tgl_kembali' ke dalam kolom yang diizinkan
$allowed_columns = ['nama_lengkap', 'judul', 'tgl_pinjam', 'tgl_jatuh_tempo', 'tgl_kembali', 'status'];
$allowed_orders  = ['ASC', 'DESC'];

if (!in_array($sort_by, $allowed_columns)) { $sort_by = 'tgl_pinjam'; }
if (!in_array($sort_order, $allowed_orders)) { $sort_order = 'DESC'; }

if ($sort_by == 'nama_lengkap') { $sort_col = 'a.nama_lengkap'; }
elseif ($sort_by == 'judul') { $sort_col = 'b.judul'; }
else { $sort_col = 'p.' . $sort_by; }

// 5. Query Ambil Data Transaksi Peminjaman
$sql_peminjaman = "SELECT p.*, a.nama_lengkap, b.judul 
                   FROM peminjaman p
                   INNER JOIN anggota a ON p.id_anggota = a.id_anggota
                   INNER JOIN buku b ON p.id_buku = b.id_buku
                   ORDER BY $sort_col $sort_order";
$query_peminjaman = mysqli_query($mysqli, $sql_peminjaman);

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
  <title>PojokBaca | Peminjaman</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    th.sortable-header { cursor: pointer; white-space: nowrap; transition: background-color 0.2s; }
    th.sortable-header:hover { background-color: rgba(0, 0, 0, 0.04) !important; }
    th.sortable-header a { text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
  </style>
</head>

<body>
  <div class="admin-shell">
    <div class="sidebar-backdrop" data-sidebar-close></div>

    <aside class="admin-sidebar" id="adminSidebar">
      <div class="sidebar-header">
        <a class="brand-mark" href="dashboard.php">
          <span class="brand-icon"><i class="bi bi-book-half" aria-hidden="true"></i></span>
          <span class="brand-copy">
            <span class="brand-title">PojokBaca</span>
            <span class="brand-subtitle">Administrator</span>
          </span>
        </a>
      </div>
      <nav class="sidebar-nav">
        <a class="nav-link" href="dashboard.php"><span class="nav-icon"><i class="bi bi-speedometer2"></i></span><span class="nav-text">Dashboard</span></a>
        <a class="nav-link" href="katalog.php"><span class="nav-icon"><i class="bi bi-journal-text"></i></span><span class="nav-text">Data Buku</span></a>
        <a class="nav-link" href="anggota.php">
          <span class="nav-icon"><i class="bi bi-people"></i></span><span class="nav-text">Data Anggota</span>
          <?php if($anggota_pending > 0): ?>
            <span class="badge bg-warning text-dark ms-auto px-2 rounded-pill"><?= $anggota_pending; ?></span>
          <?php endif; ?>
        </a>
        <a class="nav-link active" href="peminjaman.php"><span class="nav-icon"><i class="bi bi-arrow-left-right"></i></span><span class="nav-text">Peminjaman</span></a>
        <a class="nav-link" href="denda.php"><span class="nav-icon"><i class="bi bi-cash-coin"></i></span><span class="nav-text">Data Denda</span></a>
        
        <div class="nav-item-dropdown">
          <a class="nav-link dropdown-toggle" href="#menuKelola" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="menuKelola">
            <span class="nav-icon"><i class="bi bi-gear"></i></span><span class="nav-text">Kelola</span>
          </a>
          <div class="collapse ms-3" id="menuKelola">
            <a class="nav-link py-1 small" href="kategori.php"><i class="bi bi-tags me-2"></i>Kategori</a>
            <a class="nav-link py-1 small" href="penerbit.php"><i class="bi bi-building me-2"></i>Penerbit</a>
            <a class="nav-link py-1 small" href="rak.php"><i class="bi bi-bookshelf me-2"></i>Data Rak</a>
          </div>
        </div>

        <hr class="mx-3 my-2 text-secondary opacity-25">
        <a class="nav-link text-danger" href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun anda?')"><span class="nav-icon"><i class="bi bi-box-arrow-left text-danger"></i></span><span class="nav-text fw-bold">Logout</span></a>
      </nav>

      <div class="sidebar-user d-none">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
        <strong><?= htmlspecialchars($nama_admin); ?></strong>
        <small>Admin</small>
      </div>
      <div class="sidebar-user">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
        <strong><?= htmlspecialchars($nama_admin); ?></strong>
        <small>Admin</small>
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
                <img class="avatar-img avatar-sm" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
                <span class="d-none d-sm-inline"><?= htmlspecialchars($nama_admin); ?></span>
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
          <div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="page-heading-copy">
              <span class="page-icon"><i class="bi bi-arrow-left-right"></i></span>
              <div>
                <p class="eyebrow mb-1">Sirkulasi Buku</p>
                <h1 class="h3 mb-1">Data Peminjaman</h1>
                <p class="text-muted mb-0">Manajemen sirkulasi peminjaman dan pengembalian buku perpustakaan.</p>
              </div>
            </div>
            <div>
              <a href="peminjaman-tambah.php" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Peminjaman</span>
              </a>
            </div>
          </div>

          <section class="panel mt-3">
            <div class="panel-header">
              <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-table"></i><span>Daftar Seluruh Peminjaman</span></h2>
              </div>
              <input class="form-control form-control-sm table-search" type="search" placeholder="Cari peminjaman..." data-table-search="peminjamanTable" style="max-width: 250px;">
            </div>
            
            <div class="table-responsive">
              <table class="table align-middle mb-0 table-hover" id="peminjamanTable">
                <thead>
                  <tr>
                    <th class="sortable-header">
                      <a href="peminjaman.php?by=nama_lengkap&order=<?= ($sort_by == 'nama_lengkap') ? $next_order : 'ASC'; ?>">
                        Nama Anggota <?= getSortIcon('nama_lengkap', $sort_by, $sort_order); ?>
                      </a>
                    </td>
                    <th class="sortable-header">
                      <a href="peminjaman.php?by=judul&order=<?= ($sort_by == 'judul') ? $next_order : 'ASC'; ?>">
                        Judul Buku <?= getSortIcon('judul', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="peminjaman.php?by=tgl_pinjam&order=<?= ($sort_by == 'tgl_pinjam') ? $next_order : 'ASC'; ?>">
                        Tanggal Pinjam <?= getSortIcon('tgl_pinjam', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="peminjaman.php?by=tgl_jatuh_tempo&order=<?= ($sort_by == 'tgl_jatuh_tempo') ? $next_order : 'ASC'; ?>">
                        Tenggat Waktu <?= getSortIcon('tgl_jatuh_tempo', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="peminjaman.php?by=tgl_kembali&order=<?= ($sort_by == 'tgl_kembali') ? $next_order : 'ASC'; ?>">
                        Tanggal Kembali <?= getSortIcon('tgl_kembali', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="peminjaman.php?by=status&order=<?= ($sort_by == 'status') ? $next_order : 'ASC'; ?>">
                        Status <?= getSortIcon('status', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="text-end" style="min-width: 150px;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  if (mysqli_num_rows($query_peminjaman) > 0): 
                    $hari_ini = date('Y-m-d');
                    while($row = mysqli_fetch_assoc($query_peminjaman)):
                      
                      // Penentuan Tanggal Kembali fisik
                      $tgl_kembali = ($row['tgl_kembali'] && $row['tgl_kembali'] != '0000-00-00') ? date('d/m/Y', strtotime($row['tgl_kembali'])) : '-';

                      // Logika Penentuan 3 Status (dipinjam, terlambat, kembali)
                      if ($row['status'] == 'kembali') {
                          $status_badge = '<span class="badge bg-success text-white px-3 py-1.5 small"><i class="bi bi-check-circle me-1"></i>Kembali</span>';
                      } elseif ($row['status'] == 'dipinjam' && $hari_ini > $row['tgl_jatuh_tempo']) {
                          $status_badge = '<span class="badge bg-danger text-white px-3 py-1.5 small"><i class="bi bi-exclamation-triangle me-1"></i>Terlambat</span>';
                      } else {
                          $status_badge = '<span class="badge bg-warning text-dark px-3 py-1.5 small"><i class="bi bi-hourglass-split me-1"></i>Dipinjam</span>';
                      }
                  ?>
                    <tr>
                      <td class="fw-medium text-primary"><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                      <td>
                        <div class="table-media">
                          <span class="brand-icon"><i class="bi bi-book-half" aria-hidden="true"></i></span>
                          <span class="text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($row['judul']); ?>"><?= htmlspecialchars($row['judul']); ?></span>
                        </div>
                      </td>
                      <td><?= date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                      <td class="fw-semibold"><?= date('d/m/Y', strtotime($row['tgl_jatuh_tempo'])); ?></td>
                      <td><?= $tgl_kembali; ?></td>
                      <td><?= $status_badge; ?></td>
                      <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                          <?php if ($row['status'] == 'dipinjam'): ?>
                            <a href="peminjaman.php?id=<?= $row['id_peminjaman']; ?>&aksi=kembali" class="btn btn-outline-primary btn-sm px-2 py-1" style="font-size: 0.8rem;" onclick="return confirm('Apakah Anda yakin ingin menyelesaikan peminjaman ini?')">
                              <i class="bi bi-arrow-left me-1"></i>Kembalikan
                            </a>
                          <?php else: ?>
                            <button class="btn btn-secondary btn-sm px-2 py-1" style="font-size: 0.8rem;" disabled>
                              <i class="bi bi-check2-all me-1"></i>Selesai
                            </button>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php 
                    endwhile; 
                  else: 
                  ?>
                    <tr>
                      <td colspan="7" class="text-center py-4 text-muted">
                        Tidak ada data peminjaman.
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
        <div class="container-fluid px-3 px-lg-4"><span>Copyright 2026 PojokBaca. All rights reserved.</span></div>
      </footer>
    </div>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
</body>
</html>