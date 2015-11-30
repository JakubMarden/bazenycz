<?php

namespace App\FrontendModule\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;


class HomepagePresenter extends BasePresenter
{
/** @var Nette\Database\Context */
    private $database;

        public function __construct(Nette\Database\Context $database)
        {
            $this->database = $database;
        }
        
	public function renderDefault()
	{
		//$this->template->anyVariable = 'any value';
	}
        
        
        
        
        
        
        
        
        /*public function createComponentMessageForm() 
        {
            $form = new Form;
            $form->addText('name','Jméno*:')
            ->setRequired();
            $form->addText('email','E-mail*:')
            ->setRequired();
            $form->addText('phone', 'Telefon:');
            $form->addTextArea('message','Zpráva*:')
                    ->setRequired();
            $form->addSubmit('send', 'Odeslat');
            $form->onSuccess[] = array($this, 'MessageFormSucceeded');
            return $form;
        }
        
        public function commentMessageFormSucceeded($form, $values)
        {
            $postId = $this->getParameter('postId');

            $this->database->table('messages')->insert(array(
                'post_id' => $postId,
                'name' => $values->name,
                'email' => $values->email,
                'phone' => $values->phone,
                'message' => $values->message,
            ));

            $this->flashMessage('Děkujeme za vyplnění', 'success');
            $this->redirect('this');
        }*/

}
