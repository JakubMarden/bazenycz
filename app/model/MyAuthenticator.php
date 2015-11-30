<?php

use Nette\Security as NS;


class MyAuthenticator extends Nette\Object implements NS\IAuthenticator
{
	/** @var SignFormFactory @inject */
	private $db;
        private $table = "users";
               
   
        function __construct(Nette\Database\Context $database)
        {
            $this->db = $database;
        }

/**
     * Performs an authentication
     * @param  array
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
        
        public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials[0];

		$row = $this->db->table($this->table)->where('username', $username)->fetch();
                if($row){
                    $arr = $row->toArray();
                    if ($password !== $row->password) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
                    }
                }
		else {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

		}
		return new Nette\Security\Identity($row->id, $row->rights_id, $arr);
	}
       
}
