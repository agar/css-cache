<?php
/**
 * Server side css import.
 *
 * Example:
 *
 *     @server import url("modules/reset.css");
 *
 */
class ServerImportPlugin extends CssCachePlugin
{
	public function pre_process($css)
	{
		$css_cache = CssCache::get_instance();
		$imported = $css_cache->requested_files;
		while (preg_match_all('#\s+@server\s+import\s+url\(([^\)]+)+\);#i', $css, $matches))
		{
			foreach($matches[1] as $i => $include)
			{
				$include = preg_replace('#^("|\')|("|\')$#', '', $include);
				if (!in_array($include, $imported) && substr($include, -3) == 'css')
				{
					$imported[] = $include;
					if (file_exists($include))
					{
						$include_css = file_get_contents($include);
						$css = str_replace($matches[0][$i], $include_css, $css);
					}
					else
					{
						$css .= "\r\nerror { -si-missing: url('{$include}'); }";
					}
				}
				$css = str_replace($matches[0][$i], '', $css);
			}
		}
		return $css;
	}
}