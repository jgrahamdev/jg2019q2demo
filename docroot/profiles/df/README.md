# Acquia Demo Framework (DF + Proprietary Assets)
[![Build Status](https://magnum.travis-ci.com/acquia/demo_framework.svg?token=fkKCDWeX7fUCfybPUjJb&branch=8.x-3.x)](https://magnum.travis-ci.com/acquia/demo_framework)

## PRIVATE BUILD FOR ACQUIA USE ONLY 
### -- DO NOT SHARE THIS CODE!

This repository is used for development of our proprietary Drupal demo. You may not share this code with anyone outside Acquia. Neither partners nor clients may have access.

Acquia Demo Framework is powered by the open source [Demo Framework](https://www.drupal.org/project/df).

### Installation

Demo Framework has built in drush version, it is highly recommended that you use the drush version from the project. We recommend you use the [drush launcher](https://github.com/drush-ops/drush-launcher) to similify the aliases and ensure you are using the projects drush. 

Download a [pre-built tarball](http://j.mp/acquia-latest-demo-build) of the latest stable release.

To build a copy of the Acquia Demo Framework from source using composer.

  ``composer install``

Composer will combine all the private Acquia files with the open source version of Demo Framework from Drupal.org.

A build script is also provided that wraps the composer install command and moves everything into a target directory as well.

  ``./build.sh ~/Destination``

You can add in commands for composer here. We do NOT suggest using the --no-dev option unless you are managing the dev dependencies globally. Other commands, such as --prefer-dist are valid and may help depending on your situation.

  ``./build.sh ~/Destination``

At this point, you will need to prepare your settings.php file just as you would for a normal Drupal install.

We recommend Acquia Dev Desktop running ``PHP 7_1`` and using the ``Import local Drupal site...`` function.

Now use the ``site-install`` command to install Drupal with the DF installation profile.

  ``drush si df``

Enable a DF Scenario profile, use the ``site-install`` command.

  ``drush si dfs_obio``

If everything worked correctly, you should see console output that some migrations ran.

You may now login to your site.

  ``drush uli -l http://mysite.dd``

#### Fast install using the install.sh script to get both DF and a scenario quickly. 

After installing Acquia Demo Framework via Composer or build.sh, you may use the install script to simplify the installation of the site and enablement of a scenario.

  ``./install.sh ~/Destination dfs_obio ln http://localurl.com:8083``

## Running Tests
These instructions assume you have used Composer to install Lightning. Once you
have it up and running, follow these steps to execute all of Lightning's Behat
tests:

### Behat
    $ cd MYPROJECT
    $ ./bin/drupal behat:init http://YOUR.DF.SITE --merge=../docroot/profiles/df/tests/behat.yml
    $ ./bin/drupal behat:include ../docroot/profiles/df/tests/features --with-subcontexts=../docroot/profiles/df/tests/features/bootstrap --with-subcontexts=../docroot/profiles/df/src/DFExtension/Context --with-subcontexts=../docroot/profiles/lightning/src/LightningExtension/Context
    $ ./bin/behat --config ./docroot/sites/default/files/behat.yml

If necessary, you can edit ```docroot/sites/default/files/behat.yml``` to match
your environment, but generally you will not need to do this.
