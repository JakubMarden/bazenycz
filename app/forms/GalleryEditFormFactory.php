<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;


class GalleryEditFormFactory extends Nette\Object
{
	/**
	 * @return Form
	 */
	public function create()
	{
            
            $boolean = array(0 => 'ne', 1 => 'ano');
            
            $form = new Form;
            $form->addHidden('id');
            $form->addDatePicker('date', 'datum: ')
                    ->setAttribute('class', 'datepicker');                  ;
            $form->addText('name', 'nadpis: ',100)
                 ->addRule(Form::FILLED, 'Zadejte prosím nadpis.');  
            
            $form->addTextArea('description', 'krátký popis: ',105);
            $form->addSelect('active', 'zobrazit: ', $boolean)->setDefaultValue(1);
            $form->addSubmit('submit', 'uložit');
            $form->addProtection('Vypršel časový limit, odešlete formulář znovu');

            //bootstrap vzhled
            $renderer = $form->getRenderer();
            $renderer->wrappers['controls']['container'] = NULL;
            $renderer->wrappers['pair']['container'] = 'div class=form-group';
            $renderer->wrappers['pair']['.error'] = 'has-error';
            $renderer->wrappers['control']['container'] = '';
            $renderer->wrappers['label']['container'] = 'div class=custom-label';
            $renderer->wrappers['control']['description'] = 'span class=help-block';
            $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

            $form->getElementPrototype()->class('form-horizontal col-sm-12');
            return $form;
	}
}
