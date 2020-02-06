<?php
#!/usr/bin/env php
newrelic_record_custom_event('test_custom_event', ['msg' => 'this event you must see']);
newrelic_notice_error('Hey this is an error');

echo "Hello from the application. I hope my errors and events were send to the daemon and appear in the NR";
