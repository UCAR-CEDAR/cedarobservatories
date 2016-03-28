<?php
# Alert the user that this is not a valid access point to MediaWiki if they
# try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
        echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/cedarobservatories/cedarobservatories.php" );
EOT;
        exit( 1 );
}
 
$wgExtensionCredits[ 'specialpage' ][] = array(
        'path' => __FILE__,
        'name' => 'CedarObservatories',
        'author' => 'Patrick West',
        'url' => 'http://cedarweb.hao.ucar.edu/cedaradmin/index.php/Extensions:cedarobservatories',
        'descriptionmsg' => 'cedarobservatories-desc',
        'version' => '1.0.1',
);
 
$wgAutoloadClasses[ 'CedarObservatories' ] = __DIR__ .  '/CedarObservatories_body.php'; # Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
$wgExtensionMessagesFiles[ 'CedarObservatories' ] = __DIR__ .  '/CedarObservatories.i18n.php'; # Location of a messages file (Tell MediaWiki to load this file)
$wgSpecialPages[ 'CedarObservatories' ] = 'CedarObservatories'; # Tell MediaWiki about the new special page and its class name
$wgGroupPermissions['sysop']['cedar_admin'] = true;

