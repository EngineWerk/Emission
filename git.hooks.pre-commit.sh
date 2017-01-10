#!/usr/bin/env bash

if [ "$(basename "$(cat "$GIT_DIR/HEAD"|cut -d ':' -f 2)")" = "master" ] || [ "$(basename "$(cat "$GIT_DIR/HEAD"|cut -d ':' -f 2)")" = "bobmodule" ]; then
    echo "ERROR: Your want to commit to master. It is not allowed, create a pull request on Github on your branch instead."
    echo "\nTo commit anyhow use 'git commit --no-verify'"
    exit 1
fi

if `git rev-parse --show-toplevel`/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix --config-file `git rev-parse --show-toplevel`/.php_cs | grep -q "1) "
then
  echo "CS Fixer found issues and fixed them"
  echo "------------------------------------"
  exit 1
fi
