<?php
#!/usr/bin/env php

for ($i=0; $i<20; $i++) {
    trigger_error('TEST_ERROR. ID'.uniqid().'; Iteration:'.$i."\n", E_USER_WARNING);
    sleep(1);
}
trigger_error('To exit triggered E_USER_ERROR from PHP', E_USER_ERROR);