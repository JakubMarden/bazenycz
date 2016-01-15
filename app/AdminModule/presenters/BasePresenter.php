<?php
use Nette\Application\UI;
use Nette\Security as NS;


/**
 * Base presenter for all application presenters.
 */
namespace AdminModule;

abstract class BasePresenter extends \Nette\Application\UI\Presenter
{
    /** 
    * @var \Model\Admin 
    * @inject  
    */    
   public $admin;

    protected function startup() {
        parent::startup();
        if(!$this->user->isLoggedIn()){
            $this->redirect('Sign:in');
        }  
        else{
            $this->template->admin = in_array('admin', $this->user->roles);
            $this->template->userid = $this->user->id;
        }
        \Nette\Forms\Container::extensionMethod('addDatePicker', function (\Nette\Forms\Container $container, $name, $label = NULL) {
            return $container[$name] = new \JanTvrdik\Components\DatePicker($label);
        });
    }
}
