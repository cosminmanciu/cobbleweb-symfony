# Install mail-related dependencies
RUN apk add --no-cache openssl

# Install Symfony Mailer dependencies
RUN docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install opcache
