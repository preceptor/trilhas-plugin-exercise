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
 * @package    Exercise_Form
 * @copyright  Copyright (C) 2005-2010  Preceptor Educação a Distância Ltda. <http://www.preceptoead.com.br>
 * @license    http://www.gnu.org/licenses/  GNU GPL
 */
class Exercise_Form_Question extends Zend_Form
{
    /**
     * (non-PHPdoc)
     * @see Zend_Form#init()
     */
    public function init()
    {
        $table         = new Tri_Db_Table('exercise_question');
        $validators    = $table->getValidators();
        $filters       = $table->getFilters();

        $this->setAction('exercise/question/save')
             ->setMethod('post');

        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidators($validators['id'])
           ->addFilters($filters['id'])
           ->removeDecorator('Label')
           ->removeDecorator('HtmlTag');
        
        $redirect = new Zend_Form_Element_Hidden('redirect');
        $redirect->removeDecorator('Label')
                 ->removeDecorator('HtmlTag');

        $filters['description'][] = 'StripTags';
        $description = new Zend_Form_Element_Textarea('description');
        $description->setLabel('Description')
                    ->addValidators($validators['description'])
                    ->addFilters($filters['description'])
                    ->setAttrib('rows', 10)
                    ->setAllowEmpty(false);

        $this->addElement($id)
             ->addElement($redirect)
             ->addElement($description);
    }

    public function addByType($type, $questionId = null)
    {
        switch ($type) {
            case 'multi-choice':
                $this->multiChoice($questionId);
                break;
            case 'multi-select':
                $this->multiSelect($questionId);
                break;
            case 'true-false':
                $this->trueFalse($questionId);
                break;
        }
        
        $statusOptions = array('active' => 'active',
                               'inactive' => 'inactive');
        
        $status = new Zend_Form_Element_Select('status');
        $status->addMultiOptions($statusOptions)
               ->setLabel('Status');

        $this->addElement($status)
             ->addElement('hidden', 'type', array('value' => $type))
             ->addElement('submit', 'Save');
   }
   
   public function multiChoice($questionId = null)
   {
        $table         = new Tri_Db_Table('exercise_question');
        $validators    = $table->getValidators();
        $filters       = $table->getFilters();

        $multiple = new Exercise_Form_Element_Multichoice('option');
        $multiple->setLabel('Options')
                 ->setAttrib('cols', 60)
                 ->setAttrib('rows', 4);

        if ($questionId) {
            $option = new Tri_Db_Table('exercise_option');
            $options = $option->fetchAll(array('exercise_question_id = ?' => $questionId));

            if (count($options)) {
                foreach ($options as $value) {
                    if ($value->status == 'right') {
                        $multiple->setAttrib('checked', (int) $value->id);
                    }
                    $multiple->addMultiOption($value->id, $value->description);
                }
            } else {
               $multiple->setAttrib('checked', 0);
               $multiple->setMultiOptions(array('','' => '',' ' => ''));
            }
        } else {
           $multiple->setAttrib('checked', 0);
           $multiple->setMultiOptions(array('','' => '',' ' => ''));
        }
        
        $this->addElement($multiple);
   }
   
   public function multiSelect($questionId = null)
   {
        $table         = new Tri_Db_Table('exercise_question');
        $validators    = $table->getValidators();
        $filters       = $table->getFilters();

        $multiple = new Exercise_Form_Element_Multiselect('option');
        $multiple->setLabel('Options')
                 ->setAttrib('cols', 60)
                 ->setAttrib('rows', 4);

        if ($questionId) {
            $option = new Tri_Db_Table('exercise_option');
            $options = $option->fetchAll(array('exercise_question_id = ?' => $questionId));

            if (count($options)) {
                $checkeds = array();
                foreach ($options as $value) {
                    if ($value->status == 'right') {
                        $checkeds[] = (int) $value->id;
                    }
                    $multiple->addMultiOption($value->id, $value->description);
                }
                $multiple->setAttrib('checked', $checkeds);
            } else {
               $multiple->setMultiOptions(array('','' => '',' ' => ''));
            }
        } else {
           $multiple->setMultiOptions(array('','' => '',' ' => ''));
        }
        
        $this->addElement($multiple);
   }
   
   public function trueFalse($questionId = null)
   {
        $table         = new Tri_Db_Table('exercise_question');
        $validators    = $table->getValidators();
        $filters       = $table->getFilters();

        $multiple = new Exercise_Form_Element_Truefalse('option');
        $multiple->setLabel('Options')
                 ->setAttrib('cols', 60)
                 ->setAttrib('rows', 4);

        if ($questionId) {
            $option = new Tri_Db_Table('exercise_option');
            $options = $option->fetchAll(array('exercise_question_id = ?' => $questionId));

            if (count($options)) {
                $checkeds = array();
                foreach ($options as $value) {
                    if ($value->status == 'right') {
                        $checkeds[] = (int) $value->id;
                    }
                    $multiple->addMultiOption($value->id, $value->description);
                }
                $multiple->setAttrib('checked', $checkeds);
            } else {
               $multiple->setMultiOptions(array('','' => '',' ' => ''));
            }
        } else {
           $multiple->setMultiOptions(array('','' => '',' ' => ''));
        }
        
        $this->addElement($multiple);
    }
}
