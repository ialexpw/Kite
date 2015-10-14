# Kite
Kite URL Shortener

Kite is an advanced PHP URL shortener which allows you to run a website which generates short URLs, similar to goo.gl and bit.ly. Kite comes packed with lots of features as standard.

By default Kite comes with features such as easy social sharing, link statistics, removal links, password protection, user accounts, history of links and even an administration panel.

### Kite's Requirements
- PHP 5.5
- MySQL 5
- Mod Rewrite enabled with Apache or rewrite rules for nginx

### Installing Kite
Installing Kite could not be much simpler as we have packed in an automated installer. Follow the few steps below and you will be up and running with Kite in no time.

- Edit /resources/config.php and fill in your MySQL database details.
- Upload all of your files to your webhost.
- Browse to http://website-with-kite.com/install/
- Fill in user account details as well as the website details and click "Install Kite"

After the installer has finished you need to remove the install directory from your webhost.

### Clean URLs
Kite uses clean URL's when navigating to pages and comes with two separate methods to achieve this. Either through apache with Mod Rewrite or though an nginx server configuration file which is included.

### More...
You can view a demo of Kite here: http://kite.paq.nz/ and read the full documentation here: http://kite.paq.nz/documentation/