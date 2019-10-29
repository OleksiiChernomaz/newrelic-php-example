#!/usr/bin/env bash
docker-compose build
docker-compose run --rm app
docker-compose logs newrelic-daemon
docker-compose down