<VirtualHost *:80>
	<FilesMatch "\.php$">
        	AddHandler fcgid-script .php
        	Options +ExecCGI
        	FcgidWrapper /usr/bin/php5 .php
    	</FilesMatch>

	ServerName netscavator.com
	ServerAlias netscavator.airplaymusic.dk
	ServerAlias www.netscavator.com
	DocumentRoot /home/sleipner/crawler/www/drupal
	AccessFileName .htaccess
	<Directory /home/sleipner/crawler/www/drupal>
        	AddHandler fcgid-script .php
        	ExpiresActive On
        	ExpiresDefault "access plus 2 week"
        	Options +ExecCGI 
		RewriteEngine on
		RewriteBase /
		RewriteCond %{REQUEST_FILENAME} !-f
		RewriteCond %{REQUEST_FILENAME} !-d
		RewriteRule ^(.*)$ index.php?q=$1 [L,QSA]
		AllowOverride All
		Order allow,deny
		allow from all
	</Directory>

	ErrorLog ${APACHE_LOG_DIR}/error.log

	# Possible values include: debug, info, notice, warn, error, crit,
	# alert, emerg.
	LogLevel error

	CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
