# Fast MDB

## Stack

* PHP : 8.0.3
* Composer : 2.0.8
* XDebug : 3.0.2
* Docker compose : 3.7
* Nginx 1.20.0
* Mysql : 8.0

## Install

Before `make install` you need a docker hostmanager (e.g dkarlovi/docker-hostmanager)

`sudo docker run -d --name docker-hostmanager --restart=always -v /var/run/docker.sock:/var/run/docker.sock -v /etc/hosts:/hosts dkarlovi/docker-hostmanager`

## User Manual

To create the first admin account need to create user in db and use forgot password process.
