<?php
error_reporting( E_ALL | E_STRICT );

if( !file_exists( dirname( __DIR__ ) . '/composer.lock' ) )
{
    die( "Dependencies must be installed using composer:\n\nphp composer.phar install --dev\n\n"
        . "See http://getcomposer.org for help with installing composer\n" );
}
// Include the composer autoloader
$autoloader = require dirname( __DIR__ ) . '/vendor/autoload.php';