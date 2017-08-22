#!/usr/bin/env bash
# Remove stale box
if [ -f ./emission.box ]; then
    rm ./emission.box
fi

# Compacting image

# Building Vagrant box
vagrant package --output emission.box