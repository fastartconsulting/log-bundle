FastART Consulting 
=============

LogBundle v1.0 STABLE
=============

This bundle provides a configuration of RESTful API's for User

Integrated with:
- MongoDB Bundle - doctrine/mongodb-odm-bundle


Documented with:
- Nelmio API DOC Bundle - nelmio/api-doc-bundle


Note
----



Documentation
-------------
- Setup
- Configuration

Setup
------------
- **A) Download the Bundle**

Open a command console, enter your project directory and execute the following command to download the latest stable version of this package:

**`composer require fastartconsulting/log-bundle`**

- **B) Add Bundles in AppKernel.php**

Open the file AppKernel.php located inside /app folder and add the following lines:

```
new FOS\RestBundle\FOSRestBundle(),
new FOS\UserBundle\FOSUserBundle(),
new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
new JMS\SerializerBundle\JMSSerializerBundle(),
new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
new FAC\LogBundle\FACLogBundle(),
```

Configuration
------------
- Configure your application's parameters.yml and add valid values for Log dir:
```
log_dir: '%kernel.project_dir%/var/logs/'
```

- Run Server and Enjoy ;) 

After 
```
php bin/console server:start
```

you can see the API's on 127.0.0.1/doc

Our integrations
------------

License
-------

This bundle is under the MIT license.
