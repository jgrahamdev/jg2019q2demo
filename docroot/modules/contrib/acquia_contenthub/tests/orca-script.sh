#!/usr/bin/env bash
# This script is copied from orca/bin/travis/script because ORCA currently combines fixture init and tests run.
# We need to inject our dev modules before tests run, which requires essentially forking their script and injecting
# our update hook into the middle of it.

if [[ -z "$1" ]]; then
  echo "Missing required SUT argument, e.g.:"
  echo "$0 drupal/example"
  exit 127
fi

function run {
  echo "> $@"
  eval "$@"
}

shopt -s extglob
set -e

ORCA_ROOT="$(cd "$(dirname "$0")/../../orca/" && pwd)"
SCRIPT_ROOT="$(cd "$(dirname "$0")" && pwd)"
ORCA_FIXTURE=${ORCA_FIXTURE:=any}

## Perform static code analysis.
[[ ${ORCA_FIXTURE} == @(any|none) ]] && run ${ORCA_ROOT}/bin/orca static-analysis:run ./

# Run isolated tests (in the absence of other Acquia packages).
if [[ ${ORCA_FIXTURE} == @(any|sut-only) ]]; then
  run ${ORCA_ROOT}/bin/orca fixture:init -f --sut=$1 --sut-only
  run ${SCRIPT_ROOT}/update.sh
  run ${ORCA_ROOT}/bin/orca tests:run --sut=$1 --sut-only
fi

# Run integrated tests (in the presence of other Acquia packages).
if [[ ${ORCA_FIXTURE} == @(any|standard) ]]; then
  run ${ORCA_ROOT}/bin/orca fixture:init -f --sut=$1
  #run ${ORCA_ROOT}/bin/orca tests:run --sut=$1
fi
