<?php

/**
 * @author Lee Saferite <lee.saferite@aoe.com>
 * @since  3/24/14
 */
class Aoe_Layout_Model_Layout extends Mage_Core_Model_Layout
{
    /**
     * Layout XML generation
     *
     * @return Aoe_Layout_Model_Layout
     */
    public function generateXml()
    {
        $xml = $this->getUpdate()->asSimplexml();
        $removeInstructions = $xml->xpath("//remove");
        if (is_array($removeInstructions)) {
            foreach ($removeInstructions as $infoNode) {
                /** @var Mage_Core_Model_Layout_Element $infoNode */
                if (!$this->checkConditionals($infoNode, false)) {
                    continue;
                }
                $blockName = trim((string)$infoNode['name']);
                if ($blockName) {
                    $ignoreNodes = $xml->xpath("//block[@name='" . $blockName . "'] | //reference[@name='" . $blockName . "']");
                    if (is_array($ignoreNodes)) {
                        foreach ($ignoreNodes as $ignoreNode) {
                            /** @var Mage_Core_Model_Layout_Element $ignoreNode */
                            $ignoreNode['ignore'] = true;
                        }
                    }
                }
            }
        }
        $this->setXml($xml);
        return $this;
    }

    /**
     * Create layout blocks hierarchy from layout xml configuration
     *
     * @param Mage_Core_Model_Layout_Element|null $parent
     */
    public function generateBlocks($parent = null)
    {
        if ($parent instanceof Mage_Core_Model_Layout_Element) {
            // This prevents processing child blocks if the parent block fails a conditional check
            if (!$this->checkConditionals($parent)) {
                return;
            }

            // This is handled here so it catches 'block' and 'reference' elements
            $this->processOutputAttribute($parent);
        }

        parent::generateBlocks($parent);
    }

    /**
     * Add block object to layout based on xml node data
     *
     * @param Varien_Simplexml_Element $node
     * @param Varien_Simplexml_Element $parent
     *
     * @return Aoe_Layout_Model_Layout
     */
    protected function _generateBlock($node, $parent)
    {
        if ($node instanceof Mage_Core_Model_Layout_Element) {
            if (!$this->checkConditionals($node)) {
                return $this;
            }
        }

        return parent::_generateBlock($node, $parent);
    }

    /**
     * Convert an action node into a method call on the parent block
     *
     * @param Varien_Simplexml_Element $node
     * @param Varien_Simplexml_Element $parent
     *
     * @return Aoe_Layout_Model_Layout
     */
    protected function _generateAction($node, $parent)
    {
        if ($node instanceof Mage_Core_Model_Layout_Element) {
            if (!$this->checkConditionals($node)) {
                return $this;
            }
        }

        $method = (string)$node['method'];
        if (!empty($node['block'])) {
            $parentName = (string)$node['block'];
        } else {
            $parentName = $parent->getBlockName();
        }

        $_profilerKey = 'BLOCK ACTION: ' . $parentName . ' -> ' . $method;
        Varien_Profiler::start($_profilerKey);

        if (!empty($parentName)) {
            $block = $this->getBlock($parentName);
        }
        if (!empty($block)) {
            $args = (array)$node->children();

            $jsonArgs = (isset($node['json']) ? explode(' ', (string)$node['json']) : []);
            $jsonHelper = Mage::helper('core');
            $translateArgs = (isset($node['translate']) ? explode(' ', (string)$node['translate']) : []);
            $translateHelper = Mage::helper(isset($node['module']) ? (string)$node['module'] : 'core');
            $args = $this->processActionArgs($args, $jsonArgs, $jsonHelper, $translateArgs, $translateHelper);

            call_user_func_array([$block, $method], $args);
        }

        Varien_Profiler::stop($_profilerKey);

        return $this;
    }

    /**
     * @param array  $args
     * @param array  $jsonArgs
     * @param null   $jsonHelper
     * @param array  $transArgs
     * @param null   $transHelper
     * @param string $currentPath
     *
     * @return array
     */
    protected function processActionArgs(array $args, $jsonArgs = [], $jsonHelper = null, $transArgs = [], $transHelper = null, $currentPath = '')
    {
        $jsonHelper = ((!is_null($jsonHelper) && method_exists($jsonHelper, 'jsonDecode')) ? $jsonHelper : Mage::helper('core'));
        $transHelper = ((!is_null($transHelper) && method_exists($transHelper, '__')) ? $transHelper : Mage::helper('core'));

        foreach ($args as $key => $arg) {
            $path = $currentPath . $key;
            if ($arg instanceof Mage_Core_Model_Layout_Element) {
                // Process depth-first
                if ($arg->hasChildren()) {
                    $children = $this->processActionArgs((array)$arg->children(), $jsonArgs, $jsonHelper, $transArgs, $transHelper, $path . '.');
                }

                // Attempt to process helpers
                if (isset($children)) {
                    $tempArg = $children;
                } else {
                    $tempArg = (string)$arg;
                }

                if (isset($arg['helper'])) {
                    $args[$key] = $this->getHelperMethodValue($arg['helper'], $tempArg);
                } else {
                    $args[$key] = $tempArg;
                }

                unset($children);
            }

            if (is_string($args[$key]) && in_array($path, $jsonArgs)) {
                $args[$key] = $jsonHelper->jsonDecode($args[$key]);
            }

            if (in_array($path, $transArgs)) {
                if (is_string($args[$key])) {
                    $args[$key] = $transHelper->__($args[$key]);
                } else {
                    $args[$key] = call_user_func_array([$transHelper, '__'], $args[$key]);
                }
            }
        }

        return $args;
    }

    /**
     * Gets the value of a helper method from the helper method string
     *
     * @param $helperMethodString
     * @param array|string $args
     *
     * @return mixed
     */
    protected function getHelperMethodValue($helperMethodString, $args = null)
    {
        $helperName = explode('/', (string)$helperMethodString);
        $helperMethod = array_pop($helperName);
        $helperName = implode('/', $helperName);
        $helperArgs = [];

        if (!is_array($args)) {
            $helperArgs[] = $args;
        } else {
            $helperArgs = $args;
        }

        return call_user_func_array([Mage::helper($helperName), $helperMethod], $helperArgs);
    }

    /**
     * Process the 'output' attribute of a node
     *
     * This is an extension that allows the output attribute to be specified on reference elements.
     * It also adds the ability to disable output of the node by setting the value to an empty string.
     *
     * @param Mage_Core_Model_Layout_Element $node
     */
    protected function processOutputAttribute(Mage_Core_Model_Layout_Element $node)
    {
        if (isset($node['output'])) {
            $blockName = (string)$node['name'];
            if (empty($blockName)) {
                return;
            }
            $method = trim((string)$node['output']);
            if (empty($method)) {
                $this->removeOutputBlock($blockName);
            } else {
                $this->addOutputBlock($blockName, $method);
            }
        }
    }

    /**
     * Checks all conditionals for a given layout node
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @param bool $aclDefault
     *
     * @return bool
     */
    protected function checkConditionals(Mage_Core_Model_Layout_Element $node, $aclDefault = true)
    {
        return $this->checkConfigConditional($node)
               && ($aclDefault ? $this->checkAclConditional($node, $aclDefault) : !$this->checkAclConditional($node, $aclDefault))
               && $this->checkHelperConditional($node);
    }

    /**
     * Process the 'ifconfig' and 'unlessconfig' attributes to possibly disable a block/reference/action
     *
     * @param Mage_Core_Model_Layout_Element $node
     *
     * @return bool
     */
    protected function checkConfigConditional(Mage_Core_Model_Layout_Element $node)
    {
        if (isset($node['ifconfig']) && ($configPath = trim((string)$node['ifconfig']))) {
            $negativeCheck = (substr($configPath, 0, 1) === '!');
            $configPath = ($negativeCheck ? substr($configPath, 1) : $configPath);
            if (Mage::getStoreConfigFlag($configPath) === $negativeCheck) {
                return false;
            }
        }

        // This is to support compatibility with the Aoe_LayoutConditions module
        if (isset($node['unlessconfig']) && ($configPath = trim((string)$node['unlessconfig']))) {
            if (Mage::getStoreConfigFlag($configPath)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Process the 'acl' attributes to possibly disable a block/reference/action
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @param bool                           $default
     *
     * @return bool
     */
    protected function checkAclConditional(Mage_Core_Model_Layout_Element $node, $default = true)
    {
        if (isset($node['acl']) && ($aclPath = trim((string)$node['acl']))) {
            $negativeCheck = (substr($aclPath, 0, 1) === '!');
            $aclPath = ($negativeCheck ? substr($aclPath, 1) : $aclPath);
            if (Mage::getSingleton('admin/session')->isAllowed($aclPath) === $negativeCheck) {
                return false;
            } else {
                return true;
            }
        }

        return (bool)$default;
    }

    /**
     * Process the 'ifhelper' attributes to possibly disable a block/reference/action
     *
     * @param Mage_Core_Model_Layout_Element $node
     *
     * @return bool
     */
    protected function checkHelperConditional(Mage_Core_Model_Layout_Element $node)
    {
        if (isset($node['ifhelper']) && ($helperString = trim((string)$node['ifhelper']))) {
            $negativeCheck = (substr($helperString, 0, 1) === '!');
            $helperString = ($negativeCheck ? substr($helperString, 1) : $helperString);
            if ((bool)$this->getHelperMethodValue($helperString) === $negativeCheck) {
                return false;
            }
        }

        return true;
    }
}
