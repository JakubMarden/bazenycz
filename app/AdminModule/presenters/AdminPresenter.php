<?php

namespace App\AdminModule\Presenters;

/**
 * Description of AdminPresenter
 *
 * @author Marden
 */
class AdminPresenter extends \AdminModule\BasePresenter {
    
    public function renderDefault(){
    }
    
    public function actionLogout(){
        $this->user->logout();
        $this->redirect('Sign:in');
    }
}
