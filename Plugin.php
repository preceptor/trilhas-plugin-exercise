<?php
/**
 * Trilhas - Learning Management System
 * Copyright (C) 2005-2010  Preceptor Educação a Distância Ltda. <http://www.preceptoead.com.br>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @category   Exercise
 * @package    Exercise_Plugin
 * @copyright  Copyright (C) 2005-2010  Preceptor Educação a Distância Ltda. <http://www.preceptoead.com.br>
 * @license    http://www.gnu.org/licenses/  GNU GPL
 */
class Exercise_Plugin extends Tri_Plugin_Abstract
{
    protected $_name = "exercise";
    
    protected function _createDb()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `exercise` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `user_id` bigint(20) DEFAULT NULL,
                  `classroom_id` bigint(20) NOT NULL,
                  `name` varchar(255) NOT NULL,
                  `time` int(10) DEFAULT NULL,
                  `begin` date NOT NULL,
                  `end` date DEFAULT NULL,
                  `attempts` bigint(20) NOT NULL DEFAULT '2',
                  `status` enum('active','inactive','final') NOT NULL DEFAULT 'active',
                  PRIMARY KEY (`id`),
                  KEY `user_id` (`user_id`),
                  KEY `classroom_id` (`classroom_id`)
                );

                CREATE TABLE IF NOT EXISTS `exercise_answer` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `exercise_option_id` bigint(20) NOT NULL,
                  `exercise_note_id` bigint(20) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `exercise_value_id` (`exercise_option_id`),
                  KEY `exercise_note_id` (`exercise_note_id`)
                );

                CREATE TABLE IF NOT EXISTS `exercise_note` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `user_id` bigint(20) NOT NULL,
                  `exercise_id` bigint(20) NOT NULL,
                  `note` tinyint(3) NOT NULL,
                  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `user_id` (`user_id`),
                  KEY `exercise_id` (`exercise_id`)
                );

                CREATE TABLE IF NOT EXISTS `exercise_option` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `exercise_question_id` bigint(20) NOT NULL,
                  `description` text NOT NULL,
                  `justify` text,
                  `status` enum('right','wrong') NOT NULL DEFAULT 'wrong',
                  PRIMARY KEY (`id`),
                  KEY `exercise_question_id` (`exercise_question_id`)
                );

                CREATE TABLE IF NOT EXISTS `exercise_question` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `exercise_id` bigint(20) DEFAULT NULL,
                  `parent_id` bigint(20) DEFAULT NULL,
                  `description` text NOT NULL,
                  `note` tinyint(3) DEFAULT NULL,
                  `position` int(10) DEFAULT NULL,
                  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
                  PRIMARY KEY (`id`),
                  KEY `exercise_id` (`exercise_id`)
                );";
        
        $this->_getDb()->query($sql);
    }

    public function install()
    {
        $this->_createDb();
    }

    public function activate()
    {
        $this->_addClassroomMenuItem('evaluation','exercise','exercise/index/index');
        $this->_addAclItem('exercise/question/organizer', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/question/alter-note', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/question/index', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/question/save', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/question/delete', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/question/organizersave', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/question/form', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/question/types', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/question/save-relation', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/question/delete-relation', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/question/bank-question', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/question/selected-question', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/exercise/save-organizer', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/exercise/organizer', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/exercise/view-exercise', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/reply/students-evaluated', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/reply/index', 'identified');
        $this->_addAclItem('exercise/reply/save', 'identified');
        $this->_addAclItem('exercise/reply/view', 'identified');
        $this->_addAclItem('exercise/index/index', 'identified');
        $this->_addAclItem('exercise/index/view', 'identified');
        $this->_addAclItem('exercise/index/form', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/index/save', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/index/delete', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/correct/index', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/correct/view', 'teacher, coordinator, institution');
        $this->_addAclItem('exercise/correct/savenote', 'teacher, coordinator, institution');
    }

    public function desactivate()
    {
        $this->_removeClassroomMenuItem('evaluation','exercise');
        $this->_removeAclItem('exercise/question/organizer');
        $this->_removeAclItem('exercise/question/alter-note');
        $this->_removeAclItem('exercise/question/index');
        $this->_removeAclItem('exercise/question/save');
        $this->_removeAclItem('exercise/question/delete');
        $this->_removeAclItem('exercise/question/organizersave');
        $this->_removeAclItem('exercise/question/form');
        $this->_removeAclItem('exercise/question/types');
        $this->_removeAclItem('exercise/question/save-relation');
        $this->_removeAclItem('exercise/question/delete-relation');
        $this->_removeAclItem('exercise/question/bank-question');
        $this->_removeAclItem('exercise/question/selected-question');
        $this->_removeAclItem('exercise/exercise/save-organizer');
        $this->_removeAclItem('exercise/exercise/organizer');
        $this->_removeAclItem('exercise/exercise/view-exercise');
        $this->_removeAclItem('exercise/reply/students-evaluated');
        $this->_removeAclItem('exercise/reply/index');
        $this->_removeAclItem('exercise/reply/save');
        $this->_removeAclItem('exercise/reply/view');
        $this->_removeAclItem('exercise/index/index');
        $this->_removeAclItem('exercise/index/view');
        $this->_removeAclItem('exercise/index/form');
        $this->_removeAclItem('exercise/index/save');
        $this->_removeAclItem('exercise/index/delete');
        $this->_removeAclItem('exercise/correct/index');
        $this->_removeAclItem('exercise/correct/view');
        $this->_removeAclItem('exercise/correct/savenote');
    }
}

