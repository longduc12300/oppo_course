<?php
class Application_Model_DbTable_Courses extends Zend_Db_Table_Abstract
{
    protected $_name = 'courses';

    public function addCourse( $name, $content, $start_date, $end_date, $file)
    {
        $data = array(
            'name'=> $name,
            'content'=> $content,
            'start_date'=> $start_date,
            'end_date'=> $end_date,
            'file'=> $file,

        );
        $this->insert($data);
    }
    public function getCourses($id)
    {
        $id = (int)$id;
        $row = $this->fetchRow('id = ' . $id);
        if (!$row) {
            throw new Exception("Could not find row $id");
        }
        return $row->toArray();
    }

    public function updateCourse($id, $name, $content, $start_date, $end_date, $file)
    {
        $data = array(
            'name'=> $name,
            'content'=> $content,
            'start_date'=> $start_date,
            'end_date'=> $end_date,
            'file'=> $file,

        );
        $this->update($data, 'id = '. (int)$id);
    }
    
    public function getDatanew ($staffCode, $staffName) {
                $db = Zend_Db_Table::getDefaultAdapter();

                try {
                    $stmt = $this->$db->query("CALL GetStaffInformation(?, ?)", array($staffCode, $staffName));
                    $result = $stmt->fetchAll();
                    return $result;
                } catch (Exception $e) {
                    throw new Exception('Error executing stored procedure: ' . $e->getMessage());
                }
            }
        



    public function getData ($code = null, $name = null, $department = null, $team = null, $title = null) {
        $db = Zend_Db_Table::getDefaultAdapter();        
        $select = $db->select();
        $select->from(array('s' => 'staff'), [
            'staff_id' => 's.id',
            'code'=> 's.code',
            'staff_name' => 'CONCAT(s.firstname, " ", s.lastname)',
            'department' => 't.name' ,
            'team' =>'t1.name',
            'title' =>'t2.name',
            'gender' => new Zend_Db_Expr("CASE WHEN s.gender = 0 THEN 'Nữ' WHEN s.gender = 1 THEN 'Nam' ELSE 'Khác' END"),
            'Region' =>'r.name',
            'Area' =>'a.name'

        ]);
        $select->joinLeft(
                    ['t' => 'team'],
                    't.id = s.department',
                    array()
        );
        $select->joinLeft(
                    ['t1' => 'team'],
                    't1.id = s.department',
                    array()
        );   
        $select->joinLeft(
                    ['t2' => 'team'],
                    't2.id = s.title',
                    array()
        );
        $select->joinLeft(
                    ['r' => 'regional_market'],
                    'r.id = s.regional_market',
                    array()
        );
        $select->joinLeft(
                    ['a' => 'area'],
                    'a.id = r.area_id',
                    array()
        );
        if (!empty($name)) {
            $select->where('CONCAT(s.firstname, " ", s.lastname) LIKE ?', '%' . $name . '%');
        }
        
        if (!empty($code)) {
            $select->where('code LIKE ?', '%' . $code . '%');
        }
        
        if (!empty($department)) {
            $select->where('department = ?', $department);
        }
        
        if (!empty($team)) {
            $select->where('team = ?', $team);
        }
        
        if (!empty($title)) {
            $select->where('title = ?', $title);
        }
      

        $select-> order('s.lastname');
                // ->where('advertisercontest.golive is not NULL');
        $result = $db->fetchAll($select);
        // echo '<pre>'; var_dump($result);exit;
        return $result;
        }


    public function deleteCourse($id)
    {
        $id = (int)$id;
            $row = $this->fetchRow('id = ' . $id);
            if (!$row) {

        throw new Exception("Could not find row $id");
            }
            $this->delete('id = ' . $id);
    }


    public function deleteFile($id)
    {
        $course = $this->fetchRow('id = ' . (int)$id);
        if (!$course) {
            throw new Exception("Could not find course with id $id");
        }

        $filePath = 'file/' . $course['file'];
        if (file_exists($filePath)) {
            unlink($filePath); // Xóa file trên hệ thống
        }

        // Cập nhật lại trường 'file' trong cơ sở dữ liệu
        $this->update(['file' => null], 'id = ' . (int)$id);
    }

}