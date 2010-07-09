CssCache
========

Simple PHP script to parse and compile CSS into a single cached file. It's based 
mostly on the CssCacheer script by Shaun Inman (http://www.shauninman.com/archive/2008/05/30/check_out_css_cacheer)

Requirements
------------

- PHP5 

Usage
-----

Include the target CSS files in the query string. Multiple files can be 
separated with a comma.

	<link href=css.php?core.css" rel="stylesheet" type="text/css" />
	
Make sure the cache directory is writeable by the web server. Plugins live in 
the plugins directory (duh), and are loaded in alphabetical order according to 
the server file system. Those prefixed with a - are ignored.