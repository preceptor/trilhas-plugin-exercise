CREATE TABLE IF NOT EXISTS `exercise` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `classroom_id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `time` int(10) DEFAULT NULL,
  `begin` date NOT NULL,
  `end` date DEFAULT NULL,
  `attempts` bigint(20) NOT NULL DEFAULT '2',
  `random` bigint(20) NOT NULL DEFAULT '0',
  `status` enum('active','inactive','final') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `classroom_id` (`classroom_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `exercise_answer` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `exercise_option_id` bigint(20) DEFAULT NULL,
  `exercise_note_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `exercise_value_id` (`exercise_option_id`),
  KEY `exercise_note_id` (`exercise_note_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `exercise_answer_text` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `exercise_question_id` bigint(20) NOT NULL,
  `exercise_note_id` bigint(20) NOT NULL,
  `value` text NOT NULL,
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `exercise_question_id` (`exercise_question_id`,`exercise_note_id`),
  KEY `exercise_note_id` (`exercise_note_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `exercise_note` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `exercise_id` bigint(20) NOT NULL,
  `note` tinyint(3) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('open','waiting','end') NOT NULL DEFAULT 'open',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `exercise_id` (`exercise_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `exercise_note_question` (
  `exercise_question_id` bigint(20) NOT NULL,
  `exercise_note_id` bigint(20) NOT NULL,
  PRIMARY KEY (`exercise_question_id`,`exercise_note_id`),
  KEY `exercise_note_id` (`exercise_note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `exercise_option` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `exercise_question_id` bigint(20) NOT NULL,
  `description` text NOT NULL,
  `justify` text,
  `status` enum('right','wrong') NOT NULL DEFAULT 'wrong',
  PRIMARY KEY (`id`),
  KEY `exercise_question_id` (`exercise_question_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `exercise_question` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  `type` enum('multi-choice','multi-select','true-false','text','value','text') NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `exercise_relation` (
  `exercise_id` bigint(20) NOT NULL,
  `exercise_question_id` bigint(20) NOT NULL,
  `note` tinyint(3) NOT NULL,
  `position` tinyint(2) NOT NULL,
  PRIMARY KEY (`exercise_id`,`exercise_question_id`),
  KEY `exercise_question_id` (`exercise_question_id`)
) ENGINE=InnoDB;

ALTER TABLE `exercise`
  ADD CONSTRAINT `exercise_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `exercise_ibfk_2` FOREIGN KEY (`classroom_id`) REFERENCES `classroom` (`id`);

ALTER TABLE `exercise_answer`
  ADD CONSTRAINT `exercise_answer_ibfk_2` FOREIGN KEY (`exercise_option_id`) REFERENCES `exercise_option` (`id`),
  ADD CONSTRAINT `exercise_answer_ibfk_3` FOREIGN KEY (`exercise_note_id`) REFERENCES `exercise_note` (`id`);

ALTER TABLE `exercise_answer_text`
  ADD CONSTRAINT `exercise_answer_text_ibfk_1` FOREIGN KEY (`exercise_question_id`) REFERENCES `exercise_question` (`id`),
  ADD CONSTRAINT `exercise_answer_text_ibfk_2` FOREIGN KEY (`exercise_note_id`) REFERENCES `exercise_note` (`id`);

ALTER TABLE `exercise_note_question`
  ADD CONSTRAINT `exercise_note_question_ibfk_1` FOREIGN KEY (`exercise_question_id`) REFERENCES `exercise_question` (`id`),
  ADD CONSTRAINT `exercise_note_question_ibfk_2` FOREIGN KEY (`exercise_note_id`) REFERENCES `exercise_note` (`id`);

ALTER TABLE `exercise_option`
  ADD CONSTRAINT `exercise_option_ibfk_1` FOREIGN KEY (`exercise_question_id`) REFERENCES `exercise_question` (`id`);

ALTER TABLE `exercise_relation`
  ADD CONSTRAINT `exercise_relation_ibfk_1` FOREIGN KEY (`exercise_id`) REFERENCES `exercise` (`id`),
  ADD CONSTRAINT `exercise_relation_ibfk_2` FOREIGN KEY (`exercise_question_id`) REFERENCES `exercise_question` (`id`);