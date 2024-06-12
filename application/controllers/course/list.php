<?php

try {
    $coursesModel = new Application_Model_DbTable_Courses();

    $page         = $this->getRequest()->getParam('page', 1);
    $limit        = 5;
    $offset       = ($page - 1) * $limit;
    $totalCourses = $coursesModel->fetchAll()->count();
    $select       = $coursesModel->select()
        ->limit($limit, $offset);
    $courses    = $coursesModel->fetchAll($select);
    $totalPages = ceil($totalCourses / $limit);

    $this->view->courses     = $courses;
    $this->view->currentPage = $page;
    $this->view->totalPages  = $totalPages;
} catch (Exception $e) {
    $this->view->errorMessage = $e->getMessage();
}