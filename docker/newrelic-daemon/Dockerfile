ARG BASE_IMAGE
#you probably want to have daemon running in the same environment as application..
FROM ${BASE_IMAGE}

ARG NEWRELIC_VERSION
ARG NEWRELIC_ARCHIVE_URL

RUN export NR_INSTALL_USE_CP_NOT_LN=1 \
        && export NR_INSTALL_SILENT=1 \
#install new relic
        && wget -q ${NEWRELIC_ARCHIVE_URL}/newrelic-php5-${NEWRELIC_VERSION}-linux-musl.tar.gz \
        && tar xf newrelic-php5-${NEWRELIC_VERSION}-linux-musl.tar.gz \
        && ./newrelic-php5-${NEWRELIC_VERSION}-linux-musl/newrelic-install install \
        && rm -rf newrelic-php5*tar.gz /usr/local/etc/php/conf.d/newrelic.ini /tmp/* /etc/newrelic/*
COPY newrelic.cfg /etc/newrelic/newrelic.cfg

EXPOSE 13424
CMD /usr/bin/newrelic-daemon --wait-for-port 3s --foreground --watchdog-foreground -c /etc/newrelic/newrelic.cfg