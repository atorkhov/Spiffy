Introduction
============
The Spiffy library is inteded to be used with Doctrine 2.x and Zend Framework 1.x to provide further
integration with the two libraries. Currently, the following features are supported:

*	Assisted form generation using Doctrine 2 annotations.
*	Zend Validate annotations directly in entities.
*	Zend Filter annotations directly in entities.

Following is a rundown of each class and the features it provides.

Spiffy\Entity
-------------
This class is intended to be used as a parent for all your Doctrine 2 entities. By extending this class
your entities have several new features:

*	Zend Validator annotations.
*	Zend Filter annotations.
*	isValid() method callable on the entity itself.

Spiffy\Form & Spiffy\Dojo\Form
------------------------------
This class provides additional form generation features by extending the Zend_Form base class. Spiffy\Dojo\Form
extends Zend_Dojo_Form to provide additional Dojo integration. Features for both classes include:

*	Integration with Doctrine 2 entities.
*	Integration with Spiffy\Entity to set validators and filters via annotations.
*	Automatic persistance of Doctrine 2 entities when the form is valid.
*	Additional form elements for Entity, EntityComboBox, and EntityFilteringSelect.
*	Automatic validators include: Zend_Validate_StringLength for string fields and Zend_Validate_NotEmpty (required = true) for fields that are not nullable.
*	Automatic filters include: Zend_Filter_Int for bigint, smallint, and integer. Zend_Filter_Boolean for boolean. Zend_Filter_StringTrim for string.

Installing
=============

Configuration
-------------

1. [Download or clone the source] (https://github.com/spiffyjr/spiffy) into the appropriate include path.
2. Add the autoloader namespace to application.ini
		
		// application/configs/application.ini
		autoloaderNamespaces[] = Spiffy
		autoloaderNamespaces[] = Spiffy_
		
3. Add the pluginPaths to application.ini

		// application/configs/application.ini
		pluginPaths.Spiffy_Application_Resource = "Spiffy/Application/Resource"
		
4. Add the annotations to Doctrine in your bootstrap.

		// application/Bootstrap.php
		public function _initDoctrineAutoloaderNamespace() {
			\Doctrine\Common\Annotations\AnnotationRegistry::registerFile("Spiffy/Doctrine/Annotations/Filters/Filter.php");
			\Doctrine\Common\Annotations\AnnotationRegistry::registerFile("Spiffy/Doctrine/Annotations/Validators/Validator.php");
		}
		
5. Set the default EntityManager.

		// application/Bootstrap.php
		public function _initSpiffyEntityManager() {
			\Spiffy\Form::setDefaultEntityManager($em);
		}
		
Creating an entity
------------------

	namespace My\Entity;
	use Doctrine\ORM\Mapping as ORM;
	use Doctrine\Common\Collections\ArrayCollection;
	use Spiffy\Doctrine\Annotations\Filters as Filter;
	use Spiffy\Doctrine\Annotations\Validators as Assert;
	use Spiffy\Entity;

	/**
	 * My\Entity\User
	 *
	 * @ORM\Table(name="user")
	 * @ORM\Entity
	 */
	class User extends Entity
	{
		/**
		 * @var integer $id
		 *
		 * @ORM\Column(name="id", type="integer")
		 * @ORM\Id
		 * @ORM\GeneratedValue(strategy="AUTO")
		 */
		private $id;

		/**
		 * @var string $username
		 * 
		 * @ORM\Column(type="string",length=15)
		 */
		private $username;

		/**
		 * @var string $email
		 *
		 * @Assert\EmailAddress
		 * @ORM\Column(type="string")
		 */
		private $email;

		/**
		 * @var string $password
		 *
		 * @ORM\Column(type="string")
		 */
		private $password;
	}
		
Creating a form
---------------
	namespace My\Forms;
	use Spiffy\Dojo\Form;
	use Doctrine\ORM\EntityRepository;

	class Register extends Form
	{
		/**
		 * (non-PHPdoc)
		 * @see Zend_Form::init()
		 */
		public function init() {
			$this->setName('register');
			$this->add('username');
			$this->add('email');
			$this->add('password', 'PasswordTextBox');
			$this->add('confirm', 'PasswordTextBox', array('label' => 'Confirm'));
			$this->add('submit', 'SubmitButton', array('label' => 'Register'));
		}

		/**
		 * (non-PHPdoc)
		 * @see Spiffy.Form::getDefaultOptions()
		 */
		public function getDefaultOptions() {
			return array('entity' => 'My\Entity\User');
		}
	}
		
Creating a controller
---------------------
	use My\Forms\Register;

	class RegisterController extends Zend_Controller_Action
	{
		public function indexAction() {
			$form = new Register();
			$request = $this->getRequest();

			if ($request->isPost()) {
				if ($form->isValid($request->getPost())) {
					$entity = $form->getEntity(); // instance of My\Entity\User
					
					// entity is automatically persisted unless automaticPersisting is disabled
					// during class initialization or via getDefaultOptions().
				}
			}

			$this->view->form = $form;
		}
	}