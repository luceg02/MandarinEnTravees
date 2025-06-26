-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Giu 26, 2025 alle 16:31
-- Versione del server: 10.4.22-MariaDB
-- Versione PHP: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mandarin_travees`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `categorie`
--

CREATE TABLE `categorie` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `categorie`
--

INSERT INTO `categorie` (`id`, `nom`, `description`) VALUES
(12, 'tech', NULL),
(13, 'economie', NULL),
(14, 'societe', NULL),
(15, 'sante', NULL),
(16, 'politique', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `demande`
--

CREATE TABLE `demande` (
  `id` int(11) NOT NULL,
  `auteur_id` int(11) NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categorie_id` int(11) NOT NULL,
  `date_creation` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `date_modification` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `nb_reponses` int(11) NOT NULL,
  `liens_sources` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verdict_automatique` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score_confiance` double DEFAULT NULL,
  `date_derniere_evaluation` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `demande`
--

INSERT INTO `demande` (`id`, `auteur_id`, `titre`, `description`, `statut`, `categorie_id`, `date_creation`, `date_modification`, `nb_reponses`, `liens_sources`, `verdict_automatique`, `score_confiance`, `date_derniere_evaluation`) VALUES
(10, 30, 'Les voitures électriques polluent-elles plus que les voitures thermiques ?', 'Une vidéo virale sur les réseaux sociaux affirme que les voitures électriques polluent davantage que les voitures à essence en raison de la production des batteries au lithium et de l\'électricité provenant encore largement des centrales à charbon. La vidéo cite des études prétendument scientifiques montrant que l\'empreinte carbone totale d\'une voiture électrique serait 40% supérieure à celle d\'une voiture thermique sur toute sa durée de vie.', 'en_cours', 14, '2025-06-26 15:43:19', '2025-06-26 16:26:51', 2, 'www.agencedelenergie.com\nImage: voiture-685d4e773e61d.jpg', 'faux', 100, '2025-06-26 16:26:51'),
(11, 30, 'L\'intelligence artificielle va-t-elle remplacer 50% des emplois d\'ici 2030 ?', 'Un rapport largement partagé sur LinkedIn prétend qu\'une étude du MIT et de Stanford révèle que l\'intelligence artificielle générative remplacera automatiquement la moitié des emplois actuels d\'ici 2030, particulièrement dans les secteurs de la finance, du droit, de la médecine et de l\'éducation. Le rapport suggère que seuls les métiers manuels et créatifs survivront à cette révolution technologique, créant un chômage de masse sans précédent.', 'en_cours', 12, '2025-06-26 15:45:27', '2025-06-26 16:21:09', 3, 'www.ocde2023.com', NULL, 0, '2025-06-26 16:21:09'),
(12, 30, 'Emmanuel Macron a-t-il vraiment augmenté son salaire de 30% en 2023 ?', 'Des publications sur les réseaux sociaux affirment qu\'Emmanuel Macron se serait accordé une augmentation de salaire de 30% en 2023, passant de 15 203 euros à près de 20 000 euros mensuels, alors que les Français subissent l\'inflation. Ces posts incluent de fausses captures d\'écran du Journal Officiel et comparent cette supposée augmentation aux difficultés économiques des citoyens. L\'information circule massivement sur Facebook et Twitter avec des hashtags comme #MacronSAugmente.', 'en_cours', 16, '2025-06-26 15:48:21', '2025-06-26 16:20:27', 3, 'www.macron.com\nImage: macron-685d4fa504946.jpg', NULL, 0, '2025-06-26 16:20:27'),
(13, 30, 'L\'aspartame provoque-t-il réellement des cancers du cerveau ?', 'Suite à la classification de l\'aspartame comme \"possiblement cancérogène\" par l\'OMS en 2023, de nombreux articles sensationnalistes affirment que cet édulcorant artificiel présent dans les sodas light et les chewing-gums provoque systématiquement des tumeurs cérébrales. Des témoignages de patients prétendent avoir développé des cancers après consommation régulière de produits à l\'aspartame, créant une panique alimentaire et des appels au boycott massif des produits light.', 'en_cours', 15, '2025-06-26 15:48:59', '2025-06-26 16:19:51', 3, 'www.oms.com', NULL, 0, '2025-06-26 16:19:51'),
(14, 30, 'L\'inflation française est-elle vraiment due aux supermarchés qui s\'enrichissent ?', 'Des analyses économiques alternatives circulent sur les réseaux sociaux affirmant que l\'inflation en France n\'est pas causée par la guerre en Ukraine ou les tensions énergétiques, mais par la cupidité des grandes surfaces qui augmentent artificiellement leurs marges. Ces publications montrent des graphiques comparant les bénéfices record de Carrefour, Leclerc et Auchan en 2023 (+35% par rapport à 2019) avec l\'inflation alimentaire (+12%), suggérant une manipulation des prix pour maximiser les profits sur le dos des consommateurs français.', 'en_cours', 13, '2025-06-26 15:50:24', '2025-06-26 16:28:26', 3, 'www.auchan.com\nImage: auchan-685d50205a807.jpg', 'faux', 91.150442477876, '2025-06-26 16:28:26'),
(15, 32, 'Les antidépresseurs créent-ils plus de suicides qu\'ils n\'en préviennent ?', 'Des groupes anti-psychiatrie diffusent des études prétendant que les antidépresseurs, particulièrement chez les jeunes, augmentent les risques de suicide de 40% dans les premières semaines de traitement. Ces publications affirment que l\'industrie pharmaceutique cache ces données depuis des décennies et que les médecins prescrivent ces \"drogues dangereuses\" sans informer les patients des risques réels. Des témoignages dramatiques de familles endeuillées sont mis en avant pour étayer ces affirmations.', 'en_cours', 15, '2025-06-26 16:11:59', '2025-06-26 16:22:49', 3, 'www.oms.com', 'trompeur', 100, '2025-06-26 16:22:49');

-- --------------------------------------------------------

--
-- Struttura della tabella `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20250616100548', '2025-06-16 12:06:06', 435);

-- --------------------------------------------------------

--
-- Struttura della tabella `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `reponse`
--

CREATE TABLE `reponse` (
  `id` int(11) NOT NULL,
  `auteur_id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `contenu` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `sources` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_votes_positifs` int(11) NOT NULL DEFAULT 0,
  `nb_votes_negatifs` int(11) NOT NULL DEFAULT 0,
  `date_creation` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `date_modification` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `verdict` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `reponse`
--

INSERT INTO `reponse` (`id`, `auteur_id`, `demande_id`, `contenu`, `sources`, `nb_votes_positifs`, `nb_votes_negatifs`, `date_creation`, `date_modification`, `verdict`, `statut`) VALUES
(13, 32, 13, 'Les grandes études épidémiologiques de long terme n\'ont pas trouvé d\'augmentation des tumeurs cérébrales dans les pays gros consommateurs d\'aspartame. Paradoxalement, les sodas sucrés \"normaux\" présentent des risques prouvés (obésité, diabète, maladies cardiovasculaires) bien supérieurs aux risques hypothétiques de l\'aspartame. La \"panique alimentaire\" détourne l\'attention des vrais risques nutritionnels comme l\'excès de sucre et les aliments ultra-transformés.', NULL, 0, 0, '2025-06-26 16:08:47', NULL, NULL, NULL),
(14, 32, 12, 'Emmanuel Macron perçoit une rémunération mensuelle de 16 039 euros bruts, soit 14 586,32 euros nets avant impôt. Une augmentation de 30% porterait le salaire net à environ 19 000 euros mensuels, soit plus que le niveau de Nicolas Sarkozy. Les calculs de cotisations et d\'impôts sur les fiches de paie publiées correspondent exactement aux barèmes en vigueur depuis 2012, confirmant l\'absence de modification.', NULL, 0, 0, '2025-06-26 16:09:15', NULL, NULL, NULL),
(15, 32, 11, 'En France, seuls 5 % des emplois sont directement remplaçables par l\'IA à moyen terme. Le Bureau international du travail (BIT) ou la commission IA en France estiment que 5 % des emplois pourraient être menacés dans les pays dits avancés. Les études sérieuses convergent vers des chiffres bien inférieurs à 50%. D\'ici 2030, 27 % des tâches des salariés français pourraient être automatisées, mais automatisation des tâches ≠ suppression d\'emplois.', 'www.example.com\nImage: ia-685d54e95b25f.jpg', 0, 0, '2025-06-26 16:10:49', NULL, NULL, NULL),
(16, 31, 15, 'Certains patients (10 à 20 % selon les études) pourraient présenter une émergence ou une aggravation du risque suicidaire lors des 4 à 5 premières semaines de traitement par antidépresseur. Dans une revue Cochrane publiée en 2012, les antidépresseurs les plus récents augmentaient de 58% le risque de pensées suicidaires et de tentative de suicide chez les jeunes de 6 à 18 ans, comparé à un placebo. Ce risque est officiellement reconnu par les autorités sanitaires mais concerne une minorité de patients et une période limitée.', NULL, 1, 0, '2025-06-26 16:14:31', NULL, NULL, NULL),
(17, 31, 11, 'Aucune étude publiée conjointement par MIT et Stanford ne prédit 50% de remplacement d\'emplois d\'ici 2030. Cette affirmation circule sans source vérifiable. L\'étude a été menée par des chercheurs de Stanford et du MIT sur l\'impact des outils d\'intelligence artificielle générative sur la productivité des travailleurs - elle montre une augmentation de productivité de 14%, pas un remplacement massif. Les vrais rapports académiques sont beaucoup plus nuancés.', 'Image: iaia-685d560147f40.jpg', 0, 0, '2025-06-26 16:15:29', NULL, NULL, NULL),
(18, 31, 12, 'Emmanuel Macron perçoit une rémunération mensuelle de 16 039 euros bruts, soit 14 586,32 euros nets avant impôt. Une augmentation de 30% porterait le salaire net à environ 19 000 euros mensuels, soit plus que le niveau de Nicolas Sarkozy. Les calculs de cotisations et d\'impôts sur les fiches de paie publiées correspondent exactement aux barèmes en vigueur depuis 2012, confirmant l\'absence de modification.', 'www.info.com', 0, 0, '2025-06-26 16:16:20', NULL, NULL, NULL),
(19, 31, 10, 'L\'analyse du cycle de vie complet (ACV) des véhicules électriques montre systématiquement leur avantage environnemental. L\'AIE confirme que les VE émettent 50% moins de CO2 que les véhicules thermiques sur l\'ensemble de leur durée de vie, incluant la production des batteries. Même dans les pays où l\'électricité est encore largement carbonée (Pologne, Allemagne), les véhicules électriques restent moins polluants que leurs équivalents thermiques. En France, avec un mix électrique décarboné, l\'avantage est encore plus marqué.', 'www.exaample.com', 0, 0, '2025-06-26 16:17:09', NULL, NULL, NULL),
(20, 29, 15, 'La plupart des études pharmaco-épidémiologiques montrent un effet protecteur de l\'utilisation d\'antidépresseurs sur le suicide. En Scandinavie, dans les années 1990, l\'utilisation des antidépresseurs qui a été multipliée par 3,4 fois, s\'est accompagnée d\'une diminution du nombre de suicides de 19 %. Si tous les patients souffrant d\'un épisode dépressif majeur recevaient un traitement antidépresseur, plus d\'un suicide sur trois pourrait être évité. Les bénéfices populationnels dépassent largement les risques individuels.', NULL, 2, 0, '2025-06-26 16:18:17', NULL, NULL, NULL),
(21, 29, 14, 'La Banque de France identifie clairement que \"les chocs de prix de l\'énergie puis de l\'alimentation ont été les moteurs de l\'inflation post-pandémie en France\". L\'Autorité de la concurrence confirme l\'absence de preuves d\'entente sur les prix entre enseignes. Les prix de l\'énergie sont passés de 105 mi-2020 à près de 150 mi-2022, soit plus de 40% d\'augmentation, impactant directement les coûts de production et transport. Les causes de l\'inflation sont externes aux distributeurs.', 'www.banquedefrance.com', 1, 0, '2025-06-26 16:18:56', NULL, NULL, NULL),
(22, 29, 13, 'La science ne peut jamais prouver l\'absence totale de risque. Les études actuelles ont des limites : durées de suivi insuffisantes (le cancer peut mettre 20-30 ans à se développer), difficultés à isoler l\'effet de l\'aspartame d\'autres facteurs, variabilité génétique individuelle. Le principe de précaution justifie une surveillance continue, mais ne nécessite pas l\'interdiction. Les autorités réévaluent régulièrement les données disponibles.', 'www.example.com', 1, 0, '2025-06-26 16:19:51', NULL, NULL, NULL),
(23, 29, 12, 'Cette fausse information suit le schéma classique des \"fake news\" : document officiel falsifié, chiffre choc (30%), timing pendant une période de tensions sociales, diffusion via comptes anonymes puis relais par des influenceurs politiques. L\'absence totale de couverture médiatique mainstream malgré l\'énormité de l\'information supposée confirme son caractère fictif. Les algorithmes de réseaux sociaux amplifient ce type de contenu générateur d\'indignation.', NULL, 1, 0, '2025-06-26 16:20:27', NULL, NULL, NULL),
(24, 29, 11, '77 % des Français perçoivent l\'IA comme une révolution, mais 68 % souhaitent en ralentir le développement. Cette prédiction de 50% exploite les peurs naturelles face aux changements technologiques. Historiquement, les révolutions industrielles ont créé plus d\'emplois qu\'elles n\'en ont détruits, mais les transitions sont douloureuses. L\'alarmisme sert souvent des agendas politiques ou commerciaux.', 'www.example.com', 0, 0, '2025-06-26 16:21:09', NULL, NULL, NULL),
(25, 30, 15, 'Seuls 15 % des participants aux essais cliniques bénéficient réellement des antidépresseurs en comparaison avec le placebo. Dans les études on sélectionne strictement les patients. Sont exclus par exemple ceux présentant un risque suicidaire, une maladie chronique. Les essais cliniques ne représentent pas la vraie vie où les patients sont plus complexes. Il faudrait qu\'environ 7 personnes soient traitées avant qu\'une personne puisse en effet avoir des effets positifs. Les données sur le risque suicidaire sont donc incomplètes.', NULL, 0, 1, '2025-06-26 16:22:49', NULL, NULL, NULL),
(26, 32, 14, 'Foodwatch dénonce : \"La réponse en un mot : l\'opacité. Pour l\'industrie agroalimentaire et la grande distribution, le manque de transparence nourrit l\'impunité. Pas vu, pas pris ! Tant que les fabricants et les distributeurs sont libres de ne pas communiquer les marges qu\'ils réalisent par produit, c\'est la porte ouverte à tous les abus.\" Cette opacité permet de dissimuler des pratiques d\'augmentation artificielle des prix. Sans transparence totale, impossible de vérifier les vraies causes de l\'inflation alimentaire.', NULL, 0, 0, '2025-06-26 16:25:26', NULL, NULL, NULL),
(28, 32, 10, 'Le débat VE vs thermique masque une question plus fondamentale sur notre modèle de mobilité. Les écologistes \"radicaux\" considèrent que les VE perpétuent la société de l\'automobile individuelle au lieu de promouvoir transports en commun, vélo et réduction des déplacements. Pour eux, les VE sont un \"greenwashing\" technologique qui évite de remettre en cause nos modes de vie. À l\'inverse, les partisans du progrès technologique voient les VE comme une solution pragmatique de décarbonation sans sacrifice du confort. Le choix révèle des visions opposées de l\'avenir social.', 'www.example.com', 0, 0, '2025-06-26 16:26:51', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `report`
--

CREATE TABLE `report` (
  `id` int(11) NOT NULL,
  `signale_par_id` int(11) NOT NULL,
  `auteur_contenu_id` int(11) NOT NULL,
  `type_contenu` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_contenu` int(11) NOT NULL,
  `raison` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `commentaire` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_report` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `statut` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `report`
--

INSERT INTO `report` (`id`, `signale_par_id`, `auteur_contenu_id`, `type_contenu`, `id_contenu`, `raison`, `commentaire`, `date_report`, `statut`) VALUES
(10, 32, 30, 'reponse', 25, 'spam', 'Ce type de contenu nuit à la qualité des échanges, pollue les discussions légitimes de fact-checking et peut détourner l\'attention des utilisateurs vers des contenus non pertinents. Il va à l\'encontre de la mission de la plateforme qui est de lutter contre la désinformation par des échanges constructifs et documentés.', '2025-06-26 16:28:07', 'en_attente'),
(11, 32, 31, 'reponse', 18, 'harcelement', 'Ce type de contenu nuit à la qualité des échanges, pollue les discussions légitimes de fact-checking et peut détourner l\'attention des utilisateurs vers des contenus non pertinents. Il va à l\'encontre de la mission de la plateforme qui est de lutter contre la désinformation par des échanges constructifs et documentés.', '2025-06-26 16:29:32', 'en_attente');

-- --------------------------------------------------------

--
-- Struttura della tabella `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_inscription` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut_moderation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut_validation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_carte_presse` int(11) DEFAULT NULL,
  `score_reputation` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `date_inscription`, `nom`, `prenom`, `statut_moderation`, `statut_validation`, `numero_carte_presse`, `score_reputation`) VALUES
(28, 'admin@admin', '[\"ROLE_ADMIN\"]', '$2y$13$.czOhLI7OVW1CNrTfxHhx.D86.BN6oeFi0jK4/L2It.Bfr8SNXZ7a', '2025-06-26 15:27:02', 'Admin', 'User', 'actif', NULL, NULL, 0),
(29, 'jean@jean', '[\"ROLE_JOURNALISTE\"]', '$2y$13$rTkjR7d5WBJykWBIaw.WMuOFB79cNBgcKRV6/SOTD4TTdpb2ika4u', '2025-06-26 15:28:32', 'Jean', 'Dupont', 'actif', 'valide', 1234, 0),
(30, 'sara@sara', '[\"ROLE_USER\"]', '$2y$13$PCA0kj7oPIVIww7sadazAOnfPbSKSzRC/X4LefqJAZpvuVkXMd42y', '2025-06-26 15:29:43', 'Sara', 'Bianchi', 'actif', NULL, NULL, 1),
(31, 'claire@claire', '[\"ROLE_USER\"]', '$2y$13$1FsoCwuYnRiTcQsclHVQe.KDHb6VIOgExl/GrdRdZviN8U3f7maK6', '2025-06-26 15:51:57', 'Claire', 'Girard', 'actif', NULL, NULL, 4),
(32, 'emmanuel@emmanuel', '[\"ROLE_USER\"]', '$2y$13$KpL6vDZO2Sl1pdpxFBlpL.EqyeJkrpGFSRmvENkRsgVg9sPBVEk9u', '2025-06-26 15:58:41', 'Emmanuel', 'Durand', 'actif', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `vote`
--

CREATE TABLE `vote` (
  `id` int(11) NOT NULL,
  `date_vote` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `type_vote` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commentaire` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reponse_id` int(11) DEFAULT NULL,
  `demande_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `vote`
--

INSERT INTO `vote` (`id`, `date_vote`, `type_vote`, `commentaire`, `user_id`, `reponse_id`, `demande_id`) VALUES
(7, '2025-06-26 15:54:10', 'trompeur', NULL, 31, NULL, 14),
(8, '2025-06-26 15:57:09', 'faux', NULL, 31, NULL, 13),
(9, '2025-06-26 16:08:47', 'vrai', NULL, 32, NULL, 13),
(10, '2025-06-26 16:09:15', 'faux', NULL, 32, NULL, 12),
(11, '2025-06-26 16:10:49', 'faux', NULL, 32, NULL, 11),
(12, '2025-06-26 16:14:31', 'trompeur', NULL, 31, NULL, 15),
(13, '2025-06-26 16:15:29', 'faux', NULL, 31, NULL, 11),
(14, '2025-06-26 16:16:20', 'faux', NULL, 31, NULL, 12),
(15, '2025-06-26 16:17:09', 'faux', NULL, 31, NULL, 10),
(16, '2025-06-26 16:18:17', 'trompeur', NULL, 29, NULL, 15),
(17, '2025-06-26 16:18:56', 'faux', NULL, 29, NULL, 14),
(18, '2025-06-26 16:19:51', 'vrai', NULL, 29, NULL, 13),
(19, '2025-06-26 16:20:27', 'faux', NULL, 29, NULL, 12),
(20, '2025-06-26 16:21:09', 'non_identifiable', NULL, 29, NULL, 11),
(21, '2025-06-26 16:22:22', 'utile', NULL, 30, 16, NULL),
(22, '2025-06-26 16:22:25', 'utile', NULL, 30, 20, NULL),
(23, '2025-06-26 16:22:49', 'non_identifiable', NULL, 30, NULL, 15),
(24, '2025-06-26 16:24:09', 'utile', NULL, 32, 20, NULL),
(25, '2025-06-26 16:24:18', 'pas_utile', NULL, 32, 25, NULL),
(26, '2025-06-26 16:24:31', 'utile', NULL, 32, 21, NULL),
(27, '2025-06-26 16:25:28', 'faux', NULL, 32, NULL, 14),
(28, '2025-06-26 16:25:46', 'utile', NULL, 32, 22, NULL),
(29, '2025-06-26 16:26:01', 'utile', NULL, 32, 23, NULL),
(30, '2025-06-26 16:26:51', 'trompeur', NULL, 32, NULL, 10);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `demande`
--
ALTER TABLE `demande`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_2694D7A560BB6FE6` (`auteur_id`),
  ADD KEY `IDX_2694D7A5BCF5E72D` (`categorie_id`);

--
-- Indici per le tabelle `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Indici per le tabelle `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  ADD KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  ADD KEY `IDX_75EA56E016BA31DB` (`delivered_at`);

--
-- Indici per le tabelle `reponse`
--
ALTER TABLE `reponse`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5FB6DEC760BB6FE6` (`auteur_id`),
  ADD KEY `IDX_5FB6DEC780E95E18` (`demande_id`);

--
-- Indici per le tabelle `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_C42F7784AE190A20` (`signale_par_id`),
  ADD KEY `IDX_C42F778447B70894` (`auteur_contenu_id`);

--
-- Indici per le tabelle `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`);

--
-- Indici per le tabelle `vote`
--
ALTER TABLE `vote`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_reponse` (`user_id`,`reponse_id`),
  ADD UNIQUE KEY `uniq_user_demande` (`user_id`,`demande_id`),
  ADD KEY `IDX_5A108564A76ED395` (`user_id`),
  ADD KEY `IDX_5A108564CF18BB82` (`reponse_id`),
  ADD KEY `IDX_5A10856480E95E18` (`demande_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT per la tabella `demande`
--
ALTER TABLE `demande`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT per la tabella `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `reponse`
--
ALTER TABLE `reponse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT per la tabella `report`
--
ALTER TABLE `report`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT per la tabella `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT per la tabella `vote`
--
ALTER TABLE `vote`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `demande`
--
ALTER TABLE `demande`
  ADD CONSTRAINT `FK_2694D7A560BB6FE6` FOREIGN KEY (`auteur_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_2694D7A5BCF5E72D` FOREIGN KEY (`categorie_id`) REFERENCES `categorie` (`id`);

--
-- Limiti per la tabella `reponse`
--
ALTER TABLE `reponse`
  ADD CONSTRAINT `FK_5FB6DEC760BB6FE6` FOREIGN KEY (`auteur_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_5FB6DEC780E95E18` FOREIGN KEY (`demande_id`) REFERENCES `demande` (`id`);

--
-- Limiti per la tabella `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `FK_C42F778447B70894` FOREIGN KEY (`auteur_contenu_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_C42F7784AE190A20` FOREIGN KEY (`signale_par_id`) REFERENCES `user` (`id`);

--
-- Limiti per la tabella `vote`
--
ALTER TABLE `vote`
  ADD CONSTRAINT `FK_5A10856480E95E18` FOREIGN KEY (`demande_id`) REFERENCES `demande` (`id`),
  ADD CONSTRAINT `FK_5A108564A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_5A108564CF18BB82` FOREIGN KEY (`reponse_id`) REFERENCES `reponse` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
