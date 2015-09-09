# check-rrsig

## Introduction

`check-rrsig` is a tool for checking the expiry time of `RRSIG`
resource records for `DNSSEC`. I use it to monitor my domains via
[zabbix](http://www.zabbix.com/). It expects one parameter the
hostname to check. It will determine the nameserver responsible for
the domain and then directly query the nameserver to bypass caches of
your recurive resolver. Finaly the script will check the expiry date
of the first `RRSIG` record it can get and will print days from now
till expiry date. If no `RRSIG` record is present nothing will
returned beside the return code 3.

## Usage

```
Usage: check-rrsig.php [options] <hostname>
Options:
  -r, --resolver <arg>    Use a custom resolver and not the one from /etc/resolv.conf.
  -d, --debug             Print debuging messages to tackle your problems.
  -h, --help              Print this help.
```

## Install

You can get the latest build of check-rrsig from my build server:

https://buildserver.datenknoten.me/job/check-rrsig/lastSuccessfulBuild/artifact/build/check-rrsig.phar

This File has all the depedencies you need. You only need to install
the php interpreter, on debian this would be `php5-cli`. Drop the
binary in `/usr/local/bin` and just use it.

## Building

If you want to make some edits or dont trust me, you can build
`check-rrsig` relativly easy.

First you need to install the dependencies:

* [composer](https://getcomposer.org/download/)
* [box2](https://github.com/box-project/box2)

Then you can do the following steps

* git clone https://github.com/datenknoten/check-rrsig.git
* cd check-rrsig
* mkdir build
* composer install
* box build

Then you have a check-rrsig.phar in the build directory.
