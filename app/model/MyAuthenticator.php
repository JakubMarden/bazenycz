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
                $rights = $this->db->table('rights')->fetchPairs('id','name');
                
		$row = $this->db->table($this->table)->where('username', $username)->where('active',1)->fetch();
                if($row){
                    $arr = $row->toArray();
                    $password_is_correct = password_verify($password, $row->password);
                    if ($password_is_correct !== true) {
			throw new NS\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
                    }
                }
		else {
			throw new NS\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

		}
		return new Nette\Security\Identity($row->id, $rights[$row->rights_id], $arr);
	}      
}
