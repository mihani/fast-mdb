FROM node:14.16-alpine

RUN apk add --no-cache \
  nano \
  bash \
  bash-doc \
  bash-completion \
  py-pip \
  build-base

RUN apk add --no-cache su-exec git python3 && \
  addgroup bar && \
  adduser -D -h /home -s /bin/sh -G bar foo

ADD entrypoint.sh /entrypoint

WORKDIR /srv
ENTRYPOINT ["/entrypoint"]
