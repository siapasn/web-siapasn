-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: cpns_tryout
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB-log

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
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `aksi` varchar(200) NOT NULL,
  `detail` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES (1,1,'Edit Akun','Memperbarui akun: irfandiricon1993@gmail.com (role: user)','::1','2026-05-04 09:13:53'),(2,1,'Edit Akun','Memperbarui akun: irfandiricon1993@gmail.com (role: user)','::1','2026-05-04 09:14:12'),(3,1,'Edit Akun','Memperbarui akun: irfandiricon1993@gmail.com (role: user)','::1','2026-05-04 09:15:54'),(4,1,'Simpan Konfigurasi Aplikasi','Memperbarui konfigurasi master aplikasi','::1','2026-05-07 00:29:25'),(5,1,'Simpan Konfigurasi Aplikasi','Memperbarui konfigurasi master aplikasi','::1','2026-05-07 00:33:33'),(6,1,'Simpan Konfigurasi Aplikasi','Memperbarui konfigurasi master aplikasi','::1','2026-05-07 18:23:33');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hasil_tryout`
--

DROP TABLE IF EXISTS `hasil_tryout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hasil_tryout` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sesi_tryout_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `tryout_id` int(10) unsigned NOT NULL,
  `skor_total` decimal(8,2) NOT NULL,
  `total_nilai` int(11) NOT NULL DEFAULT 0,
  `max_nilai` int(11) NOT NULL DEFAULT 0,
  `jumlah_benar` int(11) NOT NULL,
  `jumlah_salah` int(11) NOT NULL,
  `jumlah_kosong` int(11) NOT NULL,
  `detail_kategori` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'skor per kategori' CHECK (json_valid(`detail_kategori`)),
  `peringkat` int(11) DEFAULT NULL,
  `status_lulus` enum('lulus','tidak_lulus') DEFAULT NULL,
  `detail_passing_grade` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detail_passing_grade`)),
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hasil_sesi` (`sesi_tryout_id`),
  KEY `idx_hasil_user` (`user_id`),
  KEY `idx_hasil_tryout` (`tryout_id`),
  CONSTRAINT `fk_hasil_sesi` FOREIGN KEY (`sesi_tryout_id`) REFERENCES `sesi_tryout` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_hasil_tryout` FOREIGN KEY (`tryout_id`) REFERENCES `tryout` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_hasil_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hasil_tryout`
--

LOCK TABLES `hasil_tryout` WRITE;
/*!40000 ALTER TABLE `hasil_tryout` DISABLE KEYS */;
INSERT INTO `hasil_tryout` VALUES (1,6,6,2,100.00,30,30,4,0,0,'[{\"kategori_id\":\"1\",\"kategori_nama\":\"CPNS\",\"sub_kategori_id\":\"7\",\"sub_kategori_nama\":\"TIU\",\"tipe_soal\":\"POINT\",\"benar\":2,\"salah\":0,\"kosong\":0,\"total\":2,\"total_nilai\":10,\"max_nilai\":10,\"skor\":100},{\"kategori_id\":\"1\",\"kategori_nama\":\"CPNS\",\"sub_kategori_id\":\"6\",\"sub_kategori_nama\":\"TWK\",\"tipe_soal\":\"POINT\",\"benar\":2,\"salah\":0,\"kosong\":0,\"total\":2,\"total_nilai\":10,\"max_nilai\":10,\"skor\":100},{\"kategori_id\":\"1\",\"kategori_nama\":\"CPNS\",\"sub_kategori_id\":\"8\",\"sub_kategori_nama\":\"TKP\",\"tipe_soal\":\"SCORE\",\"benar\":2,\"salah\":0,\"kosong\":0,\"total\":2,\"total_nilai\":10,\"max_nilai\":10,\"skor\":100}]',1,'tidak_lulus','[{\"label\":\"TWK\",\"kategori_id\":\"1\",\"sub_kategori_id\":\"6\",\"tipe_soal\":\"POINT\",\"nilai_minimum\":70,\"total_nilai\":10,\"lulus\":false},{\"label\":\"TIU\",\"kategori_id\":\"1\",\"sub_kategori_id\":\"7\",\"tipe_soal\":\"POINT\",\"nilai_minimum\":65,\"total_nilai\":10,\"lulus\":false},{\"label\":\"TKP\",\"kategori_id\":\"1\",\"sub_kategori_id\":\"8\",\"tipe_soal\":\"SCORE\",\"nilai_minimum\":50,\"total_nilai\":10,\"lulus\":false}]','2026-05-09 13:50:49'),(2,7,6,2,73.33,22,30,3,1,0,'[{\"kategori_id\":\"1\",\"kategori_nama\":\"CPNS\",\"sub_kategori_id\":\"7\",\"sub_kategori_nama\":\"TIU\",\"tipe_soal\":\"POINT\",\"benar\":1,\"salah\":1,\"kosong\":0,\"total\":2,\"total_nilai\":5,\"max_nilai\":10,\"skor\":50},{\"kategori_id\":\"1\",\"kategori_nama\":\"CPNS\",\"sub_kategori_id\":\"6\",\"sub_kategori_nama\":\"TWK\",\"tipe_soal\":\"POINT\",\"benar\":2,\"salah\":0,\"kosong\":0,\"total\":2,\"total_nilai\":10,\"max_nilai\":10,\"skor\":100},{\"kategori_id\":\"1\",\"kategori_nama\":\"CPNS\",\"sub_kategori_id\":\"8\",\"sub_kategori_nama\":\"TKP\",\"tipe_soal\":\"SCORE\",\"benar\":2,\"salah\":0,\"kosong\":0,\"total\":2,\"total_nilai\":7,\"max_nilai\":10,\"skor\":70}]',2,'tidak_lulus','[{\"label\":\"TWK\",\"kategori_id\":\"1\",\"sub_kategori_id\":\"6\",\"tipe_soal\":\"POINT\",\"nilai_minimum\":70,\"total_nilai\":10,\"lulus\":false},{\"label\":\"TIU\",\"kategori_id\":\"1\",\"sub_kategori_id\":\"7\",\"tipe_soal\":\"POINT\",\"nilai_minimum\":65,\"total_nilai\":5,\"lulus\":false},{\"label\":\"TKP\",\"kategori_id\":\"1\",\"sub_kategori_id\":\"8\",\"tipe_soal\":\"SCORE\",\"nilai_minimum\":50,\"total_nilai\":7,\"lulus\":false}]','2026-05-09 13:52:57'),(3,8,6,2,46.67,14,30,1,3,0,'[{\"kategori_id\":\"1\",\"kategori_nama\":\"CPNS\",\"sub_kategori_id\":\"6\",\"sub_kategori_nama\":\"TWK\",\"tipe_soal\":\"POINT\",\"benar\":0,\"salah\":2,\"kosong\":0,\"total\":2,\"total_nilai\":0,\"max_nilai\":10,\"skor\":0},{\"kategori_id\":\"1\",\"kategori_nama\":\"CPNS\",\"sub_kategori_id\":\"7\",\"sub_kategori_nama\":\"TIU\",\"tipe_soal\":\"POINT\",\"benar\":1,\"salah\":1,\"kosong\":0,\"total\":2,\"total_nilai\":5,\"max_nilai\":10,\"skor\":50},{\"kategori_id\":\"1\",\"kategori_nama\":\"CPNS\",\"sub_kategori_id\":\"8\",\"sub_kategori_nama\":\"TKP\",\"tipe_soal\":\"SCORE\",\"benar\":2,\"salah\":0,\"kosong\":0,\"total\":2,\"total_nilai\":9,\"max_nilai\":10,\"skor\":90}]',3,'tidak_lulus','[{\"label\":\"TWK\",\"kategori_id\":\"1\",\"sub_kategori_id\":\"6\",\"tipe_soal\":\"POINT\",\"nilai_minimum\":70,\"total_nilai\":0,\"lulus\":false},{\"label\":\"TIU\",\"kategori_id\":\"1\",\"sub_kategori_id\":\"7\",\"tipe_soal\":\"POINT\",\"nilai_minimum\":65,\"total_nilai\":5,\"lulus\":false},{\"label\":\"TKP\",\"kategori_id\":\"1\",\"sub_kategori_id\":\"8\",\"tipe_soal\":\"SCORE\",\"nilai_minimum\":50,\"total_nilai\":9,\"lulus\":false}]','2026-05-09 13:53:51');
/*!40000 ALTER TABLE `hasil_tryout` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jawaban_user`
--

DROP TABLE IF EXISTS `jawaban_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jawaban_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sesi_tryout_id` int(10) unsigned NOT NULL,
  `soal_id` int(10) unsigned NOT NULL,
  `jawaban` char(1) DEFAULT NULL COMMENT 'null = tidak dijawab',
  `is_benar` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sesi_soal` (`sesi_tryout_id`,`soal_id`),
  KEY `idx_jawaban_sesi` (`sesi_tryout_id`),
  KEY `idx_jawaban_soal` (`soal_id`),
  CONSTRAINT `fk_jawaban_sesi` FOREIGN KEY (`sesi_tryout_id`) REFERENCES `sesi_tryout` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_jawaban_soal` FOREIGN KEY (`soal_id`) REFERENCES `soal` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jawaban_user`
--

LOCK TABLES `jawaban_user` WRITE;
/*!40000 ALTER TABLE `jawaban_user` DISABLE KEYS */;
INSERT INTO `jawaban_user` VALUES (1,6,3,'c',1,'2026-05-09 13:50:12','2026-05-09 13:50:12'),(2,6,5,'a',1,'2026-05-09 13:50:15','2026-05-09 13:50:15'),(3,6,4,'b',0,'2026-05-09 13:50:27','2026-05-09 13:50:27'),(4,6,2,'a',1,'2026-05-09 13:50:37','2026-05-09 13:50:38'),(5,6,6,'c',1,'2026-05-09 13:50:41','2026-05-09 13:50:41'),(6,6,7,'b',0,'2026-05-09 13:50:45','2026-05-09 13:50:46'),(7,7,3,'b',0,'2026-05-09 13:52:12','2026-05-09 13:52:13'),(8,7,5,'a',1,'2026-05-09 13:52:15','2026-05-09 13:52:15'),(9,7,6,'c',1,'2026-05-09 13:52:17','2026-05-09 13:52:17'),(10,7,4,'a',0,'2026-05-09 13:52:19','2026-05-09 13:52:19'),(11,7,2,'a',1,'2026-05-09 13:52:30','2026-05-09 13:52:30'),(12,7,7,'b',0,'2026-05-09 13:52:41','2026-05-09 13:52:41'),(13,8,3,'a',0,'2026-05-09 13:53:14','2026-05-09 13:53:15'),(14,8,5,'c',0,'2026-05-09 13:53:17','2026-05-09 13:53:17'),(15,8,4,'b',0,'2026-05-09 13:53:24','2026-05-09 13:53:24'),(16,8,6,'c',1,'2026-05-09 13:53:29','2026-05-09 13:53:29'),(17,8,7,'c',0,'2026-05-09 13:53:36','2026-05-09 13:53:36'),(18,8,2,'d',0,'2026-05-09 13:53:40','2026-05-09 13:53:51');
/*!40000 ALTER TABLE `jawaban_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategori`
--

DROP TABLE IF EXISTS `kategori`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kategori` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT 'self-referencing FK',
  `tipe_soal` enum('SCORE','POINT') DEFAULT NULL COMMENT 'Tipe penilaian: SCORE=pilihan ganda kunci jawaban, POINT=nilai per pilihan. Hanya untuk sub-kategori.',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kategori_parent` (`parent_id`),
  CONSTRAINT `fk_kategori_parent` FOREIGN KEY (`parent_id`) REFERENCES `kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori`
--

LOCK TABLES `kategori` WRITE;
/*!40000 ALTER TABLE `kategori` DISABLE KEYS */;
INSERT INTO `kategori` VALUES (1,'CPNS',NULL,NULL,'2026-05-05 23:36:15','2026-05-05 23:40:59'),(2,'SEKDIN',NULL,NULL,'2026-05-05 23:37:00','2026-05-05 23:41:13'),(3,'PPPK',NULL,NULL,'2026-05-05 23:38:29','2026-05-05 23:38:29'),(4,'BUMN',NULL,NULL,'2026-05-05 23:39:05','2026-05-05 23:39:05'),(6,'TWK',1,'POINT','2026-05-06 00:26:42','2026-05-06 16:12:31'),(7,'TIU',1,'POINT','2026-05-06 00:27:01','2026-05-06 16:12:37'),(8,'TKP',1,'SCORE','2026-05-06 00:27:11','2026-05-06 16:12:43');
/*!40000 ALTER TABLE `kategori` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mapping_soal`
--

DROP TABLE IF EXISTS `mapping_soal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mapping_soal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tryout_id` int(10) unsigned NOT NULL,
  `soal_id` int(10) unsigned NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tryout_soal` (`tryout_id`,`soal_id`),
  KEY `idx_mapping_soal_tryout` (`tryout_id`),
  KEY `idx_mapping_soal_soal` (`soal_id`),
  CONSTRAINT `fk_mapping_soal_soal` FOREIGN KEY (`soal_id`) REFERENCES `soal` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_mapping_soal_tryout` FOREIGN KEY (`tryout_id`) REFERENCES `tryout` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mapping_soal`
--

LOCK TABLES `mapping_soal` WRITE;
/*!40000 ALTER TABLE `mapping_soal` DISABLE KEYS */;
INSERT INTO `mapping_soal` VALUES (5,1,4,2,NULL),(6,1,3,1,NULL),(7,1,2,3,NULL),(8,1,5,4,NULL),(9,2,3,1,NULL),(10,2,5,2,NULL),(11,2,6,3,NULL),(12,2,4,4,NULL),(13,2,7,5,NULL),(14,2,2,6,NULL),(15,3,2,1,NULL),(16,3,3,2,NULL),(17,3,4,3,NULL),(18,3,6,4,NULL),(19,3,7,5,NULL),(20,3,5,6,NULL);
/*!40000 ALTER TABLE `mapping_soal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mapping_tryout`
--

DROP TABLE IF EXISTS `mapping_tryout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mapping_tryout` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `produk_id` int(10) unsigned NOT NULL,
  `tryout_id` int(10) unsigned NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_produk_tryout` (`produk_id`,`tryout_id`),
  KEY `idx_mapping_tryout_produk` (`produk_id`),
  KEY `idx_mapping_tryout_tryout` (`tryout_id`),
  CONSTRAINT `fk_mapping_tryout_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_mapping_tryout_tryout` FOREIGN KEY (`tryout_id`) REFERENCES `tryout` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mapping_tryout`
--

LOCK TABLES `mapping_tryout` WRITE;
/*!40000 ALTER TABLE `mapping_tryout` DISABLE KEYS */;
INSERT INTO `mapping_tryout` VALUES (1,1,1,1,NULL),(3,2,2,2,NULL),(5,3,2,1,NULL),(6,3,1,2,NULL);
/*!40000 ALTER TABLE `mapping_tryout` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_aplikasi`
--

DROP TABLE IF EXISTS `master_aplikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_aplikasi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL,
  `config_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_config_key` (`config_key`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_aplikasi`
--

LOCK TABLES `master_aplikasi` WRITE;
/*!40000 ALTER TABLE `master_aplikasi` DISABLE KEYS */;
INSERT INTO `master_aplikasi` VALUES (1,'app_name','SiapASN Simulation Center','2026-05-07 00:26:13'),(2,'app_description','','2026-05-07 00:26:13'),(3,'app_logo','uploads/logo/1778088565_999017c97022d2986306.png','2026-05-07 00:26:13'),(4,'midtrans_server_key','SB-Mid-server-rhNLP8nLPcXaMSOq02iSsIgc','2026-05-07 00:26:13'),(5,'midtrans_client_key','SB-Mid-client-w-kAkauiuTnnwXpW','2026-05-07 00:26:13'),(6,'midtrans_environment','sandbox','2026-05-07 00:26:13'),(7,'email_host','smtp.gmail.com','2026-05-07 00:26:13'),(8,'email_port','587','2026-05-07 00:26:13'),(9,'email_username','siapasnsimulationcenter@gmail.com','2026-05-07 00:26:13'),(10,'email_password','nfzp mpfr baqv bles','2026-05-07 00:26:13'),(11,'email_encryption','tls','2026-05-07 00:26:13'),(12,'email_from','siapasnsimulationcenter@gmail.com','2026-05-07 00:26:13'),(13,'email_from_name','SiapASN Simulation Center','2026-05-07 00:26:13'),(14,'session_timeout','60','2026-05-07 00:26:13'),(15,'midtrans_url','https://api.sandbox.midtrans.com/v2/charge','2026-05-07 00:30:07'),(16,'midtrans_merchant_id','G406304292','2026-05-07 00:30:07'),(17,'redis_socket','/home/bimz2241/tmp/redis.sock','2026-05-07 00:46:48'),(18,'redis_host','127.0.0.1','2026-05-07 00:46:48'),(19,'redis_port','6379','2026-05-07 00:46:48'),(20,'redis_password','','2026-05-07 00:46:48'),(21,'redis_db','0','2026-05-07 00:46:48'),(22,'cron_api_key','36446d37b2778f7944ce32a09febd6ee6bef4f13ed6161e002f3b4261818ed90','2026-05-07 15:23:38');
/*!40000 ALTER TABLE `master_aplikasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_data_file`
--

DROP TABLE IF EXISTS `master_data_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_data_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(200) NOT NULL,
  `path` varchar(500) NOT NULL,
  `tipe` varchar(50) DEFAULT NULL,
  `ukuran` int(11) DEFAULT NULL COMMENT 'file size in bytes',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_data_file`
--

LOCK TABLES `master_data_file` WRITE;
/*!40000 ALTER TABLE `master_data_file` DISABLE KEYS */;
INSERT INTO `master_data_file` VALUES (1,'SiapASN.png','uploads/datafile/1777999905_587d3e6a3b910abd5ca4.png','png',1153947,NULL);
/*!40000 ALTER TABLE `master_data_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_mapping`
--

DROP TABLE IF EXISTS `menu_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_mapping` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role` enum('user','admin','super_admin') NOT NULL,
  `menu_key` varchar(100) NOT NULL,
  `label` varchar(100) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `url` varchar(200) DEFAULT NULL,
  `parent_key` varchar(100) DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_mapping`
--

LOCK TABLES `menu_mapping` WRITE;
/*!40000 ALTER TABLE `menu_mapping` DISABLE KEYS */;
INSERT INTO `menu_mapping` VALUES (1,'user','dashboard','Dashboard','bi-house','user/dashboard',NULL,1,1,'2026-05-05 23:25:33'),(2,'user','produk','Paket Tryout','bi-box','user/produk',NULL,2,1,'2026-05-05 23:25:33'),(3,'user','transaksi','Transaksi','bi-receipt','user/transaksi',NULL,3,1,'2026-05-05 23:25:33'),(4,'user','tryout','Paket Saya','bi-journal-bookmark','user/tryout',NULL,4,1,'2026-05-05 23:25:33'),(5,'admin','dashboard','Dashboard','bi-speedometer2','admin/dashboard',NULL,1,1,'2026-05-05 23:25:35'),(6,'admin','master','Master Data','bi-database','#',NULL,2,1,'2026-05-05 23:25:35'),(7,'admin','mapping','Mapping','bi-diagram-3','#',NULL,3,1,'2026-05-05 23:25:35'),(8,'admin','promosi','Promosi','bi-megaphone','admin/promosi',NULL,4,1,'2026-05-05 23:25:35'),(9,'admin','voucher','Voucher','bi-ticket-perforated','admin/voucher',NULL,5,1,'2026-05-05 23:25:35'),(10,'admin','laporan','Laporan','bi-file-earmark-bar-graph','admin/laporan/transaksi',NULL,6,1,'2026-05-05 23:25:35'),(11,'admin','menu_mapping','Menu Mapping','bi-layout-sidebar','admin/menu-mapping',NULL,7,1,'2026-05-05 23:25:35'),(12,'admin','master_user','Master User','bi-people','admin/master/user','master',1,1,'2026-05-05 23:25:35'),(13,'admin','master_kategori','Kategori','bi-tags','admin/master/kategori','master',2,1,'2026-05-05 23:25:35'),(14,'admin','master_soal','Soal','bi-question-circle','admin/master/soal','master',3,1,'2026-05-05 23:25:35'),(15,'admin','master_tryout','Tryout','bi-journal-text','admin/master/tryout','master',4,1,'2026-05-05 23:25:35'),(16,'admin','master_produk','Produk','bi-bag','admin/master/produk','master',5,1,'2026-05-05 23:25:35'),(17,'admin','master_passing_grade','Passing Grade','bi-bar-chart','admin/master/passing-grade','master',6,1,'2026-05-05 23:25:35'),(18,'admin','master_datafile','Data File','bi-file-earmark','admin/master/datafile','master',7,1,'2026-05-05 23:25:35'),(19,'admin','mapping_soal','Mapping Soal','bi-link','admin/mapping/soal','mapping',1,1,'2026-05-05 23:25:35'),(20,'admin','mapping_tryout','Mapping Tryout','bi-link-45deg','admin/mapping/tryout','mapping',2,1,'2026-05-05 23:25:35'),(21,'super_admin','dashboard','Dashboard','bi-speedometer2','admin/dashboard',NULL,1,1,'2026-05-05 23:25:38'),(22,'super_admin','master','Master Data','bi-database','#',NULL,2,1,'2026-05-05 23:25:38'),(23,'super_admin','mapping','Mapping','bi-diagram-3','#',NULL,3,1,'2026-05-05 23:25:38'),(24,'super_admin','promosi','Promosi','bi-megaphone','admin/promosi',NULL,4,1,'2026-05-05 23:25:38'),(25,'super_admin','voucher','Voucher','bi-ticket-perforated','admin/voucher',NULL,5,1,'2026-05-05 23:25:38'),(26,'super_admin','laporan','Laporan','bi-file-earmark-bar-graph','admin/laporan/transaksi',NULL,6,1,'2026-05-05 23:25:38'),(27,'super_admin','menu_mapping','Menu Mapping','bi-layout-sidebar','admin/menu-mapping',NULL,7,1,'2026-05-05 23:25:38'),(28,'super_admin','audit_log','Audit Log','bi-shield-check','superadmin/audit-log',NULL,8,1,'2026-05-05 23:25:38'),(29,'super_admin','akun','Manajemen Akun','bi-person-gear','superadmin/akun',NULL,9,1,'2026-05-05 23:25:38'),(30,'super_admin','backup','Backup & Restore','bi-cloud-download','superadmin/backup',NULL,10,1,'2026-05-05 23:25:38'),(31,'super_admin','master_user','Master User','bi-people','admin/master/user','master',1,1,'2026-05-05 23:25:38'),(32,'super_admin','master_kategori','Kategori','bi-tags','admin/master/kategori','master',2,1,'2026-05-05 23:25:38'),(33,'super_admin','master_soal','Soal','bi-question-circle','admin/master/soal','master',3,1,'2026-05-05 23:25:38'),(34,'super_admin','master_tryout','Tryout','bi-journal-text','admin/master/tryout','master',4,1,'2026-05-05 23:25:38'),(35,'super_admin','master_produk','Produk','bi-bag','admin/master/produk','master',5,1,'2026-05-05 23:25:38'),(36,'super_admin','master_passing_grade','Passing Grade','bi-bar-chart','admin/master/passing-grade','master',6,1,'2026-05-05 23:25:38'),(37,'super_admin','master_datafile','Data File','bi-file-earmark','admin/master/datafile','master',7,1,'2026-05-05 23:25:38'),(38,'super_admin','master_aplikasi','Master Aplikasi','bi-gear','superadmin/master-aplikasi','master',8,1,'2026-05-05 23:25:38'),(39,'super_admin','mapping_soal','Mapping Soal','bi-link','admin/mapping/soal','mapping',1,1,'2026-05-05 23:25:38'),(40,'super_admin','mapping_tryout','Mapping Tryout','bi-link-45deg','admin/mapping/tryout','mapping',2,1,'2026-05-05 23:25:38'),(41,'admin','web_content','Web Content','bi-file-richtext','/admin/konten',NULL,8,1,'2026-05-16 17:04:29'),(42,'super_admin','web_content','Web Content','bi-file-richtext','/admin/konten',NULL,8,1,'2026-05-16 17:04:29');
/*!40000 ALTER TABLE `menu_mapping` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` text NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2024-01-01-000001','App\\Database\\Migrations\\CreateUsersTable','default','App',1778003018,1),(2,'2024-01-01-000002','App\\Database\\Migrations\\CreateKategoriTable','default','App',1778003018,1),(3,'2024-01-01-000003','App\\Database\\Migrations\\CreateMasterDataFileTable','default','App',1778003018,1),(4,'2024-01-01-000004','App\\Database\\Migrations\\CreateSoalTable','default','App',1778003018,1),(5,'2024-01-01-000005','App\\Database\\Migrations\\CreateTryoutTable','default','App',1778003018,1),(6,'2024-01-01-000006','App\\Database\\Migrations\\CreateMappingSoalTable','default','App',1778003018,1),(7,'2024-01-01-000007','App\\Database\\Migrations\\CreateProdukTable','default','App',1778003018,1),(8,'2024-01-01-000008','App\\Database\\Migrations\\CreateMappingTryoutTable','default','App',1778003018,1),(9,'2024-01-01-000009','App\\Database\\Migrations\\CreateVoucherTable','default','App',1778003018,1),(10,'2024-01-01-000010','App\\Database\\Migrations\\CreateTransaksiTable','default','App',1778003018,1),(11,'2024-01-01-000011','App\\Database\\Migrations\\CreateUserProdukTable','default','App',1778003018,1),(12,'2024-01-01-000012','App\\Database\\Migrations\\CreateSesiTryoutTable','default','App',1778003018,1),(13,'2024-01-01-000013','App\\Database\\Migrations\\CreateJawabanUserTable','default','App',1778003018,1),(14,'2024-01-01-000014','App\\Database\\Migrations\\CreateHasilTryoutTable','default','App',1778003018,1),(15,'2024-01-01-000015','App\\Database\\Migrations\\CreatePromosiTable','default','App',1778003018,1),(16,'2024-01-01-000016','App\\Database\\Migrations\\CreatePassingGradeTable','default','App',1778003018,1),(17,'2024-01-01-000017','App\\Database\\Migrations\\CreateMenuMappingTable','default','App',1778003018,1),(18,'2024-01-01-000018','App\\Database\\Migrations\\CreateMasterAplikasiTable','default','App',1778003018,1),(19,'2024-01-01-000019','App\\Database\\Migrations\\CreateAuditLogTable','default','App',1778003018,1),(20,'2024-01-01-000020','App\\Database\\Migrations\\CreatePasswordResetsTable','default','App',1778003018,1),(21,'2024-01-02-000001','App\\Database\\Migrations\\AddSubKategoriToSoal','default','App',1778003018,2),(22,'2024-01-03-000001','App\\Database\\Migrations\\AddTipeSoalToKategori','default','App',1778925869,3),(23,'2024-01-04-000001','App\\Database\\Migrations\\AddSubKategoriToPassingGrade','default','App',1778925869,3),(24,'2024-01-05-000001','App\\Database\\Migrations\\AddThumbnailToProduk','default','App',1778925869,3),(25,'2026-05-16-000001','App\\Database\\Migrations\\CreateWebContentTable','default','App',1778925869,3),(26,'2026-05-16-000002','App\\Database\\Migrations\\AddWebContentMenu','default','App',1778925869,3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `passing_grade`
--

DROP TABLE IF EXISTS `passing_grade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `passing_grade` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tryout_id` int(10) unsigned DEFAULT NULL,
  `kategori_id` int(10) unsigned DEFAULT NULL,
  `sub_kategori_id` int(10) unsigned DEFAULT NULL,
  `nilai_minimum` decimal(8,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pg_tryout` (`tryout_id`),
  KEY `idx_pg_kategori` (`kategori_id`),
  KEY `fk_pg_sub_kategori` (`sub_kategori_id`),
  CONSTRAINT `fk_pg_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pg_sub_kategori` FOREIGN KEY (`sub_kategori_id`) REFERENCES `kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pg_tryout` FOREIGN KEY (`tryout_id`) REFERENCES `tryout` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `passing_grade`
--

LOCK TABLES `passing_grade` WRITE;
/*!40000 ALTER TABLE `passing_grade` DISABLE KEYS */;
INSERT INTO `passing_grade` VALUES (3,NULL,1,6,70.00,'2026-05-06 22:34:58','2026-05-06 22:38:54'),(4,NULL,1,7,65.00,'2026-05-06 22:39:16','2026-05-06 22:39:16'),(5,NULL,1,8,50.00,'2026-05-09 02:00:53','2026-05-09 02:00:53');
/*!40000 ALTER TABLE `passing_grade` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL COMMENT 'used to enforce 60-minute expiry',
  PRIMARY KEY (`id`),
  KEY `idx_pr_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (1,'irfandi.ricon@gmail.com','44e69149ec25ed372176f16bee677677fcc6a9196ed366501d320cc28c6f87b4','2026-05-03 22:41:24'),(6,'irfandiricon1993@gmail.com','278b5f90255bd469d7605792703f0e7b41e0e506d3412cde2a201f22395ac9ac','2026-05-07 18:24:01');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produk`
--

DROP TABLE IF EXISTS `produk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `produk` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(200) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `harga` decimal(12,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk`
--

LOCK TABLES `produk` WRITE;
/*!40000 ALTER TABLE `produk` DISABLE KEYS */;
INSERT INTO `produk` VALUES (1,'Simulasi CPNS Dasar','<p><br></p>','1778086769_3613c6b8501c5f99b5f1.png',80000.00,1,'2026-05-06 21:47:28','2026-05-07 22:50:49'),(2,'Simulasi CPNS Lanjutan','<p><br></p>',NULL,50000.00,1,'2026-05-07 00:35:00','2026-05-07 22:50:37'),(3,'Simulasi CPNS Gabungan','<p><br></p>',NULL,30000.00,1,'2026-05-07 17:02:27','2026-05-07 22:50:21');
/*!40000 ALTER TABLE `produk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promosi`
--

DROP TABLE IF EXISTS `promosi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `promosi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `produk_id` int(10) unsigned NOT NULL,
  `nama` varchar(200) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `jenis_diskon` enum('persentase','nominal') NOT NULL,
  `nilai_diskon` decimal(12,2) NOT NULL,
  `mulai_at` datetime NOT NULL,
  `berakhir_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_promosi_produk` (`produk_id`),
  CONSTRAINT `fk_promosi_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promosi`
--

LOCK TABLES `promosi` WRITE;
/*!40000 ALTER TABLE `promosi` DISABLE KEYS */;
INSERT INTO `promosi` VALUES (1,1,'HUT RI 80','','persentase',70.00,'2026-05-07 00:00:00','2026-05-10 23:59:00',1,'2026-05-07 00:36:30','2026-05-07 00:36:30');
/*!40000 ALTER TABLE `promosi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesi_tryout`
--

DROP TABLE IF EXISTS `sesi_tryout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesi_tryout` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `tryout_id` int(10) unsigned NOT NULL,
  `mulai_at` datetime NOT NULL,
  `selesai_at` datetime DEFAULT NULL,
  `status` enum('berlangsung','selesai','timeout') NOT NULL DEFAULT 'berlangsung',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sesi_tryout_user` (`user_id`),
  KEY `idx_sesi_tryout_tryout` (`tryout_id`),
  CONSTRAINT `fk_sesi_tryout_tryout` FOREIGN KEY (`tryout_id`) REFERENCES `tryout` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_sesi_tryout_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesi_tryout`
--

LOCK TABLES `sesi_tryout` WRITE;
/*!40000 ALTER TABLE `sesi_tryout` DISABLE KEYS */;
INSERT INTO `sesi_tryout` VALUES (6,6,2,'2026-05-09 13:50:09','2026-05-09 13:50:49','selesai','2026-05-09 13:50:09'),(7,6,2,'2026-05-09 13:52:10','2026-05-09 13:52:57','selesai','2026-05-09 13:52:10'),(8,6,2,'2026-05-09 13:53:12','2026-05-09 13:53:51','selesai','2026-05-09 13:53:12');
/*!40000 ALTER TABLE `sesi_tryout` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `soal`
--

DROP TABLE IF EXISTS `soal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `soal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kategori_id` int(10) unsigned NOT NULL,
  `sub_kategori_id` int(10) unsigned DEFAULT NULL,
  `pertanyaan` text NOT NULL,
  `pilihan_a` text NOT NULL,
  `pilihan_b` text NOT NULL,
  `pilihan_c` text NOT NULL,
  `pilihan_d` text NOT NULL,
  `pilihan_e` text DEFAULT NULL,
  `nilai_a` tinyint(1) unsigned DEFAULT NULL COMMENT 'Nilai pilihan A (1-5, tipe POINT)',
  `nilai_b` tinyint(1) unsigned DEFAULT NULL COMMENT 'Nilai pilihan B (1-5, tipe POINT)',
  `nilai_c` tinyint(1) unsigned DEFAULT NULL COMMENT 'Nilai pilihan C (1-5, tipe POINT)',
  `nilai_d` tinyint(1) unsigned DEFAULT NULL COMMENT 'Nilai pilihan D (1-5, tipe POINT)',
  `nilai_e` tinyint(1) unsigned DEFAULT NULL COMMENT 'Nilai pilihan E (1-5, tipe POINT)',
  `kunci_jawaban` char(1) DEFAULT NULL COMMENT 'a/b/c/d/e — null untuk tipe POINT',
  `pembahasan` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_soal_kategori` (`kategori_id`),
  KEY `fk_soal_sub_kategori` (`sub_kategori_id`),
  CONSTRAINT `fk_soal_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_soal_sub_kategori` FOREIGN KEY (`sub_kategori_id`) REFERENCES `kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `soal`
--

LOCK TABLES `soal` WRITE;
/*!40000 ALTER TABLE `soal` DISABLE KEYS */;
INSERT INTO `soal` VALUES (2,1,6,'Pancasila sebagai dasar negara Indonesia pertama kali dirumuskan dalam sidang BPUPKI. Siapakah yang mengusulkan nama \"Pancasila\"?','Ir. Soekarno','Drs. Mohammad Hatta','Mr. Soepomo','Mr. Muhammad Yamin',NULL,NULL,NULL,NULL,NULL,NULL,'a','Ir. Soekarno mengusulkan nama Pancasila pada sidang BPUPKI tanggal 1 Juni 1945.','2026-05-06 21:17:22','2026-05-06 21:17:22'),(3,1,7,'Jika 3x + 7 = 22, maka nilai x adalah...','3','4','5','6',NULL,NULL,NULL,NULL,NULL,NULL,'c','3x = 22 - 7 = 15, maka x = 15/3 = 5.','2026-05-06 21:17:22','2026-05-06 21:17:22'),(4,1,8,'Rekan kerja Anda meminta bantuan menyelesaikan tugasnya karena ia sedang sakit. Padahal Anda sendiri juga memiliki pekerjaan yang harus diselesaikan hari ini. Apa yang Anda lakukan?','Menolak karena pekerjaan saya sendiri belum selesai','Membantu sebagian sambil tetap menyelesaikan pekerjaan saya','Membantu sepenuhnya dan menunda pekerjaan saya','Melaporkan kepada atasan agar rekan kerja mendapat bantuan lain','Mengabaikan permintaan tersebut',2,5,4,3,1,NULL,'Pilihan B mencerminkan keseimbangan antara empati dan tanggung jawab pribadi.','2026-05-06 21:17:22','2026-05-06 21:17:22'),(5,1,6,'Pancasila sebagai dasar negara Indonesia pertama kali dirumuskan dalam sidang BPUPKI. Siapakah yang mengusulkan nama \"Pancasila\"?','Ir. Soekarno','Drs. Mohammad Hatta','Mr. Soepomo','Mr. Muhammad Yamin',NULL,NULL,NULL,NULL,NULL,NULL,'a','Ir. Soekarno mengusulkan nama Pancasila pada sidang BPUPKI tanggal 1 Juni 1945.','2026-05-06 23:34:30','2026-05-06 23:34:30'),(6,1,7,'Jika 3x + 7 = 22, maka nilai x adalah...','3','4','5','6',NULL,NULL,NULL,NULL,NULL,NULL,'c','3x = 22 - 7 = 15, maka x = 15/3 = 5.','2026-05-06 23:34:30','2026-05-06 23:34:30'),(7,1,8,'Rekan kerja Anda meminta bantuan menyelesaikan tugasnya karena ia sedang sakit. Padahal Anda sendiri juga memiliki pekerjaan yang harus diselesaikan hari ini. Apa yang Anda lakukan?','Menolak karena pekerjaan saya sendiri belum selesai','Membantu sebagian sambil tetap menyelesaikan pekerjaan saya','Membantu sepenuhnya dan menunda pekerjaan saya','Melaporkan kepada atasan agar rekan kerja mendapat bantuan lain','Mengabaikan permintaan tersebut',2,5,4,3,1,NULL,'Pilihan B mencerminkan keseimbangan antara empati dan tanggung jawab pribadi.','2026-05-06 23:34:30','2026-05-06 23:34:30');
/*!40000 ALTER TABLE `soal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi`
--

DROP TABLE IF EXISTS `transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaksi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `produk_id` int(10) unsigned NOT NULL,
  `voucher_id` int(10) unsigned DEFAULT NULL,
  `kode_transaksi` varchar(50) NOT NULL,
  `harga_asli` decimal(12,2) NOT NULL,
  `diskon` decimal(12,2) NOT NULL DEFAULT 0.00,
  `harga_bayar` decimal(12,2) NOT NULL,
  `status` enum('pending','success','failed','expired') NOT NULL DEFAULT 'pending',
  `snap_token` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_channel` varchar(50) DEFAULT NULL,
  `midtrans_order_id` varchar(100) DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_transaksi_kode` (`kode_transaksi`),
  KEY `idx_transaksi_user` (`user_id`),
  KEY `idx_transaksi_produk` (`produk_id`),
  KEY `idx_transaksi_voucher` (`voucher_id`),
  CONSTRAINT `fk_transaksi_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `voucher` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi`
--

LOCK TABLES `transaksi` WRITE;
/*!40000 ALTER TABLE `transaksi` DISABLE KEYS */;
INSERT INTO `transaksi` VALUES (1,6,1,1,'TRX-69FC3365A1CEB',80000.00,40000.00,40000.00,'success','63e66235-5386-4e9e-8986-b96b1b002689','bni','bni','TRX-69FC3365A1CEB',NULL,'2026-05-07 13:38:29','2026-05-07 15:34:27'),(2,7,1,NULL,'TRX-69FC68B1E4225',80000.00,0.00,80000.00,'pending','c26a67ee-f557-40df-b5b2-aeba11e4883c','qris',NULL,'TRX-69FC68B1E4225',NULL,'2026-05-07 17:25:53','2026-05-07 17:25:54'),(4,7,1,NULL,'TRX-69FC6BFA8F0C1',80000.00,0.00,80000.00,'pending','d8077e88-3cec-4abc-a5ab-c1dba316a5c3','bni',NULL,'TRX-69FC6BFA8F0C1',NULL,'2026-05-07 17:39:54','2026-05-07 17:39:55'),(5,7,1,NULL,'TRX-69FC6D727FB61',80000.00,56000.00,24000.00,'success','2ab021f6-1b55-43dc-960d-24faa00d2d86','bni','bni','TRX-69FC6D727FB61',NULL,'2026-05-07 17:46:10','2026-05-07 17:46:58'),(6,6,3,NULL,'TRX-69FC76B153633',30000.00,0.00,30000.00,'success','92d87405-e6d9-4073-a03e-ce71083f172d','bni','bni','TRX-69FC76B153633',NULL,'2026-05-07 18:25:37','2026-05-07 18:26:16');
/*!40000 ALTER TABLE `transaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tryout`
--

DROP TABLE IF EXISTS `tryout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tryout` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(200) NOT NULL,
  `durasi` int(11) NOT NULL COMMENT 'duration in minutes',
  `jumlah_soal` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tryout`
--

LOCK TABLES `tryout` WRITE;
/*!40000 ALTER TABLE `tryout` DISABLE KEYS */;
INSERT INTO `tryout` VALUES (1,'Tryout CPNS Dasar',90,100,1,'2026-05-06 21:22:06','2026-05-06 21:22:06'),(2,'Tryout CPNS Lanjutan',90,110,1,'2026-05-07 17:03:06','2026-05-07 17:03:06'),(3,'Tryout CPNS Gabungan',100,100,1,'2026-05-07 17:03:27','2026-05-07 17:03:27');
/*!40000 ALTER TABLE `tryout` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_produk`
--

DROP TABLE IF EXISTS `user_produk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_produk` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `produk_id` int(10) unsigned NOT NULL,
  `transaksi_id` int(10) unsigned NOT NULL,
  `expired_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_produk` (`user_id`,`produk_id`),
  KEY `idx_user_produk_user` (`user_id`),
  KEY `idx_user_produk_produk` (`produk_id`),
  KEY `idx_user_produk_transaksi` (`transaksi_id`),
  CONSTRAINT `fk_user_produk_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_user_produk_transaksi` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_user_produk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_produk`
--

LOCK TABLES `user_produk` WRITE;
/*!40000 ALTER TABLE `user_produk` DISABLE KEYS */;
INSERT INTO `user_produk` VALUES (1,6,1,1,NULL,'2026-05-07 15:34:27'),(3,7,1,5,NULL,'2026-05-07 17:46:58'),(4,6,3,6,NULL,'2026-05-07 18:26:16');
/*!40000 ALTER TABLE `user_produk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL COMMENT 'bcrypt hash',
  `role` enum('user','admin','super_admin') NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` datetime DEFAULT NULL,
  `login_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super Administrator','superadmin@cpns.test',NULL,'$2y$10$0r5JYOTUh0PT980a8JV3FO6irX3BPc6auFRPhjY3oOd4skjrn43lO','super_admin',1,'2026-05-03 22:23:49',0,NULL,'2026-05-03 22:23:49','2026-05-16 17:11:19'),(2,'Administrator','admin@cpns.test',NULL,'$2y$10$LxEkAbgaHzUINwldqJRoqull/Y3so9bvCtTGXpRXOqv0E2i4C11Cy','admin',1,'2026-05-03 22:32:22',0,NULL,'2026-05-03 22:32:22','2026-05-07 01:16:25'),(3,'Peserta Demo','user@cpns.test',NULL,'$2y$10$b/AsE2DLGboElJbNxUzsuuKKR8jBkmstRdpK9zNMuG73so0yNK98.','user',1,'2026-05-03 22:32:22',0,NULL,'2026-05-03 22:32:22','2026-05-03 23:11:02'),(6,'Irfandi Ricon','irfandiricon1993@gmail.com','0895320294566','$2y$10$tkhlssRy.sz3fV8Ra.C3ouiQyhgZcEE6VPE9kJNuX3agGvhim3FxO','user',1,'2026-05-03 23:12:09',0,NULL,'2026-05-03 23:11:53','2026-05-16 16:00:53'),(7,'Irfandi Ricon','irfandi.ricon@gmail.com','0895320294566','$2y$10$pz8L/Dkg.JzdtMbxLaAwEOiIt4GnlbakrxV39MoeBSbp/o98tiYke','user',1,'2026-05-07 17:25:22',0,NULL,'2026-05-07 17:25:02','2026-05-07 17:25:30');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voucher`
--

DROP TABLE IF EXISTS `voucher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voucher` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) NOT NULL,
  `jenis_diskon` enum('persentase','nominal') NOT NULL,
  `nilai_diskon` decimal(12,2) NOT NULL,
  `batas_penggunaan` int(11) DEFAULT NULL,
  `jumlah_digunakan` int(11) NOT NULL DEFAULT 0,
  `expired_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_voucher_kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voucher`
--

LOCK TABLES `voucher` WRITE;
/*!40000 ALTER TABLE `voucher` DISABLE KEYS */;
INSERT INTO `voucher` VALUES (1,'DISKON50','persentase',50.00,10,1,'2026-05-09 16:00:00',1,'2026-05-07 00:09:48','2026-05-07 00:09:48');
/*!40000 ALTER TABLE `voucher` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `web_content`
--

DROP TABLE IF EXISTS `web_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `web_content` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL COMMENT 'Unique key: syarat-ketentuan, kebijakan-privasi, hubungi-kami, hero_title, dll',
  `judul` varchar(200) NOT NULL,
  `konten` longtext DEFAULT NULL,
  `tipe` enum('halaman','teks','angka') NOT NULL DEFAULT 'halaman' COMMENT 'halaman=rich HTML, teks=plain text, angka=numeric',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `web_content`
--

LOCK TABLES `web_content` WRITE;
/*!40000 ALTER TABLE `web_content` DISABLE KEYS */;
INSERT INTO `web_content` VALUES (1,'syarat-ketentuan','Syarat dan Ketentuan','<h5>1. Penerimaan Syarat</h5><p>Dengan mengakses dan menggunakan layanan SiapASN Simulation Center, Anda menyetujui untuk terikat oleh syarat dan ketentuan ini.</p><h5>2. Layanan</h5><p>SiapASN menyediakan platform simulasi tryout CPNS berbasis web untuk membantu persiapan ujian seleksi ASN.</p><h5>3. Akun Pengguna</h5><p>Anda bertanggung jawab untuk menjaga kerahasiaan akun dan password Anda. Segala aktivitas yang terjadi di bawah akun Anda menjadi tanggung jawab Anda.</p><h5>4. Pembayaran</h5><p>Semua transaksi pembelian paket tryout bersifat final dan tidak dapat dikembalikan kecuali terdapat kesalahan teknis dari pihak kami.</p><h5>5. Hak Kekayaan Intelektual</h5><p>Seluruh konten, soal, dan materi yang tersedia di platform ini dilindungi hak cipta dan merupakan milik SiapASN Simulation Center.</p><h5>6. Perubahan Layanan</h5><p>Kami berhak mengubah, menangguhkan, atau menghentikan layanan kapan saja tanpa pemberitahuan sebelumnya.</p>','halaman',1,'2026-05-16 17:04:29','2026-05-16 17:04:29'),(2,'kebijakan-privasi','Kebijakan Privasi','<h5>1. Informasi yang Kami Kumpulkan</h5><p>Kami mengumpulkan informasi yang Anda berikan saat mendaftar, termasuk nama, alamat email, dan nomor telepon.</p><h5>2. Penggunaan Informasi</h5><p>Informasi Anda digunakan untuk menyediakan layanan, memproses transaksi, dan mengirimkan notifikasi terkait akun Anda.</p><h5>3. Keamanan Data</h5><p>Kami menerapkan langkah-langkah keamanan teknis dan organisasi yang wajar untuk melindungi data pribadi Anda dari akses tidak sah.</p><h5>4. Berbagi Data</h5><p>Kami tidak menjual, memperdagangkan, atau mentransfer informasi pribadi Anda kepada pihak ketiga tanpa persetujuan Anda, kecuali diwajibkan oleh hukum.</p><h5>5. Cookie</h5><p>Platform kami menggunakan cookie untuk meningkatkan pengalaman pengguna. Anda dapat mengatur browser untuk menolak cookie, namun beberapa fitur mungkin tidak berfungsi optimal.</p><h5>6. Perubahan Kebijakan</h5><p>Kami dapat memperbarui kebijakan privasi ini sewaktu-waktu. Perubahan akan diberitahukan melalui email atau notifikasi di platform.</p>','halaman',1,'2026-05-16 17:04:29','2026-05-16 17:04:29'),(3,'hubungi-kami','Hubungi Kami','<p>Kami siap membantu Anda. Silakan hubungi kami melalui salah satu saluran berikut:</p>','halaman',1,'2026-05-16 17:04:29','2026-05-16 17:04:29'),(4,'kontak_email','Email Kontak','info@siapasn.id','teks',1,'2026-05-16 17:04:29','2026-05-16 17:04:29'),(5,'kontak_whatsapp','WhatsApp','6281234567890','teks',1,'2026-05-16 17:04:29','2026-05-16 17:04:29'),(6,'kontak_alamat','Alamat Kantor','Jakarta, Indonesia','teks',1,'2026-05-16 17:04:29','2026-05-16 17:04:29'),(7,'hero_tagline','Hero Tagline','Raih Impian ASN-mu Bersama Kami','teks',1,'2026-05-16 17:04:29','2026-05-16 17:04:29'),(8,'hero_deskripsi','Hero Deskripsi','Platform simulasi tryout CPNS terlengkap dengan ribuan soal, pembahasan mendalam, dan analisis nilai real-time untuk mempersiapkan Anda menghadapi seleksi ASN.','teks',1,'2026-05-16 17:04:29','2026-05-16 17:04:29'),(9,'stat_pengguna','Statistik Pengguna','10.000+','teks',1,'2026-05-16 17:04:29','2026-05-16 17:04:29'),(10,'stat_soal','Statistik Soal','5.000+','teks',1,'2026-05-16 17:04:29','2026-05-16 17:04:29'),(11,'stat_paket','Statistik Paket','50+','teks',1,'2026-05-16 17:04:29','2026-05-16 17:04:29');
/*!40000 ALTER TABLE `web_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'cpns_tryout'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-16 17:49:11
