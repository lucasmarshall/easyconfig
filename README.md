Easy Config
===========
This package provides an easy-to-use YAML-based configuration system for PHP, including process and APC caching.

It currently doesn't work as I just pulled it out of a framework that I built that I've been using for projects
for the past couple of years. There are currently a number of dependencies on other parts of the framework that
need to be removed for it to be a stand-alone package.


Usage
-----
The idea behind it is that you can have a tree of YAML configuration files like:

    appconfig/
        |- myconfig.yaml

With myconfig.yaml looking like:

    secret_key: my_secret
    redis_url: redis://localhost:6379/1
    list_of_stuff:
    	- item1
    	- item2
    	- item3

And you can access the configuration like this:

    $config = easyconfig\Loader::getConfig('appconfig');
    $secret = $config->myconfig->secret_key;
    $redis_url = $config->myconfig->redis_url;

    foreach ($config->myconfig->list_of_stuff as $item)
    {
        print "$item\n";
	}

	// You could also do:
	$config = easyconfig\Loader::getConfig('appconfig/myconfig');

Configs are lazy-loaded, so they only get read from disk or APC when an attempt is made to access them.

Extensibility
-------------
It's also extendable to handle configurations with specific yaml tags differently. See easyconfig\yamltaghandler\*
classes for examples.

Future Plans
------------
Keep watching this space - I hope to have this package available as a composer package for use soon, but feel free
to adopt the concepts here in your own code if you like.
