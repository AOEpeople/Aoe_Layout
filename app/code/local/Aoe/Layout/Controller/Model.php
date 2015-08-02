<?php

abstract class Aoe_Layout_Controller_Model extends Aoe_Layout_Controller_Abstract
{
    /**
     * List existing records via a grid
     */
    public function indexAction()
    {
        if ($this->getRequest()->isAjax()) {
            try {
                $this->loadLayout(null, false, false);
                $this->getLayout()->getUpdate()->setCacheId(null);
                $this->getLayout()->getUpdate()->addHandle(strtolower($this->getFullActionName()) . '_AJAX');
            } catch (Exception $e) {
                Mage::logException($e);
                $this->getResponse()->setHeader('Content-Type', 'application/json');
                $this->getResponse()->setBody(Zend_Json::encode(['error' => true, 'message' => $e->getMessage()]));
            }
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * View existing record
     */
    public function viewAction()
    {
        $model = $this->loadModel();
        if (!$model->getId()) {
            $this->_forward('noroute');
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function loadModel()
    {
        /** @var Mage_Core_Model_Abstract $model */
        $model = $this->getHelper()->getModel();

        $id = $this->getRequest()->getUserParam($model->getIdFieldName(), $this->getRequest()->getQuery($model->getIdFieldName()));
        $model->load($id);

        $this->getHelper()->setCurrentRecord($model);

        return $model;
    }

    /**
     * @return Aoe_Layout_Helper_AbstractModelManager
     */
    abstract protected function getHelper();

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->getHelper()->getAclPermission($this->getAclActionName());
    }

    protected function getAclActionName()
    {
        $action = $this->getRequest()->getActionName();

        if ($action === 'index') {
            $action = 'view';
        }

        return $action;
    }
}
