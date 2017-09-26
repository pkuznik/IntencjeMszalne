<?php
require_once __DIR__.'/IntencjaBean.inc';


/**
 * Created by PhpStorm.
 * @package          wp_parafia
 * @createdate       2017-04-24 07:33
 * @lastmodification 2017-04-24 07:33
 * @version          0.0.1
 * @author           Piotr Kuźnik <piotr.damian.kuznik@gmail.com>
 */
final class IntencjeMszalne {

	const POST_TYPE = 'intencja_mszalna';
	const NONCE_KEY = 'IM_VER_NONCE';

	/**
	 * @var string
	 */
	public static $FILE = __FILE__;

	/**
	 * @var IntencjeMszalne
	 */
	private static $instance;

	/**
	 * @var array
	 */
	protected $templates;

	/**
	 * @var string
	 */
	protected $templatesPath;

	/**
	 * IntencjeMszalne constructor.
	 *
	 */
	private function __construct() {
		self::$FILE = basename(__FILE__);
		add_action('init', [$this, 'registerPost']);
		add_filter('wp_insert_post_data', [$this, 'changePostName'], '99', 1);
		add_action("save_post", [$this, 'savePost']);

		if (version_compare(floatval(get_bloginfo('version')), '4.7', '<')) {
			// 4.6 and older
			add_filter('page_attributes_dropdown_pages_args', [$this, 'registerProjectTemplates']);
		} else {
			// Add a filter to the wp 4.7 version attributes metabox
			add_filter('theme_page_templates', [$this, 'addNewTemplate']);
		}
		$this->templates = [];

		add_filter('wp_insert_post_data', [$this, 'registerProjectTemplates']);
		add_filter('template_include', [$this, 'viewProjectTemplate']);
		add_filter('single_template', [$this, 'registerSinglePostTemplate'], 12);

		$this->templates = [
			'page-group-from-today.php' => 'Strona z intencjami (od dziś + 2 tyg)',
			'archive.php'    => 'Archiwum intencji',
		];

		$this->templatesPath = plugin_dir_path(__FILE__).'templates/';

		add_action('admin_menu', [$this, 'addMenu']);
	}

	private function __clone() {
	}

	/**
	 *
	 * @param bool $num
	 *
	 * @return array|bool|mixed
	 */
	public static function getRomanNumbers($num = FALSE) {
		$numbers = [
			1  => 'I',
			2  => 'II',
			3  => 'III',
			4  => 'IV',
			5  => 'V',
			6  => 'VI',
			7  => 'VII',
			8  => 'VIII',
			9  => 'IX',
			10 => 'X',
			11 => 'XI',
			12 => 'XII',
		];

		if ($num === FALSE) {
			return $numbers;
		}

		return (isset($numbers[ $num ])) ? $numbers[ $num ] : FALSE;
	}

	/**
	 *
	 * @param bool $num
	 *
	 * @return array|bool|mixed
	 */
	public static function getWeekName($num = false) {
		$week  = [
			0 => 'Niedziela',
			1 => 'Poniedziałek',
			2 => 'Wtorek',
			3 => 'Środa',
			4 => 'Czwartek',
			5 => 'Piątek',
			6 => 'Sobota',
		];
		if ($num === FALSE) {
		    return $week;
        }

        return (isset($week[$num])) ? $week[$num] : false;
    }
	/**
	 *
	 * @return IntencjeMszalne
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new IntencjeMszalne();
		}

		return self::$instance;
	}

	public function registerPost() {
		register_post_type(IntencjeMszalne::POST_TYPE, [
			'labels'               => [
				'name'          => __('Intencje mszalne'),
				'singular_name' => __('Intencja'),
			],
			'public'               => TRUE,
			'has_archive'          => TRUE,
			'rewrite'              => ['slug' => 'intencja', 'with_front' => FALSE],
			'exclude_from_search'  => FALSE,
			'menu_position'        => 6,
			'supports'             => FALSE,
			'register_meta_box_cb' => [$this,'printFormPost'],
		]);
	}

	public function printFormPost() {
		add_meta_box('box-'.IntencjeMszalne::POST_TYPE, 'Intencja Mszalna', function ($obj) {
			if (!is_admin()) {
				return;
			}
			wp_nonce_field(IntencjeMszalne::$FILE, IntencjeMszalne::NONCE_KEY);
			wp_register_script('jquery321', plugins_url('/js/jquery-3.2.1.min.js', __FILE__));
			wp_enqueue_script('jquery321');

			wp_register_script('jqueryUI', plugins_url('/js/jquery-ui.min.js', __FILE__));
			wp_enqueue_script('jqueryUI');

			wp_register_style('jqueryUI-style', plugins_url('/style/jquery-ui.css', __FILE__));
			wp_enqueue_style('jqueryUI-style');

			$requiredSymbol = '<b style="color: red;font-size: 1.2em;">*</b>';
			?>
            <div>
                <label for="im_date"><?php echo $requiredSymbol; ?>Data</label>
                <input id="im_date" type="text" name="im_date" required
                       value="<?php echo get_post_meta($obj->ID, "im_date", TRUE); ?>"/>
                <script>
                    jQuery(function () {
                        jQuery("#im_date").datepicker({
                            dateFormat: "yy-mm-dd"
                        });
                    });
                </script>

                <label for="im_hour"><?php echo $requiredSymbol; ?>Godzina</label>
                <input id="im_hour" name="im_hour" type="time" required
                       value="<?php echo get_post_meta($obj->ID, "im_hour", TRUE); ?>"/>

                <label for="im_patron">Patron dnia</label>
                <input id="im_patron" name="im_patron" type="text" style="width: 350px"
                       value="<?php echo get_post_meta($obj->ID, "im_patron", TRUE); ?>"/>
            </div>
            <div>&nbsp;</div>
			<?php for ($i = 1; $i <= 5; $i++) { ?>
                <div>
                    <label for="im_intencja_<?php echo $i; ?>"><?php echo ($i == 1) ? $requiredSymbol : '&nbsp;&nbsp;';
						?>Intencja <?php echo $i;
						?>:</label>
                    <input id="im_intencja_<?php echo $i; ?>" name="im_intencja_<?php echo $i; ?>"
						<?php echo ($i == 1) ? 'required' : '' ?>
                           type="text"
                           style="width: 400px"
                           value="<?php echo get_post_meta($obj->ID, "im_intencja_".$i, TRUE); ?>">
                </div>
				<?php
			}
		}, IntencjeMszalne::POST_TYPE, 'normal', 'high', NULL);
	}

	/**
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function savePost($post_id) {
		if (!isset($_POST[ IntencjeMszalne::NONCE_KEY ]) || !wp_verify_nonce($_POST[ IntencjeMszalne::NONCE_KEY ], IntencjeMszalne::$FILE)) {
			return $post_id;
		}

		if (!current_user_can("edit_post", $post_id)) {
			return $post_id;
		}

		if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
			return $post_id;
		}

		$slug = IntencjeMszalne::POST_TYPE;
		if ($slug != $_POST['post_type']) {
			return $post_id;
		}

		$keys = [
			'im_date',
			'im_hour',
			'im_patron',
		];
		for ($i = 1; $i <= 5; $i++) {
			$keys[] = 'im_intencja_'.$i;
		}

		foreach ($keys as $key) {
			$value = '';
			if (isset($_POST[ $key ])) {
				$value = $_POST[ $key ];
			}
			update_post_meta($post_id, $key, $value);
		}

	}

	/**
	 *
	 * @param array $data
	 *
	 * @return mixed
	 */
	public function changePostName($data) {

		if ($data['post_type'] == IntencjeMszalne::POST_TYPE) {
			if (isset($_POST['im_date']) && isset($_POST['im_hour'])) {
				$time = $_POST['im_date'].' '.$_POST['im_hour'];
			} else {
				$time = date('Y-m-d H:i:s');
			}
			
			$title = date('YmdHis', strtotime($time));
			if (!isset($data['post_name'])) {
				$data['post_name']  = $title;
			}
			if (!isset($data['post_title'])) {
				$data['post_title'] = $title;
            }
			
		}

		return $data;
	}

	/**
	 *
	 * @return null|string
	 */
	public function registerSinglePostTemplate() {
		$template = NULL;
		global $wp_query;
		/** @var WP_Post $post */
		if (!is_null($wp_query)) {
			$post = $wp_query->get_queried_object();
			if ($post->post_type == IntencjeMszalne::POST_TYPE) {
				$template = $this->templatesPath.'page-single.php';
			}
		}

		return $template;
	}

	/**
	 *
	 * @param array $posts_templates
	 *
	 * @return array
	 */
	public function addNewTemplate($posts_templates) {
		$posts_templates = array_merge($posts_templates, $this->templates);
		return $posts_templates;
	}

	/**
	 *
	 * @param $atts
	 *
	 * @return mixed
	 */
	public function registerProjectTemplates($atts) {
		$cache_key = 'page_templates-'.md5(get_theme_root().'/'.get_stylesheet());
		$templates = wp_get_theme()->get_page_templates();
		if (empty($templates)) {
			$templates = [];
		}

		wp_cache_delete($cache_key, 'themes');

		$templates = array_merge($templates, $this->templates);

		wp_cache_add($cache_key, $templates, 'themes', 1800);

		return $atts;
	}

	/**
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public function viewProjectTemplate($template) {
		global $post;

		if (!$post) {
			return $template;
		}

		if (!isset($this->templates[ get_post_meta($post->ID, '_wp_page_template', TRUE) ])) {
			return $template;
		}

		$file = $this->templatesPath.get_post_meta($post->ID, '_wp_page_template', TRUE);

		if (file_exists($file)) {
			return $file;
		} else {
			echo $file;
		}

		return $template;
	}

	public function addMenu() {
		add_menu_page('Załaduj intencje z pliku',     // page title
			'Załaduj intencje z pliku',     // menu title
			'manage_options',   // capability
			'read-data-from-file',     // menu slug
			[$this, 'createPageLoadDataFromFile'] // callback function
		);
	}
	
	public function createPageLoadDataFromFile() {
		
		if (isset($_POST['intencje_upload_nonce'], $_POST['post_id']) && wp_verify_nonce($_POST['intencje_upload_nonce'], 'intencje_upload') && $_POST['post_id'] == 0) {
			// The nonce was valid and the user has the capabilities, it is safe to continue.
			
			// These files need to be included as dependencies when on the front end.
			require_once(ABSPATH.'wp-admin/includes/image.php');
			require_once(ABSPATH.'wp-admin/includes/file.php');
			require_once(ABSPATH.'wp-admin/includes/media.php');
			
			// Let WordPress handle the upload.
			// Remember, 'intencje_upload' is the name of our file input in our form above.
			$attachment_id = media_handle_upload('intencje_upload', 0);
			
			echo "<pre>";
			if (is_wp_error($attachment_id)) {
				echo "\nBłąd podczas przesłania!";
			} else {
				echo "\nPlik został przesłany!";
				
				$url = get_attached_file($attachment_id);
				
				echo "\nPobieram dane z pliku.. \n$url\n";
				
				
				$text = FALSE;
				
				if (preg_match('/\.docx$/', $url)) {
					$text = $this->read_docx($url);
				} elseif (preg_match('/\.doc$/', $url)) {
					$text = $this->read_doc($url);
				}
				
				
				$lines = explode(PHP_EOL, $text);
				$n     = 0;
				
				$bean     = FALSE;
				$date     = NULL;
				$hour     = NULL;
				$intencje = [];
				$patron   = NULL;
				
				$multi_intencja = FALSE;
				
				if (is_array($lines)) {
					foreach ($lines as $line) {
						$n++;
						
						if (preg_match('/^[a-zA-ZłŁśŚąĄ]* [0-9]{2}\.[IVX0-9]{1,4}\.[0-9]{4}r\./', $line, $matches)) {
							$date   = str_replace(['r.', '.'], ['', '-'], explode(' ', $matches[0])[1]);
							$patron = trim(str_replace($matches[0], '', $line));
						} elseif (preg_match('/^[0-9]{1,2}:[0-9]{2}-/', $line, $matches)) {
							if ($bean) {
								for ($i = 0; $i < count($intencje); $i++) {
									$bean->set('intencja_'.($i + 1), $intencje[ $i ]);
								}
								$intencje       = [];
								$multi_intencja = FALSE;
								$bean->save();
								$bean = false;
							}
							$hour = trim(str_replace('-', '', $matches[0]));
							
							$bean = IntencjaBean::getByChangeID(FALSE, $date, $hour);
							$bean->set('patron', $patron);
							
							$tmp_intencja = trim(str_replace($matches[0], '', $line));
							if (preg_match('/^I./', $tmp_intencja, $matches)) {
								$multi_intencja = TRUE;
								
								$tmp_intencja = str_replace($matches[0], '', $tmp_intencja);
							}
							$intencje[] = $tmp_intencja;
						} elseif ($multi_intencja) {
							$line       = trim($line);
							$intencje[] = trim(preg_replace('/^[IVX]{1,3}./', '', $line));
						} elseif (preg_match('/^(Chrzest|Roczki|Po Mszy św.|Po Mszy|Chrzty:)/', trim($line))) {
							$il = count($intencje) - 1;
							$intencje[$il] .= '</br>'.trim($line);
						} elseif(empty(trim($line))) {
							//nic do zrobienia
						} else {
							$line = trim($line);
							echo "\nNie znaleziono schematu dla następującej lini:";
							echo "<b>$line</b>\n";
						}
					}
					if ($bean) {
						for ($i = 0; $i < count($intencje); $i++) {
							$bean->set('intencja_'.($i + 1), $intencje[ $i ]);
						}
						$bean->save();
					}
					
				}
				
			}
			echo "\nDane zostały zapisane do bazy!";
			echo "</pre>";
		}
		
		?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Intencje mszalne</h1>

            <hr class="wp-header-end">


            <p>Załaduj intencje z pliku *.docx</p>
            <form id="featured_upload" method="post" action="#" enctype="multipart/form-data">
                <input type="file" name="intencje_upload" id="intencje_upload" multiple="false"/>
                <input type="hidden" name="post_id" id="post_id" value="0"/>
				<?php wp_nonce_field('intencje_upload', 'intencje_upload_nonce'); ?>
                <input id="submit_intencje_upload" name="Załaduj" type="submit" value="Upload"/>
            </form>

            <div id="ajax-response"></div>
            <br class="clear">
        </div>
		<?php
	}
	
	private function read_docx($filename) {
		
		$striped_content = '';
		$content         = '';
		
		$zip = zip_open($filename);
		
		if (!$zip || is_numeric($zip)) {
			return FALSE;
		}
		
		while ($zip_entry = zip_read($zip)) {
			
			if (zip_entry_open($zip, $zip_entry) == FALSE) {
				continue;
			}
			
			if (zip_entry_name($zip_entry) != "word/document.xml") {
				continue;
			}
			
			$content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
			
			zip_entry_close($zip_entry);
		}// end while
		
		zip_close($zip);
		
		$content         = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
		$content         = str_replace('</w:r></w:p>', "\r\n", $content);
		$striped_content = strip_tags($content);
		
		return $striped_content;
	}
	
	private function read_doc($filename) {
		$fileHandle = fopen($filename, "r");
		$line       = @fread($fileHandle, filesize($filename));
		$lines      = explode(chr(0x0D), $line);
		$outtext    = "";
		foreach ($lines as $thisline) {
			$pos = strpos($thisline, chr(0x00));
			if (($pos !== FALSE) || (strlen($thisline) == 0)) {
			} else {
				$outtext .= $thisline." ";
			}
		}
		$outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/", "", $outtext);
		
		return $outtext;
	}
	
}

/*
Plugin Name: Intencje Mszalne
Plugin URI:
Description: Szybka wtyczka do pokazywania intencji mszalnych
Version: 0.3
Author: Piotr Kuźnik <piotr.damian.kuznik@gmail.com>
Author URI: https://github.com/pkuznik/
Text Domain: intencje mszalne
License: GPLv3

	Restrict User Access for WordPress
	Copyright (C) 2017 Piotr Kuźnik <piotr.damian.kuznik@gmail.com>

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!defined('ABSPATH')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit;
}
define('PKIM_PATH', plugin_dir_path( __FILE__ ));
add_action( 'plugins_loaded', array( 'IntencjeMszalne', 'getInstance' ) );
