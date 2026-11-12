-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: silat_multimedia
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alat`
--

DROP TABLE IF EXISTS `alat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alat` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_alat` varchar(255) NOT NULL,
  `subjudul` varchar(60) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `stok` int(11) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `kelengkapan` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`kelengkapan`)),
  `aturan` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`aturan`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `alat_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alat`
--

LOCK TABLES `alat` WRITE;
/*!40000 ALTER TABLE `alat` DISABLE KEYS */;
INSERT INTO `alat` VALUES (1,'Sony Mirrorless Camera','Sony A6000','sony-mirrorless','Kamera mirrorless ringkas dengan sensor APS-C untuk foto dan video dokumentasi kegiatan sekolah. Ringan dibawa dan mudah dipakai, termasuk oleh pemula.',1,'sony','2026-11-04 00:29:19','2026-11-04 00:29:19','[\"Body kamera\",\"Lensa kit\",\"Baterai\",\"Charger\",\"Kartu memori\",\"Tali kamera\",\"Tas kamera\"]','[]'),(2,'Canon DSLR Camera','Canon EOS 1200D','canon-dslr','Kamera DSLR tingkat pemula untuk memotret acara, potret, dan dokumentasi. Dilengkapi jendela bidik optik dengan hasil gambar yang tajam.',1,'cannon','2026-11-04 00:29:19','2026-11-04 00:29:19','[\"Body kamera\",\"Lensa kit\",\"Baterai\",\"Charger\",\"Kartu memori\",\"Tali kamera\",\"Tas kamera\"]','[]'),(3,'Drone Dokumentasi 1','DJI Mini 2','drone-dokumentasi-1','Drone ringan dan ringkas untuk mengambil foto dan video dari udara. Cocok untuk dokumentasi kegiatan di area terbuka.',1,'drone1-','2026-11-04 00:29:19','2026-11-04 00:29:19','[\"Unit drone\",\"Remote controller\",\"Baterai\",\"Baling-baling cadangan\",\"Kabel pengisi daya\",\"Tas atau case penyimpanan\"]','[]'),(4,'Drone Dokumentasi 2','DJI Mavic Air 2','drone-dokumentasi-2','Drone lipat dengan kamera beresolusi tinggi untuk video udara yang stabil. Cocok untuk dokumentasi acara besar dan area yang luas.',1,'drone2-','2026-11-04 00:29:19','2026-11-04 00:29:19','[\"Unit drone\",\"Remote controller\",\"Baterai\",\"Baling-baling cadangan\",\"Kabel pengisi daya\",\"Tas atau case penyimpanan\"]','[]'),(5,'Handycam','Sony HDR-CX405','handycam','Kamera video untuk merekam acara dan kegiatan dalam durasi panjang, dengan zoom optik yang praktis digunakan.',1,'handycam','2026-11-04 00:29:19','2026-11-04 00:29:19','[\"Unit handycam\",\"Baterai\",\"Charger atau adaptor\",\"Kabel data\",\"Kartu memori\",\"Tas handycam\"]','[]'),(6,'Gimbal Stabilizer','Rekaman Mulus Saat Bergerak','gimbal-stabilizer','Penstabil gambar agar rekaman tetap mulus saat berjalan atau bergerak. Cocok untuk video liputan, vlog, dan dokumentasi kegiatan.',1,'gimbal','2026-11-04 00:29:19','2026-11-04 00:29:19','[\"Unit gimbal\",\"Baterai\",\"Kabel pengisi daya\",\"Plat dudukan (quick release)\",\"Tas penyimpanan\"]','[]'),(7,'Tripod Stabilizer','Penopang Kamera Dan Handphone','tripod','Penopang yang stabil untuk kamera dan handphone saat memotret atau merekam video, dengan tinggi yang dapat diatur.',1,'tripod','2026-11-04 00:29:19','2026-11-04 00:29:19','[\"Unit tripod\",\"Dudukan handphone\",\"Plat dudukan kamera\",\"Tas tripod\"]','[]'),(8,'Handphone Dokumentasi','iPhone 13','handphone-dokumentasi','Handphone untuk dokumentasi cepat, menghasilkan foto dan video berkualitas dari perangkat yang praktis dibawa ke mana saja.',1,'ipone','2026-11-04 00:29:19','2026-11-04 00:29:19','[\"Handphone iPhone 13\",\"Kabel pengisi daya\",\"Adaptor charger\",\"Casing pelindung\"]','[]');
/*!40000 ALTER TABLE `alat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_27_055733_create_alat_table',1),(5,'2026_09_27_055741_create_peminjaman_table',1),(6,'2026_10_11_121853_add_foto_profil_to_users_table',1),(7,'2026_10_21_013500_add_tanggal_dikembalikan_to_peminjaman_table',1),(8,'2026_11_04_000000_add_menunggu_pengambilan_status_to_peminjaman_table',1),(9,'2026_11_04_000001_add_catatan_pengambilan_to_peminjaman_table',1),(10,'2026_11_06_000000_add_kelengkapan_and_aturan_to_alat_table',1),(11,'2026_11_08_193820_add_subjudul_to_alat_table',1),(12,'2026_11_09_101509_add_status_akun_to_users_table',1),(13,'2026_11_09_212733_add_tanggal_diambil_and_modify_tanggal_dikembalikan_in_peminjaman_table',1),(14,'2026_11_09_101510_add_permintaan_hapus_pada_to_users_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `peminjaman`
--

DROP TABLE IF EXISTS `peminjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `peminjaman` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `alat_id` bigint(20) unsigned NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `tanggal_diambil` datetime DEFAULT NULL,
  `tanggal_dikembalikan` datetime DEFAULT NULL,
  `keperluan` text DEFAULT NULL,
  `status` enum('menunggu_persetujuan','menunggu_pengambilan','dipinjam','selesai','ditolak') NOT NULL DEFAULT 'menunggu_persetujuan',
  `catatan_admin` text DEFAULT NULL,
  `catatan_pengambilan` text DEFAULT NULL,
  `kondisi_alat_kembali` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `peminjaman_user_id_foreign` (`user_id`),
  KEY `peminjaman_alat_id_foreign` (`alat_id`),
  CONSTRAINT `peminjaman_alat_id_foreign` FOREIGN KEY (`alat_id`) REFERENCES `alat` (`id`) ON DELETE CASCADE,
  CONSTRAINT `peminjaman_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `peminjaman`
--

LOCK TABLES `peminjaman` WRITE;
/*!40000 ALTER TABLE `peminjaman` DISABLE KEYS */;
INSERT INTO `peminjaman` VALUES (1,4,7,'2026-11-05','2026-11-06','2026-11-05 15:05:00','2026-11-06 15:05:00','vsyvfwefqi','selesai','Catatan pengembalian: Kelengkapan sesuai.','dapodik','Baik','2026-11-05 07:09:53','2026-11-06 15:05:00'),(2,3,2,'2026-11-06','2026-11-19',NULL,NULL,'untuk dokumentasi kASDHUXJ','ditolak','bohong',NULL,NULL,'2026-11-06 10:00:00','2026-11-06 10:05:00');
/*!40000 ALTER TABLE `peminjaman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(255) NOT NULL,
  `nomor_induk` varchar(255) NOT NULL,
  `peran` enum('admin','siswa','guru_karyawan') NOT NULL DEFAULT 'siswa',
  `kelas_jurusan` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `pin` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `permintaan_hapus_pada` timestamp NULL DEFAULT NULL,
  `status_akun` varchar(20) NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_nomor_induk_unique` (`nomor_induk`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'ADMINISTRATOR SISTEM','00000','admin',NULL,'$2y$12$NorgDAuLrcLxjcIhV99qgueVKFZWbqY3YJJ2j00kj8aBmfqPzMPgq','$2y$12$ArHZirLmVB8MHxaBTQ4lB.fCSrDYvd7VLqgorDr8i.z5wjJxtvV56',NULL,'2026-11-03 22:33:01','2026-11-09 13:39:13',NULL,NULL,'aktif'),(2,'ZIDAN PRADANA KUSUMA','24087','siswa','XII RPL A','$2y$12$YXVQ7hi.Z35v2GdsvaHC3eHTi7CgSUXQHudoT6BZ9rrtBilplXP1e','$2y$12$SlPFhjBdwdcQf6lfQiMZI.Uuh2pjItJ2f1SnRM4bTU/qesifbXv6O',NULL,'2026-11-04 00:20:59','2026-11-10 11:14:37',NULL,NULL,'aktif'),(3,'DIANA NOVITASARI','08129697826','guru_karyawan',NULL,'$2y$12$PZhzbqb0uoop7yIzqs2j0.yPd1OfVidF/WnhZPKwh4OkKzl82EJKG','$2y$12$RS.I1cCzuKMAMlqSX1oh/.EDeU6nV92GxMYX0i6qaxMYvgR42.VxK',NULL,'2026-11-06 02:24:22','2026-11-09 11:14:58',NULL,NULL,'aktif'),(4,'MUHAMMAD RIDWAN ARROSYID','24075','siswa','XII RPL A','$2y$12$3v23dZDiqxEa4Am4KDcaSOTkPElUChG55XboqgQfn0GgJ2BFTRTYq','$2y$12$FeN5YH6fwJTa4.x/IiEfm.7TPZNF1welr7vwFTkR8MnuMV/px8lj.',NULL,'2026-11-10 07:07:20','2026-11-10 07:08:17',NULL,NULL,'aktif'),(5,'UJI COBA','12345','siswa','X DPIB B','$2y$12$WxPOGKUG05FyqK9EIg4gdO6j3OvxlSDNDYLithT8dc.yb8zQAOEUC','$2y$12$vvQOWGnEDIBbGivppEKXNe5Mz1Rshy1jJW5I5qPKWflkVznH6A6O2',NULL,'2026-11-10 11:09:39','2026-11-10 11:12:52','user-5-1794309172.jpg',NULL,'aktif');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-10 20:03:44
