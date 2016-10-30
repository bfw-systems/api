<?php

//Create src/api directory
echo " >> \033[0;33m".'Create src/api directory ... '."\033[0m";

if (file_exists(BFW_PATH.'/src/api/')) {
    echo "\033[1;33mAlready exist.\033[0m";
    return;
}
    
if (mkdir(BFW_PATH.'/src/api/', 0755)) {
    echo "\033[1;32mCreated.\033[0m\n";
    return;
}

//If error during the directory creation
trigger_error(
    'Module '.$this->name.' install error : Fail to create /src/api/ directory',
    E_USER_WARNING
);
echo "\033[1;31mFail. \033[0m\n";
