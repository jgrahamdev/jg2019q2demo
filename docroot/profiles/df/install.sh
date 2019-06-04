#!/bin/sh

# Use: Run this file install Demo Framework and optionally automatically enable
# a scenario.
# Example: ./install.sh ../d8df dfs_obio ln http://demo.dd:8083 my_db_backup

# required [TARGET] The local docroot to install Drupal and Demo Framework.
# optional [SCENARIO] Automatically enable a scenario. eg: dfs_fin
# optional [COMMAND] Include 'ln' to symlink the private files (used for development).
# optional [URI] Include a URI for the login command on post-install
# optional [DBDUMP] Post-install, dump a db for later usage. Subsequent calls to the same db name will be imported.

TARGET=$1
SCENARIO=$2
COMMAND=$3
URI=$4
DBDUMP=$5

# Ensure the target directory exists.
if [ ! -r $TARGET ]; then
  echo "Target not found."
  exit
fi

cd $TARGET

# Check if a settings.php file exists before starting the script.
if [ ! -r docroot/sites/default/settings.php ]; then
  echo "No DB connection for docroot: $TARGET. Ensure settings.php is configured before installation."
  exit
fi

# Link up acquia/demo_framework files from the local repo in the target dir.
if [ "$COMMAND" = "ln"  ]; then
   rm -rf docroot/profiles/df/*/private/*
   rm -rf docroot/profiles/df/profiles/*
   ln -sf $TARGET/profiles/* docroot/profiles/df/profiles/.
   ln -sf $TARGET/modules/private/* docroot/profiles/df/modules/private/.
   ln -sf $TARGET/themes/private/* docroot/profiles/df/themes/private/.
   echo "Symlinks created"
fi

# Move into the docroot.
cd docroot

# Install Demo Framework.
if [ ! -z $DBDUMP ] && [ -r $TARGET/$DBDUMP.sql ]; then
  drush sql-drop -y
  echo "Now importing $DBDUMP.sql ..."
  drush sql-cli < $TARGET/$DBDUMP.sql
else
  drush si $SCENARIO --account-name=admin --account-pass=presales -y
fi

# Manage the SQL dump.
if [ ! -z $DBDUMP ]; then
  if [ -r $TARGET/$DBDUMP.sql ]; then
    echo "DB '$DBDUMP' imported successfully."
  else
    drush sql-dump --result-file=$TARGET/$DBDUMP.sql -y
  fi
fi

# Drush does not set the correct permissions on sites/default.
chmod -R 744 sites/default

# Set local URI and Login as admin.
if [ ! -z "$URI" ]; then
  touch ../drush/drushrc.local.php
  echo "<?php \$options['uri'] = '$URI';" > ../drush/drushrc.local.php
  drush uli
else
  drush uli
fi

cd -
