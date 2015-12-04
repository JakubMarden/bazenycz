<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


class UserEditFormFactory extends Nette\Object
{
	/** @var Nette\Database\Context */
	private $database;
    
        public function __construct(\Nette\Database\Context $database)
        {
            $this->database = $database;
        }


	/**
	 * @return Form
	 */
	public function create($role)
	{
            $rights = $this->database->table('rights')->fetchPairs('id','name');

            $form = new Form;
            $form->addHidden('id');

            $form->addText('username', 'uživatelské jméno: ')
                 ->addRule(Form::FILLED, 'Zadejte prosím jméno.');  
            $form->addText('email', 'email: ')
                 ->addRule(Form::FILLED, 'Zadejte prosím email.')
                 ->addRule(Form::EMAIL, 'Email by měl mít platný formát');

            if(in_array("admin",$role)) { // editace prav jen adminem
                $form->addSelect('rights_id','level práv: ',$rights);            
            }

            $form->addSubmit('submit', 'uložit');
            $form->addProtection('Vypršel časový limit, odešlete formulář znovu');

            //bootstrap vzhled
            $renderer = $form->getRenderer();
            $renderer->wrappers['controls']['container'] = NULL;
            $renderer->wrappers['pair']['container'] = 'div class=form-group';
            $renderer->wrappers['pair']['.error'] = 'has-error';
            $renderer->wrappers['control']['container'] = '';
            $renderer->wrappers['label']['container'] = 'div class="control-label col-sm-5"';
            $renderer->wrappers['control']['description'] = 'span class=help-block';
            $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

            $form->getElementPrototype()->class('form-horizontal col-sm-12');
            return $form;
	}
}
