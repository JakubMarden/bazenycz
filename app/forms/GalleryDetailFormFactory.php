<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;


class GalleryDetailFormFactory extends Nette\Object
{
	/**
	 * @return Form
	 */
	public function create()
	{           
            $form = new Form;
            $form->getElementPrototype()->enctype = 'multipart/form-data';
            $form->addHidden('galeries_id');
            $form->addUpload('files', '', true)
                 ->setAttribute('id', "files")
                 ->setAttribute('class', "btn btn-primary");
            $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
            $form->addSubmit('submit', 'uložit')
                 ->setAttribute('class', "btn btn-primary btn-md")
                 ->setAttribute('id', "submit_files");


            //bootstrap vzhled
            $renderer = $form->getRenderer();
            $renderer->wrappers['controls']['container'] = 'well well-sm';
            $renderer->wrappers['pair']['container'] = '';
            $renderer->wrappers['pair']['.error'] = 'has-error';
            $renderer->wrappers['control']['container'] = '';
            $renderer->wrappers['label']['container'] = 'div class=custom-label';
            $renderer->wrappers['control']['description'] = 'span class=help-block';
            $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

            $form->getElementPrototype()->class('form-horizontal col-sm-12');
        
            return $form;
	}
}
