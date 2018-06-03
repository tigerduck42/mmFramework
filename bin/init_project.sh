#!/bin/bash

CURRENT_DIR=$(pwd)

if [ ! -d init ]; then
  mkdir -p ${CURRENT_DIR}/class
  mkdir -p ${CURRENT_DIR}/template_c
  chmod 777 ${CURRENT_DIR}/template_c

  cp -r ${CURRENT_DIR}/vendor/tigerduck42/mm-framework/default/html ${CURRENT_DIR}
  cp -r ${CURRENT_DIR}/vendor/tigerduck42/mm-framework/default/template ${CURRENT_DIR}
  cp -r ${CURRENT_DIR}/vendor/tigerduck42/mm-framework/default/init ${CURRENT_DIR}

else
  echo Project already set up!
  exit 0;
fi


