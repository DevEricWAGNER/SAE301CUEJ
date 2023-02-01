CREATE TABLE `article` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `article_name` varchar(40) NOT NULL,
  `article_desc` mediumtext NOT NULL,
  `article_chapo` longtext NOT NULL,
  `article_auteur` varchar(255) NOT NULL,
  `article_page` int(11) NOT NULL,
  `article_time` int(11) NOT NULL,
  `article_img` text NOT NULL,
  `article_vignette` text NOT NULL
);

CREATE TABLE `element` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `balise` varchar(255) NOT NULL,
  `classCSS1` varchar(255) NOT NULL,
  `classCSS2` varchar(255) NOT NULL,
  `encadre_titre` text NOT NULL,
  `content` longtext NOT NULL,
  `alt1` varchar(255) NOT NULL,
  `src1` varchar(255) NOT NULL,
  `alt2` varchar(255) NOT NULL,
  `src2` varchar(255) NOT NULL,
  `alt_media1` varchar(255) NOT NULL,
  `src_media1` varchar(255) NOT NULL,
  `alt_media2` varchar(255) NOT NULL,
  `src_media2` varchar(255) NOT NULL,
  `legende1` text NOT NULL,
  `credit1` text NOT NULL,
  `legende2` varchar(255) NOT NULL,
  `credit2` varchar(255) NOT NULL,
  `position` int(11) NOT NULL,
  `article` bigint(20) UNSIGNED NOT NULL
);

CREATE TABLE `membres` (
  `id` int(11) NOT NULL,
  `pseudo` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `motdepasse` text NOT NULL,
  `admin` int(11) NOT NULL
);

INSERT INTO `membres` (`id`, `pseudo`, `mail`, `motdepasse`, `admin`) VALUES
(1, 'Eric WAGNER', 'ericwagner.contact@gmail.com', 'fa75d53002abfa32d4efcccb5fde0647452fe77d', 1),
(2, 'Mickaël Joly', 'jolymickael67340@gmail.com', '694619b0bd86e7c8c99c57844536221f80989b52', 1),
(3, 'CUEJ_Etu', 'cuej_etu@gmail.com', '9815ecf1fd09fc7e1a6ba45edfb35df8bd6ea6df', 1);

CREATE TABLE `page` (
  `id` int(11) NOT NULL,
  `page_name` varchar(40) NOT NULL,
  `page_desc` longtext NOT NULL,
  `page_img` varchar(255) NOT NULL
);

ALTER TABLE `article`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `fk_article_page` (`article_page`);

ALTER TABLE `element`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `fk_element_article` (`article`);

ALTER TABLE `membres`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `page`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `article`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `element`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `membres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `page`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `article`
  ADD CONSTRAINT `fk_article_page` FOREIGN KEY (`article_page`) REFERENCES `page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `element`
  ADD CONSTRAINT `fk_element_article` FOREIGN KEY (`article`) REFERENCES `article` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;