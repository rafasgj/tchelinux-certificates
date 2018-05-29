#!/bin/sh

if [ $# -ne 3 ]
then
    echo "usage: `basename $0` organizadores palestrantes participantes"
    exit 1
fi

MKTEMP=`which mktemp || which tempnam`

tmpfile=`eval ${MKTEMP}`

ORG="$1"
PAL="$2"
shift 2

dos2unix ${ORG} 2> /dev/null
dos2unix ${PAL} 2> /dev/null

touch ${tmpfile}
sed "s/^.*$/&x,organizador/" < ${ORG} >> ${tmpfile}
sed "s/^.*$/&x,palestrante/" < ${PAL} >> ${tmpfile}

dos2unix "$1" 2> /dev/null
cat $tmpfile >> "$1"

