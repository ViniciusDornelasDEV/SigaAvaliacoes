# Welcome to Their Icarus Project #

** Setting Up ** 

1) Run php composer.phar install from the root directory

2) Create a virtual host called icarus.local and point it to the folder public, follow the example below:

````

<VirtualHost *:80>
   DocumentRoot "/websites/their_icarus/app/public"
   ServerName icarus.local

   <Directory "/websites/their_icarus/app/public">
       Options Indexes MultiViews FollowSymLinks
       AllowOverride All
       Order allow,deny
       Allow from all
   </Directory>

</VirtualHost>

````


3) Create a local.php file inside of : config/autoload with the following contents:

````
<?php

//DEFINE CONSTANTS 
defined('SITE_URL')
    || define('SITE_URL', 'http://icarus.techstudiohq.com');

return array(
    'db'   => array(
        'dsn' => 'mysql:dbname=their_icarus;host=localhost',
        'username'      => 'icarus',
        'password'      => 'Letmein89'
    ),
);

````

4) Create an aws.local.php inside of config/autoload with the following contents:

````
<?php

return array(
    /**
     * You can define global configuration settings for the SDK as an array. Typically, you will want to a provide your
     * credentials (key and secret key) and the region (e.g. us-west-2) in which you would like to use your services.
     */
     'aws' => array(
         'key'    => 'AKIAICKDKH7C66UKLK7Q',
         'secret' => 'DIxogHkXHRlvh/jKkqqbUNy5voPDYfPrkwO78nrG',
         'region' => 'eu-west-1'
     ) 
    
    /**
     * You can alternatively provide a path to an AWS SDK for PHP config file containing your configuration settings.
     * Config files can allow you to have finer-grained control over the configuration settings for each service client.
     */
    // 'aws' => 'path/to/your/aws-config.php'
);

````

If after everything you're having problems, verify your apache error log, and make sure your server matches the requirements below: 

** Server Requirements **

* php 5.3 or later
* php mod_rewrite
* GD library 
* PHP Lib Intl
* Mysql 5.6 (Only if using a local server, which is not being used on this guide)