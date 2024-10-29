<?php
/**
 * Class Lexique has different processing plugin
 * @category	Wordpress
 * @copyright	Copyright (c) 2013 Amine BETARI (email: amine.betari@gmail.com)
 * @version 1.0
 * @author Amine BETARI
 */
class Lexique
{
	private $postType = 'lexique_agency';
	private $colors = array();


    function __construct()
	{
		// Load files Init
		// wp_ajax_nopriv
		if(get_option('agency_ajax') == 'on') {
			add_action('init', array($this, 'add_enqueue'));
			//  Non-admin actions
			add_action("wp_ajax_nopriv_search_ajax", array($this, 'search_ajax') );
			add_action("wp_ajax_nopriv_searchMot_ajax", array($this, 'searchMot_ajax') );

			// Authenticated actions
			add_action("wp_ajax_search_ajax", array($this, 'search_ajax') );
			add_action("wp_ajax_searchMot_ajax", array($this, 'searchMot_ajax') );
		}
		// Load files of traduction
		add_action( 'plugins_loaded', array($this, 'lexiqueInit'));
		// Load file of colors
		$fp = fopen(PLUGIN_PATH.'/colors.txt','r');
		while(!feof($fp))
		{
			$ligne = fgets($fp);
			$dataColor = explode(':', $ligne);
			$this->colors[$dataColor[0]] = trim(str_replace("\n", "", $dataColor[1]));
		}
		fclose($fp);
		add_shortcode('LEXIQUE', array($this, 'lexiqueShortCode'));
		add_action( 'wp_enqueue_scripts', array($this, 'lexiqueDesign'));
		add_action( 'admin_menu', array($this, 'lexiqueSettingsMenu') );
		add_action( 'searchLettre', array($this, 'searchLettre'));
		add_action( 'searchMot', array($this, 'searchMot'));
	}


	/**
	 *
	 */
	function add_enqueue()
	{
		// Load js
		wp_enqueue_script('search_ajax_script', WP_PLUGIN_URL . '/lexiqueAgency/js/ajax_lexique.js', array( 'jquery') );
		wp_localize_script('search_ajax_script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
		wp_enqueue_script('search_ajax_script');
	}


	/**
	 * search ajax
	 * @param void
	 * @return HTML Result
	 */
	function search_ajax()
	{
		if($_REQUEST['lettre'] && $_REQUEST['action']) {
			$result = $this->searchLettreAjax($_REQUEST['lettre']);
			die($result);
		}
		else {
			die('KO');
		}
	}


	/**
	 * searchMot
	 * @param void
	 * @return HTML Result
	 */
	function searchMot_ajax()
	{
		if($_REQUEST['mot'] && $_REQUEST['action']) {
			$result = $this->searchMotAjax($_REQUEST['mot']);
			die($result);
		}
		else {
			die('KO');
		}
	}


	/**
	 * Add option : agency_lexique, once the plugin is installed
	 * @param  void
	 * @return void
	 */
	static function install()
	{
		update_option('agency_lexique', '2');
		update_option('agency_color', '#000000');
		update_option('agency_liste_letter', '#FFFFFF');
		update_option('agency_back_current_letter', '#FFFFFF');
		update_option('agency_ajax', 'off');
	}


	/**
	 * Load textdomain
	 * @param void
	 * @param void
	 */
	function lexiqueInit()
	{
		load_plugin_textdomain(LEXIQUE, false, LANGUAGE_PATH  );
	}


	/**
	 * Load files (js,css)
	 * @param  void
	 * @return void
	 */
	function lexiqueDesign()
	{
		wp_enqueue_style ( 'lexique',  PLUGIN_PATH_CSS);
		wp_enqueue_script( 'lexique', includes_url().'js/jquery/jquery.js');
		wp_enqueue_script( 'lexique', PLUGIN_PATH_JS);

	}


	/**
	 * Add Page Option
	 * @param  void
	 * @return void
	 */
	function lexiqueSettingsMenu()
	{
		add_options_page(__('Settings Agency Glossary', LEXIQUE), __('Glossary Option', LEXIQUE), 'manage_options', 'slug-agency-lexique', array($this, 'pageConfig'));
	}


	/**
	 * Page Config of Plugin : Modifiy filtre, Colours
	 * @param  void
	 * @return void
	 */
	function pageConfig()
	{
		$erreurs = false;
		if (isset($_POST["update_settings"]) && $_POST["update_settings"]!= '' ) {
			//SearchMot
			$searchMot = $_POST["searchmot"];
			if(filter_var($searchMot, FILTER_VALIDATE_INT) !== false) {
				update_option("agency_lexique", $searchMot);
			} else {
				$erreurs = true;
			}
			//ColorButton
			$colorButton = $_POST["agency_color"];
			update_option("agency_color", $colorButton);
			// ColorListeLetter
			$listeLetter = $_POST["agency_liste_letter"];
			update_option("agency_liste_letter", $listeLetter);
			// ColorCurrentLetter
			$currentLetter = $_POST["agency_back_current_letter"];
			update_option("agency_back_current_letter", $currentLetter);
			// Use Ajax
			$useAjax = $_POST['agency_ajax'];
			update_option("agency_ajax", $useAjax);
			// Tester s'il a'git d'une erreur
			if($erreurs) :
			?>
			<div id="message" class="error"><?php _e('error seizure', LEXIQUE) ?></div>
			<?php
			endif;
			if(!$erreurs):
			?>
			<div id="message" class="updated"><?php _e('Settings saved', LEXIQUE) ?></div>
			<?php
			endif;
		}
		?>
		<h2> <?php screen_icon('themes'); ?> <?php _e('Settings Agency Glossary', LEXIQUE) ?></h2>
		<form  method="POST" action="">
			<div>
				<input type="hidden" name="update_settings" value="Y" />
				<br />
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label><?php _e('Minimum number of letters to search', LEXIQUE)?></label></th>
							<td><input type="text" value="<?php echo esc_html( get_option('agency_lexique') )?>" size="1"  name="searchmot" /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Do you want to use the Ajax processing', LEXIQUE) ?></th>
							<td><input type="checkbox" name="agency_ajax" <?php if(get_option('agency_ajax') == 'on'): echo 'checked'; endif; ?>/></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Color button', LEXIQUE) ?></label></th>
							<td><select name="agency_color">
								<?php foreach($this->colors as $key => $color): ?>
									<?php
										if($key == 'White' || $color == '#FFFFFF') continue;
										$selected = get_option('agency_color');
										if($selected == $color):
											$selected = 'selected';
										else:
											$selected = '';
										endif;
										echo '<option style="color:'.$color.'" value="'.$color.'" '.$selected.'>'.$key.'</option>';
									?>
								<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Color Background list of letter', LEXIQUE) ?></label></th>
							<td>
							<select name="agency_liste_letter">
								<?php foreach($this->colors as $key => $color): ?>
									<?php
										$selected = get_option('agency_liste_letter');
										if($selected == $color):
											$selected = 'selected';
										else:
											$selected = '';
										endif;
										echo '<option style="color:'.$color.'" value="'.$color.'" '.$selected.'>'.$key.'</option>';
									?>
								<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Color Background current letter', LEXIQUE) ?></th>
							<td>
								<select name="agency_back_current_letter">
									<?php foreach($this->colors as $key => $color): ?>
										<?php
											$selected = get_option('agency_back_current_letter');
											if($selected == $color):
											$selected = 'selected';
											else:
												$selected = '';
											endif;
											echo '<option style="color:'.$color.'" value="'.$color.'" '.$selected.'>'.$key.'</option>';
										?>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</tbody>
				</table>
				<p class="submit">
					<input type="submit" value="<?php _e('Submit', LEXIQUE) ?>" class="button-primary" />
				</p>
			</div>
		</form>
		<?php
	}


	/**
	 * ShortCode of Plugin : that allows to search words
	 * @param array atts
	 * @return string Display content
	 */
	function lexiqueShortCode($atts)
	{
		//extract( shortcode_atts( array('id' => ''), $atts ) );
		// current URL
		$currentUri = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		$output	= '';
		$output .= '<div class="lexique_agency_search">';
				$output .= '<h3>'._e("Search word", LEXIQUE). '</h3>';
				$output .= '<form  action="" method="POST" id="agency_searchform">';
					$output .= '<div class="lexique_agency_form">';
						$output .= '<input type="text" name="lettre" id="agency_lettre" value="" />';
						$output .= '<input type="submit" name="btn-sbm" id="agency_search-sbm" value="Rechercher" style="background: '.get_option('agency_color').'" />';
						$output .= '<input type="hidden" name="agency_hidden_limit_lettre" id="agency_hidden_limit_lettre" value="'.get_option('agency_lexique').'" />';
						$output .= '<input type="hidden" name="agency_back_current_letter" id="agency_back_current_letter" value="'.get_option('agency_back_current_letter').'" />';
						$output .= '<span class="agency_error" style="display:none;">'.__('You must enter more letters', LEXIQUE).'</span>';
					$output .= '</div>';
				$output .= '</form>';
			$output .= '</div>';
			$lettres = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
			if(strstr($currentUri, '?', false) !== false) {
				$tag = '&';
			} else {
				$tag = '?';
			}
			$output .= '<div class="lexique_agency_list" style="background: '.get_option("agency_liste_letter").'">';
			$output .= '<ul>';
			$class = "";
			$currentUri = $this->getUrl($_SERVER["REQUEST_URI"]);

			$letterGet = '';
			if(!isset($_POST['lettre']) ) {
				$letterGet = isset($_GET['lettre']) ? $_GET['lettre'] : '';
			}
			foreach($lettres as $key => $lettre):
				if($letterGet == $lettre) $class = 'background: '.get_option('agency_back_current_letter').'; font-size: 25px;';
				else $class = '';
				$url = $currentUri.$tag;
				$output .= '<li><a href="'.$url.'lettre='.$lettre.'" style="'.$class.'" rel="'.$lettre.'" class="search_ajax">'.$lettre.'</a></li>';
				$url = '';
			endforeach;
			$output .= '</ul>';
			$output .= '</div>';
			$output .= '<div class="lexique_agency_result">';
				if(isset($_POST['lettre']) && isset($_POST['btn-sbm'])):
					unset($_GET['lettre']);
					$motRequest = trim($_POST['lettre']);
					$motRequest = htmlspecialchars($motRequest);
					// Mise en tampon
					ob_start();
					do_action('searchMot', $motRequest);
					$content = ob_get_contents();
					ob_end_clean();
					// Mise en tampon
					$output .= $content;
				elseif(isset($_GET['lettre']) && $_GET['lettre'] != ''):
					$lettresRequest = trim($_GET['lettre']);
					$lettresRequest = htmlspecialchars($lettresRequest);
					// Mise en tampon
					ob_start();
					do_action('searchLettre', $lettresRequest);
					$content = ob_get_contents();
					ob_end_clean();
					// Mise en tampon
					$output .= $content;
				else:
					$args = array('post_type' => 'lexique_agency',
							  'numberposts'=> '-1',
							  'post_status' => 'publish',
							  'orderby' => 'title',
							  'order' => 'ASC'
							  );
					$glossaries = get_posts( $args );
					$output .= '<div class="allresult">';
						foreach($glossaries as $mot):
							$output .=  '<span><strong><em>'.$mot->post_title.'</em></strong></span>';
							$output .= '<p>'.apply_filters('the_content', $mot->post_content).'</p>';
						endforeach;
					$output .= '</div>';
				endif;
			$output .= '</div>';
		return $output;
	}


	/**
	 * SearchLettre
	 * @param string $lettersRequest
	 * @return string display result of request
	 */
	function searchLettre($lettresRequest)
	{
		global $wpdb;
		$output = '';
		$postType = $this->postType;
		$prefix = $wpdb->get_blog_prefix();
		$query = 'select * from '.$prefix;
		$query .= 'posts';
		$query .= ' WHERE '.$prefix.'posts.post_type = "'.$postType.'" ';
		$query .= ' AND '.$prefix.'posts.post_status = "publish" ';
		$query .= ' AND '.$prefix.'posts.post_title like "'.$lettresRequest.'%"';
		// Executes the request
		$queryResult = $wpdb->get_results($query, OBJECT);
		if(count($queryResult) > 0) {
		$output .= '<div>';
			foreach($queryResult as $key => $mot):
				$output.= '<span><strong><em>'.$mot->post_title.'</em></strong></span>';
				$output.=  '<p>'.apply_filters('the_content', $mot->post_content).'</p>';
			endforeach;
		$output.= '</div>';
		} else {
			$output = '<span class="no_result"><strong>'.__('No result', LEXIQUE).'</strong></span>';
		}
 		echo $output;
	}


	/**
	 * searchLettreAjax
	 * @param string $lettresRequest
	 * @return string display resutl of request
	 */
	function searchLettreAjax($lettresRequest)
	{
		global $wpdb;
		$output = '';
		$postType = $this->postType;
		$prefix = $wpdb->get_blog_prefix();
		$query = 'select * from '.$prefix;
		$query .= 'posts';
		$query .= ' WHERE '.$prefix.'posts.post_type = "'.$postType.'" ';
		$query .= ' AND '.$prefix.'posts.post_status = "publish" ';
		$query .= ' AND '.$prefix.'posts.post_title like "'.$lettresRequest.'%"';
		// Executes the request
		$queryResult = $wpdb->get_results($query, OBJECT);
		if(count($queryResult) > 0) {
			$output .= '<div>';
				foreach($queryResult as $key => $mot):
					$output.= '<span><strong><em>'.$mot->post_title.'</em></strong></span>';
					$output.=  '<p>'.apply_filters('the_content', $mot->post_content).'</p>';
				endforeach;
			$output.= '</div>';
		} else {
			$output .= '<div>';
				$output = '<span class="no_result"><strong>'.__('No result', LEXIQUE).'</strong></span>';
			$output.= '</div>';
		}

 		return $output;
	}


	/**
	 * SearchWord
	 * @param string $searchMot
	 * @return string display result of request
	 */
	function searchMot($searchMot)
	{
		global $wpdb;
		$output = '';
		$postType = $this->postType;
		$prefix = $wpdb->get_blog_prefix();
		$query = 'select * from '.$prefix;
		$query .= 'posts';
		$query .= ' WHERE '.$prefix.'posts.post_type = "'.$postType.'" ';
		$query .= ' AND '.$prefix.'posts.post_status = "publish" ';
		$query .= ' AND '.$prefix.'posts.post_title like "%'.$searchMot.'%"';
		// Executes the request
		$queryResult = $wpdb->get_results($query, OBJECT);
		if(count($queryResult) > 0) {
		$output .= '<div>';
			foreach($queryResult as $key => $mot):
				$output.= '<span><strong><em>'.$mot->post_title.'</em></strong></span>';
				$output.=  '<p>'.apply_filters('the_content', $mot->post_content).'</p>';
			endforeach;
		$output.= '</div>';
		} else {

			$output = '<span class="no_result"><strong>'.__('No result', LEXIQUE).'</strong></span>';

		}
 		echo $output;
	}


	/**
	 * searchMotAjax
	 * @param string $searchMot
	 * @return string display result of request
	 */
	function searchMotAjax($searchMot)
	{
		global $wpdb;
		$output = '';
		$postType = $this->postType;
		$prefix = $wpdb->get_blog_prefix();
		$query = 'select * from '.$prefix;
		$query .= 'posts';
		$query .= ' WHERE '.$prefix.'posts.post_type = "'.$postType.'" ';
		$query .= ' AND '.$prefix.'posts.post_status = "publish" ';
		$query .= ' AND '.$prefix.'posts.post_title like "%'.$searchMot.'%"';
		// Executes the request
		$queryResult = $wpdb->get_results($query, OBJECT);
		if(count($queryResult) > 0) {
			$output .= '<div>';
				foreach($queryResult as $key => $mot):
					$output.= '<span><strong><em>'.$mot->post_title.'</em></strong></span>';
					$output.=  '<p>'.apply_filters('the_content', $mot->post_content).'</p>';
				endforeach;
			$output.= '</div>';

		} else {
			$output .= '<div>';
				$output = '<span class="no_result"><strong>'.__('No result', LEXIQUE).'</strong></span>';
			$output.= '</div>';
		}
		return $output;

	}


	/**
	 * Get URL
	 * @param string $currentUri
	 * @return string true URL
	 */
	function getUrl($currentUri)
	{
		$wrongAfter = substr($currentUri, 0, strpos($currentUri, '&'));
		if($wrongAfter == null) return  $_SERVER["REQUEST_URI"];
		return $wrongAfter;
	}
}