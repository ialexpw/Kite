CREATE TABLE `kt_settings` (
   `id` int(10) not null auto_increment,
   `website_title` varchar(255),
   `website_address` varchar(255),
   `api_usage` int(25),
   `url_length` int(2),
   `items_perpage` int(5),
   `filter_urls` varchar(2050),
   `unique_string` varchar(555),
   `identifier` varchar(15),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;


CREATE TABLE `kt_shorts` (
   `id` int(10) not null auto_increment,
   `url` varchar(2000),
   `hash` varchar(25),
   `password` varchar(64),
   `total_views` int(10) default '0',
   `timestamp` int(16),
   `user_id` int(10) default '0',
   `method` varchar(1),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;


CREATE TABLE `kt_users` (
   `id` int(10) not null auto_increment,
   `email` varchar(64),
   `password` varchar(72),
   `apiKey` varchar(72),
   `joined` varchar(32),
   `type` int(1) default '1',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;


CREATE TABLE `kt_views` (
   `id` int(10) not null auto_increment,
   `hash` varchar(25),
   `views` int(10) default '0',
   `datetime` varchar(15),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;


CREATE TABLE `kt_log` (
   `id` int(10) not null auto_increment,
   `username` varchar(64),
   `message` varchar(255),
   `type` int(1),
   `timestamp` int(16),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;