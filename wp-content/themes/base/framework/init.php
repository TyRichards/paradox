<?php
/*-----------------------------------------------------------------------------------*/
/*
/*	ThemeZilla Framework
/*
/*-----------------------------------------------------------------------------------*/


/*-----------------------------------------------------------------------------------*/
/*	Define Constants
/*-----------------------------------------------------------------------------------*/

define('ZILLA_DIR', get_template_directory() .'/framework');
define('ZILLA_URL', get_template_directory_uri() .'/framework');
define('ZILLA_FRAMEWORK_VERSION', '1.1');
define('ZILLA_UPDATE_URL', 'http://updates.themezilla.com');
define('ZILLA_DEBUG', false);


/*-----------------------------------------------------------------------------------*/
/*	Load Framework Components
/*-----------------------------------------------------------------------------------*/

require_once ZILLA_DIR .'/zilla-admin-functions.php';
require_once ZILLA_DIR .'/zilla-admin-init.php';
require_once ZILLA_DIR .'/zilla-admin-metaboxes.php';
require_once ZILLA_DIR .'/zilla-admin-page-support.php';
require_once ZILLA_DIR .'/zilla-theme-functions.php';

?>