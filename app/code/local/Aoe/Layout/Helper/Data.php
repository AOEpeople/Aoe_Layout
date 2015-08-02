<?php

/**
 * Class Aoe_Layout_Helper_Data
 *
 * @author Lee Saferite <lee.saferite@aoe.com>
 * @
 */
class Aoe_Layout_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param Varien_Object $object
     * @param string        $key
     *
     * @return mixed
     */
    public function getObjectData(Varien_Object $object, $key = null)
    {
        if ($key) {
            return $object->getDataUsingMethod($key);
        } else {
            return $object->getData();
        }
    }

    /**
     * @param string $modelRef
     * @param bool   $useCollection
     * @param bool   $trimEmptyValues
     *
     * @return array|mixed
     *
     * @throws RuntimeException
     */
    public function getSourceModelArray($modelRef, $useCollection = false, $trimEmptyValues = false)
    {
        $model = Mage::getSingleton($modelRef);
        if (!$model) {
            throw new RuntimeException($this->__('Could not create source model (%s)', $modelRef));
        }

        $useCollection = (bool)$useCollection;

        if ($useCollection) {
            if (!$model instanceof Mage_Core_Model_Abstract) {
                throw new RuntimeException($this->__('Invalid source model type (%s)', $modelRef));
            }

            $collection = $model->getCollection();
            if (!$collection) {
                throw new RuntimeException($this->__('Could not create collection for source model (%s)', $modelRef));
            }

            $model = $collection;
        }

        $additionalArgs = func_get_args();
        array_shift($additionalArgs);
        array_shift($additionalArgs);
        array_shift($additionalArgs);

        if (method_exists($model, 'toOptionArray')) {
            $optionArray = call_user_func_array([$model, 'toOptionArray'], $additionalArgs);
        } elseif (method_exists($model, 'toOptionHash')) {
            $optionHash = call_user_func_array([$model, 'toOptionHash'], $additionalArgs);
            $optionArray = [];
            foreach ($optionHash as $value => $label) {
                $optionArray[] = [
                    'value' => $value,
                    'label' => $label
                ];
            }
        } else {
            if ($useCollection) {
                throw new RuntimeException($this->__('Source model (%s) collection does not have required method toOptionArray or toOptionHash', $modelRef));
            } else {
                throw new RuntimeException($this->__('Source model (%s) does not have required method toOptionArray or toOptionHash', $modelRef));
            }
        }

        if ($trimEmptyValues) {
            foreach ($optionArray as $k => $option) {
                if ($option['value'] == '') {
                    unset($optionArray[$k]);
                }
            }
        }

        return $optionArray;
    }

    /**
     * @param string $modelRef
     * @param bool   $useCollection
     * @param bool   $trimEmptyValues
     *
     * @return array|mixed
     *
     * @throws RuntimeException
     */
    public function getSourceModelHash($modelRef, $useCollection = false, $trimEmptyValues = false)
    {
        $model = Mage::getSingleton($modelRef);
        if (!$model) {
            throw new RuntimeException($this->__('Could not create source model (%s)', $modelRef));
        }

        $useCollection = (bool)$useCollection;

        if ($useCollection) {
            if (!$model instanceof Mage_Core_Model_Abstract) {
                throw new RuntimeException($this->__('Invalid source model type (%s)', $modelRef));
            }

            $collection = $model->getCollection();
            if (!$collection) {
                throw new RuntimeException($this->__('Could not create collection for source model (%s)', $modelRef));
            }

            $model = $collection;
        }

        $additionalArgs = func_get_args();
        array_shift($additionalArgs);
        array_shift($additionalArgs);
        array_shift($additionalArgs);

        if (method_exists($model, 'toOptionHash')) {
            $optionHash = call_user_func_array([$model, 'toOptionHash'], $additionalArgs);
        } elseif (method_exists($model, 'toOptionArray')) {
            $optionArray = call_user_func_array([$model, 'toOptionArray'], $additionalArgs);
            $optionHash = [];
            foreach ($optionArray as $option) {
                $optionHash[$option['value']] = $option['label'];
            }
        } else {
            if ($useCollection) {
                throw new RuntimeException($this->__('Source model (%s) collection does not have required method toOptionArray or toOptionHash', $modelRef));
            } else {
                throw new RuntimeException($this->__('Source model (%s) does not have required method toOptionArray or toOptionHash', $modelRef));
            }
        }

        if ($trimEmptyValues) {
            unset($optionHash['']);
        }

        return $optionHash;
    }

    /**
     * Simple helper method to expose Mage_Core_Model_App::isSingleStoreMode()
     *
     * @return bool
     *
     * @see Mage_Core_Model_App::isSingleStoreMode()
     */
    public function getIsSingleStoreMode()
    {
        return Mage::app()->isSingleStoreMode();
    }

    /**
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param array                                           $filters
     * @param array                                           $ifConditionals
     *
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    public function filterCollection(Mage_Core_Model_Resource_Db_Collection_Abstract $collection, array $filters, array $ifConditionals = [])
    {
        $filter = true;
        foreach ($ifConditionals as $ifConditional) {
            if ($ifConditional === 'false' || !(bool)$ifConditional) {
                $filter = false;
                break;
            }
        }

        if ($filter) {
            foreach ($filters as $field => $condition) {
                $collection->addFieldToFilter($field, $condition);
            }
        }

        return $collection;
    }

    /**
     * @author Lee Saferite <lee.saferite@aoe.com>
     * @return Mage_Admin_Model_Session
     */
    public function getAdminSession()
    {
        return Mage::getSingleton('admin/session');
    }

    /**
     * @param string $route
     * @param array  $params
     *
     * @return string
     *
     * @author Lee Saferite <lee.saferite@aoe.com>
     */
    public function getUrl($route, $params = [])
    {
        return $this->_getUrl($route, $params);
    }
}
