Spiffy\Form & Spiffy\Dojo\Form
==============================
This class provides additional form generation features by extending the Zend_Form base class. Spiffy\Dojo\Form
extends Zend_Dojo_Form to provide additional Dojo integration. Features for both classes include:

Examples
--------
        use Spiffy\Zend\Dojo\Form;
        
        class TestForm extends Form 
        {
            public function init()
            {
                // That's all there is to it - element, label, required, etc are all set from properties
                // obtained from the entity. This would create a ValidationTextBox with the appropriate data.
                $this->add('email');
        
                // This would create a drop-down with the user entities being selectable. You must implement
                // __toString() or give the 'property' option in order for the form to know what to display.
                
                // The reason we specify the class here is because there is no field called "test" in the
                // default entity set by getDefaultOptions() so the Entity form element must be given a class
                // in order to retrieve the metadata information to render the element.        
                $this->add('test', 'Entity', array(
                    'class' => 'User',
                    
                    // you can give custom query builders to each element
                    'queryBuilder' => function($er) {
                        $qb = $er->createQueryBuilder('u');
                        return $qb->orderBy('u.email');
                    }
                );
            }
        
            // This function tells the form what the default entity is for all field elements.
            public function getDefaultOptions() 
            {
                return array('entity' => 'User');
            }
        }