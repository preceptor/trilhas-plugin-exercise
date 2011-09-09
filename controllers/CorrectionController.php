<?php
class Exercise_CorrectionController extends Tri_Controller_Action
{
    public function init()
    {
        parent::init();
        $this->view->title = "Exercise reply";
    }

    public function indexAction()
    {
        $page   = Zend_Filter::filterStatic($this->_getParam('page'), 'int');
        $table  = new Tri_Db_Table('exercise_note');
        $select = $table->select(true)->setIntegrityCheck(false)
                        ->join('exercise', 'exercise_note.exercise_id = exercise.id', 'exercise.name as ename')
                        ->join('user', 'exercise_note.user_id = user.id', 'name')
                        ->where('exercise_note.status = ?', 'waiting');
        
        $paginator = new Tri_Paginator($select, $page);
        $this->view->data = $paginator->getResult();
    }

    public function viewAction()
    {
        $id               = Zend_Filter::filterStatic($this->_getParam('id'), 'int');
        $exercise         = new Tri_Db_Table('exercise');
        $exerciseRelation = new Tri_Db_Table('exercise_relation');
        $exerciseNote     = new Tri_Db_Table('exercise_note');
        $exerciseAnswer   = new Tri_Db_Table('exercise_answer');
        $textAnswer       = new Tri_Db_Table('exercise_answer_text');

        if ($id) {
            $note = $exerciseNote->fetchRow(array('id = ?' => $id));

            if ($note) {
                $row = $exercise->fetchRow(array('id = ?' => $note->exercise_id));

                if ($row) {
                    $select = $exerciseRelation->select(true)
                                               ->setIntegrityCheck(false)
                                               ->join('exercise_question', 'exercise_question.id = exercise_relation.exercise_question_id')
                                               ->join('exercise_note_question', 'exercise_note_question.exercise_question_id = exercise_question.id',array())
                                               ->where('exercise_relation.exercise_id = ?', $row->id)
                                               ->where('exercise_note_question.exercise_note_id = ?', $note->id)
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
                    $this->view->notes  = $exerciseNote->fetchAll($whereNote, 'id DESC');
                }
            } 
        }
    }
    
    public function saveAction()
    {
        if ($this->_request->isPost()) {
            $data         = $this->_getAllParams();
            $exerciseNote = new Zend_Db_Table('exercise_note');
            $exerciseText = new Zend_Db_Table('exercise_answer_text');
            
            $row = $exerciseNote->find($data['note_id'])->current();
            $row->status = 'end';
            $row->note = $data['note'];
            $row->save();
            
            if (count($data['comment'])) {
                foreach ($data['comment'] as $key => $comment) {
                    $exerciseText->update(array('comment' => $comment), 
                                          array('exercise_note_id = ?' => $data['note_id'],
                                                'exercise_question_id = ?' => $key));
                }
            }
            
            Panel_Model_Panel::addNote($row->user_id, 'exercise', $row->exercise_id, $row->note);
            
            $this->_helper->_flashMessenger->addMessage('Success');
            $this->_redirect('/exercise/correction/index');
        }
    }
}