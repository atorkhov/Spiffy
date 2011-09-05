<?php
namespace Spiffy\Zend\View\Helper;
use Zend_Form,
    Zend_View_Helper_Abstract;

class RenderForm extends Zend_View_Helper_Abstract
{
    /**
     * Renders a form with error messages.
     * 
     * @param Zend_Form $form
     * @return string
     */
    public function renderForm(Zend_Form $form)
    {
        $output = '';
        if ($form->getErrorMessages()) {
            $output .= $this->view->formErrors($form->getErrorMessages());
        }
        return $output .= $form->render();
    }
}