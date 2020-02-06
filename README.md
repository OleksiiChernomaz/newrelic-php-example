# newrelic-php-example
Just as an example of the New Relic (NR) daemon with new relic php agent running in the same container, when agent starts daemon on demand.

Most probably at some point this issues would be solved. A bunch of issues were reported long ago when docker environment started
 to be more popular, but newrelic was not prepared for it and for a while ignored comments in the community or provided just not working solutions.

But trend has started to improve and, as far as I see, NewRelic Team tries to handle issues, but we have what we have right now and lets try to deal with it. 

I hope it will help other people, who has faced similar problems, to solve them at least using similar hack.

I want to try to illustrate a registration problem between NR agent and NR daemon which cases data loss with explanation and workaround for it. Note, issue symptoms are visible in the NR log, 
BUT in debug mode only.

Here are our 2 evils, hiding and quietly waiting for us in the dark "debug" corner:
```bash
debug: unable to begin transaction: no daemon connection
debug: APPINFO reply unknown app='newrelic-testing'
``` 

# 1. A quick start guide

## 1.1 Configure environment
```bash
cp ./.env.dist ./.env
```
and put your licence, enable NR and set NR version

## 1.2 Run application without hack

This command supposed to produce 1 custom event and 1 error in APM:

```bash
(export NEWRELIC_USE_HACK=0 && ./run.sh)
```

## 1.2 Run application with NR hack
```bash
(export NEWRELIC_USE_HACK=1 && ./run.sh)
```


# 2. Explanation of the issue:

When you run application and rely on the daemon autostart and do it without additional hack, you will see in the application log:


```bash
Creating network "newrelic-testing_default" with the default driver
#############################################################################################################
Application execution here
2020-02-06 17:17:22.257 +0000 (6 6) info: attempt daemon connection via '@newrelic-daemon'
2020-02-06 17:17:22.257 +0000 (6 6) info: New Relic 9.6.1.256 ("mustardstain" - "b6618b7ea444") [daemon='@newrelic-daemon'  php='7.4.2' zts=no sapi='cli'  pid=6 ppid=1 uid=0 euid=0 gid=0 egid=0 backtrace=no startup=agent os='Linux' rel='4.19.76-linuxkit' mach='x86_64' ver='#1 SMP Thu Oct 17 19' node='c5a3dcd13824']
2020-02-06 17:17:22.263 +0000 (6 6) info: spawned daemon child pid=7
2020/02/06 17:17:22.472345 (7) Info: New Relic daemon version 9.6.1.256-b6618b7ea444 [listen="@newrelic-daemon" startup=agent pid=7 ppid=6 uid=0 euid=0 gid=0 egid=0 runtime="go1.9.7" GOMAXPROCS=6 GOOS=linux GOARCH=amd64]
2020/02/06 17:17:22.472426 (7) Debug: ARGV[0]: /usr/bin/newrelic-daemon
2020/02/06 17:17:22.472452 (7) Debug: ARGV[1]: --agent
2020/02/06 17:17:22.472484 (7) Debug: ARGV[2]: --pidfile
2020/02/06 17:17:22.472538 (7) Debug: ARGV[3]: /var/run/newrelic-daemon.pid
2020/02/06 17:17:22.472560 (7) Debug: ARGV[4]: --logfile
2020/02/06 17:17:22.472950 (7) Debug: ARGV[5]: /dev/stdout
2020/02/06 17:17:22.472967 (7) Debug: ARGV[6]: --loglevel
2020/02/06 17:17:22.472988 (7) Debug: ARGV[7]: debug
2020/02/06 17:17:22.473041 (7) Debug: ARGV[8]: --port
2020/02/06 17:17:22.473127 (7) Debug: ARGV[9]: @newrelic-daemon
2020/02/06 17:17:22.473156 (7) Debug: ARGV[10]: --wait-for-port
2020/02/06 17:17:22.473173 (7) Debug: ARGV[11]: 0s
2020/02/06 17:17:22.473216 (7) Debug: ARGV[12]: --define
2020/02/06 17:17:22.473281 (7) Debug: ARGV[13]: utilization.detect_aws=true
2020/02/06 17:17:22.474071 (7) Debug: ARGV[14]: --define
2020/02/06 17:17:22.474100 (7) Debug: ARGV[15]: utilization.detect_azure=true
2020/02/06 17:17:22.474129 (7) Debug: ARGV[16]: --define
2020/02/06 17:17:22.474148 (7) Debug: ARGV[17]: utilization.detect_gcp=true
2020/02/06 17:17:22.474177 (7) Debug: ARGV[18]: --define
2020/02/06 17:17:22.474227 (7) Debug: ARGV[19]: utilization.detect_pcf=true
2020/02/06 17:17:22.474285 (7) Debug: ARGV[20]: --define
2020/02/06 17:17:22.474309 (7) Debug: ARGV[21]: utilization.detect_docker=true
2020/02/06 17:17:22.474378 (7) Debug: process role is progenitor
2020-02-06 17:17:22.480 +0000 (6 6) debug: MINIT processing done
2020-02-06 17:17:22.480 +0000 (6 6) debug: late_init called from pid=6
2020-02-06 17:17:22.481 +0000 (6 6) warning: daemon connect(fd=5 uds=@newrelic-daemon) returned -1 errno=ECONNREFUSED. Failed to connect to the newrelic-daemon. Please make sure that there is a properly configured newrelic-daemon running. For additional assistance, please see: https://newrelic.com/docs/php/newrelic-daemon-startup-modes
2020-02-06 17:17:22.482 +0000 (6 6) debug: unable to begin transaction: no daemon connection
Hello from the application. I hope my errors and events were send to the daemon and appear in the NR2020-02-06 17:17:22.482 +0000 (6 6) debug: MSHUTDOWN processing started
Removing network newrelic-testing_default
```

That's it. Nothing was send, but application worked. 

> ### Unreliable reliability for console commands:
>
> Critical connection error is hidden in debug messages, so that
> In case if you rely a lot on the NewRelic Insights for example, e.g let's assume you use it for monitoring\alerting, so on, then you might be in trouble. Unless you implement additional monitoring for a monitoring tool.
>
>Fun part for me was there, that once agent fails, it does not try to re-connect anymore. So in case, if your application runs for 10-20 minutes, or 1-2 hours.. then, well, no luck...
>

> ### Good credits:
>
> Problem was not related to web clients. But affects commands, which executed as a separate command in console inside docker.

So yes, you are in trouble 2 times, because critical error about absence of connection has a "debug" level, so that either you set on prod "debug" mode for NR and suffer from tons of log messages, OR you will never see it.


## 2.1 What does happen behind the scene:

Actually, it seems that issue is quite simple. Agent tries to start the daemon, but to spin it up takes time. Agent is a lazy guy and does not care, so it gives up immediately and decides to have a nap ;). 

It seems to have a buffer internally, otherwise I can not explain incredible memory leaks, which has affected all the `9.X` versions, which are so far not solved.

## 2.2 What happens with enabled hack:
```bash
Creating network "newrelic-testing_default" with the default driver
#############################################################################################################
Activating newRelic for app newrelic-testing, Here hack comes (part 1): NR spin up
#####################################################
Iteration 1
2020-02-06 17:31:22.682 +0000 (8 8) info: attempt daemon connection via '@newrelic-daemon'
2020-02-06 17:31:22.682 +0000 (8 8) info: New Relic 9.6.1.256 ("mustardstain" - "b6618b7ea444") [daemon='@newrelic-daemon'  php='7.4.2' zts=no sapi='cli'  pid=8 ppid=1 uid=0 euid=0 gid=0 egid=0 backtrace=no startup=agent os='Linux' rel='4.19.76-linuxkit' mach='x86_64' ver='#1 SMP Thu Oct 17 19' node='003bb56d3018']
2020-02-06 17:31:22.683 +0000 (8 8) info: spawned daemon child pid=9
2020/02/06 17:31:22.778960 (9) Info: New Relic daemon version 9.6.1.256-b6618b7ea444 [listen="@newrelic-daemon" startup=agent pid=9 ppid=8 uid=0 euid=0 gid=0 egid=0 runtime="go1.9.7" GOMAXPROCS=6 GOOS=linux GOARCH=amd64]
2020/02/06 17:31:22.779026 (9) Debug: ARGV[0]: /usr/bin/newrelic-daemon
2020/02/06 17:31:22.779042 (9) Debug: ARGV[1]: --agent
2020/02/06 17:31:22.779063 (9) Debug: ARGV[2]: --pidfile
2020/02/06 17:31:22.779108 (9) Debug: ARGV[3]: /var/run/newrelic-daemon.pid
2020/02/06 17:31:22.779129 (9) Debug: ARGV[4]: --logfile
2020/02/06 17:31:22.779145 (9) Debug: ARGV[5]: /dev/stdout
2020/02/06 17:31:22.779166 (9) Debug: ARGV[6]: --loglevel
2020/02/06 17:31:22.779180 (9) Debug: ARGV[7]: debug
2020/02/06 17:31:22.779201 (9) Debug: ARGV[8]: --port
2020/02/06 17:31:22.779223 (9) Debug: ARGV[9]: @newrelic-daemon
2020/02/06 17:31:22.779329 (9) Debug: ARGV[10]: --wait-for-port
2020/02/06 17:31:22.779342 (9) Debug: ARGV[11]: 0s
2020/02/06 17:31:22.779365 (9) Debug: ARGV[12]: --define
2020/02/06 17:31:22.779411 (9) Debug: ARGV[13]: utilization.detect_aws=true
2020/02/06 17:31:22.779436 (9) Debug: ARGV[14]: --define
2020/02/06 17:31:22.779459 (9) Debug: ARGV[15]: utilization.detect_azure=true
2020/02/06 17:31:22.779682 (9) Debug: ARGV[16]: --define
2020/02/06 17:31:22.779712 (9) Debug: ARGV[17]: utilization.detect_gcp=true
2020/02/06 17:31:22.779728 (9) Debug: ARGV[18]: --define
2020/02/06 17:31:22.779742 (9) Debug: ARGV[19]: utilization.detect_pcf=true
2020/02/06 17:31:22.779781 (9) Debug: ARGV[20]: --define
2020/02/06 17:31:22.779806 (9) Debug: ARGV[21]: utilization.detect_docker=true
2020/02/06 17:31:22.779836 (9) Debug: process role is progenitor
2020-02-06 17:31:22.784 +0000 (8 8) debug: MINIT processing done
2020-02-06 17:31:22.785 +0000 (8 8) debug: late_init called from pid=8
2020-02-06 17:31:22.786 +0000 (8 8) warning: daemon connect(fd=4 uds=@newrelic-daemon) returned -1 errno=ECONNREFUSED. Failed to connect to the newrelic-daemon. Please make sure that there is a properly configured newrelic-daemon running. For additional assistance, please see: https://newrelic.com/docs/php/newrelic-daemon-startup-modes
2020-02-06 17:31:22.786 +0000 (8 8) debug: unable to begin transaction: no daemon connection
2020-02-06 17:31:22.786 +0000 (8 8) debug: MSHUTDOWN processing started
#####################################################
Iteration 2
2020-02-06 17:31:24.809 +0000 (36 36) info: attempt daemon connection via '@newrelic-daemon'
2020-02-06 17:31:24.809 +0000 (36 36) info: New Relic 9.6.1.256 ("mustardstain" - "b6618b7ea444") [daemon='@newrelic-daemon'  php='7.4.2' zts=no sapi='cli'  pid=36 ppid=1 uid=0 euid=0 gid=0 egid=0 backtrace=no startup=agent os='Linux' rel='4.19.76-linuxkit' mach='x86_64' ver='#1 SMP Thu Oct 17 19' node='003bb56d3018']
2020-02-06 17:31:24.809 +0000 (36 36) debug: MINIT processing done
2020-02-06 17:31:24.809 +0000 (36 36) debug: late_init called from pid=36
2020-02-06 17:31:24.810 +0000 (36 36) debug: added app='newrelic-testing' license='**...**'
2020-02-06 17:31:24.811 +0000 (36 36) debug: APPINFO reply unknown app='newrelic-testing'
2020-02-06 17:31:24.811 +0000 (36 36) debug: unable to begin transaction: app 'newrelic-testing' is unknown
2020-02-06 17:31:24.812 +0000 (36 36) debug: MSHUTDOWN processing started
2020-02-06 17:31:24.812 +0000 (36 36) debug: closed daemon connection fd=4
#############################################################################################################
Application execution here
2020-02-06 17:31:26.831 +0000 (39 39) info: attempt daemon connection via '@newrelic-daemon'
2020-02-06 17:31:26.831 +0000 (39 39) info: New Relic 9.6.1.256 ("mustardstain" - "b6618b7ea444") [daemon='@newrelic-daemon'  php='7.4.2' zts=no sapi='cli'  pid=39 ppid=1 uid=0 euid=0 gid=0 egid=0 backtrace=no startup=agent os='Linux' rel='4.19.76-linuxkit' mach='x86_64' ver='#1 SMP Thu Oct 17 19' node='003bb56d3018']
2020-02-06 17:31:26.831 +0000 (39 39) debug: MINIT processing done
2020-02-06 17:31:26.831 +0000 (39 39) debug: late_init called from pid=39
2020-02-06 17:31:26.832 +0000 (39 39) debug: added app='newrelic-testing' license='**...**'
2020-02-06 17:31:26.835 +0000 (39 39) debug: APPINFO reply connected
2020-02-06 17:31:26.836 +0000 (39 39) debug: APPINFO reply full app='newrelic-testing' agent_run_id=*******
2020-02-06 17:31:26.836 +0000 (39 39) debug: Adaptive sampling configuration. Connect: 1581010285000000 us. Frequency: 60000000 us. Target: 10.
2020-02-06 17:31:26.837 +0000 (39 39) debug: 'WT_IS_FILENAME & SCRIPT_FILENAME' naming is './index.php'
2020-02-06 17:31:26.837 +0000 (39 39) debug: CLI SAPI: marking txn as background job
Hello from the application. I hope my errors and events were send to the daemon and appear in the NR2020-02-06 17:31:26.838 +0000 (39 39) debug: txn naming freeze
2020-02-06 17:31:26.839 +0000 (39 39) debug: MSHUTDOWN processing started
2020-02-06 17:31:26.839 +0000 (39 39) debug: closed daemon connection fd=4
#############################################################################################################
Here hack comes (part 2): let daemon flush data
Removing network newrelic-testing_default
```

### 2.2.1 First Hit: no connection
So, now we have more time on startup and try to hit more times agent to ping daemon to actually do smth. So what is remarkable in the log?:
> debug: unable to begin transaction: no daemon connection

This is our lovely first message from the first test. First hit -> first failure.

### 2.2.2 Second Hit: application unknown
Let's continue cycle with hit2:
> debug: unable to begin transaction: app 'newrelic-testing' is unknown

Yay, we have moved forward from a dead point and can see smth new. At least we have stopped to fail on connection, but now we fail with unknown application.

This is actually weird for me, because one of the required parameters in the newrelic.ini is `newrelic.appname` already defined, 
so I do not know why it's not used when agent establishes connection to daemon during first hit. 

I assume that during this moment daemon tries to send request to the remote server to
make a registration of the application name, but because of network latencies it can not fetch info so fast. 
I was lazy to confirm this assumption with wireshark, so lets keep it as a mystery - it does not change too much for us anyways.


> ## Remark about application name
> Note, I use an application name, which is well known to the NR server: I did lots of the tests, 
> so that at the moment of the test execution, this application was registered for a while.

But lets continue, We have finished 2 cycles already. 2 hits were failed to NewRelic daemon. Time to execute application:

> debug: APPINFO reply connected

hell yeah. It's connected and finally takes data from the application.

BUT we are not finished. Application execution was send into the subshell, which is was replaced by command forwarded into exec.
After application is finished, you need to ensure that daemon actually has time to slush data. On daemons, which had 
lower versions than 9.X you had to wait for 60 seconds until harvesting cycle is finished, see message in the log: 

> Adaptive sampling configuration. Connect: 1581010285000000 us. Frequency: 60000000 us. Target: 10)


> ### Harvesting cycle data loose magico fixed in 9.X
>
> Starting from versions 9.X you, finally, can:
>
>   * call stop\kill process (idea is to send it correct SIGTERM) and daemon finalize it's cycle
>   * you can reconfigure harvesting cycle in newrelic.ini.
> 
> Before, you could not do anything there: you had to wait or accept fact that you gonna loose last 60 seconds.



# 3. Another attempts to make workarounds

## 3.1 timeout configuration options in newrelic.ini file
I have also tried to use settings: 
```
newrelic.daemon.app_connect_timeout=5s
newrelic.daemon.start_timeout=5s
```
but, then application used ALL the available memory from the server (especially heavier load and on we b clients). 
E.g by default servers used 5-10% of memory, and with enabled options were consumed all the 100% (was used with Symfony application).

## 3.2 run newrelic-daemon separately from app in container
Starting from 9.X finally was available to use "tcp" to connect to the daemon, which is theoreticaly, had to unlock us to use 
few containers and link them together. In this case your application container with agent depends on the newrelic-daemon container. So that
you could implement healthcheck of daemon container, add there waiting timeouts, so on. Let's say make setup more clean from hacks, BUT

I faced same issue. Because I started to use timeouts in connections, servers are exploded with memory from the agent side almost immediately. It grabed all the 
avaialble memory and basically killed application, so my test failed. Maybe someone else is more lucky on that.

## 3.3 Run daemon in same container as app, but start it manually
Well, same issue. It starts, but daemon reports "application name is unknown". So I took out this idea for now, because it does not solve 
the problem, but only moves it to another place. 