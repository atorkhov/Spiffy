Spiffy\Doctrine\AbstractEntity
==============================
This class is intended to be used as a parent for all your Doctrine 2 entities. By extending this class
your entities have several new features.

Examples
--------
        namespace My;
        use Spiffy\Doctrine\AbstractEntity,
            Spiffy\Doctrine\Annotations\Filters as Filter,
            Spiffy\Doctrine\Annotations\Validators as Assert;
        
        /**
         * @Entity
         */
        class User extends AbstractEntity 
        {
            /**
             * @ORM\Id
             * @ORM\Column(type="integer")
             * @ORM\GeneratedValue(strategy="AUTO")
             */
            public $id;
            
           /**
            * @Assert\EmailAddress
            * @Filter\StringTrim
            * @ORM\Column(type="string",length="75")
            */
            public $email;
        }
        
        $user = new \My\User;
        $user->email = 'test';
        
        var_dump($user->isValid()); 
        
        // bool(false)
        
        $fi = $user->getFilterInput();
        var_dump($fi->getMessages());
        
        // array(1) { ["email"]=> array(1) { ["emailAddressInvalidFormat"]=> string(72) "'test' is no valid email address in the basic format local-part@hostname" } }
        
        $user->email = 'test@test.com';
        
        var_dump($user->isValid());
        
        // bool(true)
        
        $user->email = '     test     '; // add spaces to check filtering
        
        var_dump($user->toArray());
        
        // array(2) { ["id"]=> string(1) "0" ["email"]=> string(14) " test " }
        
        var_dump($user->toArray(false)); // filtering disabled
        
        // array(2) { ["id"]=> NULL ["email"]=> string(14) " test " }
        
        $user->fromArray(array('email' => 'asdf@asdf.com'));