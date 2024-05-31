<?php

class Application_Model_DbTable_Answers extends Zend_Db_Table_Abstract
{
    protected $_name = 'answers';

    public function addAnswer($question_id, $answer_text, $is_correct)
    {
        $data = array(
            'question_id' => $question_id,
            'answer_text' => $answer_text,
            'is_correct' => $is_correct,
        );
        return $this->insert($data);
    }

    public function getData ($course_id) {
        $db     = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select();
        $select->from(array('p' => 'answers'),[
            'course_id'   => 'q.course_id',
            'answer_text' => 'p.answer_text',
            'is_correct'  => 'p.is_correct',
            'question_id' => 'p.question_id',
            'question'    => 'q.question'

        ]);
        $select->joinLeft(
            ['q' => 'course_question'],
                    'q.id = p.question_id',
                    array()
        );
        $select->where( 'q.course_id = ?', $course_id);
        $result = $db->fetchAll($select);
        return $result; 
    }
    public function deleteByQuestionIds(array $questionIds) {
        $where = $this->getAdapter()->quoteInto('question_id IN (?)', $questionIds);
        return $this->delete($where);
    }
    
}