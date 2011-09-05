<?php
class Exercise_ReplyController extends Tri_Controller_Action
{
    public function init()
    {
        parent::init();
        $this->view->title = "Exercise reply";
    }

    public function indexAction()
    {
        $id       = Zend_Filter::filterStatic($this->_getParam('id'), 'int');
        $identity = Zend_Auth::getInstance()->getIdentity();
        $message  = Exercise_Model_Reply::isDisabled($identity->id, $id);
        
        if ($message) {
            $this->_helper->_flashMessenger->addMessage($message);
            $this->_redirect('/exercise/reply/view/exerciseId/'.$id);
        }

        $exercise         = new Tri_Db_Table('exercise');
        $exerciseRelation = new Tri_Db_Table('exercise_relation');
        $exerciseNote     = new Tri_Db_Table('exercise_note');

        $exerciseNote->createRow(array('user_id' => $identity->id,
                                       'exercise_id' => $id,
                                       'note' => 0))->save();

        $row = $exercise->fetchRow(array('id = ?' => $id));

        if ($row) {
            $select = $exerciseRelation->select(true)
                                       ->setIntegrityCheck(false)
                                       ->join('exercise_question', 'exercise_question.id = exercise_question_id')
                                       ->where('exercise_id = ?', $row->id)
                                       ->where('status = ?', 'active')
                                       ->order('RAND()')
                                       ->limit($row->random);
            $this->view->questions = $exerciseRelation->fetchAll($select);
            $this->view->exercise  = $row;
        } else {
            $this->_helper->_flashMessenger->addMessage('Error');
            $this->_redirect('/exercise');
        }
    }

    public function saveAction()
    {
        if ($this->_request->isPost()) {
            $exerciseNote   = new Tri_Db_Table('exercise_note');
            $exerciseAnswer = new Tri_Db_Table('exercise_answer');
            $textAnswer     = new Tri_Db_Table('exercise_answer_text');
            $panelNote      = new Tri_Db_Table('panel_note');
            $identity       = Zend_Auth::getInstance()->getIdentity();
            $params         = $this->_getAllParams();
            $data           = array();
            $hasText        = false;

            $note = $exerciseNote->fetchRow(array('user_id = ?' => $identity->id),
                                            'id DESC');
                              
            if (isset($params['option'])) {
                foreach ($params['option'] as $key => $options) {
                    $data['exercise_note_id'] = $note->id;
                    switch ($params['type'][$key]) {
                        case 'true-false':
                        case 'multi-select':
                            foreach ($options as $option) {
                                if ((int) $option) {
                                    $data['exercise_option_id'] = $option;
                                    $exerciseAnswer->createRow($data)->save();
                                }
                            }
                            break;
                        case 'multi-choice':
                            $data['exercise_option_id'] = $options;
                            $exerciseAnswer->createRow($data)->save();
                            break;
                        case 'text':
                            $hasText = true;
                            $data['value'] = $options;
                            $data['exercise_question_id'] = $key;
                            $textAnswer->createRow($data)->save();
                            break;
                    }
                    $data = array();
                }
            }

            if ($hasText) {
                $note->status = 'waiting';
            } else {
                $note->status = 'end';
                Panel_Model_Panel::addNote($identity->id, 'exercise', $note->exercise_id, $note->note);
            }
            
            $note->note = Exercise_Model_Reply::sumNote($note->id, $note->exercise_id);
            $note->save();
            
            $this->_redirect('/exercise/reply/view/id/' . $note->id);
        } else {
            $this->_helper->_flashMessenger->addMessage('Error');
            $this->_redirect('/exercise');
        }
    }

    public function viewAction()
    {
        $identity         = Zend_Auth::getInstance()->getIdentity();
        $id               = Zend_Filter::filterStatic($this->_getParam('id'), 'int');
        $exerciseId       = Zend_Filter::filterStatic($this->_getParam('exerciseId'), 'int');
        $userId           = Zend_Filter::filterStatic($this->_getParam('userId', $identity->id), 'int');
        $layout           = $this->_getParam('layout');
        $exercise         = new Tri_Db_Table('exercise');
        $exerciseRelation = new Tri_Db_Table('exercise_relation');
        $exerciseNote     = new Tri_Db_Table('exercise_note');
        $exerciseAnswer   = new Tri_Db_Table('exercise_answer');
        $textAnswer       = new Tri_Db_Table('exercise_answer_text');

        if ($id) {
            $note = $exerciseNote->fetchRow(array('id = ?' => $id));
        } elseif ($exerciseId) {
            $note = $exerciseNote->fetchRow(array('exercise_id = ?' => $exerciseId,
                                                  'user_id = ?' => $userId), 'id DESC');
        }

        if ($note) {
                $row = $exercise->fetchRow(array('id = ?' => $note->exercise_id));

                if ($row) {
                    if ($note->status == 'end') {
                        $select = $exerciseRelation->select(true)
                                               ->setIntegrityCheck(false)
                                               ->join('exercise_question', 'exercise_question.id = exercise_question_id')
                                               ->join('exercise_option', 'exercise_option.exercise_question_id = exercise_question.id', array())
                                               ->join('exercise_answer', 'exercise_answer.exercise_option_id = exercise_option.id', array())
                                               ->where('exercise_id = ?', $row->id)
                                               ->where('exercise_question.status = ?', 'active')
                                               ->order('position');
                        $this->view->questions = $exerciseRelation->fetchAll($select);
                        $this->view->exercise  = $row;
                        $this->view->answers   = $exerciseAnswer->fetchAll(array('exercise_note_id = ?' => $note->id));
                        $this->view->texts     = $textAnswer->fetchAll(array('exercise_note_id = ?' => $note->id));
                        $this->view->note      = $note;

                        $whereNote = array('exercise_id = ?' => $note->exercise_id,
                                           'id <> ?' => $note->id,
                                           'user_id = ?' => $note->user_id);
                        $this->view->notes = $exerciseNote->fetchAll($whereNote, 'id DESC');
                    } elseif($note->status == 'waiting') {
                        if ($identity->role != 'student') {
                            $this->_redirect('exercise/correction/view/id/' . $note->id . '/layout/' . $layout);
                        }
                        $this->view->message = 'Waiting for correction';
                    }
                } else {
                    $this->view->message = 'there are no records';
                }
        } else {
            $this->view->message = 'there are no records';
        }
    }
}