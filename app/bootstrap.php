<?php
  // require_once 'libraries/Core.php';
  // require_once 'libraries/Controller.php';

  // Load Config
  require_once 'config/config.php';

//vendor composer
require_once HOMEROOT."/vendor/autoload.php";

  // Load Helpers
  require_once 'helpers/general_helper.php';
  require_once 'helpers/user_helper.php';
  require_once 'helpers/form_helper.php';
  require_once 'helpers/controller_helper.php';
  require_once 'helpers/table_helper.php';
  require_once 'helpers/mail_helper.php';


  // Autoload Core Classes
  spl_autoload_register(function ($className) {
    // dd($className);
    $exclude = array("PHPMailer");
      if(!in_array($className, $exclude)) {
        require_once 'libraries/'. $className . '.php';  
      }
  });
