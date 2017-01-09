#!/usr/bin/env bash
if `git rev-parse --show-toplevel`/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix --config-file `git rev-parse --show-toplevel`/.php_cs | grep -q "1) "
then
  echo "CS Fixer found issues and fixed them"
  echo "------------------------------------"
  exit 1
fi
