# newrelic-php-example
just an example of separated new relic daemon with new relic php agent

Also helps to illustrate registration problem between NR agent and NR daemon which cases data loss. 

# To run
```bash
docker-compose build && docker-compose run --rm app
```

# To see all runtime logs, use
```bash
docker-compose logs -f
```
