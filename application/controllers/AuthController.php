<?php

class AuthController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // $form = new Application_Form_Login();
        $request = $this->getRequest();
        $username = $request->getParam('username');
        $passwd = md5($request->getParam('password'));
        $QAccount = new Application_Model_DbTable_Account();
        if ($request->isPost()) {
            try {

                $Where = [];
                $Where[] = $QAccount->getAdapter()->quoteInto('username LIKE ?', $username);
                $Where[] = $QAccount->getAdapter()->quoteInto('password LIKE ?', $passwd);
                $result = $QAccount->fetchRow($Where);
                if (!empty($result)) {
                    $userStorage = Zend_Auth::getInstance()->getStorage()->write($result);
                    $this->_helper->redirector('welcome', 'course');
                }
                throw new Exception("sai ten dang nhap");
                // if ($form->isValid($request->getPost())) {
// if ($this->_process($form->getValues())) {
            } catch (Exception $e) {
                exit($e->getMessage());
            }
           
        }
        // $this->view->form = $form;
    }

    protected function _process($values)
    {
        // Get our authentication adapter and check credentials
        $adapter = $this->_getAuthAdapter();
        $adapter->setIdentity($values['username']);
        $adapter->setCredential($values['password']);

        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($adapter);
        if ($result->isValid()) {
            $user = $adapter->getResultRowObject();
            $auth->getStorage()->write($user);
            return true;
        }
        return false;
    }

    protected function _getAuthAdapter()
    {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

        $authAdapter->setTableName('account')
            ->setIdentityColumn('username')
            ->setCredentialColumn('password')
            ->setCredentialTreatment('SHA1(CONCAT(?,salt))');

        return $authAdapter;
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector('index'); // back to login page
    }

}



