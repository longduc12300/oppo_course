<?php
class Application_Model_DbTable_ModelQuestion extends Zend_Db_Table_Abstract
{
    protected $_name = 'staff_answer';
    protected $_primary = 'id';

    public function addStaffAnswer( $staff_id, $question_id, $answer_id)
    {
        $data = array(
            'staff_id'=> $staff_id,
            'question_id'=> $question_id,
            'answer_id'=> $answer_id,
        
        );
        $this->insert($data);
    }
    public function getRandomQuestions($limit = 3)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $questionsSelect = $db->select()
                              ->from('course_question')
                              ->order('RAND()')
                              ->limit($limit);
        $questions = $db->fetchAll($questionsSelect);

        foreach ($questions as &$question) {
            $answersSelect = $db->select()
                                ->from('answers')
                                ->where('question_id = ?', $question['id']);
            $question['answers'] = $db->fetchAll($answersSelect);
        }

        return $questions;
    }
}