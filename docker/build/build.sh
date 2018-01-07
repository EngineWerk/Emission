#!/bin/bash
set -e

IMAGE_MANIFEST=$1
IMAGE_VERSION=$2

if [ "${IMAGE_MANIFEST}" == "" ]; then
  echo "Container manifest can't be empty"
  echo "Please run $0 php-cli 7.1-99."
  exit 1
fi

if [ "${IMAGE_VERSION}" == "" ]; then
  echo "Container version can't be empty"
  echo "Please run $0 $1 7.1.17-99."
  exit 1
fi

echo "Creating Docker image for $1 at version $2"

# TODO Add --no-cache option for building image
docker build ./${IMAGE_MANIFEST} -t emsn-${IMAGE_MANIFEST}:${IMAGE_VERSION}
