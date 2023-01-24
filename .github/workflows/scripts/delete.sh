#!/bin/bash -ex

TERMINUS_CMD="$HOME/vendor/bin/terminus"

# Enforce 11-charater limit on multidev names
MULTIDEV="${BRANCH:0:11}"

echo "Deleting $SITE.$MULTIDEV"
$TERMINUS_CMD multidev:delete $SITE.$MULTIDEV --delete-branch -y
