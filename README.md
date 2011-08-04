Introduction
============
The Spiffy framework is inteded to be used with Doctrine 2.x and Zend Framework 1.x to provide further
integration with the two libraries. Currently, the following features are supported:

*	Assisted form generation using Doctrine 2 annotations.
*	Zend Validate annotations directly in entities.
*	Zend Filter annotations directly in entities.
*   A domain model that can be used without Doctrine.

Following is a rundown of each class and the features it provides.

Spiffy\Domain\Model
-------------------
This class provides a base domain model with filter, validation, and helper methods.

* 	Additional features are lazy loaded and only initialized if used. This keeps things fast when necessary.
*   Filterable
*   Validatable
*   Includes a toArray() and fromArray() method.
*   Can be used with Spiffy\Form to provide model based filters/validators.

For more information see docs/model.md.

Spiffy\Entity
-------------
This class is intended to be used as a parent for all your Doctrine 2 entities. By extending this class
your entities have several new features:

*   Extends Spiffy\Domain\Model so all features from it are available.
*	Zend Validator annotations.
*	Zend Filter annotations.
*	Automatic validators include: Zend_Validate_StringLength for string fields and Zend_Validate_NotEmpty (required = true) for fields that are not nullable.
*	Automatic filters include: Zend_Filter_Int for bigint, smallint, and integer. Zend_Filter_Boolean for boolean. Zend_Filter_StringTrim for string.

For more information see docs/entity.md.

Spiffy\Form & Spiffy\Dojo\Form
------------------------------
This class provides additional form generation features by extending the Zend_Form base class. Spiffy\Dojo\Form
extends Zend_Dojo_Form to provide additional Dojo integration. Features for both classes include:

*	Integration with Doctrine 2 entities.
*	Integration with Spiffy\Domain\Model and Spiffy\Entity to set validators and filters via annotations.
*	Optional automatic persistance of Doctrine 2 entities when the form is valid.
*	Additional form elements for Entity, ComboBox, and FilteringSelect.

For more information see docs/form.md.

TODO
====
*	Bug fixes, the never-ending TODO.