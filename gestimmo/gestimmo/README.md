Gestion Immobiliere
=========

Module Dolibar pour la gestion locative

Licence
-------
Thierry LECERF www.t3s-it.com
GPLv3 or (at your option) any later version.

See COPYING for more information.

INSTALL
-------

To install this module,  Dolibarr (> v = 3.3) have to be already
installed and configured on your server. It should be fully operational.

- In your  Dolibarr installation directory edit the file htdocs/conf/conf.php
- Find the following lines:
	#$=dolibarr_main_url_root_alt ...
	#$=dolibarr_main_document_root_alt ...
	or 
	//$=dolibarr_main_url_root_alt ...
	//$=dolibarr_main_document_root_alt ...
- Delete the first "#" (or "//") of these lines and assign a value consistent with your Dolibarr instalation
 $ Dolibarr_main_url_root = ... and $ dolibarr_main_document_root ...
 
example for a UNIX system:
	$dolibarr_main_url_root = 'http://localhost/Dolibarr/htdocs';
	$dolibarr_main_document_root = '/var/www/Dolibarr/htdocs';
	$dolibarr_main_url_root_alt = 'http://localhost/Dolibarr/htdocs/custom';
	$dolibarr_main_document_root_alt = '/var/www/Dolibarr/htdocs/custom';

example for a Windows system:
	$dolibarr_main_url_root = 'http://localhost/Dolibarr/htdocs';
	$dolibarr_main_document_root = 'C:/My Web Sites/Dolibarr/htdocs';
	$dolibarr_main_url_root_alt = 'http://localhost/Dolibarr/htdocs/custom';
	$dolibarr_main_document_root_alt = 'C:/My Web Sites/Dolibarr/htdocs/custom';

For more information on the file conf.php file open it conf.php.example

- Extract the module files in the directory dolibarr_main_document_root_alt

- Create the directory custom if it do not exist 

example for a UNIX system: /var/www/Dolibarr/htdocs
example for a Windows system: C:/My Web Sites/Dolibarr/htdocs/custom

- From your browser, log in as administrator dolibarr
  and left click on the "configuration" menu.
  Then click on the submenu "module".
  On the screen that appears and you whouls be able to see the new module (check all tabs, ban be in other than first one)
  The status menu should then proceed to "Enable" menu "Management
  training "should appear at the top of the application.
- Check the security right (Users->permitions) to be sure that right are correctly set for user or users group

Other Licences
--------------
Uses Michel Fortin's PHP Markdown Licensed under BSD to display this README.
