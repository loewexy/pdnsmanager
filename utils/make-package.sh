#!/bin/bash

mkdir "$1"

# Copy backend
rsync -a backend/src/ "$1"/backend
rm -f "$1"/backend/config/ConfigUser.php

# Copy frontend
cd frontend/
npm run build --prod
cd ..

rsync -a frontend/dist/ "$1"/frontend

cd frontend/
rm -rf dist/
cd ..

# Copy additional files
cp LICENSE "$1"
cp README.md "$1"

# Add version info
cat << EOF > "$1"/version.json
{
    "version": "$2"
}
EOF

# Create archive
tar -czf "$1".tar.gz "$1"

# Remove temp data
rm -rf "$1"

exit 0


