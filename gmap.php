<?php
/**
 * @Plugin "Google map"
 * @version 1.0.0 for Joomla 3
 * @author Oluic Sasa OLSA
 * @authorUrl http://www.olsa.me
**/

defined( '_JEXEC' ) or die;
jimport('joomla.plugin.plugin');

class plgContentGmap extends JPlugin {

	function plgContentGmap ( &$subject, $params ) {
		parent::__construct( $subject, $params );
 	}

	public function onContentPrepare($context, &$article, &$params, $limitstart = 0)
	{

		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer')
		{
			return true;
		}

		// Simple performance check to determine whether bot should process further
		if (strpos($article->text, '{gmap') === false)
		{
			return true;
		}

		// plugin detected start to load global params
		$p = array();
		$p['size'] =  $this->params->get('size','240x180');
		$p['zoom'] =  $this->params->get('zoom','11');
    
		$p['street'] =  str_replace(' ', '+', trim($this->params->get('street','')));
		$p['city'] =  str_replace(' ', '+', trim($this->params->get('city','')));
		$p['country'] =  str_replace(' ', '+', trim($this->params->get('country','')));
    
		$p['lat'] =  trim($this->params->get('lat','0'));
		$p['lng'] =  trim($this->params->get('lng','0'));
		$p['type'] = $this->params->get('maptype','roadmap');

		$setmode = (int) $this->params->get('setmode', 0);

		//-------------------------------------------------------------------
		$static_map_url = "https://maps.googleapis.com/maps/api/staticmap?";
		$iframe = "plugins/content/gmap/googlemap.php?";
		//-------------------------------------------------------------------

		JHtml::stylesheet('plugins/content/gmap/css/magnific.min.css', true);
		JHtml::script('plugins/content/gmap/js/magnific.min.js', true);

		// magnific popup
		$doc = JFactory::getDocument();
		$js = '';
		$js .='jQuery(document).ready(function() {';
		$js .='jQuery(".popup-gmaps").magnificPopup({type:"iframe"});';
		$js .='});';
		$doc->addScriptDeclaration($js);

		// check for plugin instances iniside article and possible override params
		$matches = array();
		$pattern = '/{gmap(.*?)}/i';
		// PREG_SET_ORDER orders results so that $matches[0] is an array of first set of matches, $matches[1] is array of second set of matches
		$content = $article->text;
		$result = preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER );

		// count $matches return number of plugin instances inside article
		if ($matches){

		$token = JSession::getFormToken();

		foreach( $matches as $match ){

			$override = false;
			$over_params = array();
			$k = array();

			if( trim($match[1]) !== ''){

			// if match[1] contain params let's prepare
			$str_params = preg_replace('/\s+/iu', ' ', $match[1]); // input error correction (multiple spaces) 
			$str_params = trim($str_params);
			$over_params = explode(' ', $str_params);

			// go through parameters
				foreach ($over_params as $param){
					  if( strpos($param,'=') !== false ){	 // need to predict another error 
						list($index, $val) = explode('=', $param);
						  if($val !== ''){
							$override = true;
							$k[$index] = $val;
						  }
					 }
				}
			}

			$map = $override ? new GoogleMap($k) : new GoogleMap($p);

			$html = "";
			$img = "";
			$img = '<img src="'.$map->staticImage($static_map_url).'" alt="Location">';
			$html = '<a class="popup-gmaps" href="'.$map->popupData($iframe).'&amp;setmode='.$setmode.'&amp;'.$token.'=1" rel="nofollow">'.$img.'</a>';
			$tag = $match[0]; // {gmap...}
			$content = str_replace( $tag, $html, $content );

			}

			$article->text = $content;
	}
  }
 }

 class GoogleMap {

  private $_street;
  private $_city;
  private $_country;
  private $_lat;
  private $_lng;

  private $_type;
  private $_size;
  private $_zoom;

  private $_address;
  private $_center;
  private $_latlng;

	public function __construct( $v )
	{
		if (is_array($v)) {
			$this->_street = isset($v['street']) ? $v['street'] : NULL;
			$this->_city = isset($v['city']) ? $v['city'] : NULL;
			$this->_country = isset($v['country']) ? $v['country'] : NULL;
			$this->_lat = isset($v['lat']) ? $v['lat'] : 0;
			$this->_lng = isset($v['lng']) ? $v['lng'] : 0;
			$this->_type = isset($v['type']) ? $v['type'] : 'roadmap';
			$this->_size = isset($v['size']) ? $v['size'] : '240x180';
			$this->_zoom = isset($v['zoom']) ? $v['zoom'] : '11';

			$this->_address = urlencode( $this->_street . ' ' . $this->_city . ' ' . $this->_country );      
			$this->_latlng = $this->_lat.','.$this->_lng;
			$this->_center = ( $this->_latlng !== '0,0' ) ? $this->_latlng : $this->_address;
		}
	}

	public function staticImage( $url ){

		$elements = array(
					'center='.$this->_center,
					'zoom='.$this->_zoom,
					'size='.$this->_size,
					'maptype='.$this->_type,
					'markers='.'color:red|label:C|'.$this->_center
					);

		$url = $url . implode('&amp;', $elements);
		return $url;
	}

	public function popupData( $url ){

		$elements = array(
					'lat='.$this->_lat,
					'lng='.$this->_lng,
					'address='.$this->_address,
					'maptype='.$this->_type
					);

		$url = $url . implode('&amp;', $elements);
		return	$url;
	}
}