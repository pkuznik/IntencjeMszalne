<?php


/**
 * Description of IntencjaBean
 *
 *
 * Created by PhpStorm.
 * @package          Wordpress
 * @tracefrom        IntencjaBean.inc
 * @createdate       2017-08-15 11:17
 * @lastmodification 2017-08-15 11:17
 * @version          0.0.1
 * @author           Piotr Kuźnik <pkuznik@edokumenty.eu>
 * @copyright (c)    eDokumenty Sp. z o.o.
 */
final class IntencjaBean {
	/**
	 * @var array
	 */
	private $data = [];
	
	/**
	 * @var string
	 */
	private $title;
	
	/**
	 * @var bool|integer
	 */
	private $ID = FALSE;
	
	/**
	 * @var array
	 */
	private static $keys = [
		'date', 'hour', 'patron',
	    'intencja_1',
	    'intencja_2',
	    'intencja_3',
		'intencja_4',
		'intencja_5',
	];
	
	/**
	 * ChangeLog constructor.
	 *
	 */
	private function __construct($ID, $title) {
		$this->ID = $ID;
		$this->title = $title;
	}
	
	/**
	 * getByChangeID
	 *
	 * @param bool $datetime
	 * @param null $date
	 * @param null $hour
	 *
	 * @return \IntencjaBean
	 */
	public static function getByChangeID($datetime = false, $date = null, $hour = null) {
		if (!$datetime) {
			$datetime = date('YmdHis',strtotime("$date $hour"));
		}
		$obj = new IntencjaBean(FALSE, $datetime);
		
		$post = get_page_by_title($datetime, OBJECT, IntencjeMszalne::POST_TYPE);
		if (is_a($post, 'WP_Post') && $post->post_type == IntencjeMszalne::POST_TYPE) {
			$obj = new IntencjaBean($post->ID, $datetime);
			
			foreach (self::$keys as $key) {
				$obj->set($key, get_post_meta($post->ID, 'im_'.$key, TRUE));
			}
		} else {
			$obj->set('date', date('Y-m-d',strtotime("$date")));
			$obj->set('hour', date('H:i',strtotime("$hour")));
		}
		unset($post);
		
		return $obj;
	}
	
	
	/**
	 *
	 * @param string $k {
	 *
	 * @type string  $date
	 * @type string  $hour
	 * @type string  $patron
	 * @type string  $intencja_1
	 * @type string  $intencja_2
	 * @type string  $intencja_3
	 * @type string  $intencja_4
	 * @type string  $intencja_5
	 *
	 * }
	 * @param mixed  $v
	 */
	public function set($k, $v) {
		if (in_array($k, self::$keys)) {
			$this->data[ $k ] = $v;
		}
	}
	
	
	/**
	 *
	 * @param string $k {
	 *
	 * @type string  $date
	 * @type string  $hour
	 * @type string  $patron
	 * @type string  $intencja_1
	 * @type string  $intencja_2
	 * @type string  $intencja_3
	 * @type string  $intencja_4
	 * @type string  $intencja_5
	 *
	 * }
	 *
	 * @return string|null
	 */
	public function get($k) {
		if (isset($this->data[ $k ])) {
			return $this->data[ $k ];
		}
		
		return NULL;
	}
	
	/**
	 * save
	 *
	 * @return bool|int
	 * @throws \Exception
	 */
	public function save() {
		if (!isset($this->data['date']) || !isset($this->data['hour'])) {
			throw new Exception('Invalid date or hour!');
		}
		$title   = date('YmdHis', strtotime($this->data['date'].' '.$this->data['hour']));
		$meta    = [];
		
		foreach ($this->data as $k => $v) {
			$meta[ 'im_'.$k ] = $v;
		}
		
		$this->ID = wp_insert_post([
			'ID'           => (($this->ID) ? $this->ID : 0),
			'post_title'   => $title,
			'post_name'    => $title,
			'post_status'  => 'publish',
			'meta_input'   => $meta,
			'post_type'    => IntencjeMszalne::POST_TYPE,
		]);
		$this->ID = (is_a($this->ID, 'WP_Error')) ? FALSE : $this->ID;
		
		return $this->ID;
	}
}