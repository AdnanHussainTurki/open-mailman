#!/bin/sh
# Local build + run helper. Usage:
#   ./docker/run_docker.sh            # run (build if image missing)
#   ./docker/run_docker.sh --rebuild  # force rebuild then run
set -e

IMAGE=open-mailman:local
CONTAINER=open-mailman
PORT="${PORT:-8080}"
ENV_FILE=".env.docker"

cd "$(dirname "$0")/.."

if [ ! -f "$ENV_FILE" ]; then
    echo "Missing $ENV_FILE — copy .env.docker.example, fill values (APP_KEY!), then rerun."
    exit 1
fi

if [ "$1" = "--rebuild" ] || ! docker image inspect "$IMAGE" >/dev/null 2>&1; then
    docker build -t "$IMAGE" .
fi

docker rm -f "$CONTAINER" >/dev/null 2>&1 || true

docker run -d \
    --name "$CONTAINER" \
    --env-file "$ENV_FILE" \
    -p "$PORT:80" \
    --add-host=host.docker.internal:host-gateway \
    "$IMAGE"

echo "Running on http://localhost:$PORT (maintenance page first, app follows)"
echo "Logs:   docker logs -f $CONTAINER"
echo "Status: docker exec $CONTAINER supervisorctl status"
