Use the following line to install Terminus:
```
curl -O https://raw.githubusercontent.com/pantheon-systems/terminus-installer/master/builds/installer.phar && php installer.phar
```

There are options you can set to configure your installation.
- `--bin-dir=<dir>` Where `<dir>` is the location of your bin directory. Defaults to `/usr/local/bin`.
- `--composer-file=<file>` Where `<file>` is the location of your composer installation. Defaults to using `composer` and installing `composer` if it is not apparent.
- `--install-dir=<dir>` Where `<dir>` is the directory to which you want Composer to install Terminus. Defaults to a Composer global install.
- `--cache-dir=<dir>` Where `<dir>` is your desired Terminus cache directory. Defaults to `$HOME/.terminus/cache`.
- `--time-zone=<zone>` Where `<zone>` is your desired time zone. Default is `UTC`.
- `--date-format=<format>` Where `<format>` is the PHP-standard format for timestamps. Default is `Y-m-d H:i:s`.

These can be used by adding them to the installer call from above like so:
`curl -O https://dev-tesladethray.pantheonsite.io/mono/installer && php installer --time-zone=America/Los_Angeles --date-format='M D, Y h:i' --composer-file=~/bin/composer.json`
