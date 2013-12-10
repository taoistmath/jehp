#!/bin/bash

usage ()
{
cat << EOF
usage: $0 options
 
Use this script to pull the depth 0 xml from a given Jenkins server
  
OPTIONS:
  -h  Help - Show this message
  -u  User name
  -p  Password
  -s  Site URL for jenkins server
  -v  Verbose

EOF
}

USER=
PASSWORD=
SITE=

while getopts "hu:p:s:v" OPTION
do
  case $OPTION in
    h)
      usage
      exit 1
      ;;
    u)
      USER=${OPTARG}
      ;;
    p)
      PASSWORD=${OPTARG}
      ;;
    s)
      SITE=${OPTARG}
      ;;
    v)
      VERBOSE=1
      ;;
    ?)
      usage
      exit 1
      ;;
    \?)
      echo "Invalid option: -$OPTARG" >&2
      exit 1
      ;;
    :)
      echo "Option -$OPTARG requires an argument." >&2
      exit 1
      ;;
  esac
done
if [[ -z $USER ]] || [[ -z $PASSWORD ]] || [[ -z $SITE ]] 
  then
    usage
    echo "\$USER=$USER";
    echo "\$PASSWORD=$PASSWORD";
    echo "\$SITE=$SITE";
    exit 1
  else
    echo "\$USER=$USER";
    echo "\$PASSWORD=$PASSWORD";
    echo "\$SITE=$SITE";

    wget --auth-no-challenge --http-user=$USER --http-password=$PASSWORD http://${SITE}/api/xml -O results/status.xml;
fi
