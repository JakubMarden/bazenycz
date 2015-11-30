<?php

namespace Model;


class Admin extends \Nette\Object
{

	/** @var \Nette\Database\Context */
	private $database;


	public function __construct(\Nette\Database\Context $database)
	{
		$this->database = $database;
	}
        
        public function updateRow($data, $table){
            $this->database->query("UPDATE $table SET ? WHERE id=?", $data, $data['id']);
        }
        
        public function insertRow($data, $table){
            $this->database->query("INSERT INTO $table", $data); 
        }
}
