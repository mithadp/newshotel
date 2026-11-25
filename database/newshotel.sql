-- News Hotel: database lengkap (struktur + isi).
-- Impor lewat phpMyAdmin (tab Import) atau: mysql -u root < newshotel.sql  (database 'newshotel' dibuat otomatis).
-- Akun admin tidak disertakan: buat dengan  php scripts/create-admin.php --password "sandi-minimal-10-karakter"


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

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `newshotel` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `newshotel`;
DROP TABLE IF EXISTS `attractions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attractions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hotel_id` int(10) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `name_en` varchar(120) DEFAULT NULL,
  `note` varchar(160) DEFAULT NULL COMMENT 'Mis. jarak atau waktu tempuh',
  `note_en` varchar(160) DEFAULT NULL,
  `sort` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_att_hotel` (`hotel_id`,`sort`),
  CONSTRAINT `fk_att_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `attractions` WRITE;
/*!40000 ALTER TABLE `attractions` DISABLE KEYS */;
INSERT INTO `attractions` VALUES (1,1,'Bandara Internasional Juanda','Juanda International Airport','Shuttle gratis dengan perjanjian','Free shuttle by arrangement',1),(2,1,'Terminal Purabaya (Bungurasih)','Purabaya Bus Terminal (Bungurasih)','Shuttle gratis dengan perjanjian','Free shuttle by arrangement',2),(3,1,'City of Tomorrow (CITO)','City of Tomorrow (CITO) mall',NULL,NULL,3),(4,2,'Simpang Lima','Simpang Lima square',NULL,NULL,1),(5,2,'Lawang Sewu','Lawang Sewu',NULL,NULL,2),(6,2,'Kota Lama Semarang','Semarang Old Town',NULL,NULL,3),(7,3,'Taman Bungkul','Taman Bungkul park','± 560 m dari hotel','About 560 m from the hotel',1),(8,3,'Kebun Binatang Surabaya','Surabaya Zoo','± 630 m dari hotel','About 630 m from the hotel',2),(9,3,'Jalan Raya Darmo','Jalan Raya Darmo',NULL,NULL,3);
/*!40000 ALTER TABLE `attractions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `dining_outlets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dining_outlets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hotel_id` int(10) unsigned NOT NULL,
  `slug` varchar(60) NOT NULL,
  `name` varchar(120) NOT NULL,
  `type` varchar(60) NOT NULL COMMENT 'Mis. Restoran, Kafe, Bar',
  `type_en` varchar(60) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `hours` varchar(120) DEFAULT NULL,
  `hours_en` varchar(120) DEFAULT NULL,
  `image` varchar(160) DEFAULT NULL COMMENT 'Kunci gambar',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dining_slug` (`hotel_id`,`slug`),
  CONSTRAINT `fk_dining_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `dining_outlets` WRITE;
/*!40000 ALTER TABLE `dining_outlets` DISABLE KEYS */;
INSERT INTO `dining_outlets` VALUES (1,1,'news-resto','News Resto','Restoran','Restaurant','Restoran utama News Hotel Surabaya untuk sarapan, makan siang, dan makan malam, buka 24 jam.','The main restaurant at News Hotel Surabaya for breakfast, lunch and dinner, open 24 hours.','Buka 24 jam','Open 24 hours','surabaya/restaurant-wide',1,1),(2,1,'cafetaria','Cafetaria','Kafe & area santai','Café & lounge','Area santai beratap kaca dengan pemandangan taman, tempat yang pas untuk minum kopi sambil membaca koran pagi.','A glass-roofed lounge overlooking the garden, made for coffee and the morning paper.',NULL,NULL,'surabaya/garden-terrace',1,2),(3,3,'coffee-shop','Coffee Shop & Bar','Kafe & bar','Café & bar','Coffee shop dan bar untuk tamu Darmo Inn. Menu dan jam buka akan diumumkan.','A coffee shop and bar for Darmo Inn guests. Menu and opening hours to be announced.',NULL,NULL,'darmo-inn/lounge-render',1,1);
/*!40000 ALTER TABLE `dining_outlets` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `facilities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hotel_id` int(10) unsigned NOT NULL,
  `icon` varchar(40) NOT NULL DEFAULT 'check' COMMENT 'Nama ikon di assets/img/icons.svg',
  `name` varchar(120) NOT NULL,
  `name_en` varchar(120) DEFAULT NULL,
  `detail` varchar(255) DEFAULT NULL,
  `detail_en` varchar(255) DEFAULT NULL,
  `sort` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_fac_hotel` (`hotel_id`,`sort`),
  CONSTRAINT `fk_fac_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `facilities` WRITE;
/*!40000 ALTER TABLE `facilities` DISABLE KEYS */;
INSERT INTO `facilities` VALUES (1,1,'utensils','Restoran 24 jam','24-hour restaurant','News Resto, buka sepanjang hari','News Resto, open around the clock',1),(2,1,'coffee','Cafetaria','Cafeteria','Area santai dengan sentuhan alam','A relaxed corner with a garden view',2),(3,1,'presentation','Ruang meeting','Meeting rooms','Kapasitas 100 dan 150 peserta, dapat digabung untuk 200','For 100 and 150 guests, combinable for 200',3),(4,1,'car','Shuttle bandara','Airport shuttle','Gratis dengan perjanjian: Bandara Juanda, Terminal Purabaya, lokasi acara','Free by arrangement: Juanda Airport, Purabaya Terminal, event venues',4),(5,1,'wifi','WiFi gratis','Free WiFi','Di setiap kamar dan seluruh area hotel','In every room and across the hotel',5),(6,1,'parking','Parkir gratis','Free parking','Area parkir tanpa biaya bagi tamu','No charge for guests',6),(7,1,'shield','Keamanan 24 jam','24-hour security','CCTV dan akses kartu ke koridor kamar','CCTV and keycard access to guest floors',7),(8,1,'spa','Massage dalam kamar','In-room massage','Pukul 09.00–23.00, pemesanan sampai 21.00','09.00–23.00, last booking 21.00',8),(9,1,'taxi','Counter taksi','Taxi counter','Buka 24 jam','Open 24 hours',9),(10,1,'pill','Drugstore','Drugstore','Kebutuhan harian dan obat ringan','Daily essentials and basic medicine',10),(11,2,'pool','Kolam renang','Swimming pool',NULL,NULL,1),(12,2,'pin','Pusat kota','City-centre location','Jl. Veteran, Semarang','Jl. Veteran, Semarang',2),(14,3,'coffee','Coffee shop','Coffee shop',NULL,NULL,2),(15,3,'glass','Bar','Bar',NULL,NULL,3),(16,3,'leaf','Taman','Garden','Taman dalam dan taman terbuka','Inner and open-air gardens',4),(17,3,'wifi','WiFi gratis','Free WiFi',NULL,NULL,5),(18,3,'clock','Resepsionis 24 jam','24-hour front desk',NULL,NULL,6),(19,3,'parking','Parkir gratis','Free parking','Tempat terbatas','Limited spaces',7);
/*!40000 ALTER TABLE `facilities` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `gallery_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gallery_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hotel_id` int(10) unsigned NOT NULL,
  `category` enum('exterior','lobby','room','dining','meeting','pool','facility','event') NOT NULL DEFAULT 'facility',
  `image` varchar(160) NOT NULL COMMENT 'Kunci gambar',
  `caption` varchar(160) DEFAULT NULL,
  `caption_en` varchar(160) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = didahulukan di mozaik beranda hotel',
  `sort` smallint(6) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_gal_hotel` (`hotel_id`,`category`,`sort`),
  CONSTRAINT `fk_gal_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `gallery_images` WRITE;
/*!40000 ALTER TABLE `gallery_images` DISABLE KEYS */;
INSERT INTO `gallery_images` VALUES (1,1,'lobby','surabaya/lobby','Lobi dengan pilar bertuliskan \"news\" dalam berbagai bahasa','The lobby, with pillars printed with \"news\" in many languages',1,1,'2026-11-19 14:34:08'),(2,1,'lobby','surabaya/news-corner','News Corner untuk membaca koran pagi','The News Corner for the morning paper',1,2,'2026-11-19 14:34:08'),(3,1,'dining','surabaya/garden-terrace','Teras taman beratap kaca','The glass-roofed garden terrace',1,3,'2026-11-19 14:34:08'),(4,1,'room','surabaya/room-executive','Kamar Executive','Executive room',1,4,'2026-11-19 14:34:08'),(5,1,'lobby','surabaya/reception-wide','Meja resepsionis','Front desk',0,5,'2026-11-19 14:34:08'),(6,1,'dining','surabaya/restaurant-wide','News Resto','News Resto',0,6,'2026-11-19 14:34:08'),(7,1,'lobby','surabaya/lobby-lounge','Lounge bertema media','The media-themed lounge',0,7,'2026-11-19 14:34:08'),(8,1,'lobby','surabaya/lobby-reading','Sudut baca di lobi','Reading corner in the lobby',0,8,'2026-11-19 14:34:08'),(9,1,'room','surabaya/room-deluxe','Kamar Deluxe','Deluxe room',0,9,'2026-11-19 14:34:08'),(10,1,'room','surabaya/room-superior','Kamar Superior twin','Superior twin room',0,10,'2026-11-19 14:34:08'),(11,1,'room','surabaya/room-sitting','Area duduk kamar','Room sitting area',0,11,'2026-11-19 14:34:08'),(12,1,'facility','surabaya/corridor','Koridor kamar','Guest corridor',0,12,'2026-11-19 14:34:08'),(13,1,'dining','surabaya/buffet','Area prasmanan','Buffet counter',0,13,'2026-11-19 14:34:08'),(14,1,'meeting','surabaya/meeting','Ruang meeting','Meeting room',0,14,'2026-11-19 14:34:08'),(15,1,'facility','surabaya/shuttle','Kendaraan shuttle News Hotel','News Hotel shuttle cars',0,15,'2026-11-19 14:34:08'),(16,1,'facility','surabaya/drugstore','Drugstore','Drugstore',0,16,'2026-11-19 14:34:08'),(17,2,'exterior','semarang/facade','Fasad News Hotel Semarang saat soft opening','News Hotel Semarang at its soft opening',1,1,'2026-11-19 14:34:08'),(18,2,'exterior','semarang/facade-tall','Fasad dan lobi kaca','Facade and glass lobby',1,2,'2026-11-19 14:34:08'),(19,2,'exterior','semarang/facade-render','Ilustrasi fasad malam hari','Facade at night illustration',0,3,'2026-11-19 14:34:08'),(20,3,'lobby','darmo-inn/reception','Resepsionis Darmo Inn','Darmo Inn front desk',1,1,'2026-11-19 14:34:08'),(21,3,'exterior','darmo-inn/facade-render','Ilustrasi fasad','Facade illustration',1,2,'2026-11-19 14:34:08'),(22,3,'lobby','darmo-inn/lobby-render','Ilustrasi lobi','Lobby illustration',0,3,'2026-11-19 14:34:08'),(23,3,'lobby','darmo-inn/lounge-render','Ilustrasi lounge','Lounge illustration',0,4,'2026-11-19 14:34:08'),(24,3,'facility','darmo-inn/garden-render','Ilustrasi taman terbuka','Open-air garden illustration',1,5,'2026-11-19 14:34:08'),(25,3,'facility','darmo-inn/mini-garden-render','Ilustrasi taman dalam','Inner garden illustration',0,6,'2026-11-19 14:34:08'),(26,3,'lobby','darmo-inn/lobby-ornament-render','Ilustrasi detail lobi','Lobby detail illustration',0,7,'2026-11-19 14:34:08'),(27,3,'room','darmo-inn/room-render','Ilustrasi kamar','Room illustration',0,8,'2026-11-19 14:34:08');
/*!40000 ALTER TABLE `gallery_images` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `hotels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hotels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(40) NOT NULL COMMENT 'Jangan diubah: sama dengan nama folder halaman (surabaya, semarang, darmo-inn)',
  `name` varchar(120) NOT NULL,
  `short_name` varchar(60) NOT NULL COMMENT 'Nama pendek untuk tab menu',
  `brand` enum('news','darmo') NOT NULL DEFAULT 'news' COMMENT 'Tema warna: news = oranye, darmo = plum & emas',
  `star` tinyint(3) unsigned DEFAULT NULL,
  `status` enum('open','soft_opening','coming_soon') NOT NULL DEFAULT 'open',
  `address` varchar(255) NOT NULL,
  `city` varchar(80) NOT NULL,
  `province` varchar(80) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `whatsapp` varchar(30) DEFAULT NULL COMMENT 'Nomor WA reservasi, mis. 0812xxxx. Kosong = pakai nomor WA grup di includes/config.php',
  `email` varchar(120) DEFAULT NULL,
  `instagram` varchar(60) DEFAULT NULL COMMENT 'Tanpa @',
  `total_rooms` smallint(5) unsigned DEFAULT NULL,
  `tagline` varchar(160) DEFAULT NULL,
  `tagline_en` varchar(160) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `highlight` varchar(160) DEFAULT NULL COMMENT 'Satu kalimat ciri khas, tampil di kartu hotel',
  `highlight_en` varchar(160) DEFAULT NULL,
  `policies` text DEFAULT NULL COMMENT 'Markdown',
  `policies_en` text DEFAULT NULL,
  `hero_image` varchar(160) DEFAULT NULL COMMENT 'Kunci gambar',
  `card_image` varchar(160) DEFAULT NULL COMMENT 'Kunci gambar',
  `logo` varchar(160) DEFAULT NULL,
  `operator` varchar(120) DEFAULT NULL,
  `virtual_tour_url` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `sort` smallint(6) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `hotels` WRITE;
/*!40000 ALTER TABLE `hotels` DISABLE KEYS */;
INSERT INTO `hotels` VALUES (1,'surabaya','News Hotel Surabaya','Surabaya','news',2,'open','Jl. Pondok Maspion Blok S No. 1, Pepelegi, Waru','Sidoarjo','Jawa Timur','61256',-7.3698825,112.7257957,NULL,'087750998583','info@newshotelsby.com','newshotelsidoarjo',NULL,'Melayani sepenuh hati','We serve you wholeheartedly','News Hotel Surabaya adalah hotel bintang 2 premium di Waru, Sidoarjo, gerbang selatan Surabaya, yang beroperasi sejak 2016. Lokasinya dekat Bandara Internasional Juanda, Terminal Purabaya, dan pusat belanja City of Tomorrow. Berada di bawah naungan Graha Pena Jawa Pos Group, hotel ini membawa nuansa media dari lobi hingga kamar: pilar bertuliskan \"news\" dalam berbagai bahasa, News Corner untuk membaca koran, dan sentuhan oranye khas News Hotel.','News Hotel Surabaya is a premium 2-star hotel in Waru, Sidoarjo, on the southern edge of Surabaya, open since 2016. It sits close to Juanda International Airport, Purabaya bus terminal and the City of Tomorrow mall. Part of the Graha Pena Jawa Pos Group, the hotel carries a media theme from lobby to room: pillars printed with the word \"news\" in many languages, a News Corner for the morning paper, and News Hotel\'s signature orange.','Dekat Bandara Juanda & Terminal Purabaya','Close to Juanda Airport & Purabaya Terminal','- Seluruh kamar bebas asap rokok; area merokok tersedia di luar ruangan.\n- Hewan peliharaan tidak diizinkan.\n- Extra bed belum tersedia.\n- Late check-out sampai pukul 18.00 dikenakan biaya 50% dari tarif kamar harian, tergantung ketersediaan.\n- Pembatalan dalam 14 hari sebelum kedatangan dikenakan biaya 1 malam; dalam 7 hari sebelum kedatangan atau tidak datang dikenakan harga penuh.\n- Satu reservasi maksimal 10 kamar.','- All rooms are non-smoking; a smoking area is available outdoors.\n- Pets are not allowed.\n- Extra beds are not yet available.\n- Late check-out until 18.00 is charged at 50% of the daily room rate, subject to availability.\n- Cancellations within 14 days of arrival are charged one night; within 7 days or no-show, the full stay.\n- A single reservation can hold up to 10 rooms.','surabaya/garden-terrace','surabaya/news-corner','brand/news-hotel','PT Graha Pena Jawa Pos','https://www.newshotelsby.com/virtual360/',1,1,'2026-11-19 14:34:08','2026-11-24 13:42:59'),(2,'semarang','News Hotel Semarang','Semarang','news',2,'soft_opening','Jl. Veteran No. 55, Lempongsari, Gajahmungkur','Semarang','Jawa Tengah',NULL,NULL,NULL,NULL,'087750998583',NULL,'newshotelsimpanglima',NULL,NULL,NULL,'News Hotel Semarang adalah hotel bujet bintang dua di pusat Kota Semarang yang dilengkapi kolam renang. Hotel ini mengusung konsep kearifan lokal, menghadirkan unsur budaya dan simbol ikonik daerah dalam pengalaman menginap. Soft launching digelar pada 31 Oktober 2026 sebagai hotel terbaru jaringan Jawa Pos Group, setelah News Hotel di Sidoarjo dan Surabaya.','News Hotel Semarang is a two-star budget hotel in central Semarang with a swimming pool. Built around a \"local wisdom\" concept, it brings regional culture and iconic local symbols into the stay. It held its soft launch on 31 October 2026 as the newest hotel in the Jawa Pos Group network, after News Hotel in Sidoarjo and Surabaya.','Baru dibuka · kolam renang di pusat kota','Newly opened · a pool in the city centre',NULL,NULL,'semarang/facade','semarang/facade-render','brand/news-hotel','PT Radar Properti Indonesia',NULL,1,2,'2026-11-19 14:34:08','2026-11-24 13:42:59'),(3,'darmo-inn','Darmo Inn by News Hotel','Darmo Inn','darmo',NULL,'open','Jl. Progo No. 5','Surabaya','Jawa Timur',NULL,NULL,NULL,NULL,'087750998583',NULL,'darmoinnsurabaya',NULL,'Feel at Home','Feel at Home','Darmo Inn by News Hotel adalah penginapan baru di kawasan Darmo, Surabaya, tidak jauh dari Taman Bungkul. Di area hotel tersedia coffee shop, bar, dan taman hijau. Darmo Inn dikelola oleh PT Graha Pena Jawa Pos dan menjadi bagian dari keluarga News Hotel.','Darmo Inn by News Hotel is a new stay in the Darmo district of Surabaya, not far from Taman Bungkul. The hotel has a coffee shop, a bar and green gardens on site. Darmo Inn is run by PT Graha Pena Jawa Pos and is part of the News Hotel family.','Dekat Taman Bungkul, Surabaya','Close to Taman Bungkul, Surabaya',NULL,NULL,'darmo-inn/facade-render','darmo-inn/reception','brand/darmo-inn-dark','PT Graha Pena Jawa Pos',NULL,1,3,'2026-11-19 14:34:08','2026-11-24 13:42:59');
/*!40000 ALTER TABLE `hotels` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `meeting_venues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meeting_venues` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hotel_id` int(10) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `name_en` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `size_m2` decimal(6,1) DEFAULT NULL,
  `cap_theatre` smallint(5) unsigned DEFAULT NULL,
  `cap_classroom` smallint(5) unsigned DEFAULT NULL,
  `cap_ushape` smallint(5) unsigned DEFAULT NULL,
  `cap_banquet` smallint(5) unsigned DEFAULT NULL,
  `image` varchar(160) DEFAULT NULL COMMENT 'Kunci gambar',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_venue_hotel` (`hotel_id`),
  CONSTRAINT `fk_venue_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `meeting_venues` WRITE;
/*!40000 ALTER TABLE `meeting_venues` DISABLE KEYS */;
INSERT INTO `meeting_venues` VALUES (1,1,'Ruang Meeting 1','Meeting Room 1','Ruang meeting untuk 100 peserta. Dapat digabung dengan Ruang Meeting 2 untuk acara hingga 200 peserta.','A meeting room for 100 guests. Combine it with Meeting Room 2 for events of up to 200.',NULL,100,NULL,NULL,NULL,'surabaya/meeting',1,1),(2,1,'Ruang Meeting 2','Meeting Room 2','Ruang meeting untuk 150 peserta. Dapat digabung dengan Ruang Meeting 1 untuk acara hingga 200 peserta.','A meeting room for 150 guests. Combine it with Meeting Room 1 for events of up to 200.',NULL,150,NULL,NULL,NULL,'surabaya/meeting',1,2);
/*!40000 ALTER TABLE `meeting_venues` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hotel_id` int(10) unsigned DEFAULT NULL COMMENT 'Hotel terkait; kosong = artikel grup',
  `slug` varchar(120) NOT NULL COMMENT 'Alamat artikel: /blog/slug (huruf kecil, angka, tanda -)',
  `category` enum('news','event','promo') NOT NULL DEFAULT 'news' COMMENT 'news = Berita, event = Acara, promo = Promo',
  `title` varchar(180) NOT NULL,
  `title_en` varchar(180) DEFAULT NULL,
  `excerpt` varchar(300) DEFAULT NULL COMMENT 'Ringkasan 1-2 kalimat untuk kartu artikel',
  `excerpt_en` varchar(300) DEFAULT NULL,
  `body` mediumtext DEFAULT NULL COMMENT 'Isi artikel dalam Markdown',
  `body_en` mediumtext DEFAULT NULL,
  `cover` varchar(160) DEFAULT NULL COMMENT 'Kunci gambar sampul',
  `source_url` varchar(255) DEFAULT NULL COMMENT 'Tautan sumber berita (opsional)',
  `status` enum('draft','published') NOT NULL DEFAULT 'draft' COMMENT 'Hanya published yang tampil',
  `published_at` datetime DEFAULT NULL COMMENT 'Tanggal terbit; artikel bertanggal di masa depan belum tampil',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_post_hotel` (`hotel_id`),
  KEY `idx_post_pub` (`status`,`published_at`),
  CONSTRAINT `fk_post_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,2,'news-hotel-semarang-resmi-soft-launching','news','News Hotel Semarang Resmi Soft Launching di Jalan Veteran','News Hotel Semarang Holds Its Soft Launch on Jalan Veteran','Hotel bujet bintang dua dengan kolam renang ini menjadi hotel terbaru jaringan Jawa Pos Group, mengusung konsep kearifan lokal di pusat Kota Semarang.','The two-star budget hotel with a swimming pool is the newest in the Jawa Pos Group network, bringing a local-wisdom concept to central Semarang.','News Hotel Semarang resmi diperkenalkan melalui soft launching pada Sabtu, 31 Oktober 2026. Pita dipotong oleh Direktur PT Radar Properti Indonesia, Yushe Marleyna, bersama CEO Jawa Pos Media, Leak Kustiyo.\n\nBerlokasi di **Jalan Veteran No. 55, Semarang**, hotel bintang dua ini melengkapi jaringan News Hotel setelah Sidoarjo dan Surabaya. News Hotel Semarang mengusung konsep kearifan lokal: unsur budaya dan simbol ikonik daerah dihadirkan dalam pengalaman menginap, dan tamu juga dapat menikmati kolam renang.\n\nMenurut Yushe Marleyna, Semarang adalah daya tarik wisata, pusat kuliner, sekaligus kota pertemuan bisnis, sehingga pasar perhotelannya menjanjikan. Tarif kamar diumumkan mulai **Rp250 ribu per malam**.\n\n*Disarikan dari pemberitaan Radar Semarang dan Radar Kudus (Jawa Pos), November 2026.*','News Hotel Semarang was introduced at a soft launch on Saturday, 31 October 2026. The ribbon was cut by PT Radar Properti Indonesia director Yushe Marleyna together with Jawa Pos Media CEO Leak Kustiyo.\n\nLocated at **Jalan Veteran No. 55, Semarang**, the two-star hotel joins the News Hotel network after Sidoarjo and Surabaya. Its concept is local wisdom: regional culture and iconic local symbols are woven into the stay, and guests can enjoy a swimming pool.\n\nYushe Marleyna described Semarang as a tourism draw, a culinary hub and a city of business meetings, which makes its hotel market promising. Room rates were announced from **Rp250,000 per night**.\n\n*Summarised from Radar Semarang and Radar Kudus (Jawa Pos) reporting, November 2026.*','semarang/facade','https://radarsemarang.jawapos.com/hotel/2610310019/news-hotel-semarang-resmi-diperkenalkan-tawarkan-konsep-kearifan-lokal','published','2026-11-02 09:00:00','2026-11-19 14:34:08','2026-11-19 14:34:08'),(2,3,'darmo-inn-by-news-hotel-hadir-di-surabaya','news','Darmo Inn by News Hotel Hadir di Kawasan Darmo, Surabaya','Darmo Inn by News Hotel Opens in Surabaya\'s Darmo District','Penginapan baru keluarga News Hotel di Jalan Progo No. 5, dengan taman dalam, coffee shop, dan bar, tidak jauh dari Taman Bungkul.','The newest stay in the News Hotel family at Jalan Progo No. 5, with an inner garden, a coffee shop and a bar, not far from Taman Bungkul.','Keluarga News Hotel bertambah dengan hadirnya **Darmo Inn by News Hotel** di Jalan Progo No. 5, Surabaya. Mengusung semangat *Feel at Home*, Darmo Inn menawarkan taman dalam yang hijau, serta coffee shop dan bar untuk tamu.\n\nLokasinya di kawasan Darmo membuat Taman Bungkul berjarak sekitar 560 m dari hotel dan Kebun Binatang Surabaya sekitar 630 m.\n\nDarmo Inn dikelola oleh PT Graha Pena Jawa Pos, bagian dari Jawa Pos Group yang juga menaungi News Hotel Surabaya dan News Hotel Semarang.','The News Hotel family has grown with **Darmo Inn by News Hotel** at Jalan Progo No. 5, Surabaya. True to its *Feel at Home* spirit, the inner garden is green, and guests have a coffee shop and bar on site.\n\nIn the Darmo district, Taman Bungkul is about 560 m from the hotel and Surabaya Zoo about 630 m.\n\nDarmo Inn is run by PT Graha Pena Jawa Pos, part of the Jawa Pos Group alongside News Hotel Surabaya and News Hotel Semarang.','darmo-inn/reception',NULL,'published','2026-11-10 09:00:00','2026-11-19 14:34:08','2026-11-23 14:16:06'),(3,NULL,'satu-grup-tiga-hotel','news','Satu Grup, Tiga Hotel: Mengenal Keluarga News Hotel','Get to know News Hotel Family','Dekat bandara di Surabaya, kolam renang di pusat Semarang, atau penginapan baru di Darmo: pilih News Hotel yang paling pas untuk perjalanan Anda.','Near the airport in Surabaya, a pool in central Semarang, or a new stay in Darmo: choose the News Hotel that fits your trip.','News Hotel adalah jaringan hotel di bawah Jawa Pos Group. Kini ada tiga pilihan tempat menginap dengan karakter berbeda.\n\n## News Hotel Surabaya\nDi Waru, Sidoarjo, dekat Bandara Juanda dan Terminal Purabaya. Nuansa media terasa dari lobi hingga kamar, dengan restoran 24 jam, ruang meeting hingga 200 peserta, dan shuttle bandara gratis dengan perjanjian.\n\n## News Hotel Semarang\nHotel terbaru di Jalan Veteran No. 55, pusat Kota Semarang, dengan kolam renang dan konsep kearifan lokal.\n\n## Darmo Inn by News Hotel\nDi Jalan Progo No. 5, Surabaya, tidak jauh dari Taman Bungkul.\n\nTanyakan ketersediaan dan tarif terkini langsung ke hotel lewat WhatsApp.','News Hotel is a hotel network under the Jawa Pos Group, now with three stays of different character.\n\n## News Hotel Surabaya\nIn Waru, Sidoarjo, near Juanda Airport and Purabaya Terminal. The media theme runs from lobby to room, with a 24-hour restaurant, meeting rooms for up to 200, and a free airport shuttle by arrangement.\n\n## News Hotel Semarang\nThe newest hotel, at Jalan Veteran No. 55 in central Semarang, with a swimming pool and a local-wisdom concept.\n\n## Darmo Inn by News Hotel\nAt Jalan Progo No. 5, Surabaya, not far from Taman Bungkul.\n\nMessage the hotel on WhatsApp for current availability and rates.','surabaya/lobby',NULL,'published','2026-11-15 09:00:00','2026-11-19 14:34:08','2026-11-23 14:16:06');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `room_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `room_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `room_type_id` int(10) unsigned NOT NULL,
  `image` varchar(160) NOT NULL COMMENT 'Kunci gambar',
  `alt` varchar(160) DEFAULT NULL,
  `alt_en` varchar(160) DEFAULT NULL,
  `sort` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_rimg_room` (`room_type_id`),
  CONSTRAINT `fk_rimg_room` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `room_images` WRITE;
/*!40000 ALTER TABLE `room_images` DISABLE KEYS */;
INSERT INTO `room_images` VALUES (1,1,'surabaya/room-standard','Kamar Standard dengan ranjang Queen','Standard room with a Queen bed',1),(2,2,'surabaya/room-superior','Kamar Superior twin','Superior twin room',1),(3,2,'surabaya/room-superior-2','Kamar Superior twin dengan meja kerja','Superior twin room with desk',2),(4,3,'surabaya/room-deluxe','Kamar Deluxe','Deluxe room',1),(5,3,'surabaya/room-deluxe-2','Kamar Deluxe dengan meja kerja','Deluxe room with desk',2),(6,4,'surabaya/room-executive','Kamar Executive','Executive room',1),(7,4,'surabaya/room-executive-2','Kamar Executive dengan jendela lebar','Executive room with wide windows',2),(8,4,'surabaya/room-sitting','Area duduk','Sitting area',3),(12,11,'semarang/room-business','Business Room: tempat tidur single dengan mural foto lawas Semarang','Business Room: single bed with a vintage Semarang photo mural',1),(13,16,'semarang/room-deluxe-double-pool','Deluxe Double Pool Access: tempat tidur double dengan jendela besar menghadap kolam renang','Deluxe Double Pool Access: double bed with a large window onto the swimming pool',1),(14,15,'semarang/room-deluxe-twin-1','Deluxe Twin: dua tempat tidur terpisah, TV, dan kursi sofa','Deluxe Twin: two single beds, TV and a sofa chair',1),(15,15,'semarang/room-deluxe-twin-2','Deluxe Twin: sofa, gantungan baju, dan meja TV','Deluxe Twin: sofa, clothes rack and TV desk',2),(16,17,'semarang/room-executive-1','Executive Room: tempat tidur double dengan mural foto lawas','Executive Room: double bed with a vintage photo mural',1),(17,17,'semarang/room-executive-2','Executive Room: tempat tidur dan jendela dengan tirai cokelat','Executive Room: bed and window with brown curtains',2),(18,17,'semarang/room-executive-3','Executive Room: sudut dengan lukisan abstrak dan dua pouf','Executive Room: corner with an abstract painting and two poufs',3),(19,18,'semarang/room-family-triple-1','Family Triple Room: tiga tempat tidur dengan mural bangunan bersejarah','Family Triple Room: three beds with a heritage building mural',1),(20,18,'semarang/room-family-triple-2','Family Triple Room: tiga tempat tidur, TV, dan meja kerja','Family Triple Room: three beds, TV and work desk',2),(21,18,'semarang/room-bathroom','Kamar mandi dengan shower dan cermin berlampu','Bathroom with a shower and a backlit mirror',3),(22,12,'semarang/room-superior-double-1','Superior Double: tempat tidur double dengan mural foto lawas','Superior Double: double bed with a vintage photo mural',1),(23,12,'semarang/room-superior-double-2','Superior Double: meja TV, meja kerja, dan pintu masuk kamar','Superior Double: TV desk, work desk and room entrance',2),(24,12,'semarang/room-bathroom','Kamar mandi dengan shower dan cermin berlampu','Bathroom with a shower and a backlit mirror',3),(25,19,'darmo-inn/room-superior-twin','Superior Twin: dua tempat tidur terpisah, TV, dan meja kerja','Superior Twin: two single beds, TV and work desk',1),(26,19,'darmo-inn/room-superior-double','Superior Double: tempat tidur double dengan mural jembatan malam hari','Superior Double: double bed with a night bridge mural',2);
/*!40000 ALTER TABLE `room_images` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `room_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `room_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hotel_id` int(10) unsigned NOT NULL,
  `slug` varchar(60) NOT NULL COMMENT 'Dipakai sebagai jangkar #slug di halaman kamar',
  `name` varchar(100) NOT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `size_m2` decimal(5,1) DEFAULT NULL,
  `bed_type` varchar(60) DEFAULT NULL,
  `bed_type_en` varchar(60) DEFAULT NULL,
  `max_adults` tinyint(3) unsigned NOT NULL DEFAULT 2,
  `max_children` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `base_price` int(10) unsigned DEFAULT NULL COMMENT 'Tarif "mulai dari" per malam (Rupiah). Kosong = "Tanyakan tarif via WhatsApp"',
  `amenities` text DEFAULT NULL COMMENT 'JSON: [{"icon":"wifi","name":"WiFi","name_en":"WiFi"}, ...]',
  `cover_image` varchar(160) DEFAULT NULL COMMENT 'Kunci gambar',
  `virtual_tour_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort` smallint(6) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_room_slug` (`hotel_id`,`slug`),
  CONSTRAINT `fk_room_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `room_types` WRITE;
/*!40000 ALTER TABLE `room_types` DISABLE KEYS */;
INSERT INTO `room_types` VALUES (1,1,'standard','Standard','Standard','Kamar seluas 17 m² ini dirancang bagi pebisnis atau individual traveler yang ingin merasakan kenyamanan dan ketenangan dalam satu waktu. Tempat tidur berukuran Queen membuat istirahat terasa lebih sempurna, ditunjang handuk yang kami sediakan tanpa biaya.','A 17 m² room for business and solo travellers who want comfort and quiet in one place. A Queen bed makes for a proper night\'s rest, and towels are provided free of charge.',17.0,'Queen','Queen',2,0,NULL,'[{\"icon\":\"tv\",\"name\":\"TV LCD\",\"name_en\":\"LCD TV\"},{\"icon\":\"snow\",\"name\":\"AC dengan remote\",\"name_en\":\"Air conditioning\"},{\"icon\":\"shower\",\"name\":\"Shower\",\"name_en\":\"Shower\"},{\"icon\":\"lock\",\"name\":\"Safety box\",\"name_en\":\"Safe\"},{\"icon\":\"wifi\",\"name\":\"WiFi gratis\",\"name_en\":\"Free WiFi\"},{\"icon\":\"key\",\"name\":\"Kunci keycard\",\"name_en\":\"Keycard\"},{\"icon\":\"towel\",\"name\":\"Handuk gratis\",\"name_en\":\"Free towels\"}]','surabaya/room-standard',NULL,1,1,'2026-11-19 14:34:08','2026-11-19 14:34:08'),(2,1,'superior','Superior','Superior','Tipe kamar dengan pilihan terbanyak, baik jumlah maupun letak: total 59 kamar yang tersebar di setiap lantai, dengan pilihan twin bed atau double bed. Kamar seluas 20 m² ini cocok untuk keluarga yang mencari kenyamanan menginap di hotel.','Our most plentiful room type: 59 rooms spread across every floor, with a choice of twin or double beds. At 20 m², it suits families looking for a comfortable stay.',20.0,'Twin atau Double','Twin or double',2,1,350000,'[{\"icon\":\"tv\",\"name\":\"TV LCD\",\"name_en\":\"LCD TV\"},{\"icon\":\"snow\",\"name\":\"AC dengan remote\",\"name_en\":\"Air conditioning\"},{\"icon\":\"shower\",\"name\":\"Shower\",\"name_en\":\"Shower\"},{\"icon\":\"lock\",\"name\":\"Safety box\",\"name_en\":\"Safe\"},{\"icon\":\"wifi\",\"name\":\"WiFi gratis\",\"name_en\":\"Free WiFi\"},{\"icon\":\"key\",\"name\":\"Kunci keycard\",\"name_en\":\"Keycard\"}]','surabaya/room-superior',NULL,1,2,'2026-11-19 14:34:08','2026-11-24 14:06:59'),(3,1,'deluxe','Deluxe','Deluxe','Kamar Deluxe sangat direkomendasikan untuk berbagai kalangan tamu, baik individual, pebisnis, maupun keluarga. Dengan luas 23 m², kamar terasa lega dan nyaman. Desain yang segar dengan sentuhan modern menambah nilai lebih kamar ini, dilengkapi welcome drink, sandal hotel, dan dental kit.','Deluxe suits every kind of guest, from solo and business travellers to families. At 23 m² it feels roomy, with a fresh, modern design plus a welcome drink, slippers and a dental kit.',23.0,NULL,NULL,2,1,450000,'[{\"icon\":\"tv\",\"name\":\"TV LCD\",\"name_en\":\"LCD TV\"},{\"icon\":\"snow\",\"name\":\"AC dengan remote\",\"name_en\":\"Air conditioning\"},{\"icon\":\"shower\",\"name\":\"Shower\",\"name_en\":\"Shower\"},{\"icon\":\"lock\",\"name\":\"Safety box\",\"name_en\":\"Safe\"},{\"icon\":\"wifi\",\"name\":\"WiFi gratis\",\"name_en\":\"Free WiFi\"},{\"icon\":\"glass\",\"name\":\"Welcome drink\",\"name_en\":\"Welcome drink\"},{\"icon\":\"slipper\",\"name\":\"Sandal hotel\",\"name_en\":\"Slippers\"},{\"icon\":\"tooth\",\"name\":\"Dental kit\",\"name_en\":\"Dental kit\"}]','surabaya/room-deluxe',NULL,1,3,'2026-11-19 14:34:08','2026-11-24 14:06:59'),(4,1,'executive','Executive','Executive','Kamar terluas di News Hotel Surabaya, 26 m², dengan AC yang dapat diatur, akses internet, dan TV 32 inci dengan saluran dalam dan luar negeri.','The largest room at News Hotel Surabaya, 26 m², with adjustable air conditioning, internet access, and a 32-inch TV with local and international channels.',26.0,NULL,NULL,2,1,550000,'[{\"icon\":\"tv\",\"name\":\"TV 32 inci, saluran dalam & luar negeri\",\"name_en\":\"32-inch TV, local & international channels\"},{\"icon\":\"snow\",\"name\":\"AC dengan remote\",\"name_en\":\"Air conditioning\"},{\"icon\":\"shower\",\"name\":\"Shower\",\"name_en\":\"Shower\"},{\"icon\":\"lock\",\"name\":\"Safety box\",\"name_en\":\"Safe\"},{\"icon\":\"wifi\",\"name\":\"WiFi gratis\",\"name_en\":\"Free WiFi\"},{\"icon\":\"key\",\"name\":\"Kunci keycard\",\"name_en\":\"Keycard\"}]','surabaya/room-executive',NULL,1,4,'2026-11-19 14:34:08','2026-11-24 14:06:59'),(11,2,'business-room','Business Room','Business Room','Kamar praktis, nyaman, dan efisien untuk kebutuhan singgah atau perjalanan bisnis.','A practical, comfortable and efficient room for a quick stop or a business trip.',6.0,'Single','Single',0,0,250000,'[{\"icon\":\"tv\",\"name\":\"Smart TV\",\"name_en\":\"Smart TV\"},{\"icon\":\"wifi\",\"name\":\"WiFi\",\"name_en\":\"WiFi\"},{\"icon\":\"shower\",\"name\":\"Water heater\",\"name_en\":\"Water heater\"},{\"icon\":\"check\",\"name\":\"Cocok untuk transit\",\"name_en\":\"Suitable for transit\"}]','semarang/room-business',NULL,1,1,'2026-11-23 14:14:55','2026-11-24 13:42:59'),(12,2,'superior-double','Superior Double','Superior Double','Kamar nyaman dan modern dengan tempat tidur double, cocok untuk istirahat yang lebih tenang dan berkualitas.','A comfortable, modern room with a double bed, made for calmer, better-quality rest.',16.0,'Double','Double',0,0,285000,'[{\"icon\":\"tv\",\"name\":\"Smart TV\",\"name_en\":\"Smart TV\"},{\"icon\":\"wifi\",\"name\":\"WiFi\",\"name_en\":\"WiFi\"},{\"icon\":\"shower\",\"name\":\"Water heater\",\"name_en\":\"Water heater\"}]','semarang/room-superior-double-1',NULL,1,2,'2026-11-23 14:14:55','2026-11-24 13:42:59'),(13,2,'superior-twin','Superior Twin','Superior Twin','Kamar modern dan nyaman dengan dua tempat tidur terpisah, cocok untuk perjalanan bisnis maupun liburan.','A modern, comfortable room with two separate beds, suited to business trips and holidays alike.',16.0,'Twin (2 tempat tidur terpisah)','Twin (2 separate beds)',0,0,285000,'[{\"icon\":\"tv\",\"name\":\"Smart TV\",\"name_en\":\"Smart TV\"},{\"icon\":\"wifi\",\"name\":\"WiFi\",\"name_en\":\"WiFi\"},{\"icon\":\"shower\",\"name\":\"Water heater\",\"name_en\":\"Water heater\"}]',NULL,NULL,1,3,'2026-11-23 14:14:55','2026-11-23 14:14:55'),(14,2,'deluxe-double-twin-with-view','Deluxe Double / Twin with View','Deluxe Double / Twin with View','Kamar seluas 24 m² dengan tempat tidur double atau twin dan pemandangan kolam renang.','A 24 m² room with a double or twin bed and a view of the swimming pool.',24.0,'Double atau Twin','Double or Twin',0,0,455000,'[{\"icon\":\"tv\",\"name\":\"Smart TV\",\"name_en\":\"Smart TV\"},{\"icon\":\"wifi\",\"name\":\"WiFi\",\"name_en\":\"WiFi\"},{\"icon\":\"shower\",\"name\":\"Water heater\",\"name_en\":\"Water heater\"},{\"icon\":\"waves\",\"name\":\"Pemandangan kolam renang\",\"name_en\":\"Swimming pool view\"}]',NULL,NULL,1,4,'2026-11-23 14:14:55','2026-11-23 14:14:55'),(15,2,'deluxe-twin-pool-access','Deluxe Twin Pool Access','Deluxe Twin Pool Access','Kamar nyaman dan modern dengan dua tempat tidur terpisah, dilengkapi akses langsung ke area kolam renang, cocok untuk perjalanan bisnis, liburan bersama teman, maupun keluarga.','A comfortable, modern room with two separate beds and direct access to the pool area, ideal for business trips, holidays with friends or family stays.',20.0,'Twin (2 tempat tidur terpisah)','Twin (2 separate beds)',0,0,540000,'[{\"icon\":\"tv\",\"name\":\"Smart TV\",\"name_en\":\"Smart TV\"},{\"icon\":\"wifi\",\"name\":\"WiFi\",\"name_en\":\"WiFi\"},{\"icon\":\"shower\",\"name\":\"Water heater\",\"name_en\":\"Water heater\"},{\"icon\":\"waves\",\"name\":\"Akses langsung ke kolam renang\",\"name_en\":\"Direct access to the swimming pool\"}]','semarang/room-deluxe-twin-1',NULL,1,5,'2026-11-23 14:14:55','2026-11-24 13:42:59'),(16,2,'deluxe-double-pool-access','Deluxe Double Pool Access','Deluxe Double Pool Access','Pilihan kamar yang nyaman dan modern dengan akses langsung ke area kolam renang, cocok untuk pengalaman menginap yang lebih praktis dan menyenangkan.','A comfortable, modern room with direct access to the pool area, made for a more practical and enjoyable stay.',20.0,'Double','Double',0,0,600000,'[{\"icon\":\"tv\",\"name\":\"Smart TV\",\"name_en\":\"Smart TV\"},{\"icon\":\"wifi\",\"name\":\"WiFi\",\"name_en\":\"WiFi\"},{\"icon\":\"shower\",\"name\":\"Water heater\",\"name_en\":\"Water heater\"},{\"icon\":\"waves\",\"name\":\"Akses langsung ke kolam renang\",\"name_en\":\"Direct access to the swimming pool\"}]','semarang/room-deluxe-double-pool',NULL,1,6,'2026-11-23 14:14:55','2026-11-24 13:42:59'),(17,2,'executive-double-pool-access','Executive Double Pool Access','Executive Double Pool Access','Kamar eksklusif dengan akses langsung ke area kolam renang, menghadirkan kenyamanan lebih untuk pengalaman menginap yang praktis dan menyenangkan.','An exclusive room with direct access to the pool area, bringing extra comfort to a practical and enjoyable stay.',34.0,'Double','Double',0,0,650000,'[{\"icon\":\"tv\",\"name\":\"Smart TV\",\"name_en\":\"Smart TV\"},{\"icon\":\"wifi\",\"name\":\"WiFi\",\"name_en\":\"WiFi\"},{\"icon\":\"shower\",\"name\":\"Water heater\",\"name_en\":\"Water heater\"},{\"icon\":\"waves\",\"name\":\"Akses langsung ke kolam renang\",\"name_en\":\"Direct access to the swimming pool\"}]','semarang/room-executive-1',NULL,1,7,'2026-11-23 14:14:55','2026-11-24 13:42:59'),(18,2,'family-triple-room','Family Triple Room','Family Triple Room','Pilihan tepat untuk keluarga atau rombongan kecil. Kamar luas, nyaman, dan dilengkapi 3 tempat tidur untuk istirahat yang lebih menyenangkan.','A great choice for families or small groups: a spacious, comfortable room with 3 beds for a more restful stay.',28.0,'3 tempat tidur','3 beds',0,0,700000,'[{\"icon\":\"tv\",\"name\":\"Smart TV\",\"name_en\":\"Smart TV\"},{\"icon\":\"wifi\",\"name\":\"WiFi\",\"name_en\":\"WiFi\"},{\"icon\":\"shower\",\"name\":\"Water heater\",\"name_en\":\"Water heater\"}]','semarang/room-family-triple-1',NULL,1,8,'2026-11-23 14:14:55','2026-11-24 13:42:59'),(19,3,'superior-double-twin','Superior Double / Twin','Superior Double / Twin','Kamar Superior seluas 14 m² dengan 1 tempat tidur double atau 2 tempat tidur twin. Bebas asap rokok.','A 14 m² Superior room with 1 double bed or 2 twin beds. Non-smoking.',14.0,'1 double atau 2 twin','1 double or 2 twin beds',0,0,NULL,'[{\"icon\":\"check\",\"name\":\"Bebas asap rokok\",\"name_en\":\"Non-smoking\"},{\"icon\":\"shower\",\"name\":\"Shower\",\"name_en\":\"Shower\"},{\"icon\":\"shower-head\",\"name\":\"Air panas\",\"name_en\":\"Hot water\"},{\"icon\":\"snow\",\"name\":\"AC\",\"name_en\":\"Air conditioning\"},{\"icon\":\"wifi\",\"name\":\"WiFi gratis\",\"name_en\":\"Free WiFi\"}]','darmo-inn/room-superior-twin',NULL,1,1,'2026-11-23 14:14:55','2026-11-24 13:42:59');
/*!40000 ALTER TABLE `room_types` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `key` varchar(80) NOT NULL,
  `value` text DEFAULT NULL,
  `label` varchar(160) DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('site_name','News Hotel','Nama situs'),('tagline_en','We serve you wholeheartedly','Tagline grup (English)'),('tagline_id','Melayani sepenuh hati','Tagline grup (Indonesia)'),('whatsapp_group','','Nomor WhatsApp cadangan untuk semua hotel (dipakai jika nomor hotel kosong)');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


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
DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `username` varchar(60) DEFAULT NULL,
  `email` varchar(160) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','hotel_admin') NOT NULL DEFAULT 'hotel_admin',
  `hotel_id` int(10) unsigned DEFAULT NULL COMMENT 'Wajib untuk hotel_admin; kosong untuk super_admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = wajib ganti password saat login berikutnya',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_admin_hotel` (`hotel_id`),
  CONSTRAINT `fk_admin_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_attempts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `email` varchar(160) NOT NULL,
  `attempted_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_attempt_ip` (`ip`,`attempted_at`),
  KEY `idx_attempt_email` (`email`,`attempted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

