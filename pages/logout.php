<?php

session_start();
session_destroy();
header('Location: index.php?lang='.$_GET["lang"]);
die();


