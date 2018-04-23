#!/bin/bash

DIRECTORY=$1

if [ "$#" -eq 0 ]
then
 echo "Missing directory argument"
 exit 1
fi

CHECK_RESULT_CODE_TEXT=`vendor/bin/composer-require-checker check $DIRECTORY/composer.json --ignore-parse-errors`
CHECK_RESULT_CODE=$?
if [ "$CHECK_RESULT_CODE" -eq "0" ]
then
 exit 0
fi

SYMBOLS_FOUND=`echo "$CHECK_RESULT_CODE_TEXT" | tail -n '+6' | head -n '-1' | sed 's/| //' | sed 's/ .*$//' | sort`

IGNORE_FILE=$DIRECTORY/composer-check-dependencies-ignore.txt

if [ ! -f $IGNORE_FILE ]
then
 echo "Following unknown symbols were found:"
 echo ""
 echo "$SYMBOLS_FOUND"
 exit $CHECK_RESULT_CODE
else
 SYMBOLS_NOT_IGNORED=`echo "$SYMBOLS_FOUND" | comm -23 - $IGNORE_FILE`

 if [ "$SYMBOLS_NOT_IGNORED" == "" ]
 then
  exit 0
 else
  echo "Following unknown symbols were found:"
  echo ""
  echo "$SYMBOLS_NOT_IGNORED"
  exit $CHECK_RESULT_CODE
 fi
fi
