<?php

// Show all information, defaults to INFO_ALL
phpinfo();

// Show just the module information.
// phpinfo(8) yields identical results.
phpinfo(INFO_MODULES);

if (require 'PEAR.php') {
    echo "You can now use PEAR with PHP!";
} else {
    echo "PEAR is not recognized with your PHP.";
}

?>