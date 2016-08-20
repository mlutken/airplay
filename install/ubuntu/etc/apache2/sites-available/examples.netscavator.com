<VirtualHost *:80>
	<FilesMatch "\.php$">
        	AddHandler fcgid-script .php
        	Options +ExecCGI
        	FcgidWrapper /usr/bin/php5 .php
    	</FilesMatch>

	ServerName examples.netscavator.com:80
	ServerAlias examples.airplaymusic.dk
	DocumentRoot /home/sleipner/crawler/doc/examples
	AccessFileName .htaccess

	<Directory /home/sleipner/crawler/doc/examples>
        	Options Indexes FollowSymLinks MultiViews
		Order allow,deny
		allow from all
	</Directory>

	<Directory /home/sleipner/crawler/doc/examples/sites>
        	AddHandler fcgid-script .php
        	Options +ExecCGI Indexes FollowSymLinks MultiViews
		Order allow,deny
		allow from all
	</Directory>

	ErrorLog ${APACHE_LOG_DIR}/error.log

	# Possible values include: debug, info, notice, warn, error, crit,
	# alert, emerg.
	LogLevel error

	CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
