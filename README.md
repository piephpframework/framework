## Setup

Setup is really easy for the **Object69** Framework, just follow the required steps below.

### Installation

To install Object69 download the files to your server, it is recommended that the files are somewhere outside of the document root. If you cannot place them outside of the document root place them in a directory within the document root. No framework files should be in the root directory of the document root though.

Example locations:

* `/usr/share/php/Object69/autoloader.php`
* `/home/$USER/Object69/autoloader.php`
* `$DOCUMENT_ROOT/Object69/autoloader.php`

Once downloaded, create the file `index.php` inside the document root of your server. Replace `path/to/autoloader.php` with the actual path of the autoloader found in the root of the `Object69` framework.

```php
<?php

require_once 'path/to/autoloader.php';

$app = Object69::module('Welcome', []);

$app->controller('Welcome', function(){
    echo '<h1>Welcome to Object69!</h1>';
});

$app->call('Welcome');
```

### Change PHP Include Path (Optional)

In the `php.ini` file that is loaded for your php, find the line `include_path` and append the path to the `Object69` framework. Lets assume that you put the framework here `/usr/share/php/Object69` in just append that to the `include_path` prefixed with a `:`

Your include path might look something like this now:

```
include_path = ".:/usr/share/php/Object69"
```

### Routing

Routing usually requires that you setup a few server settings, here are settings in order to get `Apache` or `Nginx` working.

#### Apache

With Apache, we can add this to the document root in an `.htaccess` file:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule (.*) index.php [L]
```

#### Nginx

With Nginx, this usually works without having to do a rewrite, just add this to the `server` section:

```
location / {
    try_files $uri $uri/ /index.php;
}
```

### Autoloading

When creating file such as your own classes for services and controllers, place them somewhere in the document root. The autoloader will load the files properly if the namespace is correct, as it looks at the document root.

For example if you create a class in `$DOCUMENT_ROOT/Contollers/Users.php` your namespace for that file should be `Controllers`. If you create a class in `$DOCUMENT_ROOT/Controllers/Public/Users.php` your namespace for that file should be `Controllers\Public`.

### Environment Settings

You can create an environment settings file anywhere you would like, but it is recommended that it is not within your document root. If it is, people will have access to it and be able to see any settings you have in there, such as your database username and password, or your API keys and secrets.

```php
<?php

require_once 'path/to/autoloader.php';

Env::loadFromFile('/path/to/config.ini');
```