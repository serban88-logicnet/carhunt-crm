<?php
  /* 
   *  CORE CONTROLLER CLASS
   *  Loads Models & Views
   */
  class Controller {

  	private array $attributes = [];

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    // Lets us load model from controllers
  	public function model($model){
      // Require model file
  		require_once APPROOT.'/models/' . $model . '.php';
      // Instantiate model
  		return new $model();
  	}

    // Lets us load view from controllers
  	public function view($url, $data = []){
      // Check for view file
  		if(file_exists('../app/views/'.$url.'.php')){
        // Require view file
  			require_once '../app/views/'.$url.'.php';
  		} else {
        // No view exists
  			die('View does not exist');
  		}
  	}
  }