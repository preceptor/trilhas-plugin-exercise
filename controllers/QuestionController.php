<?php
class Exercise_QuestionController extends Tri_Controller_Action
{
    public function init()
    {
        parent::init();
        $this->view->title = "Question";
    }

    public function indexAction()
    {
        $page     = Zend_Filter::filterStatic($this->_getParam('page'), 'int');
        $table    = new Tri_Db_Table('exercise_question');
        
        $select = $table->select()->order('id DESC');
        
        $paginator = new Tri_Paginator($select, $page);
        $this->view->data = $paginator->getResult();
    }

    public function formAction()
    {
        $id   = Zend_Filter::filterStatic($this->_getParam('id'), 'int');
        $type = $this->_getParam('type');
        $form = new Exercise_Form_Question();

        if ($id) {
            $table = new Tri_Db_Table('exercise_question');
            $row   = $table->find($id)->current();
            $type  = $row->type;

            if ($row) {
                $form->addByType($type, $id);
                $form->populate($row->toArray());
            }
            $this->view->update = $this->_getParam('update');
        } else {
            $form->addByType($type, $id);
        }
        
        if (!$type) {
            $this->render('type');
        } 
        
        $this->view->form = $form;
    }
    
    public function saveAction()
    {
        $form    = new Exercise_Form_Question();
        $table   = new Tri_Db_Table('exercise_question');
        $option  = new Tri_Db_Table('exercise_option');
        $session = new Zend_Session_Namespace('data');
        $allData = $this->_getAllParams();

        $form->addByType($allData['type']);

        if ($form->isValid($allData)) {
            $data = $form->getValues();
            if (isset($data['id']) && $data['id']) {
                $row = $table->find($data['id'])->current();
                $row->setFromArray($data);
                $id = $row->save();
            } else {
                unset($data['id']);
                if (isset($session->exercise_id) && $session->exercise_id) {
                    $exerciseId = $data['exercise_id'] = $session->exercise_id;
                }
                $row = $table->createRow($data);
                $id = $row->save();
            }

            if (count($allData['option'])) {
                foreach ($allData['option'] as $key => $value) {
                    $status = "wrong";
                    if (is_array($allData['right_option'])) {
                        if (in_array("$key", $allData['right_option'], true)) {
                            $status = "right";
                        }
                    } else {
                        if ($allData['right_option'] == $key) {
                            $status = "right";
                        }
                    }

                    if ($value) {
                        if (isset($allData['id_option'][$key]) && $allData['id_option'][$key] != 0) {
                            $row = $option->find($allData['id_option'][$key])->current();
                            $row->setFromArray(array('description' => $value,
                                                     'status' => $status));
                            $row->save();
                        } else {
                            $data = array('description' => $value,
                                          'exercise_question_id' => $id,
                                          'status' => $status);
                            $row = $option->createRow($data);
                            $row->save();
                        }
                    } else {
                        if (isset($allData['id_option'][$key]) && $allData['id_option'][$key] != 0) {
                            $option->find($allData['id_option'][$key])->current()->delete();
                        }
                    }
                }
            }

            $this->_helper->_flashMessenger->addMessage('Success');
            $this->_redirect('exercise/question/index/id/'. $exerciseId);
        }

        $this->view->messages = array('Error');
        $this->getResponse()->prepend('messages', $this->view->render('message.phtml'));
        
        $this->view->form = $form;
        $this->render('form');
    }
    
    public function selectAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $page     = Zend_Filter::filterStatic($this->_getParam('page'), 'int');

        $table  = new Tri_Db_Table('exercise_question');
        $select = $table->select()->order('id DESC');
        
        $paginator = new Tri_Paginator($select, $page);
        $this->view->data = $paginator->getResult();
    }
}