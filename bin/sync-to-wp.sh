#!/bin/bash

# Sync plugin files to WordPress plugins directory
rsync -av --delete \
    --exclude=.git/ \
    --exclude=.github/ \
    --exclude=vendor/ \
    /home/george/sites/gl-color-palette-generator/ \
    /home/george/sites/wordpress/wp-content/plugins/gl-color-palette-generator/
