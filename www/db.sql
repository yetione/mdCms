--
-- Структура таблицы `access_flag`
--

-- DROP TABLE IF EXISTS `access_flag`;
CREATE TABLE IF NOT EXISTS `access_flag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `flag` varchar(5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `flag_UNIQUE` (`flag`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `admin_menu`
--

-- DROP TABLE IF EXISTS `admin_menu`;
CREATE TABLE IF NOT EXISTS `admin_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `title` varchar(150) NOT NULL,
  `order` smallint(5) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `blog_category`
--

-- DROP TABLE IF EXISTS `blog_category`
CREATE TABLE IF NOT EXISTS `blog_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `public` tinyint(4) NOT NULL DEFAULT '1',
  `creation_date` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `update_date` int(11) NOT NULL,
  `updater_id` int(11) NOT NULL,
  `html_title` varchar(255) NOT NULL,
  `seo_description` varchar(255) DEFAULT NULL,
  `seo_keywords` varchar(255) DEFAULT NULL,
  `posts_count` int(10) unsigned NOT NULL DEFAULT '0',
  `public_posts_count` int(11) NOT NULL DEFAULT '0',
  `announce` mediumtext,
  `content` mediumtext,
  `all_parents` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_UNIQUE` (`url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `blog_post`
--

-- DROP TABLE IF EXISTS `blog_post`
CREATE TABLE IF NOT EXISTS `blog_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creation_date` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `header` varchar(255) NOT NULL,
  `announce` mediumtext,
  `content` mediumtext,
  `public` tinyint(4) NOT NULL DEFAULT '1',
  `update_date` int(11) NOT NULL,
  `updater_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `image` varchar(128) DEFAULT NULL,
  `thumbnail` varchar(128) DEFAULT NULL,
  `html_title` varchar(255) NOT NULL,
  `seo_keywords` varchar(255) DEFAULT NULL,
  `seo_description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_UNIQUE` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `category`
--

-- DROP TABLE IF EXISTS `category`
CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `announce` varchar(255) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `url` varchar(255) DEFAULT NULL,
  `weight` int(11) NOT NULL,
  `public` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `city`
--

-- DROP TABLE IF EXISTS `city`
CREATE TABLE IF NOT EXISTS `city` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `short` varchar(5) DEFAULT NULL,
  `machine` varchar(45) NOT NULL,
  `okato` varchar(20) NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `courier`
--

-- DROP TABLE IF EXISTS `courier`
CREATE TABLE IF NOT EXISTS `courier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `data` blob,
  `city_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `entity_access_flag`
--

-- DROP TABLE IF EXISTS `entity_access_flag`
CREATE TABLE IF NOT EXISTS `entity_access_flag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(45) NOT NULL,
  `flag_id` int(11) NOT NULL,
  `entity_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entity_flag` (`flag_id`,`entity_id`,`action`) USING BTREE,
  KEY `fk_entity_access_flag_access_flag1_idx` (`flag_id`),
  KEY `fk_entity_access_flag_entity_data1_idx` (`entity_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `entity_data`
--

-- DROP TABLE IF EXISTS `entity_data`
CREATE TABLE IF NOT EXISTS `entity_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `fields` text,
  `relationships` text,
  `table_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `itinerary`
--

-- DROP TABLE IF EXISTS `itinerary`
CREATE TABLE IF NOT EXISTS `itinerary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `courier_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `data` longblob,
  PRIMARY KEY (`id`),
  KEY `fk_itinerary_courier1_idx` (`courier_id`),
  KEY `fk_itinerary_city1_idx` (`city_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `menu`
--

-- DROP TABLE IF EXISTS `menu`
CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `data` longblob,
  `city_id` int(11) NOT NULL,
  `enabled` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `date_index` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `menu_old`
--

-- DROP TABLE IF EXISTS `menu_old`
CREATE TABLE IF NOT EXISTS `menu_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `data` longblob,
  `city_id` int(11) NOT NULL,
  `enabled` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `order`
--

-- DROP TABLE IF EXISTS `order`
CREATE TABLE IF NOT EXISTS `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_created` datetime NOT NULL,
  `status` varchar(45) CHARACTER SET cp1251 NOT NULL DEFAULT 'Не выполнен',
  `client_name` varchar(100) CHARACTER SET cp1251 NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `city_id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(45) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `promo_code_data` blob,
  `promo_code_name` varchar(100) DEFAULT NULL,
  `discount` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_created_index` (`date_created`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `order_day`
--

-- DROP TABLE IF EXISTS `order_day`
CREATE TABLE IF NOT EXISTS `order_day` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `delivery_time` varchar(45) DEFAULT NULL,
  `delivery_type` varchar(45) NOT NULL DEFAULT 'Доставка',
  `street` varchar(255) DEFAULT NULL,
  `building` varchar(45) DEFAULT NULL,
  `room` varchar(45) DEFAULT NULL,
  `client_comment` varchar(255) DEFAULT NULL,
  `manager_comment` varchar(255) DEFAULT NULL,
  `delivery_date` date NOT NULL,
  `payment_type` varchar(45) NOT NULL,
  `card_number` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `is_changed` tinyint(4) NOT NULL DEFAULT '0',
  `changed_by` int(11) NOT NULL DEFAULT '0',
  `status` varchar(45) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `persons_count` int(10) unsigned NOT NULL,
  `metro_station` varchar(100) NOT NULL,
  `courier_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `delivery_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount` varchar(10) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_day_index` (`order_id`,`delivery_date`),
  KEY `delicery_date_index` (`delivery_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `order_day_new`
--

-- DROP TABLE IF EXISTS `order_day_new`
CREATE TABLE IF NOT EXISTS `order_day_new` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `update_by` int(11) NOT NULL,
  `update_date` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `status` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `client_comment` varchar(255) DEFAULT NULL,
  `manager_comment` varchar(255) DEFAULT NULL,
  `shipping_type` int(11) NOT NULL,
  `shipping_data` longblob,
  `payment_type` int(11) DEFAULT NULL,
  `payment_data` longblob,
  PRIMARY KEY (`id`),
  KEY `fk_order_day_order2_idx` (`order_id`),
  KEY `fk_order_day_city2_idx` (`city_id`),
  KEY `fk_order_day_user2_idx` (`update_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `order_day_product`
--

-- DROP TABLE IF EXISTS `order_day_product`
CREATE TABLE IF NOT EXISTS `order_day_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_day_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `product`
--

-- DROP TABLE IF EXISTS `product`
CREATE TABLE IF NOT EXISTS `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price_spb` decimal(10,2) NOT NULL,
  `price_msk` decimal(10,2) NOT NULL,
  `description` text,
  `announce` text,
  `proteins` float DEFAULT NULL,
  `fats` float DEFAULT NULL,
  `calorie` float DEFAULT NULL,
  `carbs` float DEFAULT NULL,
  `weight` float DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `product_type`
--

-- DROP TABLE IF EXISTS `product_type`
CREATE TABLE IF NOT EXISTS `product_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `promo_code`
--

-- DROP TABLE IF EXISTS `promo_code`
CREATE TABLE IF NOT EXISTS `promo_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `expire_date` int(11) NOT NULL,
  `data` longblob NOT NULL,
  `start_date` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `used` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_name_index` (`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `route`
--

-- DROP TABLE IF EXISTS `route`
CREATE TABLE IF NOT EXISTS `route` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `data` blob NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `stock`
--

-- DROP TABLE IF EXISTS `stock`
CREATE TABLE IF NOT EXISTS `stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `city_id` int(11) NOT NULL,
  `metro_station` varchar(100) NOT NULL,
  `street` varchar(100) NOT NULL,
  `building` varchar(50) DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

-- DROP TABLE IF EXISTS `user`
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `registr_date` int(10) unsigned NOT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `name` varchar(100) DEFAULT NULL,
  `surname` varchar(100) DEFAULT NULL,
  `patronymic` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_mysql500_ci DEFAULT NULL,
  `vk_id` bigint(20) unsigned DEFAULT NULL,
  `phone` varchar(45) DEFAULT NULL,
  `fb_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_UNIQUE` (`login`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `user_address`
--

-- DROP TABLE IF EXISTS `user_address`
CREATE TABLE IF NOT EXISTS `user_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `street` varchar(255) NOT NULL,
  `building` varchar(45) DEFAULT NULL,
  `room` varchar(45) DEFAULT NULL,
  `city_id` int(11) NOT NULL,
  `metro_station` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_address_user1_idx` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Структура таблицы `user_flags`
--

-- DROP TABLE IF EXISTS `user_flags`
CREATE TABLE IF NOT EXISTS `user_flags` (
  `user_id` int(11) NOT NULL,
  `flag_id` int(11) NOT NULL,
  `id` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`flag_id`),
  KEY `fk_user_has_access_flag_access_flag1_idx` (`flag_id`),
  KEY `fk_user_has_access_flag_user1_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `user_token`
--

-- DROP TABLE IF EXISTS `user_token`
CREATE TABLE IF NOT EXISTS `user_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `value` varchar(50) NOT NULL,
  `expire_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `value` (`value`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;
