<?php
class Exercise_IndexController extends Tri_Controller_Action
{
    public function init()
    {
        parent::init();
        $this->view->title = "Exercise";
    }

    public function indexAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $exercise = new Tri_Db_Table('exercise');
        $session = new Zend_Session_Namespace('data');
        $where = array('classroom_id = ?' => $session->classroom_id);

        if ($identity->role == 'student') {
            $where['begin  <= ?'] = date('Y-m-d');
            $where['status IN(?)'] = array('active','final');
        }
        
        $this->view->data = $exercise->fetchAll($where , array("begin", "end", "name"));
    }

    public function formAction()
    {
        $id   = Zend_Filter::filterStatic($this->_getParam('id'), 'int');
        $form = new Exercise_Form_Exercise();

        if ($id) {
            $table = new Tri_Db_Table('exercise');
            $row   = $table->find($id)->current();

            if ($row) {
                $form->populate($row->toArray());
                $question = new Tri_Db_Table('exercise_question');
                $select = $question->select(true)
                                   ->setIntegrityCheck(false)
                                   ->join('exercise_relation', 'exercise_relation.exercise_question_id = exercise_question.id', array('note'))
                                   ->where('exercise_id = ?', $id)
                                   ->order('position');
                $this->view->questions = $question->fetchAll($select);
                $this->view->id = $id;
            }
        }

        $this->view->form = $form;
    }

    public function saveAction()
    {
        $form  = new Exercise_Form_Exercise();
        $table = new Tri_Db_Table('exercise');
        $session = new Zend_Session_Namespace('data');
        $data  = $this->_getAllParams();
        $questionIds = $data['question_id'];
        $notes       = $data['note'];
        
        if ($form->isValid($data)) {
            $data = $form->getValues();
            $data['user_id']      = Zend_Auth::getInstance()->getIdentity()->id;
            $data['classroom_id'] = $session->classroom_id;

            if (!$data['end']) {
                unset($data['end']);
            }

            if (isset($data['id']) && $data['id']) {
                $row = $table->find($data['id'])->current();
                $row->setFromArray($data);
                $id = $row->save();

                Exercise_Model_Question::remove($id);
                Exercise_Model_Question::associate($id, $questionIds, $notes);
            } else {
                unset($data['id']);
                $row = $table->createRow($data);
                $id = $row->save();
                Application_Model_Timeline::save('created a new exercise', $data['title']);
            }

            $this->_helper->_flashMessenger->addMessage('Success');
            $this->_redirect('exercise/index/form/id/'.$id);
        }

        $this->view->messages = array('Error');
        $this->getResponse()->prepend('messages', $this->view->render('message.phtml'));
        
        $this->view->form = $form;
        $this->render('form');
    }

    public function viewAction()
    {
        $id     = Zend_Filter::filterStatic($this->_getParam('id'), 'int');
        $userId = Zend_Filter::filterStatic($this->_getParam('userId'), 'int');
        $this->_redirect('/exercise/reply/view/layout/box/exerciseId/'.$id.'/userId/'.$userId);
    }
}