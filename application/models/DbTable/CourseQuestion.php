<?php
class Application_Model_DbTable_CourseQuestion extends Zend_Db_Table_Abstract
{
    protected $_name = 'course_question';

    public function addQuestion($course_id, $question)
    {
        $data = array(
            'course_id' => $course_id,
            'question' => $question,

        );
        $this->insert($data);
    }

    public function getQuestions($id)
    {
        $id = (int) $id;
        $row = $this->fetchRow('id = ' . $id);
        if (!$row) {
            throw new Exception("Could not find row $id");
        }
        return $row->toArray();
    }

    public function updateQuestion($id, $course_id, $question)
    {
        $data = array(
            'course_id' => $course_id,
            'question' => $question,
        );
        $this->update($data, 'id = ' . (int) $id);
    }


    public function getQuestionIdsByCourseId($course_id)
    {
        $select = $this->select()
        ->from($this->_name, 'id')
        ->where('course_id = ?', $course_id); 

        $result = $this->fetchAll($select);
        $questionIds = array();
        foreach ($result as $row) {
            $questionIds[] = $row['id'];
        }

        return $questionIds;
    }

    public function deleteByCourseId($course_id) {
        $where = $this->getAdapter()->quoteInto('course_id = ?', $course_id);
        return $this->delete($where);
    }

    public function deleteById($id) {
        $where = $this->getAdapter()->quoteInto('id = ?', $id);
        return $this->delete($where);
    }


    public function getIdsByCourseId($courseId)
    {
        $select = $this->select()
                       ->from($this->_name, 'id')
                       ->where('course_id = ?', $courseId); 

        return $this->getAdapter()->fetchAll($select);
    }
}