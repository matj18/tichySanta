<?php //kod z cviceni
  /** @var \PDO $db - připojení k databázi */
  $db = new PDO('mysql:host=127.0.0.1;dbname=matj18;charset=utf8', 'matj18', 'vi4pheihaa7quohsh7');

  //při chybě v SQL chceme vyhodit Exception
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);