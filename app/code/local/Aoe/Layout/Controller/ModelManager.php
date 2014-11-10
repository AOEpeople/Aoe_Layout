<?php
abstract class Aoe_Layout_Controller_ModelManager extends Aoe_Layout_Controller_Model
{
    /**
     * Create a new record
     */
    public function newAction()
    {
        $model = $this->loadModel();
        if (!$model->isObjectNew()) {
            $this->_forward('noroute');
            return;
        }

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            try {
                $model->isObjectNew(true);
                foreach($this->preprocessPostData($postData) as $key => $value) {
                    $model->setDataUsingMethod($key, $value);
                }
                $model->save();
                $this->_redirectUrl($this->getHelper()->getGridUrl());
                $this->_getSession()->addSuccess('Saved Successfully');
                return;
            } catch (Exception $e) {
                $message = $e->getMessage();
                if ($e instanceof Zend_Db_Statement_Exception && $e->getChainedException() instanceof PDOException) {
                    /** @var PDOException $e */
                    $e = $e->getChainedException();
                    if (isset($e->errorInfo[0]) && $e->errorInfo[0] == 23000 && isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                        $message = $this->getHelper()->__(
                            "Record already exists for %s '%s'",
                            $model->getIdFieldName(),
                            $model->getId()
                        );
                    }
                }
                Mage::logException($e);
                $this->_getSession()->addError($message);
            }
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Edit an existing record
     */
    public function editAction()
    {
        $model = $this->loadModel();
        if ($model->isObjectNew()) {
            $this->_forward('noroute');
            return;
        }

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            try {
                foreach($this->preprocessPostData($postData) as $key => $value) {
                    $model->setDataUsingMethod($key, $value);
                }
                $model->save();
                $this->_redirectUrl($this->getHelper()->getGridUrl());
                $this->_getSession()->addSuccess('Saved Successfully');
                return;
            } catch (Exception $e) {
                if ($e instanceof Zend_Db_Statement_Exception && $e->getChainedException() instanceof PDOException) {
                    $e = $e->getChainedException();
                }
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Delete an existing record
     */
    public function deleteAction()
    {
        $model = $this->loadModel();
        if ($model->isObjectNew()) {
            $this->_forward('noroute');
            return;
        }

        try {
            $model->delete();
            $this->_redirectUrl($this->getHelper()->getGridUrl());
            $this->_getSession()->addSuccess('Deleted Successfully');
        } catch (Exception $e) {
            if ($e instanceof Zend_Db_Statement_Exception && $e->getChainedException() instanceof PDOException) {
                $e = $e->getChainedException();
            }
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
            $this->_redirectUrl($this->getHelper()->getEditUrl($model));
        }
    }

    /**
     * Pre-process the POST data before adding to the model
     *
     * @param array $postData
     *
     * @return array
     */
    protected function preprocessPostData(array $postData)
    {
        unset($postData['form_key']);
        return $postData;
    }

    protected function getAclActionName()
    {
        $action = parent::getAclActionName();

        if ($action !== 'view') {
            $action = 'edit';
        }

        return $action;
    }

}
