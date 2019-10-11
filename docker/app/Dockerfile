ARG BASE_IMAGE
FROM ${BASE_IMAGE}

ARG NEWRELIC_VERSION

RUN export NR_INSTALL_USE_CP_NOT_LN=1 \
        && export NR_INSTALL_SILENT=1 \
#install new relic agent and remove daemon
        && wget -q http://download.newrelic.com/php_agent/archive/${NEWRELIC_VERSION}/newrelic-php5-${NEWRELIC_VERSION}-linux-musl.tar.gz \
        && tar xf newrelic-php5-${NEWRELIC_VERSION}-linux-musl.tar.gz \
        && ./newrelic-php5-${NEWRELIC_VERSION}-linux-musl/newrelic-install install \
        && rm -rf newrelic-php5*tar.gz /usr/local/etc/php/conf.d/newrelic.ini /usr/bin/newrelic-daemon

COPY docker-php-entrypoint /usr/local/bin/docker-php-entrypoint
COPY newrelic/newrelic.ini /usr/local/etc/php/conf.d/newrelic.ini

ADD ./src/ /var/app/
WORKDIR /var/app/

CMD php ./error.php
