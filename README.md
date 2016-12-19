# terminus-installer : An installer script for Terminus, Pantheon's CLI
 
## Status
[![Build Status](https://travis-ci.org/pantheon-systems/terminus-installer.svg?branch=master)](https://travis-ci.org/pantheon-systems/terminus-installer)
[![Dependency Status](https://gemnasium.com/pantheon-systems/terminus-installer.svg)](https://gemnasium.com/pantheon-systems/terminus-installer)
[![Coverage Status](https://coveralls.io/repos/github/pantheon-systems/terminus-installer/badge.svg?branch=master)](https://coveralls.io/github/pantheon-systems/terminus-installer?branch=master)
 
## About
This repository houses the source for and PHAR archive of the Terminus Installer script. Its goal is to provide a
fail-proof installation method for users less familiar with manual installation methods such as Composer and Git, or
those simply wanting a single-step process to get it running on their local machines.
 
## Index
| Component | Description | Notes |
| --------- | ----------- | ----- |
| bin | User-executable scripts | |
| builds | PHAR copies of the script | |
| scripts | Scripts for creating PHAR archives of the source and maintaining the source code | |
| src | Source code for the script | |
| tests | Tests for maintaining the codebase | |
 
## Developing & Running
### Running
Use the following line to install Terminus:
```
curl -O https://raw.githubusercontent.com/pantheon-systems/terminus-installer/master/builds/installer.phar && php installer.phar
```
The installer will attempt to do the following:

1. Search for Composer. If it is not present, the installer will install it to your `bin` directory for you.
2. Installs Terminus via Composer. If no install location was given, it will install Terminus globally.
3. If the `time-zone`, `date-format`, and/or `cache-dir` options were provided, the installer will update your global
Terminus configuration file at `$HOME/.terminus/config.yml`.

### Developing
0. Create an issue on this repository to discuss the change you propose should be made.
1. Fork this repository.
2. Clone the forked repository.
3. Run `composer install` at the repository root directory to install all necessary dependencies.
3. Make changes to the code.
4. Run the test suite. The tests must pass before any code will be accepted.
5. Commit your changes and push them up to your fork.
6. Open a pull request on this repository to merge your fork.

Your pull request will be reviewed by Pantheon and changes may be requested before the code is accepted.
 
## Debugging
_No tips at present._
 
## Known Issues/Limitations
- If the `bin` directory is unavailable, Terminus cannot be automatically set up as a command. A symlink or an alias
to the Terminus executable be made in either the `~/.bashrc` or `~/.bash_profile` files.
- The installer only can search the PHP path for Composer, not `$PATH`.
 
## Runtime Dependencies
- A command-line client
- PHP
- PHP-CLI
- cURL
 
## Runtime Configuration
There are options you can set to configure your installation.
- `--bin-dir=<dir>` Where `<dir>` is the location of your bin directory. Defaults to `/usr/local/bin`.
- `--install-dir=<dir>` Where `<dir>` is the directory to which you want Composer to install Terminus. If not given, Terminus will be installed to your current working directory.
- `--install-version=<version>` Where `<version>` is any released version of Terminus. If not given, the latest version will be installed.

These can be used by adding them to the installer call from above like so:
```
curl -O https://raw.githubusercontent.com/pantheon-systems/terminus-installer/master/builds/installer.phar && php installer.phar --install-version=1.2.3 --install-dir=/tmp/terminus
```
 
## Testing
Tests are run via the `.scripts/test.sh` script. Components thereof can be run as follows:
- `composer cs` runs the code sniffer to ensure all code is appropriately formatted.
- `composer phpunit` runs the PHPUnit unit tests.
- `composer behat` runs the Behat feature tests
 
## Managing Third-Party Libraries
Dependencies are easily updated by Composer. To update this codebase:

1. Check out a new branch off of an up-to-date copy of master.
2. Run `composer update` at the repository root directory.
3. Run the test suite. If there are errors, address them.
4. Commit the changes, push the branch, and create a pull request for the update.
 
## Deployment
New versions are automatically deployed once an update is accepted into the master branch.
