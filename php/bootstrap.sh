#!/bin/bash

cat <<EOF > .env

APP_ENV=dev
APP_SECRET=
DEFAULT_URI=http://localhost
TOKEN=${TOKEN}
BASE_URI=${BASE_URI}
DISCORD_TOKEN=${DISCORD_TOKEN}

EOF

echo ".env file created!"

