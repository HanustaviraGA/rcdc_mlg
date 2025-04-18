-- MySQL dump 10.13  Distrib 9.0.1, for macos15.0 (arm64)
--
-- Host: localhost    Database: rcdc_mlg
-- ------------------------------------------------------
-- Server version	9.0.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
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
-- Table structure for table `database_dosen`
--

DROP TABLE IF EXISTS `database_dosen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `database_dosen` (
  `kode_dosen` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_dosen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pendidikan_dosen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jurusan_dosen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jja_dosen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ft_dosen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`kode_dosen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `database_dosen`
--

LOCK TABLES `database_dosen` WRITE;
/*!40000 ALTER TABLE `database_dosen` DISABLE KEYS */;
INSERT INTO `database_dosen` VALUES ('D1789','Choirul Huda, S.Kom., M.M.','S2','CS','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D2693','Dr. Robertus Tang Herman, S.E., M.M.','S3','BC','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D3555','Alvin Chandra, S.Kom., M.M.','S2','CS','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D3682','Bambang Kartono Kurniawan, S.Sn., M.A.','S2','DI','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D3708','Albert Verasius Dian Sano, S.T., M.Kom.','S2','CS','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D4749','Dr. Anita Rahayu, S.Si., M.Si.','S3','CS','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5567','Windaningsih, S.Sos., M.I.Kom','S2','Ilkom','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5707','Elizabeth Paskahlia Gunawan, S.Kom., M.Cs.','S2','CS','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5835','Yoseph Benny Kusuma, S.M., M.SM.','S2','BC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5836','Anindya Widita, B.A., M.A.','S2','PR','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5837','Galuh Ayu Savitri, S.I.Kom., M.I.Kom.','S2','PR','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5838','Yudhistya Ayu Kusumawati, S.Sn., M.Ds.','S2','DKV','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5839','Yanuarita Kusuma Permata Sari, S.Sosio., M.Med.Kom.','S2','Ilkom','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5844','Riesta Devi Kumalasari, S.E., M.M.','S2','BC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5845','Victor Adiluhung Abednego, S.T., M.Ds.','S2','DKV','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5847','Dr. Kukuh Lukiyanto, S.T., M.M., M.T.','S3','BC','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5848','Sidharta, S.Si., M.MT.','S2','CS','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5850','Hanugra Aulia Sidharta, S.T., M.MT.','S2','CS','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5868','Agung Purnomo, S.P., MBA','S2','BC','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5910','Gabriella Sagita Putri, S.Sosio., M.Med.Kom.','S2','PR','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5911','Nur Kholis, S.S., M.I.Kom','S2','Ilkom','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5925','Frederik Masri Gasa, S.IP., M.Si.','S2','Ilkom','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5936','Nisrin Husna, S.I.Kom., M.I.Kom.','S2','PR','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5942','Miranti Nurul Huda, S.Sn., M.Ds.','S2','DKV','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5943','Asih Zunaidah, S.S., M.Li.','S2','LC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D5975','Wina Permana Sari, S.T., M.Kom.','S2','CS','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6032','Yongkie Angkawijaya, S.Sn., M.Ds.','S2','DKV','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6066','Fairuz Iqbal Maulana, S.T., M.T., M.Eng.','S2','CS','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6067','Priska Arindya Purnama, S.Si., M.Si.','S2','BC','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6069','Eflina Nurdini Febrita Mona, S.I.Kom., M.I.Kom.','S3','PR','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6070','Adhi Murti Citra Amalia H, S.Ant., M.Med.Kom.','S2','PR','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6071','Priskardus Hermanto Candra, S.S., M.Hum.','S2','CBDC','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6077','Ira Audia Agustina, S.T., M.Ds.','S3','DI','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6103','Lutfi Tri Atmaji, S.T., M.Sn.','S2','DKV','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6104','Ida Bagus Ananta Wijaya, S.T., M.T.','S2','DI','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6106','Tiara Ika Widia Primadani, S.T., M.Ds.','S2','DI','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6110','Cahyaning Umul Chasanah Nursyifani, S.T., M.Ds.','S2','DKV','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6112','Nina Amalia Nurichsania, S.Pd, M.Pd.','S2','LC','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6161','Etsa Astridya Setiyati, S.E, PGradDiplBus, M.Com.','S3','BC','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6162','Gamal Kusuma Zamahsari, S.Pd., M.Pd.','S2','LC','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6166','Hera Rachmahani, S.M., M.S.M.','S2','BC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6167','Wahyu Waskito Putra, S.Sn., MSn','S2','DI','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6258','Febby Candra Pratama, S.E., M.M.','S2','BC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6259','Dr. Meilinda Trisilia, S.Si., M.Si.','S3','BC','L','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6261','Mirza Ramadhani, S.Kom., M.Kom.','S2','CS','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6262','Satrya Dirgantara, ST, MSn','S3','DKV','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6263','Achmad Safiaji, S.I.Kom, M.I.Kom','S2','PR','TP','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6266','Faishal Hilmy Maulida, S.Hum., M.Hum.','S2','CBDC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6284','Mardhatilah Shanti, S.E., M.M.','S2','BC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6295','Lila Nathania, S.I.Kom, M.Litt.','S2','Ilkom','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6335','Pandu Meidian Pratama, S.Pd., M.Pd.','S2','LC','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6336','Miftahul Hamim, S.Pd., M.Pd.','S2','LC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6337','Mufidah Nur Amalia, S.Pd., M.Pd.','S2','LC','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6339','Andi Pramono, S.T. M.Arch','S2','DI','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6358','Murniati, S.E., M.Si.','S2','BC','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6365','Chasandra Puspitasari, S.Kom., M.Cs.','S2','CS','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6371','Frihandhika Permana, S.Pd., M.Kom.','S2','CS','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6373','Dr. Sahnaz Ubud, S.T., M.MT','S3','BC','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6381','Lailatul Rif\'ah, S.Pd., M.Pd.','S2','LC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6383','Baskoro Azis, S.T., M.T.','S2','DI','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6384','Dika Sri Pandanari, S.Fil., M.Sos.','S2','CBDC','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6390','Muhammad Idham Sofyan','S2','BC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6392','Hindam Basith Rafiqi, S.Sn., M.Sn.','S2','DKV','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6394','M. Aldiki Febriantono, S.T., M.T.','S2','CS','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6395','Okky Pramudita, S.I.Kom., M.I.Kom','S2','PR','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6399','Riefky Prabowo, S.E., M.A.B','S2','BC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6404','Gusti Pangestu, S.Kom., M.Kom.','S2','CS','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6418','Anak Agung Ayu Mirah Krisnawati, S.Sos., M.I.Kom','S2','Ilkom','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6494','Nyoman Wira Prasetya, S.Kom., M.T.','S2','CS','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6495','Asri Radhitanti, S.Sn., M.Ds.','S2','DKV','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6496','Safitri Aprillia Putri, S.Sn., M.Ds.','S2','DKV','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6497','Wahyu Kurnia Dewi, S.Sn., M.Sn.','S2','DKV','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6529','Bhekti Setyowibowo, S.Ikom., M.Si.','S2','Ilkom','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6552','Audio Valentino Himawan Marhendra, S.AB. M.AB.','S2','BC','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6553','Riza Rizqiyah, S.M., MBA','S2','BC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6556','Danang Wahyu Wicaksono, S.Si., M.Si.','S2','CS','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6561','Wilyan Adiasari, S.E., M.M.','S2','BC','TP','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6581','Dr. R. Aditya Kristamtomo Putra, S.T., M.M','S3','BC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6585','Andhika Pramalystianto, S.T., M.Ds.','S2','DI','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6587','Mufida Sekardhani, S.E. MBA','S2','BC','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6588','Zainiyah Alfirdaus, S.M., M.SM.','S2','BC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6605','Helena Hanindya Kartika Putri, S.S., MBA','S2','BC','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6654','Satria Fadil Persada, S.Kom., MBA., Ph.D.','S2','BC','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6671','Yulianto, S.Kom., M.Kom.','S2','CS','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6701','Gedong Maulana Kabir, S.Ag., M.A.','S2','CBDC','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6720','Brainnisa Ramadhani Nur Nisrina, S.Ds., M.Ds.','S2','DI','TP','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6747','Milkhatussyafa\'ah Taufiq, S.T., M.T.','S2','CS','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6772','Wahyu Kristian Natalia, S.I.Kom., M.I.Kom.','S2','Ilkom','AA','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6773','Radityo Widiatmojo, S.Sos., M.Si.','S2','Ilkom','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6775','Rudi Yulio Arindiono, S.T., M.A.','S2','DKV','TP','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6790','Hilmi Dzakaaul Islam, S.Ds., M.Ds.','S2','DI','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6866','Risca Kurnia Sari, S.E., M.M.','S2','BC','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6874','Muhammad Khirzan Ulinnuha, S.T., M.Sn.','S2','DKV','TP','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6875','Jevon Jeremy, S.Ds., M.Sn.','S2','DKV','TP','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6899','Dr. Nur Pratiwi, S.E., M.Sc.','S3','BC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6910','Dio Saputra Kudori, S.Kom., M.Kom.','S2','CS','TP','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6916','Ir. Fourry Handoko, S.T., S.S., M.T., Ph.D.','S2','BC','LK','-','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6917','Ni Putu Elvian Andreani, S.Ds., M.Ds.','S2','DKV','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6923','Muhammad Reza Alfasina, S.Ag., M.Phil.','S2','CBDC','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6926','Rio Satria Nugroho, S.Hum., M.Med.Kom.','S2','Ilkom','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6966','Krismi Budi Sienatra, S.E., M.M.','S2','BC','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6974','Dr. Sari Wijayanti S.P., M.M','S3','BC','L','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D6999','Elsa Graciana Nugraeni, S.Ds., M.Ds','S2','DKV','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D7000','Reyza Dwi Dzulqarnain Kahfi, S.Sn., M.Ds','S2','DKV','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D7001','Asteria Nabila Noviandari Ernanda, S.Ds., M.Ds.','S2','DI','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D7002','Erik Arma Yuda, S.Sn, M.Ds., Ph.D.','S2','DKV','L','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D7026','Reven Praga Deva, S.Ds., M.Ds','S2','DKV','TP','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D7051','Romanus Piter, S.Fil.','S2','CBDC','TP','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D7056','Hany Azza Umama, S.E. M.M','S2','BC','AA','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D7057','Nur Yudiyono, S.AB., M.BA.','S2','BC','TP','Functional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D7058','Harris Prasetya Rahmandika, S.M., M.M.','S2','BC','TP','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15'),('D7059','Veronica Swasti Paramitha, S.E., M.B.A','S2','BC','TP','Professional','2025-04-18 03:23:41','2025-04-18 03:39:15');
/*!40000 ALTER TABLE `database_dosen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('gSBwE6mcyBGHTnziyZBPFTqc7uUlsxyvL4BYsw0K',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoid0pNeE1sUWpVV3ZQVTNOS0IyM09BTGY0eHQwOGpQMTd1MXFqbVlHNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQvZG9zZW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1745013971);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblAuthSessions`
--

DROP TABLE IF EXISTS `tblAuthSessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblAuthSessions` (
  `intAuthID` int NOT NULL AUTO_INCREMENT,
  `txtSessionKey` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dtExpires` datetime DEFAULT NULL,
  `txtRedir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `txtRefreshToken` text COLLATE utf8mb4_unicode_ci,
  `txtCodeVerifier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `txtToken` text COLLATE utf8mb4_unicode_ci,
  `txtIDToken` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`intAuthID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblAuthSessions`
--

LOCK TABLES `tblAuthSessions` WRITE;
/*!40000 ALTER TABLE `tblAuthSessions` DISABLE KEYS */;
INSERT INTO `tblAuthSessions` VALUES (1,'c9823c52-7e06-4f11-97da-69d9dccaf11c','2025-04-14 05:25:32','http://localhost',NULL,'wuumT2z9Z_FIt-6n89TvgYef7wjmWypA6k7kik3eeOhcp~inkz52EwYxpzKMpUgKUR_j9.AAUQmSIZN7PyJDQ-nikkZrvqdPQUVn2p_9JRux9tFJVUiVxv2Bziju9J9E',NULL,NULL),(2,'6f9b05fc-8db9-45f3-9488-fb577d192443','2025-04-14 06:48:01','','1.AVQAY7mFNLqCb0qBD7XMIm_4mFPU4QyHP2FJnIZAUJeS7cWiAFJUAA.AgABAwEAAABVrSpeuWamRam2jAF1XRQEAwDs_wUA9P_Zmb2GXpzkOmS_PLvEmwkSaPUbLXy7MG2MiKURiKEv0XIPgd0QWtXnPoYxG35WL_7VPS6ueMz1qEkAMd1MwojGAjdS7-GL5hZrgZ5VxeyjVPzcZspTbWVhPJ6rGB3XMPxdczPZezPacT4AZfKfUR9j3ybXrLYfsXGY_NXs__P7jtUmKcnrrkn4lGr4fj0ojMJpsjWUEg2WQqvyOR3Lt9Ke8-_gqpXgKu4NNqMr8LwQWgefAIEfIUM6EJbe_xyCKllo-xMO-ckdbUoHXMMJz-ExOkDLXov3nw5j1tGnXA0IO4XVwsPyFGr5o4NOj4uJrEY7RjT4O-tydlr4EGL8KAthiSEMdjRTgjS4y1JCWyT1pdDUaGplW-lXikVtoK3RxrEPZPfhBowLL4E3nemFefIQC_wwj-oEp-E7dttKTqS12KbwQ3xZP07w2S9tF4yFGRY-3xpQ2HtScq8famUkyaZxmXgLBhc9xMxvuVV-xdbOoqz77LmRKhIh-btn93Gd7f42B5exH8y0Gds1XewFLjQGOVHa5n4gBMoEFW01Zvsmt03CWOQiLPAI28OnXhZU71gBUx0w5mOQtPQFjDHmPkz7xyNHLHx-BwCyqRRtibwuol9mBmWtidnNwir7N9L3Os5sAYqYWNQLNYuTtj35qt1bS4MsUcSQ02aM55oM_rAzw1SX1JsPGtFcJQIg4TuozzBHFjpZIHHVZIT66jguAGtwu-KGODnei_puaR1_7kmQetLJ76KauxWsSMjCxNg9ZmlblUnDMeWkyHCe2TrgHr4','ZEyx174HPDmJXcwwR0TbLarqxlw_w6LRTHz~0fmD4SYQQlNdaw9~OoVzOfuKD1UJgRC5uZblOCHL6uvt0xD3WWEA9kCecj0GrWYnTs66Y9CwW9Bc84hW~rhh.OqtTDgC','eyJ0eXAiOiJKV1QiLCJub25jZSI6IlpwZzN2NW1WX0xuYTlETzFVdFE2QlhUbVhyVUgyMHNWTUJIdTEtX005RTQiLCJhbGciOiJSUzI1NiIsIng1dCI6IkNOdjBPSTNSd3FsSEZFVm5hb01Bc2hDSDJYRSIsImtpZCI6IkNOdjBPSTNSd3FsSEZFVm5hb01Bc2hDSDJYRSJ9.eyJhdWQiOiIwMDAwMDAwMy0wMDAwLTAwMDAtYzAwMC0wMDAwMDAwMDAwMDAiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC8zNDg1Yjk2My04MmJhLTRhNmYtODEwZi1iNWNjMjI2ZmY4OTgvIiwiaWF0IjoxNzQ0NjA4MDU2LCJuYmYiOjE3NDQ2MDgwNTYsImV4cCI6MTc0NDYxMzI4MiwiYWNjdCI6MCwiYWNyIjoiMSIsImFpbyI6IkFVUUF1LzhaQUFBQXlYalNtV2thLzZtRldsbWRhdTh0cnRFbllRRkhFb0RLaVJuLzIzRVc4aHNUdGJYTGg5U3JJUm5RMStzOU8vbWtRSnFiNWZlTjBXdWF5QTd5b1prYmhRPT0iLCJhbXIiOlsicHdkIl0sImFwcF9kaXNwbGF5bmFtZSI6IlJDREMgQmludXMgTWFsYW5nIiwiYXBwaWQiOiIwY2UxZDQ1My0zZjg3LTQ5NjEtOWM4Ni00MDUwOTc5MmVkYzUiLCJhcHBpZGFjciI6IjEiLCJmYW1pbHlfbmFtZSI6IkdVUlUgQUNBUllBIiwiZ2l2ZW5fbmFtZSI6IkhBTlVTVEFWSVJBIiwiaWR0eXAiOiJ1c2VyIiwiaXBhZGRyIjoiMzYuOTUuMy4yNiIsIm5hbWUiOiJIQU5VU1RBVklSQSBHVVJVIEFDQVJZQSIsIm9pZCI6IjQ4NTAyNmNiLTMxMzAtNDAxOC04OTM0LTY4MjUzNGM3YTZjMSIsIm9ucHJlbV9zaWQiOiJTLTEtNS0yMS0zNTEzMzY4NjUtMTQxMjQwOTA3OC0yMzczOTAwMzk1LTM0OTAxOSIsInBsYXRmIjoiNSIsInB1aWQiOiIxMDAzMjAwMEM3MzAzNDUwIiwicmgiOiIxLkFWUUFZN21GTkxxQ2IwcUJEN1hNSW1fNG1BTUFBQUFBQUFBQXdBQUFBQUFBQUFDaUFGSlVBQS4iLCJzY3AiOiJVc2VyLlJlYWQgcHJvZmlsZSBvcGVuaWQgZW1haWwiLCJzaWQiOiIwMDNmMzNjOS1iMjMyLTU4ZWYtNzUxNS1jMjRmYjkwMDY2MzYiLCJzdWIiOiItQk5MWUhpT0dENEpzQ3FjT281UWtrY2ZmMEFxcEM5SEY4RjB6N2kxQzY0IiwidGVuYW50X3JlZ2lvbl9zY29wZSI6IkFTIiwidGlkIjoiMzQ4NWI5NjMtODJiYS00YTZmLTgxMGYtYjVjYzIyNmZmODk4IiwidW5pcXVlX25hbWUiOiJoYW51c3RhdmlyYS5hY2FyeWFAYmludXMuYWMuaWQiLCJ1cG4iOiJoYW51c3RhdmlyYS5hY2FyeWFAYmludXMuYWMuaWQiLCJ1dGkiOiI3TDVyQWNzT0IweUFRbDREdUp3ZEFBIiwidmVyIjoiMS4wIiwid2lkcyI6WyJiNzlmYmY0ZC0zZWY5LTQ2ODktODE0My03NmIxOTRlODU1MDkiXSwieG1zX2Z0ZCI6IlFib3d6RmlXbnRROFhCVXRKTnBtcVE0djFMOWNtS0NIMXNYSWozaEJKd2MiLCJ4bXNfaWRyZWwiOiIxIDEyIiwieG1zX3N0Ijp7InN1YiI6IjFWd0tlTEdJZEpaWGw5THpyOW5ocVB3ZzR0bEdYclotemwzcTlHZ05vb1UifSwieG1zX3RjZHQiOjEzNTk0MjY1NjB9.Lm-KDTIG60HIzpVdjGx12X5dRp8jSKw3fZ3T5kxrEcUAeLNZiW7yL_-HpffvRVTyZv4W3Z4BbLaqNCRKqOujpkwQ5W8EVobD644U54e1lbe0bps0xyriAaUkdD7Pj1i9aT6S8PcOZ0DuslsOlPTM25Hj8LRmVz-rdQV7uXrnKETcE-B2MeZ2__Im_t1P0oeIdZ16bPG2Beudcm86wL4d56I3XKPKM5XP6B76ivB9Shq4bBNdvEN-pxmkOroiN2W0veH8C0UGjPSFURi_tJIudTzMY15KQZsVvO_xz6NNv6i0Qxph0iB3GVPfaO5-aHYpYbK3-LVCTy5K7SGgBcWkbw','{\"aud\":\"0ce1d453-3f87-4961-9c86-40509792edc5\",\"iss\":\"https://login.microsoftonline.com/3485b963-82ba-4a6f-810f-b5cc226ff898/v2.0\",\"iat\":1744608056,\"nbf\":1744608056,\"exp\":1744611956,\"name\":\"HANUSTAVIRA GURU ACARYA\",\"oid\":\"485026cb-3130-4018-8934-682534c7a6c1\",\"preferred_username\":\"hanustavira.acarya@binus.ac.id\",\"rh\":\"1.AVQAY7mFNLqCb0qBD7XMIm_4mFPU4QyHP2FJnIZAUJeS7cWiAFJUAA.\",\"sid\":\"003f33c9-b232-58ef-7515-c24fb9006636\",\"sub\":\"1VwKeLGIdJZXl9Lzr9nhqPwg4tlGXrZ-zl3q9GgNooU\",\"tid\":\"3485b963-82ba-4a6f-810f-b5cc226ff898\",\"uti\":\"7L5rAcsOB0yAQl4DuJwdAA\",\"ver\":\"2.0\"}'),(3,'33d0b04d-fbd4-4f60-9ca3-a01cd5cffd13','2025-04-14 05:31:56','http://localhost:8888',NULL,'DUQkhGeD9y9YVZ2FHxPVf2FkB2bQ68gInC-mhMtpJEtDHmaYZ1twRU~_-IYxCjz9r-dAxENErKW75Y.iL4aMKjWVlaH9.QCkqAXR3iGpuN1biqnY6SO2mS8NApxpwLBj',NULL,NULL),(4,'1695a6b8-8ae8-41db-8411-7b61112eb7e8','2025-04-14 05:32:11','http://localhost:8888',NULL,'UHIMzxSAQZLUDqoKqb.1T~mIMyc6KT_Yj4~bx7fFwQYyFVtyW3BOr.pU2HyvHcYlDO8BBv0afdKakAW-OfrCF.wmM.5E8YuHe~HbdOAz~yLZaHFLWBTt0ClSJO9hz6bW',NULL,NULL),(5,'25185a97-648f-4199-9d4f-037877eba3da','2025-04-14 06:54:07','','1.AVQAY7mFNLqCb0qBD7XMIm_4mFPU4QyHP2FJnIZAUJeS7cWiADJUAA.AgABAwEAAABVrSpeuWamRam2jAF1XRQEAwDs_wUA9P_355lYGGWEd3nXEx1TIfqYEvLCAJzuERWvI83gnfXZ15OIDELyBVStBxNftMU8ids3oKlNgHCoQ5ETqcgXdJEi251rtC4KtZzBXpp7-L_KW8EWmSRW6-Lxo5mtp1NQxx0D4ZrKj4RBsmLGKgY2apUcb02LxMOi6VgPBhi3n52FI7_JZbQKfFVoJUZUJa1HjhrdBs-mvIFC7Y_v10Bl1kPaKHEQYLXTroLdvnl7F0k3CM2RRciAsM4Ft7ebw15uX_TNPJ5mE1n6QWhLDGshD91ifoz6EVO6gy_e6GovwVjOcgK3xPQA9ha7HH0dYGwENzaiv6YqczAs_UtjWQc4c1cDYSx-GLjNlxlMbLCHPjOB6b8z02_eJwUyNILxRCGt3rwGe7CGdY-4_9iAMVgCee-BMowlRzIKExErOuehhlnT7Br0V040FHWl-kT7MXGqXENo2KO81dk_Tk65ModImc8DULgMh4rVQY27Rz5OeOpbjm6iDH-h0DHNp66fqvc_LuFuABbuO5cN5_GxZ33M82P1xPPWEqFtruZnfMa8XTKIVcwENk2iX0hoWoGbjUM8PxRSxie2SsYenqzoZdMhEV7TsySC_3TsyuG9WlsC35S4CujXcYy-vjglgVOLA4o2DmNKpY0SZvq1vKqV7h79N8JadsdP7nA2XeSNeSpGMpR6FJsZmHdLkc025c1B3XnneXKYtGcSi8vx561T5lPnpM8i4idqYS0gwJexy2SSp7kWCrvA0sIBlMCxegg7hf6pa6wsb5vUQFNaUFDImg9TD-Sy87D-7dTc7vxhDZimkwocEs1IRj0z5njlCa9VuoGYcXtcV4_z62xZlv1XrauBsY6Fug','ENg9dsqGAPBgH1nEaKRYjQrosMISLy3BH3LK7GWKr--E2kFDHeCU6GQeN8A4BvXQxknPsb.x3MrxQhvYbAUrTaqeTFLx73tL.KGzWocFCIGs1jbDAMxpUy85fRjtE_4H','eyJ0eXAiOiJKV1QiLCJub25jZSI6IldKYTZSbFFOVDNwLXd5b0JrSHZaNU5tQTNOcV9NejZUaURuQXk2T2lHYW8iLCJhbGciOiJSUzI1NiIsIng1dCI6IkNOdjBPSTNSd3FsSEZFVm5hb01Bc2hDSDJYRSIsImtpZCI6IkNOdjBPSTNSd3FsSEZFVm5hb01Bc2hDSDJYRSJ9.eyJhdWQiOiIwMDAwMDAwMy0wMDAwLTAwMDAtYzAwMC0wMDAwMDAwMDAwMDAiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC8zNDg1Yjk2My04MmJhLTRhNmYtODEwZi1iNWNjMjI2ZmY4OTgvIiwiaWF0IjoxNzQ0NjA4MjY1LCJuYmYiOjE3NDQ2MDgyNjUsImV4cCI6MTc0NDYxMzY0OCwiYWNjdCI6MCwiYWNyIjoiMSIsImFjcnMiOlsicDEiXSwiYWlvIjoiQVdRQW0vOFpBQUFBbjU3MHMvbWwzbzl6Wnl2QXYvMjl0TStwTUJFTWc5N2VQeUo5UTB2ZXJqSzlxeE9iMmgyS2FCczdrSGJrSENGQW1DaWVHMGRiUEt6RjVVUUZSdlJJc2RHS1phdHh5cEErT1M0MXhOMDZHRXkyMlg1d0NOVVA2MEZxbFJWa0FZT1AiLCJhbXIiOlsicHdkIiwibWZhIl0sImFwcF9kaXNwbGF5bmFtZSI6IlJDREMgQmludXMgTWFsYW5nIiwiYXBwaWQiOiIwY2UxZDQ1My0zZjg3LTQ5NjEtOWM4Ni00MDUwOTc5MmVkYzUiLCJhcHBpZGFjciI6IjEiLCJmYW1pbHlfbmFtZSI6IkFjYXJ5YSIsImdpdmVuX25hbWUiOiJIYW51c3RhdmlyYSIsImlkdHlwIjoidXNlciIsImlwYWRkciI6IjM2Ljk1LjMuMjYiLCJuYW1lIjoiSGFudXN0YXZpcmEgR3VydSBBY2FyeWEiLCJvaWQiOiIyYjExZDg3YS0xODYyLTQ3OWItOTNmYi0wYmZmOTJlNzdiN2EiLCJvbnByZW1fc2lkIjoiUy0xLTUtMjEtNTM0Nzg5MjE0LTM1NTU5NTEzNC0zMDM5MjYyNTk3LTg1Nzc0IiwicGxhdGYiOiI1IiwicHVpZCI6IjEwMDMyMDA0N0NEMzA1RkMiLCJyaCI6IjEuQVZRQVk3bUZOTHFDYjBxQkQ3WE1JbV80bUFNQUFBQUFBQUFBd0FBQUFBQUFBQUNpQURKVUFBLiIsInNjcCI6Im9wZW5pZCBwcm9maWxlIFVzZXIuUmVhZCBlbWFpbCIsInNpZCI6IjAwM2YzM2M5LTM1MTctY2RiMi0yZjEzLTI5MTdhMTI5MzQ0MyIsInN1YiI6Ii1Jam5xV0lVRGFPeUVUX0ZrVVJsQUM1OGtZUEVEamxveXdSdmtkbVhZVjQiLCJ0ZW5hbnRfcmVnaW9uX3Njb3BlIjoiQVMiLCJ0aWQiOiIzNDg1Yjk2My04MmJhLTRhNmYtODEwZi1iNWNjMjI2ZmY4OTgiLCJ1bmlxdWVfbmFtZSI6ImhhbnVzdGF2aXJhLmFjYXJ5YUBiaW51cy5lZHUiLCJ1cG4iOiJoYW51c3RhdmlyYS5hY2FyeWFAYmludXMuZWR1IiwidXRpIjoiNlJ1WFJ3UnJURXFvb2tFakVGMDhBQSIsInZlciI6IjEuMCIsIndpZHMiOlsiYjc5ZmJmNGQtM2VmOS00Njg5LTgxNDMtNzZiMTk0ZTg1NTA5Il0sInhtc19mdGQiOiJjUFJIVmswVm1SSWp2bnlSWDJlMDlwUG5PTjN4THEtN1JDdExTWWdLMTVRIiwieG1zX2lkcmVsIjoiMSA0IiwieG1zX3N0Ijp7InN1YiI6IkRSZ3hJYUR4MHRlenV4YXFpMm5CdFVPZ09GdUxWaDRCZEFMWk9VdXNodUUifSwieG1zX3RjZHQiOjEzNTk0MjY1NjB9.dKIaYQC7ZVsGSxQ7egtP8qQkCGMUnivpmWluwK7yofzJat_79bDlQVwLNdcE64mXLGi0I4cr27OsxQKa7Il2zvdH6UV2StuxjQSWixdaCJvf-df50vBGGykBu9PHcFvsE6vD9Ry3UKpveyqasp85CugezVHYcAEoajfX1SA1eLm0t1O_6d_FQRYrSanczeSQjn6EyS4GKq-g43c3cPeIRQikpXw6Uyln8C-ppKMl73FaJ2WBc6tTKe6Fn24ocwZPtfDMMbBYE_oO9_9T12OoqnmF8KVJpBk7gJHKWmu3DvWMnGUU-fsJ1dAMeKBOPL86s-2ekLc6H_EUCbTIugvXSw','{\"aud\":\"0ce1d453-3f87-4961-9c86-40509792edc5\",\"iss\":\"https://login.microsoftonline.com/3485b963-82ba-4a6f-810f-b5cc226ff898/v2.0\",\"iat\":1744608265,\"nbf\":1744608265,\"exp\":1744612165,\"name\":\"Hanustavira Guru Acarya\",\"oid\":\"2b11d87a-1862-479b-93fb-0bff92e77b7a\",\"preferred_username\":\"hanustavira.acarya@binus.edu\",\"rh\":\"1.AVQAY7mFNLqCb0qBD7XMIm_4mFPU4QyHP2FJnIZAUJeS7cWiADJUAA.\",\"sid\":\"003f33c9-3517-cdb2-2f13-2917a1293443\",\"sub\":\"DRgxIaDx0tezuxaqi2nBtUOgOFuLVh4BdALZOUushuE\",\"tid\":\"3485b963-82ba-4a6f-810f-b5cc226ff898\",\"uti\":\"6RuXRwRrTEqookEjEF08AA\",\"ver\":\"2.0\"}'),(6,'32fd798c-6fb3-4c2a-9b3f-06c989cd2878','2025-04-14 06:46:03','','1.AVQAY7mFNLqCb0qBD7XMIm_4mFPU4QyHP2FJnIZAUJeS7cWiAJ5UAA.AgABAwEAAABVrSpeuWamRam2jAF1XRQEAwDs_wUA9P8ooS7dztFSWl3Cqoo0l7ZLexNsrC52CAE3SLlVkwFtx23SDeUuQJXYN_UFk0Iq0MqEOQiKzAp89VXh0K8WjaObsTENjXZMow8jDoJUvIwglMzpwgaM4VVlL9XpRzc2_zGUrXkvkv5m72z9hWm-URPzCk5YYLFQNboHV8SUMwzqZoM_4Sz-Z7Jxhv4c8341GLH10bAuEK91Hc7NQbI28TQXmU75r5P2XWZoPUvwkSicUXiqcQnbOMqnopSkpM4pE0N2BZTgb_q2pWtVxI6zQZv2ADsNIqujibE-jvqbVSBd6-C_BbktRdVkUw91MGq83nGCgchqmxK5_CNbEThhA-RDP1kORDCmeWne1Td8RSeHM9AP6HbWRxWsACLocwYdtgW8WGb8SS_xh11Pnqk016oqNq1BIhANnv1t7CpbxWKf0zY0rQiIWKyeVxazo9QejfcIqRo9xKpyhkyCzCyb1vxW_ua9UHqIRZ_L28aQ2M9RPXE6WwgVkILr70gNv9czft0tN1ALMlH9avPZxx_vmGWZYmXATIPYOHUyn5UOSlFLeWftBbF_rYRXt5Q-8fK54PtLOwMvlDBDBWvFqNZyGWbeAwdAKkVJg1zekJdKlpG_XBARWB-REhiwgoEr1w-FVFEZWVmJxN1qx20gRI2efon21IUEvnRCAFysFz395KRXJnXkAE2ruRYmTHMOCVCmqrWrANvGxXoiyBL2LgDnypLsVy-YQNC7uqDc2h4oJ52QHcmLPe0gNomSmQd95lQdwGUSQNnxWttU-cR8oIco5BhRYLATd0hZjZnInsEXSZUEKlyLsnu45d6ShIV2NcIGNPrjJXD_','VK05m.qdo5ZAXhEAx0c63WPvKdu5EW.~0oDl2.W~A-F1H2QXpl76QwnX9YmF7Q9Bwsu8x~ASkFHp7D~y3KRNkhPqRu0vAHyW16uvf7BEZuSE3.nl4g6L-mJ1rYwqJevk','eyJ0eXAiOiJKV1QiLCJub25jZSI6InJuajdFeFR2RzJkMHcyckVXYzZFUmdJYzU1cjNLN2U5YVptRW5VVTNRVEEiLCJhbGciOiJSUzI1NiIsIng1dCI6IkNOdjBPSTNSd3FsSEZFVm5hb01Bc2hDSDJYRSIsImtpZCI6IkNOdjBPSTNSd3FsSEZFVm5hb01Bc2hDSDJYRSJ9.eyJhdWQiOiIwMDAwMDAwMy0wMDAwLTAwMDAtYzAwMC0wMDAwMDAwMDAwMDAiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC8zNDg1Yjk2My04MmJhLTRhNmYtODEwZi1iNWNjMjI2ZmY4OTgvIiwiaWF0IjoxNzQ0NjA4NTA1LCJuYmYiOjE3NDQ2MDg1MDUsImV4cCI6MTc0NDYxMzE2NCwiYWNjdCI6MCwiYWNyIjoiMSIsImFjcnMiOlsicDEiXSwiYWlvIjoiQVdRQW0vOFpBQUFBOTlzU0IxYzEvTkdpbjhXRWcvQzdOK1ZHMUJJQmJhdUkzaElZejNMVE1iS1ViVUdEeGtyS0VteThpZWhCeTYwczhrQVhXUjZoRDZ6VHkwTkRZQ2JuNklhUGNSYWRPeERqYjlQQTlzR091WTBpYnBWUTI1ckRiNGlRZ3AzajY0KzgiLCJhbXIiOlsicHdkIiwibWZhIl0sImFwcF9kaXNwbGF5bmFtZSI6IlJDREMgQmludXMgTWFsYW5nIiwiYXBwaWQiOiIwY2UxZDQ1My0zZjg3LTQ5NjEtOWM4Ni00MDUwOTc5MmVkYzUiLCJhcHBpZGFjciI6IjEiLCJmYW1pbHlfbmFtZSI6IlNoYWJyaW5hIiwiZ2l2ZW5fbmFtZSI6IkVrYSIsImlkdHlwIjoidXNlciIsImlwYWRkciI6IjM2Ljk1LjMuMjYiLCJuYW1lIjoiRWthIE51ciBTaGFicmluYSIsIm9pZCI6IjIxYmE1ZmFmLTFkMTUtNDI1Mi1hOTE0LWM5MjU0MzE4NGYxZSIsIm9ucHJlbV9zaWQiOiJTLTEtNS0yMS01MzQ3ODkyMTQtMzU1NTk1MTM0LTMwMzkyNjI1OTctNTg4NzQiLCJwbGF0ZiI6IjUiLCJwdWlkIjoiMTAwMzIwMDM4MEFFODQ4OCIsInJoIjoiMS5BVlFBWTdtRk5McUNiMHFCRDdYTUltXzRtQU1BQUFBQUFBQUF3QUFBQUFBQUFBQ2lBSjVVQUEuIiwic2NwIjoib3BlbmlkIHByb2ZpbGUgVXNlci5SZWFkIGVtYWlsIiwic2lkIjoiMDAzZjMzYzktNTBkZS02NmE2LTY1ZmEtMzBhMmJlM2E1YzI1Iiwic3ViIjoiclBZZmdGaEZpdEdJWEl3RzVpV25WcGRibWhpOXBLSXZhcEdPdHNsWVV4RSIsInRlbmFudF9yZWdpb25fc2NvcGUiOiJBUyIsInRpZCI6IjM0ODViOTYzLTgyYmEtNGE2Zi04MTBmLWI1Y2MyMjZmZjg5OCIsInVuaXF1ZV9uYW1lIjoiZWthLnNoYWJyaW5hQGJpbnVzLmVkdSIsInVwbiI6ImVrYS5zaGFicmluYUBiaW51cy5lZHUiLCJ1dGkiOiI2TXpXT1BnT1FVYV9YdVdNaXk5RkFBIiwidmVyIjoiMS4wIiwid2lkcyI6WyJiNzlmYmY0ZC0zZWY5LTQ2ODktODE0My03NmIxOTRlODU1MDkiXSwieG1zX2Z0ZCI6Ik9SdGd0TVhobUxYem9QamxqaklhRVFpV29FZVhyVGdiV29zc2gzVUxCbFkiLCJ4bXNfaWRyZWwiOiIxIDI4IiwieG1zX3N0Ijp7InN1YiI6IkxuU2pXZ1VETEktV0dtZlY3WlN3Sno2eGdiVGwtWU1yVnJmc2w0eXU5aVkifSwieG1zX3RjZHQiOjEzNTk0MjY1NjB9.hhpJZ53TQpf17Tjm5mfBVEpVg8OH6NZjHuPSH8rDXbsLeQjkmB1QAhTYzSzwJZaxL1LVlF5Mpmb7mhgRlBqnD3qUORIrFOUykUI9P8KmI1wOOfhClrlYKsMTZOzg6J8KLAXqbXiIJXzXB81KAucXLL44PhcsSaj19flIliWs2XVrdTmYGhMD6lXmDOIdpagp-9xMocja0f4AoGOgfR_TSfQkF8VgQWLxmnBTnGW6Mr1qNYk3Cw2CHxw4qYaJhWklVhSWZ0uP6wORNyK37nKVvB7KsphMlWT5G3fbAL0gw9fbenY5AyBAxohU-iRbsV6BpuPa9NXkVqm_eT-c8CASiQ','{\"aud\":\"0ce1d453-3f87-4961-9c86-40509792edc5\",\"iss\":\"https://login.microsoftonline.com/3485b963-82ba-4a6f-810f-b5cc226ff898/v2.0\",\"iat\":1744608505,\"nbf\":1744608505,\"exp\":1744612405,\"name\":\"Eka Nur Shabrina\",\"oid\":\"21ba5faf-1d15-4252-a914-c92543184f1e\",\"preferred_username\":\"eka.shabrina@binus.edu\",\"rh\":\"1.AVQAY7mFNLqCb0qBD7XMIm_4mFPU4QyHP2FJnIZAUJeS7cWiAJ5UAA.\",\"sid\":\"003f33c9-50de-66a6-65fa-30a2be3a5c25\",\"sub\":\"LnSjWgUDLI-WGmfV7ZSwJz6xgbTl-YMrVrfsl4yu9iY\",\"tid\":\"3485b963-82ba-4a6f-810f-b5cc226ff898\",\"uti\":\"6MzWOPgOQUa_XuWMiy9FAA\",\"ver\":\"2.0\"}');
/*!40000 ALTER TABLE `tblAuthSessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'rcdc_mlg'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-19  5:21:59
