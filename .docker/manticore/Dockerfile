FROM debian:buster-slim

RUN apt update
RUN apt install -y wget cron curl mariadb-client gnupg
RUN wget https://repo.manticoresearch.com/manticore-repo.noarch.deb
RUN dpkg -i manticore-repo.noarch.deb
RUN apt update
RUN apt install -y manticore=5.0.2-220530-348514c86 manticore-server=5.0.2-220530-348514c86 manticore-tools=5.0.2-220530-348514c86 manticore-dev=5.0.2-220530-348514c86 manticore-server-core=5.0.2-220530-348514c86  manticore-common=5.0.2-220530-348514c86


RUN mkdir -pv /var/lib/manticore/data/ /var/lib/manticore/conf/

VOLUME /var/lib/manticore/data/
VOLUME /var/lib/manticore/conf/

COPY crontab /var/lib/manticore/conf/
COPY entrypoint.sh /var/lib/manticore/conf/

# redirect logs to stdout
RUN ln -sv /dev/stdout /var/log/query.log \
    && ln -sv /dev/stdout /var/log/manticore/searchd.log.

EXPOSE 36307

ENTRYPOINT [ "/bin/bash", "/var/lib/manticore/conf/entrypoint.sh" ]
