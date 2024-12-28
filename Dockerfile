FROM ubuntu:24.04

LABEL maintainer="Taylor Otwell"

ARG WWWGROUP
ARG NODE_VERSION=22
ARG MYSQL_CLIENT="mysql-client"
ARG POSTGRES_VERSION=17
ARG URL_INSTALL_CLIENT_BASIC='https://download.oracle.com/otn_software/linux/instantclient/217000/instantclient-basic-linux.x64-21.7.0.0.0dbru.zip'
ARG URL_INSTALL_CLIENT_SDK='https://download.oracle.com/otn_software/linux/instantclient/217000/instantclient-sdk-linux.x64-21.7.0.0.0dbru.zip'


WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="sail"

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN echo "Acquire::http::Pipeline-Depth 0;" > /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::http::No-Cache true;" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::BrokenProxy    true;" >> /etc/apt/apt.conf.d/99custom

RUN apt-get update && apt-get upgrade -y \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python3 dnsutils librsvg2-bin fswatch ffmpeg nano  \
    && curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c' | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y php8.4-cli php8.4-dev \
       php8.4-pgsql php8.4-sqlite3 php8.4-gd \
       php8.4-curl php8.4-mongodb \
       php8.4-imap php8.4-mysql php8.4-mbstring \
       php8.4-xml php8.4-zip php8.4-bcmath php8.4-soap \
       php8.4-intl php8.4-readline \
       php8.4-ldap \
       php8.4-msgpack php8.4-igbinary php8.4-redis \
#       php8.4-swoole \
       php8.4-memcached php8.4-pcov php8.4-imagick php8.4-xdebug \
    && curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y nodejs \
    && npm install -g npm \
    && npm install -g pnpm \
    && npm install -g bun \
    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | tee /etc/apt/keyrings/yarn.gpg >/dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/yarn.gpg] https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
    && curl -sS https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor | tee /etc/apt/keyrings/pgdg.gpg >/dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/pgdg.gpg] http://apt.postgresql.org/pub/repos/apt noble-pgdg main" > /etc/apt/sources.list.d/pgdg.list \
    && apt-get update \
    && apt-get install -y yarn \
    && apt-get install -y $MYSQL_CLIENT \
    && apt-get install -y postgresql-client-$POSTGRES_VERSION \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN apt install -y unzip curl

RUN curl -O http://launchpadlibrarian.net/646633572/libaio1_0.3.113-4_amd64.deb

RUN dpkg -i libaio1_0.3.113-4_amd64.deb

RUN mkdir -p /opt/oracle && \
    curl -o /opt/oracle/instantclient-basic-linux.zip "$URL_INSTALL_CLIENT_BASIC" && \
    curl -o /opt/oracle/instantclient-sdk-linux.zip "$URL_INSTALL_CLIENT_SDK" && \
    unzip /opt/oracle/instantclient-basic-linux.zip -d /opt/oracle && \
    unzip /opt/oracle/instantclient-sdk-linux.zip -d /opt/oracle && \
    rm -f /opt/oracle/instantclient-basic-linux.zip /opt/oracle/instantclient-sdk-linux.zip && \
    mv /opt/oracle/instantclient_* /opt/oracle/instantclient

# Configuration du système pour Oracle Instant Client
RUN echo "/opt/oracle/instantclient" > /etc/ld.so.conf.d/oracle-instantclient.conf && ldconfig

# Création des répertoires de configuration PHP pour FPM et CLI
RUN mkdir -p /etc/php/8.4/fpm/conf.d /etc/php/8.4/cli/conf.d

# Installation et activation de l'extension PHP OCI8
RUN echo "instantclient,/opt/oracle/instantclient" | pecl install oci8 && \
    echo "extension=oci8.so" >> /etc/php/8.4/cli/php.ini && \
    echo "extension=oci8.so" >> /etc/php/8.4/fpm/php.ini

#Mise à jour de composer
#Copier les fichiers de l'application dans le conteneur

COPY . /var/www/html

RUN composer install

RUN composer upgrade


# Nettoyage
RUN apt-get -y autoremove && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Vérifications
#RUN php -m | grep oci8

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.4

RUN userdel -r ubuntu
RUN groupadd --force -g $WWWGROUP sail
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail

COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/8.4/cli/conf.d/99-sail.ini
RUN chmod +x /usr/local/bin/start-container

EXPOSE 80/tcp

ENTRYPOINT ["start-container"]
