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

``` html
<link href=css.php?core.css" rel="stylesheet" type="text/css" />
```

Make sure the cache directory is writeable by the web server. Plugins live in 
the plugins directory (duh), and are loaded in alphabetical order according to 
the server file system. Those prefixed with a - are ignored.

Plugins
-------

Located in the `plugins/` directory, these are run in filename order against the css.

### 1. Server Import

Allows you to include other css files server side to reducs HTTP requests from the browser:

	@server import url("modules/reset.css");

### 2. Based On

Define a base set of css properties and apply them to other selectors easily. For example, the following:

``` css
@base(base-font-spec) {
	font-family: Arial, sans-serif;
	font-size: 12px;
	line-height: 1.4;
}
body {
	based-on: base(base-font-spec);
}
input {
	based-on: base(base-font-spec);
	color: #444;
}
```

Will be compiled into:

``` css
body {
	font-family: Arial, sans-serif;
	font-size: 12px;
	line-height: 1.4;
}
input {
	font-family: Arial, sans-serif;
	font-size: 12px;
	line-height: 1.4;
	color: #444;
}
```

### 3. Constants

Constants can be defined once and re-used throughout all css (so only need to be changed in one spot).

``` css
@constants {
	textcolor: #444;
}
input {
	color: const(textcolor);
}
```

### 4. Vendor Prefix Expansion

Expands out CSS3 properties to include applicable vendor prefixes. See the plugin file for a list of properties that get expanded.

``` css
div {
	border-radius: 4px;
}
```

Get expanded to become:

``` css
div {
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
}
```

### 5. Condenser

Removes comments and extra whitespace.
