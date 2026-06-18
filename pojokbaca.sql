-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 18, 2026 at 11:53 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pojokbaca`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `nama_admin` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `nama_admin`, `email`, `password`) VALUES
(1, 'Budi Santoso', 'budi@admin.com', 'budi123'),
(2, 'Siti Aminah', 'siti@admin.com', 'siti123'),
(3, 'Andi Wijaya', 'andi@admin.com', 'andi123');

-- --------------------------------------------------------

--
-- Table structure for table `anggota`
--

CREATE TABLE `anggota` (
  `id_anggota` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_telepon` varchar(15) NOT NULL,
  `alamat` text DEFAULT NULL,
  `status_akun` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `anggota`
--

INSERT INTO `anggota` (`id_anggota`, `nama_lengkap`, `email`, `password`, `no_telepon`, `alamat`, `status_akun`, `created_at`) VALUES
(2, 'Rian Hidayat', 'rian@gmail.com', 'rian1234', '081234567890', 'Jl. Merdeka No. 10, Jakarta', 'pending', '2026-06-16 13:30:20'),
(3, 'Dewi Lestari', 'dewi@gmail.com', 'dewi123', '085712345678', 'Jl. Mawar No. 4, Bandung', 'approved', '2026-06-16 13:30:20'),
(4, 'Eko Prasetyo', 'eko@gmail.com', 'eko123', '081987654321', 'Jl. Diponegoro No. 15, Surabaya', 'rejected', '2026-06-16 13:30:20');

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id_buku` int(11) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `id_penerbit` int(11) DEFAULT NULL,
  `isbn` varchar(20) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `tahun_terbit` year(4) DEFAULT NULL,
  `jumlah_halaman` int(11) DEFAULT NULL,
  `sinopsis` text DEFAULT NULL,
  `id_rak` int(11) DEFAULT NULL,
  `stok_total` int(11) DEFAULT 0,
  `stok_tersedia` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id_buku`, `id_kategori`, `id_penerbit`, `isbn`, `judul`, `penulis`, `tahun_terbit`, `jumlah_halaman`, `sinopsis`, `id_rak`, `stok_total`, `stok_tersedia`) VALUES
(1, 1, 1, '978-602-03-3160-7', 'Laskar Pelangi', 'Andrea Hirata', '2005', 529, 'Kisah penuh inspirasi tentang perjuangan sepuluh anak dari keluarga miskin di Pulau Belitong yang bersekolah di sebuah sekolah Muhammadiyah yang nyaris roboh. Ditengah segala keterbatasan fasilitas dan ancaman penutupan sekolah, mereka bersama dua guru yang luar biasa, Bu Muslimah dan Pak Harfan, membuktikan bahwa keterbatasan ekonomi bukanlah penghalang untuk merajut mimpi besar dan meraih masa depan.', 1, 10, 10),
(2, 1, 1, '978-979-22-3869-3', 'Bumi Manusia', 'Pramoedya Ananta Toer', '1980', 535, 'Berlatar belakang masa kolonial Hindia Belanda pada akhir abad ke-19, novel ini mengisahkan perjalanan hidup Minke, seorang pemuda pribumi yang cerdas dan revolusioner. Hubungan cintanya dengan Annelies, seorang gadis peranakan Indo-Belanda, membawanya masuk ke dalam pusaran konflik sosial, hukum kolonial yang tidak adil, dan pergulatan batin dalam melawan penindasan bangsa asing terhadap kaum pribumi.', 1, 5, 5),
(3, 1, 4, '978-602-291-663-5', 'Aroma Karsa', 'Dee Lestari', '2018', 528, 'Sebuah novel yang menggabungkan unsur romansa, misteri, dan fiksi ilmiah, berpusat pada tokoh Jati Wesi yang memiliki kemampuan penciuman luar biasa dan hidup di tempat pembuangan sampah. Kehidupannya berubah total ketika bertemu dengan Tanaya Paramitha yang membawanya masuk ke dalam obsesi pencarian tanaman mistis legendaris bernama Puspa Karsa, yang menyimpan rahasia besar sekaligus kutukan kuno.', 2, 8, 8),
(4, 1, 2, '978-602-385-529-2', 'Dilan 1990', 'Pidi Baiq', '2014', 332, 'Mengisahkan romansa masa remaja antara Milea dan Dilan, seorang panglima geng motor yang terkenal nakal namun memiliki cara unik, cerdas, dan romantis dalam mendekati wanita. Berlatar kota Bandung tahun 1990, cerita ini menyuguhkan nostalgia masa SMA yang manis, penuh canda tawa, konflik persahabatan antar geng, serta lika-liku kisah cinta yang sederhana namun membekas di hati.', 2, 15, 15),
(5, 2, 5, '978-602-0822-31-0', 'Catatan Seorang Demonstran', 'Soe Hok Gie', '2015', 494, 'Buku ini merupakan kumpulan catatan harian jujur dari seorang mahasiswa, aktivis, dan cendekiawan muda bernama Soe Hok Gie selama era pergolakan politik Indonesia tahun 1960-an. Di dalamnya terekam pandangan kritis, keresahan moral, kesepian batin, serta kecintaannya yang mendalam pada alam dan keadilan sosial, menjadikannya refleksi penting tentang integritas seorang pemuda.', 3, 4, 4),
(6, 3, 2, '978-979-433-851-3', 'Sejarah Ringkas Dunia', 'H.G. Wells', '2014', 420, 'Sebuah karya monumental yang merangkum seluruh jalannya sejarah peradaban manusia secara kronologis, mulai dari kemunculan makhluk purba hingga era modern abad ke-20. Buku ini mengupas bagaimana perang, agama, revolusi industri, sains, dan pemikiran politik saling memengaruhi dan membentuk dunia seperti yang kita tinggali saat ini dengan bahasa yang mudah dipahami.', 3, 6, 6),
(7, 3, 9, '978-602-6232-51-9', 'Arkeologi Indonesia', 'Soekmono', '2016', 250, 'Buku klasik karya arkeolog terkemuka ini menyajikan pandangan komprehensif mengenai sejarah penemuan, penggalian, dan pelestarian berbagai situs purbakala di Indonesia. Mulai dari candi-candi megah di Jawa hingga peninggalan prasejarah di berbagai daerah, pembaca diajak memahami warisan budaya nusantara serta metodologi ilmiah dalam merekonstruksi masa lalu.', 4, 4, 4),
(8, 4, 3, '978-623-01-0302-5', 'Pengenalan SQL untuk Pemula', 'Jubilee Enterprise', '2020', 180, 'Panduan komprehensif yang dirancang khusus untuk memandu pemula dalam memahami konsep basis data relasional dan menguasai bahasa SQL dari dasar. Buku ini membahas sintaks-sintaks utama seperti DDL, DML, hingga teknik manipulasi data kompleks melalui contoh kasus dunia nyata yang disajikan secara bertahap dan mudah dipraktikkan langsung.', 4, 7, 7),
(9, 4, 6, '978-602-04-9580-4', 'Dasar-Dasar Python', 'Yuniar Supardi', '2019', 220, 'Buku tutorial praktis yang menuntun pembaca untuk menguasai Python, salah satu bahasa pemrograman paling populer di dunia saat ini, dari tingkat paling dasar. Pembaca akan diajarkan mengenai instalasi perangkat lunak, pemahaman struktur data, logika percabangan dan perulangan, hingga pembuatan fungsi sederhana yang sangat berguna untuk memulai karir di bidang teknologi.', 5, 6, 6),
(10, 4, 6, '978-623-00-2411-5', 'Kecerdasan Buatan (AI)', 'M. Suyanto', '2021', 310, 'Membahas perkembangan mutakhir teknologi Kecerdasan Buatan (AI) beserta dampaknya terhadap berbagai sektor industri dan kehidupan manusia sehari-hari. Buku ini mengupas konsep Machine Learning, Deep Learning, serta etika pemanfaatan robotika, memberikan wawasan strategis bagi pembaca untuk mempersiapkan diri menghadapi era otomatisasi global masa depan.', 5, 5, 5),
(11, 4, 9, '978-602-6232-24-3', 'Belajar Jaringan Komputer', 'Iwan Sofana', '2017', 240, 'Menyajikan teori dasar dan implementasi praktis mengenai arsitektur jaringan komputer modern, mulai dari konsep LAN, WAN, hingga protokol internet. Dilengkapi dengan panduan konfigurasi perangkat keras, pengelolaan alamat IP, dan sistem keamanan jaringan, buku ini menjadi referensi wajib bagi mahasiswa IT maupun teknisi jaringan pemula.', 6, 8, 8),
(12, 5, 3, '978-979-29-6610-7', 'Metode Penelitian Pendidikan', 'Sugiyono', '2018', 450, 'Buku referensi utama bagi mahasiswa dan peneliti yang mengupas tuntas berbagai metode penelitian ilmiah, baik kuantitatif, kualitatif, maupun metode gabungan (mixed methods). Penulis menjabarkan secara sistematis mulai dari perumusan masalah, penyusunan hipotesis, teknik pengumpulan dan analisis data, hingga penyusunan laporan akhir penelitian yang valid.', 6, 10, 10),
(13, 6, 5, '978-602-899-761-4', 'Fiqih Sunnah', 'Sayyid Sabiq', '2016', 600, 'Sebuah kitab rujukan fikih ibadah dan muamalah yang disusun secara sistematis berdasarkan dalil-dalil sahih dari Al-Quran, Sunnah Nabi, serta ijma para ulama terkemuka. Buku ini menguraikan hukum Islam sehari-hari dengan pendekatan yang moderat, mudah dipahami, serta relevan untuk menjawab berbagai persoalan umat Islam di era kontemporer.', 7, 5, 4),
(14, 6, 2, '978-979-433-912-1', 'Islam Tuhan, Islam Manusia', 'Haidar Bagir', '2017', 280, 'Kumpulan esai reflektif yang mengajak pembaca untuk melihat Islam bukan sekadar sebagai rangkaian ritus formal, melainkan sebagai agama cinta yang memanusiakan manusia. Penulis menekankan pentingnya spiritualitas Islam yang inklusif, toleran, dan penuh kasih sayang dalam menghadapi realitas sosial serta keragaman budaya di dunia modern.', 7, 7, 7),
(15, 8, 8, '978-623-244-123-1', 'Panduan Seni Fotografi Lanskap', 'Andi Sukma', '2020', 160, 'Buku panduan visual yang mengupas tuntas teknik-teknik penting dalam menangkap keindahan alam terbuka menggunakan kamera digital maupun mirrorless. Mulai dari pemahaman komposisi, pengaturan pencahayaan pada momen golden hour, penggunaan filter lensa, hingga proses penyuntingan digital untuk menghasilkan foto pemandangan yang dramatis dan bernilai seni tinggi.', 8, 4, 4),
(16, 9, 1, '978-602-03-8821-2', 'Rich Dad Poor Dad', 'Robert T. Kiyosaki', '2016', 340, 'Sebuah buku keuangan pribadi legendaris yang mendobrak paradigma lama tentang uang melalui perbandingan pola pikir antara dua sosok ayah: ayah miskin dan ayah kaya. Pembaca diajak memahami perbedaan penting antara aset dan liabilitas, pentingnya kecerdasan finansial sejak dini, serta bagaimana cara membuat uang bekerja untuk kita melalui investasi dan bisnis.', 9, 12, 12),
(17, 9, 6, '978-602-04-5122-3', 'The Intelligent Investor', 'Benjamin Graham', '2018', 640, 'Dianggap sebagai alkitab dalam dunia investasi saham, buku ini menjabarkan filosofi value investing atau investasi berbasis nilai yang diperkenalkan oleh ekonom Benjamin Graham. Buku ini mengajarkan taktik melindungi modal dari kerugian besar, menganalisis nilai intrinsik perusahaan secara rasional, serta mengendalikan emosi dari fluktuasi pasar demi keuntungan jangka panjang.', 9, 6, 6),
(18, 10, 1, '978-602-06-3317-6', 'Atomic Habits', 'James Clear', '2019', 352, 'Menawarkan kerangka kerja yang terbukti secara ilmiah untuk membangun kebiasaan baik dan menghilangkan kebiasaan buruk melalui perubahan-perubahan kecil sebesar satu persen setiap harinya. Penulis menguraikan empat hukum perubahan perilaku yang mudah diterapkan guna menciptakan sistem hidup yang mendukung produktivitas, kesuksesan, dan kebahagiaan jangka panjang.', 10, 25, 25),
(19, 10, 1, '978-602-06-2319-1', 'Filosofi Teras', 'Henry Manampiring', '2018', 320, 'Sebuah panduan praktis menerapkan filsafat kuno Yunani-Romawi (Stoisisme atau Filosofi Teras) untuk mengatasi kecemasan, stres, dan emosi negatif dalam kehidupan modern. Buku ini mengajarkan cara melatih mental agar tetap tangguh, fokus pada hal-hal yang berada di bawah kendali kita, serta merespons segala tantangan hidup dengan pikiran yang tenang dan rasional.', 10, 14, 13),
(20, 10, 2, '978-602-441-143-5', 'Sebuah Seni untuk Bersikap Bodo Amat', 'Mark Manson', '2018', 256, 'Melalui gaya penulisan yang blak-blakan dan humoris, buku ini mengajak pembaca untuk berhenti memaksakan diri agar selalu menjadi positif dan sempurna di setiap waktu. Penulis menekankan pentingnya memilih hal-hal berharga yang layak dipedulikan, serta menerima segala keterbatasan dan kegagalan sebagai bagian alami untuk membentuk karakter diri yang lebih kuat.', 10, 18, 18);

-- --------------------------------------------------------

--
-- Table structure for table `denda`
--

CREATE TABLE `denda` (
  `id_denda` int(11) NOT NULL,
  `id_peminjaman` int(11) NOT NULL,
  `jumlah_denda` int(11) NOT NULL DEFAULT 0,
  `status_denda` enum('belum_bayar','lunas') DEFAULT 'belum_bayar',
  `tgl_bayar` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `denda`
--

INSERT INTO `denda` (`id_denda`, `id_peminjaman`, `jumlah_denda`, `status_denda`, `tgl_bayar`) VALUES
(1, 3, 15000, 'lunas', '2026-05-25'),
(6, 5, 50000, 'belum_bayar', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Fiksi'),
(2, 'Non Fiksi'),
(3, 'Sejarah'),
(4, 'Sains & Teknologi'),
(5, 'Pendidikan'),
(6, 'Agama'),
(7, 'Seni & Budaya'),
(8, 'Hobi & Gaya Hidup'),
(9, 'Ekonomi & Bisnis'),
(10, 'Pengembangan Diri'),
(11, 'Olahraga & Kesehatan');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_peminjaman` int(11) NOT NULL,
  `id_anggota` int(11) NOT NULL,
  `id_buku` int(11) NOT NULL,
  `id_admin_pinjam` int(11) NOT NULL,
  `id_admin_kembali` int(11) DEFAULT NULL,
  `tgl_pinjam` date NOT NULL,
  `tgl_jatuh_tempo` date NOT NULL,
  `tgl_kembali` date DEFAULT NULL,
  `status` enum('dipinjam','kembali','hilang','rusak') DEFAULT 'dipinjam'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id_peminjaman`, `id_anggota`, `id_buku`, `id_admin_pinjam`, `id_admin_kembali`, `tgl_pinjam`, `tgl_jatuh_tempo`, `tgl_kembali`, `status`) VALUES
(1, 3, 3, 1, 1, '2026-05-01', '2026-05-08', '2026-05-07', 'kembali'),
(2, 3, 7, 1, 2, '2026-05-10', '2026-05-17', '2026-05-15', 'kembali'),
(3, 3, 12, 2, 1, '2026-05-15', '2026-05-22', '2026-05-25', 'kembali'),
(4, 3, 18, 1, NULL, '2026-06-15', '2026-06-22', NULL, 'dipinjam'),
(5, 3, 5, 2, NULL, '2026-06-01', '2026-06-08', NULL, 'dipinjam');

-- --------------------------------------------------------

--
-- Table structure for table `penerbit`
--

CREATE TABLE `penerbit` (
  `id_penerbit` int(11) NOT NULL,
  `nama_penerbit` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penerbit`
--

INSERT INTO `penerbit` (`id_penerbit`, `nama_penerbit`) VALUES
(1, 'Gramedia Pustaka Utama'),
(2, 'Mizan Pustaka'),
(3, 'Erlangga'),
(4, 'Bentang Pustaka'),
(5, 'Republika Penerbit'),
(6, 'Elex Media Komputindo'),
(7, 'GagasMedia'),
(8, 'Penerbit Andi'),
(9, 'Informatika Bandung'),
(10, 'Bukune');

-- --------------------------------------------------------

--
-- Table structure for table `rak`
--

CREATE TABLE `rak` (
  `id_rak` int(11) NOT NULL,
  `nama_rak` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rak`
--

INSERT INTO `rak` (`id_rak`, `nama_rak`) VALUES
(1, 'Rak A-1'),
(2, 'Rak A-2'),
(3, 'Rak A-3'),
(4, 'Rak B-1'),
(5, 'Rak B-2'),
(6, 'Rak B-3'),
(7, 'Rak C-1'),
(8, 'Rak C-2'),
(9, 'Rak C-3'),
(10, 'Rak D-1'),
(11, 'Rak D-2');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id_anggota`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id_buku`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `id_kategori` (`id_kategori`),
  ADD KEY `fk_buku_penerbit` (`id_penerbit`),
  ADD KEY `fk_buku_rak` (`id_rak`);

--
-- Indexes for table `denda`
--
ALTER TABLE `denda`
  ADD PRIMARY KEY (`id_denda`),
  ADD KEY `id_peminjaman` (`id_peminjaman`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `id_anggota` (`id_anggota`),
  ADD KEY `id_buku` (`id_buku`),
  ADD KEY `id_admin_pinjam` (`id_admin_pinjam`),
  ADD KEY `id_admin_kembali` (`id_admin_kembali`);

--
-- Indexes for table `penerbit`
--
ALTER TABLE `penerbit`
  ADD PRIMARY KEY (`id_penerbit`);

--
-- Indexes for table `rak`
--
ALTER TABLE `rak`
  ADD PRIMARY KEY (`id_rak`),
  ADD UNIQUE KEY `nama_rak` (`nama_rak`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id_anggota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id_buku` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `denda`
--
ALTER TABLE `denda`
  MODIFY `id_denda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `penerbit`
--
ALTER TABLE `penerbit`
  MODIFY `id_penerbit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `rak`
--
ALTER TABLE `rak`
  MODIFY `id_rak` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `buku_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_buku_penerbit` FOREIGN KEY (`id_penerbit`) REFERENCES `penerbit` (`id_penerbit`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_buku_rak` FOREIGN KEY (`id_rak`) REFERENCES `rak` (`id_rak`) ON DELETE SET NULL;

--
-- Constraints for table `denda`
--
ALTER TABLE `denda`
  ADD CONSTRAINT `denda_ibfk_1` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjaman` (`id_peminjaman`) ON DELETE CASCADE;

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_3` FOREIGN KEY (`id_admin_pinjam`) REFERENCES `admin` (`id_admin`),
  ADD CONSTRAINT `peminjaman_ibfk_4` FOREIGN KEY (`id_admin_kembali`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
