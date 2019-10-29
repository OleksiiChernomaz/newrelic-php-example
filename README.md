# newrelic-php-example
just an example of separated new relic daemon with new relic php agent

Also helps to illustrate registration problem between NR agent and NR daemon which cases data loss. 

# To run
```bash
./run.sh
```
># Warning! 
>
> NewRelic (NR) agent will be able to send data to daemon 
> from short running scripts only starting from version `9.3.0.246`
>
> And it works ONLY when you have enabled `newrelic.daemon.app_connect_timeout` and defined to at least few seconds.