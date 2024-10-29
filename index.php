<?php
/**
 * page index of plugin 'LexiqueAgency'
 * @category Wordpress
 * @copyright	Copyright (c) 2013 Amine BETARI (email: amine.betari@gmail.com)
 * @version 1.0
 * @author Amine BETARI
 */
/*
Plugin Name: LexiqueAgency
Description: Add a new custom post type (lexique_agency), a LEXIQUE shortcode in a page is added to the implement the search words by letters or by word
Version: 1.0
Author: Amine BETARI
Author URI: http://www.abetari.com
License: GPL2
*/
// Different Constante of Plugin 'LexiqueAgency'
define('PLUGIN_PATH', dirname(__FILE__));
define('PLUGIN_PATH_CSS', plugins_url('css/lexique.css', __FILE__));
define('PLUGIN_PATH_JS', plugins_url('js/lexique.js', __FILE__));
define('ICONE', plugins_url('icon_lexique.png', __FILE__));
define('LANGUAGE_PATH', dirname( plugin_basename( __FILE__ ) ) .'/languages/' );
// TextDomain for tradution
define('LEXIQUE', 'agency-lexique');

// Files Required
require_once  PLUGIN_PATH.'/classes/post_type.php';
require_once  PLUGIN_PATH.'/classes/lexique.php';


// Activation plugin
//register_activation_hook( __FILE__, array( 'Lexique', 'install' ) );

// New Custom Post Type : lexique
$postType = 'lexique_agency';
new wordpress_custom_post_type($postType,
				   array('singular' => 'Lexique',
                         'plural' => 'Lexiques',
                         'slug' => $postType,
                         'menu_icon' => ICONE,
                         'args' => array('supports' => array('title', 'editor', 'excerpt', 'thumbnail'))
                         ));

// Initialisation Lexique
$lexique = new Lexique();
?>