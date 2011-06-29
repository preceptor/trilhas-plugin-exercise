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
 * @package    Exercise_Model
 * @copyright  Copyright (C) 2005-2010  Preceptor Educação a Distância Ltda. <http://www.preceptoead.com.br>
 * @license    http://www.gnu.org/licenses/  GNU GPL
 */
class Exercise_Model_Reply
{
    /**
     * Checks if the user still has attempted and period
     *
     * @param integer $userId
     * @param integer $exerciseId
     */
    public static function isDisabled($userId, $exerciseId)
    {
        $exercise = new Zend_Db_Table('exercise');
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
                     ->from('exercise_note', 'COUNT(0) as total')
                     ->where('user_id = ?', $userId)
                     ->where('exercise_id = ?', $exerciseId);
        $result = $db->fetchRow($select);

        $where = array('id = ?' => $exerciseId);
        $row = $exercise->fetchRow($where);

        if ($row) {
            if ($row->attempts === "0" || ((int) $result['total'] < (int) $row->attempts)) {
                if ($row->begin) {
                    $begin = strtotime($row->begin);
                    if ($begin > time()) {
                        return 'Exercise does not started. Verify the date of initiation';
                    }
                }   
                
                if ($row->end) {
                    $end = strtotime($row->end);
                    if ($end < time()) {
                        return 'Expired period';
                    }
                }
                return false;
            } else {
                return 'Number of attempts exec';
            }
        } else {
            return 'Error';
        }

        return false;
    }

    public static  function isFinal($exerciseId)
    {

    }

    public static function sumNote($noteId, $exerciseId)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
                     ->from(array('a' => 'exercise_answer'), array('o.id', 'o.status'))
                     ->join(array('o' => 'exercise_option'), 'a.`exercise_option_id` = o.id', array())
                     ->join(array('q' => 'exercise_question'), 'q.id = o.exercise_question_id', array())
                     ->where('exercise_note_id = ?', $noteId);
        $answereds = $db->fetchAll($select);
        
        $select = $db->select()
                     ->from(array('o' => 'exercise_option'), array('o.id','o.status','r.note', 'qid' => 'q.id', 'q.type'))
                     ->join(array('q' => 'exercise_question'), 'q.id = o.exercise_question_id', array())
                     ->join(array('r' => 'exercise_relation'), 'q.id = r.exercise_question_id', array())
                     ->where('exercise_id = ?', $exerciseId);
        $questions = $db->fetchAll($select);
        $rights = array();
        
        foreach ($questions as $question) {
            if (!isset($rights[$question['qid']])) {
                $rights[$question['qid']] = 0;
                $total[$question['qid']]['total'] = 0;
            }
            
            $total[$question['qid']]['total']++;
            $total[$question['qid']]['type'] = $question['type'];
            $total[$question['qid']]['note'] = $question['note'];
            $found = false;
            
            foreach ($answereds as $answered) {
                if ($question['id'] == $answered['id']) {
                    if ($answered['status'] == 'right' && $question['status'] == "right") {
                        $rights[$question['qid']]++;
                    }
                    
                    $found = true;
                } 
            }
            
            if (!$found) {
                if ($question['status'] == "wrong" 
                    && $question['type'] != "multi-choice") {
                    $rights[$question['qid']]++;
                }
            }
            
        }
        
        $sum = 0;
        
        foreach ($rights as $questionId => $value) {
            if ($total[$questionId]['type'] == 'multi-choice') {
                $noteValue = (float) $total[$questionId]['note'];
            } else {
                $noteValue = (float) $total[$questionId]['note']/$total[$questionId]['total'];
            }
            $sum += $value * $noteValue;
        }
        return (int) ceil($sum);
    }
}