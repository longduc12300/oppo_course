<?php

class CourseController extends Zend_Controller_Action
{

    public function init()
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        if(empty($userStorage)){
            $this->_helper->redirector('index', 'auth');
        }
    }


    public function indexAction()
    {


    }


    // view, insert
    public function createAction()
    {
        $id = $this->_getParam('id', 0);
        if ($id > 0) {
            $courses = new Application_Model_DbTable_Courses();
            $this->view->courses = $courses->getCourses($id);
        }
    }
    public function saveAction()
    {
        echo '<script> parent.document.getElementById("iframe").style.display = "block" </script>';
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">';

        try {
            $db = Zend_Db_Table::getDefaultAdapter();
            $db->beginTransaction();

            $id = (int) $this->getRequest()->getParam('id');
            $name = $this->getRequest()->getParam('name');
            $content = $this->getRequest()->getParam('editor1');
            $start_date = $this->getRequest()->getParam('timestart');
            $end_date = $this->getRequest()->getParam('timeend');


            if (!$name)
                throw new Exception("chưa có name");
            if (!$start_date)
                throw new Exception("chưa có ngày bắt đầu");
            if (!$end_date)
                throw new Exception("chưa có ngày kết thúc");


            $courses = new Application_Model_DbTable_Courses();

            $data = array(
                'name' => $name,
                'content' => $content,
                'start_date' => $start_date,
                'end_date' => $end_date
                // 'file'       => $myuiq
            );

            // if($_FILES['file']){
            //     $info = pathinfo($_FILES['file']['name']);
            //     $ext  = $info['extension'];                 
            //     // get the extension of the file
            //     // $newname = "newname.".$ext; 
            //     $myuiq = uniqid('').'.'.$ext;
            //     // echo '<pre>'; var_dump($newname);exit;
            //     $target = 'file/'.$myuiq;
            //     move_uploaded_file( $_FILES['file']['tmp_name'], $target);
            //     $data['file'] = $myuiq;

            // } 
            if (!empty($_FILES['file']['name'])) {
                $info = pathinfo($_FILES['file']['name']);
                $ext = $info['extension'];
                // get the extension of the file
                $myuiq = uniqid('') . '.' . $ext;
                $target = 'file/' . $myuiq;
                if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                    $data['file'] = $myuiq;
                } else {
                    throw new Exception('File upload failed.');
                }
            }


            if ($id > 0) {
                $id = $courses->update($data, 'id = ' . (int) $id);
            } else {
                $id = $courses->insert($data);
            }



            $db->commit();



            echo '<div class="alert alert-success">Hoàn thành</div>';

        } catch (Exception $e) {
            // exit($e->getMessage());
            $db->rollBack();

            echo '<script> parent.document.getElementById("btnADD").disabled = false </script>';

            echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';



        }
        //
        exit;
    }
    public function listAction()
    {
        try {
            $coursesModel = new Application_Model_DbTable_Courses();

            $page = $this->getRequest()->getParam('page', 1);
            $limit = 5;
            $offset = ($page - 1) * $limit;
            $totalCourses = $coursesModel->fetchAll()->count();
            $select = $coursesModel->select()
                ->limit($limit, $offset);
            $courses = $coursesModel->fetchAll($select);
            $totalPages = ceil($totalCourses / $limit);

            $this->view->courses = $courses;
            $this->view->currentPage = $page;
            $this->view->totalPages = $totalPages;
        } catch (Exception $e) {
            $this->view->errorMessage = $e->getMessage();
        }
    }


    public function deleteAction()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $id = (int) $id;
            if (!$id) {
                throw new Exception("Invalid course ID");
            }
            $courseModel = new Application_Model_DbTable_Courses();
            $courseModel->deleteCourse($id);
            $this->_helper->redirector('list');
        } catch (Exception $e) {
            $this->view->error = 'Error: ' . $e->getMessage();
        }
    }



    public function questionAction()
    {


        $id = $this->_getParam('id', 0);

        if ($id > 0) {
            $Courses = new Application_Model_DbTable_Courses();
            $where = null;
            $where = $Courses->getAdapter()->quoteInto('id = ?', $id);
            $courses = $Courses->fetchRow($where)->toArray();
            $this->view->courses = $courses;




            $CourseQuestion = new Application_Model_DbTable_CourseQuestion();
            $courseQuestionIds = $CourseQuestion->getIdsByCourseId($id);
            $this->view->courseQuestionIds = $courseQuestionIds;
        }


        $courseId = $this->getRequest()->getParam('id');
        $Answers = new Application_Model_DbTable_Answers();
        $answers = $Answers->getData($courseId);
        $data = [];
        foreach ($answers as $value) {
            $data[$value['question_id']]['question'] = $value['question'];
            $data[$value['question_id']]['data'][] = [
                'answer_text' => $value['answer_text'],
                'is_correct' => $value['is_correct'],
            ];
        }
        $this->view->questionsAndAnswers = $data;



    }

    public function questionSaveAction()
    {
        echo '<script> parent.document.getElementById("iframe").style.display = "block" </script>';
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">';
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $course_id = $this->getRequest()->getParam('id');
            $question = $this->getRequest()->getParam('question');
            $answers = $this->getRequest()->getParam('answer');
            $correctAnswers = $this->getRequest()->getParam('correct');



            $CourseQuestion = new Application_Model_DbTable_CourseQuestion();
            $AnswersModel = new Application_Model_DbTable_Answers();


            $currentQuestionIds = $CourseQuestion->getQuestionIdsByCourseId($course_id);

            if (!empty($currentQuestionIds)) {
                $where = $AnswersModel->getAdapter()->quoteInto('question_id IN (?)', $currentQuestionIds);
                $AnswersModel->delete($where);

                $CourseQuestion->deleteByCourseId($course_id);
            }


            foreach ($question as $index => $question_text) {
                $data = array(
                    'course_id' => $course_id,
                    'question' => $question_text
                );
                $id = $CourseQuestion->insert($data);


                $answerData = array();
                foreach ($answers[$index] as $answerIndex => $answer_text) {
                    $is_correct = ($correctAnswers[$index] == $answerIndex) ? 1 : 0;
                    $answerData[] = array(
                        'question_id' => $id,
                        'answer_text' => $answer_text,
                        'is_correct' => $is_correct
                    );
                }

                foreach ($answerData as $data) {
                    $AnswersModel->insert($data);
                }
            }
            $db->commit();

            echo '<div class="alert alert-success">Hoàn thành</div>';

        } catch (Exception $e) {
            $db->rollBack();

            echo '<script> parent.document.getElementById("btnADD").disabled = false </script>';
            echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
        }
        exit;
    }




    public function staffAction()
    {
        $code = $this->getRequest()->getParam('code', null);
        $name = $this->getRequest()->getParam('name', null);
        $department = $this->getRequest()->getParam('department', null);
        $team1 = $this->getRequest()->getParam('team', null);
        $title = $this->getRequest()->getParam('title', null);


        $QTeam = new Application_Model_DbTable_Team();

        $params = [
            'department' => $department,
            'team' => $team1,
            'title' => $title,
        ];
        $this->view->params = $params;


        // lấy được danh sách team
        if ($department) {
            $where = array();
            $where[] = $QTeam->getAdapter()->quoteInto('parent_id = ?', $department);
            $data_team = $QTeam->fetchAll($where)->toArray();
            $this->view->data_team = $data_team;
        }

        if ($team1) {

            $where = array();
            $where[] = $QTeam->getAdapter()->quoteInto('parent_id = ?', $team1);
            $data_title = $QTeam->fetchAll($where)->toArray();
            $this->view->data_title = $data_title;
        }
        //danh sách title        
        // $title_id = $this->getRequest()->getParam('title_id', null);
        // $where = null;
        // $where = $QTeam->getAdapter()->quoteInto('parent_id = ?', $title_id);
        // $data_title  = $QTeam->fetchAll($where)->toArray();
        // $this->view->data_title = $data_title;
        $where = array();
        $where[] = $QTeam->getAdapter()->quoteInto('parent_id = ?', 0);
        $team = $QTeam->fetchAll($where)->toArray();
        $this->view->team = $team;


        $Courses = new Application_Model_DbTable_Courses();
        $data = $Courses->getData($code, $name, $department, $team1, $title);
        $this->view->data = $data;

        $id = $this->_getParam('id', 0);
        if ($id > 0) {
            $Courses = new Application_Model_DbTable_Courses();
            $courses = $Courses->getCourses($id);
            $this->view->courses = $courses;
        }

    }



    public function staffSaveAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        echo '<script> parent.document.getElementById("iframe").style.display = "block" </script>';
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">';
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try {

            $course_id = $this->getRequest()->getParam('id');
            $staff_ids = $this->getRequest()->getParam('staff_ids', []);



            $CourseStaff = new Application_Model_DbTable_CourseStaff();

            foreach ($staff_ids as $staff_id) {
                $data = array(
                    'course_id' => $course_id,
                    'staff_id' => $staff_id
                );
                $CourseStaff->insert($data);
            }
            $db->commit();



            echo '<div class="alert alert-success">Hoàn thành</div>';

        } catch (Exception $e) {
            // exit($e->getMessage());
            $db->rollBack();

            echo '<script> parent.document.getElementById("btnADD").disabled = false </script>';

            echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';



        }
        //
        exit;
    }

    public function getStaffInformationAction()
    {
        $code = $this->getRequest()->getParam('code', '');
        $name = $this->getRequest()->getParam('name', '');
        $department = $this->getRequest()->getParam('department', '');
        $team = $this->getRequest()->getParam('team', '');
        $title = $this->getRequest()->getParam('title', '');




        $Courses = new Application_Model_DbTable_Courses();

        try {
            $courses = $Courses->getDatanew($code, $name, $department, $team, $title);
            $this->view->courses = $courses;

        } catch (Exception $e) {
            $this->view->error = 'Error: ' . $e->getMessage();
        }

    }

    public function deleteFileAction()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            $coursesModel = new Application_Model_DbTable_Courses();
            try {
                $coursesModel->deleteFile($id);
                $this->_redirect('/course/create/id/' . $id);
            } catch (Exception $e) {
                $this->view->error = 'Error: ' . $e->getMessage();
            }
        }
    }

    public function fetchTeamsAction()
    {
        $QTeam = new Application_Model_DbTable_Team();
        $department_id = $this->getRequest()->getParam('department_id', null);

        $where = null;
        $where = $QTeam->getAdapter()->quoteInto('parent_id = ?', $department_id);
        $data = $QTeam->fetchAll($where)->toArray();
        echo json_encode($data);
        exit;
    }

    // Action to fetch titles based on team ID
    public function fetchTitlesAction()
    {
        $QTeam = new Application_Model_DbTable_Team();
        $title_id = $this->getRequest()->getParam('title_id', null);

        $where = null;
        $where = $QTeam->getAdapter()->quoteInto('parent_id = ?', $title_id);
        $data = $QTeam->fetchAll($where)->toArray();
        echo json_encode($data);
        exit;
    }

    // public function answerAction()
    // {
    //     try {
    //         $courseAnswers = new Application_Model_DbTable_Answers();
    //         $answers = $courseAnswers->fetchAll();
    //         $this->view->answers = $answers;
    //     } catch (Exception $e) {
    //     }
    // 
    public function excelAction()
    {
        require_once __DIR__ . '/../../vendor/autoload.php';

        $coursesModel = new Application_Model_DbTable_Courses();
        $courses = $coursesModel->fetchAll();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Content');
        $sheet->setCellValue('D1', 'Start Date');
        $sheet->setCellValue('E1', 'End Date');

        $row = 2;
        foreach ($courses as $course) {
            $sheet->setCellValue('A' . $row, $course->id);
            $sheet->setCellValue('B' . $row, $course->name);
            $sheet->setCellValue('C' . $row, $course->content);
            $sheet->setCellValue('D' . $row, $course->start_date);
            $sheet->setCellValue('E' . $row, $course->end_date);
            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'courses_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Clean output buffer to prevent corrupted files
        if (ob_get_contents()) {
            ob_end_clean();
        }

        // Redirect output to client browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function answerAction()
    {
        $answerModel = new Application_Model_DbTable_Answers();
        $answers = $answerModel->fetchAll();
        $this->view->answers = $answers;
    }

}