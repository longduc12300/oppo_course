<?php
class Application_Model_DbTable_CourseStaff extends Zend_Db_Table_Abstract
{
    protected $_name = 'course_staff';

    public function addstaff( $course_id, $staff_id)
    {
        $data = array(
            'course_id' => $course_id,
            'staff_id'  => $staff_id,
        );
        $this->insert($data);
    }

}