#!/usr/bin/sh
DIR=$(git rev-parse --show-toplevel)
RELEASE=$(git rev-parse --verify HEAD | cut -c1-8)
echo "var buildVersion = '$RELEASE';" >  $DIR/src/SimpleIT/ClaireExerciseBundle/Resources/public/frontend/js/modules/utils/build.js
echo "parameters:
    version: $RELEASE" > $DIR/app/config/version.yml
