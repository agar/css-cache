<?php
/**
 * Adds vendor prefixed attributes
 *
 * Example, expands:
 *
 * 	border-radius: 2px 10px;
 *
 * To be:
 *
 * 	border-radius: 2px 10px;
 * 	-moz-border-radius: 2px 10px;
 * 	-webkit-border-radius: 2px 10px;
 *
 */
class VendorPrefixPlugin extends CssCachePlugin
{
	public function process($css)
	{
		$bases = array();

		$prefixed = array(
			'border-radius' 			=> array('-moz-', '-webkit-'),
			'border-top-left-radius'		=> array('-moz-', '-webkit-'),
			'border-bottom-left-radius'		=> array('-moz-', '-webkit-'),
			'border-top-right-radius'		=> array('-moz-', '-webkit-'),
			'border-bottom-right-radius'		=> array('-moz-', '-webkit-'),
			'border-image'				=> array('-moz-', '-webkit-'),
			
			'background-origin'			=> array('-moz-', '-webkit-'),
			'background-size'			=> array('-moz-', '-webkit-'),
		
			'overflow-x'				=> array('-ms-'),
			'overflow-y'				=> array('-ms-'),

			'word-wrap'				=> array('-ms-'),
			'word-break'				=> array('-ms-'),
	
			'box-shadow' 				=> array('-moz-', '-webkit-', '-o-'),
			'text-shadow' 				=> array('-moz-', '-webkit-', '-o-'),

			'transform' 				=> array('-moz-', '-webkit-', '-o-', '-ms-'),
			'transform-origin' 			=> array('-moz-', '-webkit-', '-o-', '-ms-'),

			'transition' 				=> array('-moz-', '-webkit-', '-o-'),
			'transition-delay' 			=> array('-moz-', '-webkit-', '-o-'),
			'transition-duration'			=> array('-moz-', '-webkit-', '-o-'),
			'transition-property'			=> array('-moz-', '-webkit-', '-o-'),
			'transition-timing-function'		=> array('-moz-', '-webkit-', '-o-'),

			'column-count' 				=> array('-moz-', '-webkit-'),
			'column-gap' 				=> array('-moz-', '-webkit-'),
			'column-rule'				=> array('-moz-', '-webkit-'),
			'column-rule-color' 			=> array('-moz-', '-webkit-'),
			'column-rule-style' 			=> array('-moz-', '-webkit-'),
			'column-rule-width' 			=> array('-moz-', '-webkit-'),
			'column-width' 				=> array('-moz-', '-webkit-'),
		);

		$replaces = array();

		foreach($prefixed as $attribute => $prefixes)
		{
			if (preg_match_all('#\n([\t\ ]+)('.$attribute.'\s*\:[^;]+\;)#i', $css, $matches))
			{
				foreach($matches[2] as $key => $match)
				{
					$match = trim($match);
					if (!in_array($match, $replaces))
					{
						$replaced = $match."\n".$matches[1][$key];
						foreach($prefixes as $prefix) {
							$replaced .= $prefix.trim($match)."\n".$matches[1][$key];
						}
						$css = str_replace($match, $replaced, $css);
						$replaces[] = $match;
					}
				}
			}
		}
		return $css;
	}

}
