# WordPress & SAPRFC
Call ABAP functions for display content from SAP in WordPress Widgets

## Description
This plugin is an small example for display data from an SAP R/3 system in WordPress Widgets.
The plugin have include two widgets, one for get material data for an specific material number and other one a widget with a list of all users in the SAP system.
The plugin use the fine libary [SAPRFC extension module for PHP](http://saprfc.sourceforge.net/)

SAPRFC is a extension module for PHP 4 and PHP 5. With SAPRFC is possible call ABAP function modules in SAP R/3 from PHP scripts. You can use the powerful PHP language to create a web applications or interface programs with a connectivity to the SAP R/3. You can also write RFC server program in PHP and call PHP functions from SAP R/3.

## Installation
### Requirements
* WordPress (also Multisite) version 3.3 and later (tested at 3.4)
* PHP 5.3.8
* [SAPRFC extension module for PHP](http://saprfc.sourceforge.net/)

### Installation
#### SAPRFC
1. Crab the [SAPRFC](http://saprfc.sourceforge.net/) lib for PHP
1. Install, follow the [install instruction](http://saprfc.sourceforge.net/src/INSTALL) of the project page of this libary

#### WordPress plugin
1. Unpack the download-package
1. Upload the file to the `/wp-content/plugins/` directory
1. Change the login data to your SAP system in `saprfc.php` inside the plugin folder
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to *Widgets* for active the widgtes in your theme
1. Ready


## Screenshots
 * [See this example screenshot in WordPress 3.4](https://github.com/bueltge/WordPress-SAPRFC/blob/master/screenshot-1.png)

## Other Notes
### Licence
Good news, this plugin is free for everyone! Since it's released under the GPL, you can use it free of charge on your personal or commercial blog. But if you enjoy this plugin, you can thank me and leave a [small donation](http://bueltge.de/wunschliste/ "Wishliste and Donate") for the time I've spent writing and supporting this plugin. And I really don't want to know how many hours of my life this plugin has already eaten ;)

### Translations
The plugin comes with various translations, please refer to the [WordPress Codex](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress in Your Language") for more information about activating the translation. If you want to help to translate the plugin to your language, please have a look at the .pot file which contains all defintions and may be used with a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) (Linux, Mac OS X, Windows).

### Contact & Feedback
The plugin is designed and developed by me ([Frank Bültge](http://bueltge.de))

Please let me know if you like the plugin or you hate it or whatever ... Please fork it, add an issue for ideas and bugs.

### Disclaimer
I'm German and my English might be gruesome here and there. So please be patient with me and let me know of typos or grammatical farts. Thanks
