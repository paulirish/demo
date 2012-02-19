
<?php
class cms {
	function _setup () {
		function _parsePanel ($parse, $str_in, &$array_out) {
			$array_out = array();
			preg_match_all($parse, $str_in, $i);
			array_shift($i);
			for ($a = 0; $a < count($i[0]); $a++) {
				$name = trim((($i[0][$a]) ? strtolower($i[0][$a]) : $a));
				$array_out[$name] = trim($i[1][$a]);
			}
		}
		function _parseWebsite ($parse, &$array_out) {
			_parsePanel($parse, $array_out, $array_out);
			$opt_default = array(missing=>'missing', theme=>'cimus', template=>'index.html');
			$opt = array();
			foreach ($array_out as $a => $b) {
				if ($a !== 'opts') {
					$opt[$a] = $b;
					if ($a !== 'name') {
						unset($array_out[$a]);
					}
				}
			}
			$array_out['opts'] = array_merge($opt_default, $opt);
			$array_out['page'] = strtolower((($_GET['p']) ? $_GET['p'] : 'index'));
			if ($array_out['opts']['index']) {
				$array_out['index'] = $array_out['opts']['index'];
				unset($array_out['opts']['index']);
			}
			else {
				$array_out['index'] = 'index';
			}
		}
		function _parseNavigation ($parse, &$panel) {
			$website = &$panel['website'];
			$array_out = &$panel['sitemap'];
			preg_match_all($parse, $array_out, $i);
			array_shift($i);
			$array_out = array();
			$array_deep = array();
			for ($a = 0; $a < count($i[0]); $a++) {
				$call = $i[1][$a];
				$deep = strlen($i[0][$a]);
				$name = trim(preg_replace('/\[.*?\]/', '', $call));
				$page = strtolower(str_replace(array(0=>' ', 1=>'.'), array(0=>'-', 1=>''), $name));
				preg_match_all('/\[(.*?):(.*?)\]|\[(.*?)\]/', $call, $opts);
				if (count($opts[0])) {
					$opts_ = array();
					for ($b = 0; $b < count($opts[0]); $b++) {
						if ($opts[1][$b] == 'url') {
							$page = trim($opts[2][$b]);
						}
						else {
							$opts_[trim($opts[1][$b])] = trim($opts[2][$b]);
						}
					}
					$opts = $opts_;
				}
				else {
					$opts = array();
				}
				if ($a == 0) {
					$page = 'index';
					if ($website['page'] == $page) {
						$website['opts'][$page] = $page;
					}
				}
				$opts = array_merge(array(name=>$name), $opts);
				$array_deep[$deep] = $page;
				$array_out_ = &$array_out;
				for ($b = 0; $b < $deep; $b++) {
					$opts = array_merge($array_out_[$array_deep[$b]]['opts'], $opts);
					$array_out_ = &$array_out_[$array_deep[$b]]['kids'];
				}
				if ($array_out_['name']) {
					if (!$array_out_['kids']) {
						$array_out_['kids'] = array();
					}
					$array_write = &$array_out_['kids'];
				}
				else {
					$array_write = &$array_out_;
				}
				$array_write[$page] = array(name=>$name, deep=>$deep, opts=>$opts);
				if ($page == $website['page']) {
					$array_write[$page]['current'] = true;
				}
			}
		}
		function _parsePage (&$sitemap, &$current) {
			foreach ($sitemap as &$a) {
				if ($a['kids']) {
					$is_selected = _parsePage($a['kids'], $current);
					if ($is_selected) {
						$a['selected'] = true;
						return true;
					}
				}
				if ($a['current']) {
					$a['selected'] = true;
					$current = $a;
					return true;
				}
			}
			return false;
		}
		function _htmlAllNavigation (&$panel) {
			$index = $panel['website']['index'];
			$sitemap = $panel['sitemap'];
			$panel['html'] = array(
				'navigation-sitemap' => _htmlNavigationSitemap($sitemap, $index),
				'navigation-breadcrumb' => _htmlNavigationBreadcrumb($sitemap, $index)
			);
			$hold = true;
			$deep = 1;
			while ($hold) {
				$hold = false;
				foreach ($sitemap as &$b) {
					if ($b['selected']) {
						$hold = true;
						$panel['html']['navigation-level-' . $deep] =  _htmlNavigation($sitemap, $index, $deep);
						$sitemap = $b['kids'];
						$deep++;
						break;
					}
				}
				if ($sitemap) {
					$panel['html']['navigation-siblings'] = $panel['html']['navigation-level-' . ($deep - 1)];
					$panel['html']['navigation-children'] = $panel['html']['navigation-level-' . $deep] = _htmlNavigation($sitemap, $index, $deep);
				}
				else {
					$hold = false;
				}
			}
		}
		function _htmlNavigationSitemap (&$sitemap, $index, $deep = 1) {
			$h = '<ul class="level-' . $deep . '">';
			foreach ($sitemap as $a => $b) {
				$a_ = &$sitemap[$a];
				$c = 'level-' . $deep . (($a_['selected']) ? ' selected' : '') . (($a_['current']) ? ' current' : '');
				$h_ = '<li class="' . $c . '"><a class="' . $c . '" href="./' . (($a !== $index) ? $a : '') . '"><span>' . $a_['name'] . '</span></a>';
				if ($a_['kids']) {
					$h_ .= _htmlNavigationSitemap($a_['kids'], $index, $deep + 1);
				}
				$h_ .= '</li>';
				if (!$a_['hidden']) {
					$h .= $h_;
				}
			}
			$h .= '</ul>';
			return $h;
		}
		function _htmlNavigation (&$sitemap, $index, $deep = 1) {
			$h = '<ul class="level-' . $deep . '">';
			foreach ($sitemap as $a => $b) {
				$a_ = &$sitemap[$a];
				$c = 'level-' . $deep . (($a_['selected']) ? ' selected' : '') . (($a_['current']) ? ' current' : '');
				if (!$a_['hidden']) {
					$h .= '<li class="' . $c . '"><a class="' . $c . '" href="./' . (($a !== $index) ? $a : '') . '"><span>' . $a_['name'] . '</span></a>';
				}
			}
			$h .= '</ul>';
			return $h;
		}
		function _htmlNavigationBreadcrumb (&$sitemap, $index, $deep = 1) {
			$h = false;
			foreach ($sitemap as $a => $b) {
				$a_ = &$sitemap[$a];
				if ($a_['selected']) {
					$c = 'level-' . $deep . ' selected' . (($a_['current']) ? ' current' : '');
					$h = '<a class="' . $c . '" href="./' . (($a !== $index) ? $a : '') . '"><span>' . $a_['name'] . '</span></a>';
					if ($a_['current']) {
						return $h;
					}
					elseif ($a_['kids']) {
						$return = _htmlNavigationBreadcrumb($a_['kids'], $index, $deep + 1);
						if ($return) {
							return $h . '&nbsp;&gt;&nbsp;' . $return;
						}
					}
				}
			}
			return $h;
		}
		_parsePanel('/\/\*\s{0,}-+\s{0,}(.*?)\s{0,}-+\s{0,}\*\/([^\/]+)/', trim(file_get_contents('cms.txt')), $panel);
		_parseWebsite('/(.*?):(.+)/', $panel['website']);
		_parseNavigation('/(\t{0,})(.+?)(\n|$)/sm', $panel);
		_parsePage($panel['sitemap'], $panel['current']);
		$panel['current']['opts'] = array_merge($panel['website']['opts'], $panel['current']['opts']);
		_htmlAllNavigation($panel);
		return $panel;
	}
	function _theme (&$panel) {
		function _commentCompression ($str) {
			$str = preg_replace('/<!-- BEGIN:(.*?) -->.*?<!-- END:\1 -->/s', '##$1##', $str);
			$str = preg_replace('/##BEGIN:(.*?)##.*?##END:\1##/s', '##$1##', $str);
			return $str;
		}
		function _replace ($replace, $with, &$inside) {
			$inside = str_ireplace('<!-- ' . $replace . ' -->', $with, $inside);
			$inside = str_ireplace('##' . $replace . '##', $with, $inside);
		}
		function _include ($u, $a) {
			foreach ($a as $b) {
				if (file_exists($u . $b)) {
					ob_start();
					include $u . $b;
					$u = ob_get_contents();
					ob_end_clean();
					return _commentCompression($u);
				}
			}
			return false;
		}
		$current = $panel['current'];
		if (!$current) {
			$current = $panel['website'];
		}
		$theme = _include('themes/' . $current['opts']['theme'] . '/templates/' . $current['opts']['template'], array(0=>'', 1=>'.php', 2=>'.html', 3=>'.txt'));
		$page = _include('pages/' . $panel['website']['page'], array(0=>'.php', 1=>'.html', 2=>'.txt'));
		if (!$page) {
			$page = _include('pages/' . $current['opts']['missing'], array(0=>'.php', 1=>'.html', 2=>'.txt'));
		}
		_replace('content', $page, $theme);
		$theme = preg_replace('/(href|src)="\.\./', '$1="' . 'themes/' . $current['opts']['theme'], $theme);
		foreach ($panel['html'] as $a => $b) {
			_replace($a, $b, $theme);
		}
		if ($current['opts']) {
			foreach ($current['opts'] as $a => $b) {
				if (is_string($b)) {
					_replace($a, $b, $theme);
				}
			}
		}
		foreach ($panel['website']['opts'] as $a => $b) {
			if (is_string($b)) {
				_replace('site-' . $a, $b, $theme);
			}
		}
		$theme = preg_replace('/(<!--\s|##).*?(\s-->|##)/', '', $theme);
		echo $theme;
	}
	function __construct () {
		$t = $this;
		$panel = $t->_setup();
		$theme = $t->_theme($panel);
	}
}
$site = new cms();
?>