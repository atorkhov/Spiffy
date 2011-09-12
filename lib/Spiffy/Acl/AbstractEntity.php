<?php
namespace Spiffy\Acl;
use Spiffy\Doctrine\AbstractEntity as BaseEntity,
    Zend_Acl_Resource_Interface;

class AbstractEntity extends BaseEntity implements Zend_Acl_Resource_Interface
{
    /**
     * Resource id.
     * @var string
     */
    protected $_resourceId;
    
    /**
     * (non-PHPdoc)
     * @see Zend_Acl_Resource_Interface::getResourceId()
     */
    public function getResourceId()
    {
        if (!$this->_resourceId) {
            $metadata = $this->getClassMetadata();
            $idFields = $metadata->getIdentifierValues($this);
            
            $this->_resourceId = get_called_class();
            if (!empty($idFields)) {
                $this->_resourceId .= '.' . serialize($idFields);    
            }
        }
        return $this->_resourceId;
    }
}