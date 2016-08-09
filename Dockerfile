FROM php:5.6-apache

RUN pecl install xdebug-beta \
    && docker-php-ext-enable xdebug

RUN echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_connect_back=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.default_enable=0" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.coverage_enable=0" >> /usr/local/etc/php/conf.d/xdebug.ini

ENV MAGENTO_VERSION=1.9.2.4

RUN cd /tmp \
    && curl https://codeload.github.com/OpenMage/magento-mirror/tar.gz/$MAGENTO_VERSION -o $MAGENTO_VERSION.tar.gz \
    && tar xf $MAGENTO_VERSION.tar.gz \
    && rm $MAGENTO_VERSION.tar.gz \
    && rm -rf /var/www/html/ \
    && mv magento-mirror-$MAGENTO_VERSION /var/www/html/

RUN cd /tmp \
    && curl -OL https://github.com/alexcheng1982/docker-magento/raw/master/sampledata/magento-sample-data-1.9.1.0.tgz \
    && tar xf magento-sample-data-1.9.1.0.tgz \
    && rm magento-sample-data-1.9.1.0.tgz \
    && cp -R magento-sample-data-1.9.1.0/media/* /var/www/html/media/ \
    && cp -R magento-sample-data-1.9.1.0/skin/* /var/www/html/skin/

RUN mv /var/www/html/errors/local.xml.sample /var/www/html/errors/local.xml

RUN curl -o n98-magerun.phar https://files.magerun.net/n98-magerun.phar \
    && chmod +x n98-magerun.phar

ENV MAGENTO_DATABASE=magento
ENV MAGENTO_DB_USER=root

RUN buildDeps="libxml2-dev" \
    && set -x \
    && apt-get update && apt-get install -y \
        $buildDeps \
        mysql-client-5.5 \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        --no-install-recommends && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install -j$(nproc) pdo_mysql mcrypt soap \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false $buildDeps

ADD ./.ci/ /
RUN chmod +x /*.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]

#COPY ./extension/ /var/www/html/

RUN ln -s /aplazame/app/code/community/Aplazame /var/www/html/app/code/community/Aplazame \
    && ln -s /aplazame/lib/Aplazame /var/www/html/lib/Aplazame \
    && ln -s /aplazame/app/etc/modules/Aplazame_Aplazame.xml /var/www/html/app/etc/modules/Aplazame_Aplazame.xml \
    && ln -s /aplazame/app/design/adminhtml/default/default/layout/aplazame.xml /var/www/html/app/design/adminhtml/default/default/layout/aplazame.xml \
    && ln -s /aplazame/app/design/adminhtml/default/default/template/aplazame /var/www/html/app/design/adminhtml/default/default/template/aplazame \
    && ln -s /aplazame/app/design/frontend/base/default/layout/aplazame.xml /var/www/html/app/design/frontend/base/default/layout/aplazame.xml \
    && ln -s /aplazame/app/design/frontend/base/default/template/aplazame /var/www/html/app/design/frontend/base/default/template/aplazame \
    && mkdir -p /var/www/html/app/locale/es_ES/ \
    && ln -s /aplazame/app/locale/es_ES/Aplazame_Aplazame.csv /var/www/html/app/locale/es_ES/Aplazame_Aplazame.csv \
    && ln -s /aplazame/var/connect /var/www/html/var/connect \
    && chown www-data:www-data -R /var/www/html
