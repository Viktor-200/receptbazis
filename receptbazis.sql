-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2026 at 08:59 AM
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
-- Database: `receptbazis`
--

-- --------------------------------------------------------

--
-- Table structure for table `felhasznalok`
--

CREATE TABLE `felhasznalok` (
  `id` int(11) NOT NULL,
  `felhasznalonev` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `jelszo` varchar(255) NOT NULL,
  `regisztracio_ideje` timestamp NOT NULL DEFAULT current_timestamp(),
  `profilkep` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- Dumping data for table `felhasznalok`
--

INSERT INTO `felhasznalok` (`id`, `felhasznalonev`, `email`, `jelszo`, `regisztracio_ideje`, `profilkep`, `is_admin`) VALUES
(1, 'Viktor', 'viktor@receptbazis.com', '$2y$10$q4LyqOjvMt5x/SJ36lCX5emcPuYY0woGSx5.Im5VZb.5k7L9oqLR2', '2025-09-11 11:37:00', 'uploads/profilkepek/1_1774791774.png', 1),
(2, 'receptbazis', 'receptbazis@receptbazis.com', '$2y$10$EwJfKtFAreEcm9Lh8P.yiedsp3c5Q1bN82R0nPYPrWVaxZm9wkw7.', '2026-03-29 13:45:16', 'uploads/profilkepek/2_1774792286.png', 1),
(3, 'teszt', 'teszt@teszt.hu', '$2y$10$Ox4pnJWrFc0ahTi7jW0jLO4/OXbdff2I.fgJXcddlnxvunArT1RSS', '2026-04-13 07:54:55', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `kommentek`
--

CREATE TABLE `kommentek` (
  `id` int(11) NOT NULL,
  `recept_id` int(11) NOT NULL,
  `felhasznalo_id` int(11) NOT NULL,
  `szoveg` text NOT NULL,
  `datum` timestamp NOT NULL DEFAULT current_timestamp(),
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- Dumping data for table `kommentek`
--

INSERT INTO `kommentek` (`id`, `recept_id`, `felhasznalo_id`, `szoveg`, `datum`, `parent_id`) VALUES
(1, 14, 1, 'Remek recept!', '2026-03-29 15:33:34', NULL),
(2, 14, 2, 'Köszönöm!', '2026-03-29 15:40:34', 1);

-- --------------------------------------------------------

--
-- Table structure for table `receptek`
--

CREATE TABLE `receptek` (
  `id` int(11) NOT NULL,
  `felhasznalo_id` int(11) NOT NULL,
  `cim` varchar(150) NOT NULL,
  `leiras` text NOT NULL,
  `hozzavalok` text NOT NULL,
  `elkeszitesi_ido` int(11) DEFAULT NULL,
  `letrehozva` timestamp NOT NULL DEFAULT current_timestamp(),
  `kategoria` varchar(50) NOT NULL,
  `indexkep` varchar(255) DEFAULT NULL,
  `receptkep` varchar(255) DEFAULT NULL,
  `youtube_link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- Dumping data for table `receptek`
--

INSERT INTO `receptek` (`id`, `felhasznalo_id`, `cim`, `leiras`, `hozzavalok`, `elkeszitesi_ido`, `letrehozva`, `kategoria`, `indexkep`, `receptkep`, `youtube_link`) VALUES
(1, 2, 'Frankfurti leves', '1. Az olívaolajon megpirítjuk a hagymát és a fokhagymát, majd hozzáadjuk a pirospaprikát, a római köményt és a majoránnát.\r\n2. Miután összepirítottuk őket, hozzáadjuk a feldarabolt burgonyát és kis kevergetés után felöntjük másfél liter vízzel. Az edényt lefedjük és 5 percig hagyjuk főni.\r\n3. A leves ízeinek összeérése után, belekerülnek a felszeletelt kelkáposzta levelek, valamint a só és a bors ízlés szerint. Fedő alatt ismét főzzük, de ezúttal 20 percig.\r\n4. Amíg fő a leves, egy serpenyőben megpirítjuk a karikára vágott frankfurti virsli darabokat.\r\n5. Egy külön tálba kimérjük a tejfölt, majd belekanalazzuk a lisztet és csomómentesre keverjük. Az edényből forró levet merünk a tálba, hogy a habarás is átmelegedjen, majd szűrő segítségével hozzáadjuk a leveshez.\r\n6. Miután a megpirult virsli is az edényben landolt, felforrás után tálalhatjuk.', '2 ek olívaolaj\r\n1 közepes fej vöröshagyma\r\n2 gerezd fokhagyma\r\n1 teáskanál fűszerpaprika\r\n1 g köménymag\r\n1 teáskanál majoranna\r\n2 db burgonya\r\n1.5 l víz\r\n1 kis fej kelkáposzta\r\nsó ízlés szerint\r\nbors ízlés szerint\r\n250 g frankfurti virsli\r\n250 g tejföl\r\n1 ek finomliszt', 45, '2026-03-29 14:27:31', 'Leves', 'uploads/1774794451_index_frankfurti.png', 'uploads/1774794451_recept_frankfurti.png', ''),
(2, 2, 'Gulyásleves', 'Elkészítés: A hagymát apróra vágjuk, megdinszteljük, rátesszük a paprikát és a kockázott húst. Felöntjük vízzel, fűszerezzük. Amikor a hús félig puha, hozzáadjuk a zöldségeket és a krumplit. Készre főzzük.', '50 dkg marhahús\r\n30 dkg krumpli\r\n2 sárgarépa\r\n1 gyökér \r\n2 fej hagyma\r\nfűszerpaprika\r\nkömény', 180, '2026-03-29 14:29:25', 'Leves', 'uploads/1774794565_index_gulyas.png', 'uploads/1774794565_recept_gulyas.png', ''),
(3, 2, 'Amerikai palacsinta', 'A száraz és a nedves összetevőket külön összekeverjük, majd csomómentesre dolgozzuk az egészet. Egy teflon serpenyőt vékonyan kiolajozunk, és kis merőkanállal köröket formázunk. Amikor buborékosodik a teteje, megfordítjuk. Juharsziruppal az igazi!', '20 dkg liszt \r\n1 tojás\r\n3 dl tej\r\n5 dkg olvasztott vaj\r\n1 csomag sütőpor\r\n2 ek cukor\r\n1 csipet só.', 10, '2026-03-29 14:32:35', 'Reggeli', 'uploads/1774794755_index_amerikaipalacsinta.png', 'uploads/1774794755_recept_amerikaipalacsinta.png', ''),
(4, 2, 'Bundáskenyér', 'A tojásokat egy mélytányérban felverjük a sóval. A kenyérszeletek mindkét oldalát alaposan megmártjuk benne. Forró olajban aranybarnára sütjük mindkét felét. Papírtörlőn lecsöpögtetjük, fokhagymával vagy tejföllel tálaljuk.', '4 szelet fehér\r\n2 tojás \r\n1 csipet só\r\n1 dl olaj a sütéshez', 10, '2026-03-29 14:33:58', 'Reggeli', 'uploads/1774794838_index_bundas.png', 'uploads/1774794838_recept_bundas.png', ''),
(5, 2, 'Bakonyi csirkemell', '1. Hús előkészítése: A hússzeleteket enyhén klopfold ki, sózd és borsozd, majd egy kevés olajon mindkét oldalukat hirtelen süsd fehéredésig, és vedd ki őket egy tányérra.\r\n\r\n2. Alap elkészítése: Ugyanabban a zsiradékban dinszteld meg az apróra vágott hagymát. Add hozzá a felkockázott paprikát és paradicsomot, majd pár perc után a szeletelt gombát is.\r\n\r\n3. Ízesítés: Amikor a gomba összeesett, húzd le a tűzről, szórd rá a fűszerpaprikát, keverd el, majd tedd vissza a hússzeleteket és öntsd fel annyi vízzel, ami éppen ellepi.\r\n\r\n4. Puhítás: Fedő alatt, lassú tűzön párold készre (kb. 20 perc)\r\n\r\n5. Habarás: A tejszínt keverd csomómentesre a liszttel. Öntsd a mártáshoz, forrald össze 1-2 perc alatt, amíg besűrűsödik.', '60 dkg csirkemell\r\n40 dkg gomba (szeletelve)\r\n1 nagy fej vöröshagyma\r\n1 db TV paprika\r\n1 db paradicsom\r\n2 dl főzőtejszín\r\n1 evőkanál finomliszt\r\n2 teáskanál fűszerpaprika.\r\nSó, bors, kevés olaj vagy zsír a sütéshez.', 60, '2026-03-29 14:35:47', 'Főfogás', 'uploads/1774794947_index_bakonyi.png', 'uploads/1774794947_recept_bakonyi.png', ''),
(6, 2, 'Fokhagymás-rozmaringos sült csirkecombok tepsis burgonyával', 'A sütőt melegítsd elő 200 °C-ra (alsó-felső sütés).\r\n\r\nA burgonyát hámozd meg, és vágd közepes cikkekre. Tedd egy nagyobb tepsi aljába, locsold meg 2 evőkanál olívaolajjal, sózd, borsozd, és forgasd össze.\r\n\r\nA csirkecombokat mosd meg, töröld szárazra. Dörzsöld be őket sóval, borssal és egy kevés pirospaprikával.\r\n\r\nFektesd a csirkecombokat a burgonyára. A fokhagymagerezdeket (héjastul, csak kicsit megroppantva) és a rozmaringágakat dobd a tepsibe a hús és a krumpli közé.\r\n\r\nLocsold meg a csirkék bőrét a maradék olívaolajjal, majd told a sütőbe kb. 45-50 percre, amíg a krumpli puha nem lesz, a csirke bőre pedig aranybarnára és ropogósra nem sül.', '4 db csirkecomb\r\n\r\n80 dkg burgonya\r\n\r\n4-5 gerezd fokhagyma\r\n\r\n2-3 szál friss rozmaring (vagy 1 teáskanál szárított)\r\n\r\n4 evőkanál olívaolaj\r\n\r\nSó, frissen őrölt feketebors, édes pirospaprika', 60, '2026-03-29 14:52:51', 'Főfogás', 'uploads/1774795971_index_csirkecomb.png', 'uploads/1774795971_recept_csirkecomb.png', ''),
(7, 2, 'Klasszikus görög saláta', 'A paradicsomot, az uborkát és a zöldpaprikát vágd nagyobb, rusztikus kockákra. A lila hagymát szeleteld vékony félkarikákra.\r\n\r\nTedd az összes zöldséget egy nagy salátástálba, és óvatosan forgasd össze egy csipet sóval (vigyázz, a feta és az olíva is sós!).\r\n\r\nSzórd a tetejére az olívabogyókat.\r\n\r\nA feta sajtot vágd nagyobb kockákra (vagy hagyd egyben, ahogy az eredeti görög recept tartja), és tedd a saláta tetejére.\r\n\r\nLocsold meg bőségesen olívaolajjal, és morzsold rá az oregánót. Ne keverd össze tálalás előtt, hogy a sajt ne törjön össze!', '3 db közepes paradicsom\r\n\r\n1 db kígyóuborka\r\n\r\n1 db lila hagyma\r\n\r\n1 db zöldpaprika\r\n\r\n15 dkg minőségi feta sajt\r\n\r\n1 marék fekete olívabogyó (magozott)\r\n\r\n3 evőkanál extra szűz olívaolaj\r\n\r\n1 teáskanál szárított oregánó, csipet só', 15, '2026-03-29 14:54:08', 'Saláta', 'uploads/1774796048_index_gorogsalata.png', 'uploads/1774796048_recept_gorogsalata.png', ''),
(8, 2, 'Rukkola saláta koktélparadicsommal és parmezánnal', 'A rukkolát mosd meg és alaposan csepegtesd le (vagy használd a konyhakész verziót).\r\n\r\nA koktélparadicsomokat mosd meg, és vágd őket félbe.\r\n\r\nEgy kis tálkában keverd ki az öntetet: az olívaolajat, a balzsamecetet, egy pici sót és a borsot.\r\n\r\nA rukkolát és a paradicsomokat tedd egy tálba, öntsd rá a dresszinget, és lazán keverd össze.\r\n\r\nTálalás előtt egy zöldséghámozó segítségével forgácsolj friss parmezánt a saláta tetejére.', '1 nagy csomag (kb. 150g) friss rukkola\r\n\r\n25 dkg koktélparadicsom\r\n\r\n5 dkg parmezán sajt (vagy Grana Padano)\r\n\r\n3 evőkanál extra szűz olívaolaj\r\n\r\n1,5 evőkanál balzsamecet (vagy balzsamecetkrém)\r\n\r\nSó, frissen őrölt bors', 10, '2026-03-29 14:57:03', 'Saláta', 'uploads/1774796223_index_rukkola.png', 'uploads/1774796223_recept_rukkola.png', ''),
(9, 2, 'Csokipudding', 'Nézd meg a videót :)', 'Vaj\r\nKristálycukor\r\nKakaópor\r\nTej\r\nSzódabikarbóna', 20, '2026-03-29 15:09:14', 'Desszert', 'uploads/1774796954_index_csokipudding.png', '', 'https://www.youtube.com/watch?v=C9T9dSCb3B4'),
(12, 2, 'Erdei gyümölcsös mascarpone pohárkrém', 'A hideg habtejszínt verd fel kemény habbá.\r\n\r\nEgy másik tálban a mascarponét keverd simára a porcukorral és a vaníliával.\r\n\r\nEgy spatula segítségével, óvatos mozdulatokkal forgasd a tejszínhabot a mascarponés krémbe, hogy ne törjön össze.\r\n\r\nA kekszet tedd egy zacskóba, és egy nyújtófával törd darabosra (ne legyen teljesen por állagú).\r\n\r\nKészíts elő 4 szép poharat, és kezdd el a rétegezést: az aljára szórj egy réteg kekszet, erre kanalazz egy réteg mascarpone krémet, majd tegyél rá egy adag gyümölcsöt. Folytasd a rétegezést, amíg a pohár meg nem telik. A tetejét gyümölccsel díszítsd.\r\n\r\nFogyasztásig tedd hűtőbe.', '25 dkg mascarpone\r\n\r\n2 dl habtejszín (hideg)\r\n\r\n3-4 evőkanál porcukor (ízlés szerint)\r\n\r\n1 teáskanál vanília kivonat (vagy 1 csomag vaníliás cukor)\r\n\r\n25 dkg erdei gyümölcs keverék (lehet fagyasztott is, de kiolvasztva)\r\n\r\n15 dkg zabkeksz vagy vajas keksz', 15, '2026-03-29 15:11:35', 'Desszert', 'uploads/1774797095_index_erdeigyumolcsos.png', 'uploads/1774797095_recept_erdeigyumolcsos.png', ''),
(13, 2, 'Epres-bazsalikomos frissítő', 'Az epret mosd meg, csumázd ki, és vágd negyedekre. A felét tedd a kancsó aljába.\r\n\r\nFacsard az eperre a lime levét, add hozzá a mézet, és egy fakanál nyelével (vagy muddlerrel) kicsit törd össze az epreket, hogy kiadják a levüket és a színüket.\r\n\r\nAdd hozzá a maradék eperdarabokat és a bazsalikomleveleket (a bazsalikomot is enyhén nyomkodd meg).\r\n\r\nSzórj a kancsóba bőségesen jeget, majd öntsd fel a szódavízzel.\r\n\r\nHosszú kanállal óvatosan keverd át alulról felfelé.', '25 dkg friss eper\r\n\r\n1 db lime\r\n\r\n1 marék friss bazsalikomlevél\r\n\r\n2-3 evőkanál méz vagy cukorszirup\r\n\r\n1 liter szódavíz\r\n\r\nJégkocka', 10, '2026-03-29 15:13:04', 'Ital', 'uploads/1774797184_index_epres.png', 'uploads/1774797184_recept_epres.png', ''),
(14, 2, 'Mojito', 'Mentás-limes alap készítése:\r\nTedd a mentaleveleket egy pohárba.\r\nFacsard rá a lime levét, majd add hozzá a cukrot.\r\nEgy muddlerrel (vagy kanál hátuljával) finoman nyomkodd össze a mentát és a lime-ot, hogy kioldódjanak az aromák, de ne törd teljesen szét a leveleket.\r\nRum hozzáadása:\r\nÖntsd rá a fehér rumot a pohárba.\r\nJég és szódavíz:\r\nTöltsd meg a poharat jégkockákkal.\r\nÖnts rá szódavizet ízlés szerint (kb. 100–150 ml).\r\nÖsszekeverés:\r\nEgy hosszú kanállal óvatosan keverd össze, hogy a menta, a lime, a cukor és a rum ízei elegyenlítődjenek.\r\nDíszítés:\r\nTegyél a tetejére egy friss mentalevelet és egy lime karikát.', '50 ml fehér rum\r\n1 evőkanál cukor (vagy ízlés szerint)\r\n1 lime (kb. fél lime leve)\r\n6-8 friss mentalevél\r\nSzódavíz\r\nJégkockák', 10, '2026-03-29 15:20:00', 'Ital', 'uploads/1774797600_index_mojito.png', 'uploads/1774797600_recept_mojito.png', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `felhasznalok`
--
ALTER TABLE `felhasznalok`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `felhasznalonev` (`felhasznalonev`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `kommentek`
--
ALTER TABLE `kommentek`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recept_id` (`recept_id`),
  ADD KEY `felhasznalo_id` (`felhasznalo_id`);

--
-- Indexes for table `receptek`
--
ALTER TABLE `receptek`
  ADD PRIMARY KEY (`id`),
  ADD KEY `felhasznalo_id` (`felhasznalo_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `felhasznalok`
--
ALTER TABLE `felhasznalok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kommentek`
--
ALTER TABLE `kommentek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `receptek`
--
ALTER TABLE `receptek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kommentek`
--
ALTER TABLE `kommentek`
  ADD CONSTRAINT `kommentek_ibfk_1` FOREIGN KEY (`recept_id`) REFERENCES `receptek` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kommentek_ibfk_2` FOREIGN KEY (`felhasznalo_id`) REFERENCES `felhasznalok` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `receptek`
--
ALTER TABLE `receptek`
  ADD CONSTRAINT `receptek_ibfk_1` FOREIGN KEY (`felhasznalo_id`) REFERENCES `felhasznalok` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
