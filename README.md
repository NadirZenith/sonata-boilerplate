Sonata Boilerplate
==================


What's inside?
--------------

Sonata Standard Edition comes pre-configured with the following bundles:

* Bundles from Symfony Standard distribution
* Sonata Admin Bundles: Admin and Doctrine ORM Admin
* Sonata Ecommerce Bundles: Payment, Customer, Invoice, Order and Product
* Sonata Foundation Bundles: Core, Notification, Formatter, Intl, Cache, Seo and Easy Extends
* Sonata Feature Bundles: Page, Media, News, User, Block, Timeline
* Api Bundles: FOSRestBundle, BazingaHateoasBundle, NelmioApiDocBundle and JMSSerializerBundle

Installation
------------

    git clone https://github.com/NadirZenith/sonata-boilerplate.git

    cd sonata-boilerplate

    composer install


### Auto load default data
* php bin/load_data.php

### Manual data creation
*   sym doctrine:schema:update --force
*   sym fos:user:create
*   sym fos:user:promote --super
*   sym sonata:page:create-site
*   sym sonata:page:update-core-routes --site=all
*   sym sonata:page:create-snapshots --site=all


Run
---

If you are running PHP5.4, you can use the built in server to start the demo:

    app/console server:run localhost:9090

Now open your browser and go to http://localhost:9090/

Tests
-----

### Functional testing

To run the Behat tests, copy the default configuration file and adjust the base_url to your needs

    # behat.yml
    imports:
        - behat.yml.dist

    # Overwrite only the config you want to change here

You can now run the tests suite using the following command

    bin/qa_behat.sh

To get more informations about Behat, feel free to check [the official documentation][link_behat].


### Unit testing

To run the Sonata test suites, you can run the command:

    bin/qa_client_ci.sh

Enjoy!

[link_behat]: http://docs.behat.org "the official Behat documentation"
[link_vagrant]: http://www.vagrantup.com/downloads.html "Download Vagrant"
[link_virtualbox]: https://www.virtualbox.org/wiki/Downloads "Download VirtualBox"
[link_sonata]: http://sonata.local "Sonata"
