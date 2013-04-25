<?php

$root = Bundle::path('mimeil');

Autoloader::map(array(
  'MiMeil'                => $root.'mimeil.php',
  'LaMeil'                => $root.'lameil.php',
));
