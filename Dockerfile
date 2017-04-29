FROM debian:jessie

RUN \
    apt-get update -y && \
    apt-get install -y apache2 php5 && \
    a2enmod rewrite && \
    a2enmod ssl && \
    apt-get autoremove -y && \
    apt-get clean \
    && rm -rf /var/lib/apt/lists


RUN echo Europe/Paris > /etc/timezone && dpkg-reconfigure --frontend noninteractive tzdata

RUN ln -sf /dev/stdout /var/log/apache2/access.log \
    && ln -sf /dev/stderr /var/log/apache2/error.log

ADD ./danaworld /var/www/html/danaworld

ADD ./apache2.conf /etc/apache2/apache2.conf 

RUN chown www-data /var/www/html -R

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
