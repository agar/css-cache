<?php

	/**
	 * CssCache
	 *
	 * Include css files you want to compile and cache as the querystring for
	 * the file. For example:
	 *
	 *   <link href=css.php?site.css,page.css" rel="stylesheet" type="text/css" />
	 *
	 * Based on the original CssCacheer by Shaun Inman:
	 * http://www.shauninman.com/archive/2008/05/30/check_out_css_cacheer
	 *
	 */
	class CssCache
	{

		/**
		 * Directories for the cache and plugins.
		 */
		const PLUGIN_DIR = './plugins';
		const CACHE_DIR = './cache';

		/**
		 * Lis of requested css files
		 */
		public $requested_files = array();

		private static $instance;
		private $plugins;
		private $css_dir;
		private $most_recent_mtime;

		/**
		 * Constructor, private as it's a singleton class
		 */
		private function __construct()
		{
			// load plugins
			$this->load_plugins();

			// wrk out files to cache
			$files = explode(',', $_SERVER['QUERY_STRING']);
			$this->css_dir = dirname(__FILE__).'/';
			$this->most_recent_mtime = 0;
			foreach($files as $file)
			{
				if (stristr($file, '..') || !preg_match('/\.css$/', $file))
				{
					continue;
				}
				else if (file_exists($this->css_dir.$file))
				{
					$this->requested_files[] = $file;
					$mtime = filemtime($this->css_dir.$file);
					$this->most_recent_mtime = $mtime > $this->most_recent_mtime ? $mtime : $this->most_recent_mtime;
				}
			}
		}

		/**
		 * Get CssCache instance
		 */
		final public static function get_instance()
		{
			if (self::$instance == null)
			{
				self::$instance = new self();
			}
			return self::$instance;
		}
		final private function __clone() { }

		/**
		 * Render the requested css files (or serve from the cache if it can)
		 */
		public function render()
		{
			if (!$this->is_cached())
			{
				$css = '';
				foreach($this->requested_files as $file)
				{
					$css .= file_get_contents($this->css_dir.$file);
				}
				// Pre-process for importers
				foreach($this->plugins as $plugin)
				{
					$css = $plugin->pre_process($css);
				}
				// Process for heavy lifting
				foreach($this->plugins as $plugin)
				{
					$css = $plugin->process($css);
				}
				// Post-process for formatters
				foreach($this->plugins as $plugin)
				{
					$css = $plugin->post_process($css);
				}
				$this->cache($css);
			}
			$this->serve_cached();
		}

		/**
		 * Check if the requested css is cached and possibly un-modified
		 */
		private function is_cached()
		{
			$cached_file = self::CACHE_DIR.'/'.md5($_SERVER['QUERY_STRING']).'.css';
			$mod_time = @filemtime($cached_file);

			// check the cached file exists
			if (file_exists($cached_file))
			{
				// check modified time against the requested files
				$cached_mod_time = (int) @filemtime($cached_file);
				foreach($this->requested_files as $file)
				{
					$requested_mod_time	= filemtime($this->css_dir.$file);
					if ($cached_mod_time < $requested_mod_time)
					{
						return FALSE;
					}
				}

				//  send 304 header if appropriate
				if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'], $_SERVER['SERVER_PROTOCOL']))
				{
					if ($mod_time <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']))
					{
						header("{$_SERVER['SERVER_PROTOCOL']} 304 Not Modified");
						exit();
					}
				}
				return TRUE;
			}
			return FALSE;
		}

		/**
		 * Output a copy of the cached file
		 */
		private function serve_cached()
		{
			$cached_file = self::CACHE_DIR.'/'.md5($_SERVER['QUERY_STRING']).'.css';
			if (file_exists($cached_file))
			{
				$mod_time = filemtime($cached_file);
				header('Content-Type: text/css');
				header('Last-Modified: '.gmdate('D, d M Y H:i:s', $mod_time).' GMT');
				readfile($cached_file);
			}
			exit();
		}

		/**
		 * Cache the compliled css file
		 *
		 * @param string $css
		 */
		private function cache($css = '')
		{
			$cached_file = self::CACHE_DIR.'/'.md5($_SERVER['QUERY_STRING']).'.css';
			if (file_exists(self::CACHE_DIR) && is_dir(self::CACHE_DIR) && is_writable(self::CACHE_DIR))
			{
				file_put_contents($cached_file, $css);
				touch($cached_file, $this->most_recent_mtime);
			}
		}

		/**
		 * Load any plugins
		 */
		private function load_plugins()
		{
			$this->plugins = array();
			if (is_dir(self::PLUGIN_DIR))
			{
				$plugin_files = scandir(self::PLUGIN_DIR);
				foreach ($plugin_files as $plugin_file)
				{
					if (substr($plugin_file, 0, 1) == '.' || substr($plugin_file, 0, 1) == '-')
					{
						continue;
					}
					include(self::PLUGIN_DIR.'/'.$plugin_file);
					$plugin_class = ucwords(ltrim(substr($plugin_file, 0, -4), '-0123456789')).'Plugin';
					if (class_exists($plugin_class))
					{
						$this->plugins[$plugin_class] = new $plugin_class();
					}
				}
			}
		}
	}

	/**
	 * Plugin base class
	 */
	abstract class CssCachePlugin
	{
		public function pre_process($css)
		{
			return $css;
		}
		public function process($css)
		{
			return $css;
		}
		public function post_process($css)
		{
			return $css;
		}
	}

	// create and render
	$css_cache = CssCache::get_instance();
	$css_cache->render();

