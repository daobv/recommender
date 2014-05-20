<?php
class CategoryController extends AppController {
    public function index(){
        parent::init();
        $slug = $this->params['slug'];
        $this->set('slug', $slug);
    }
}