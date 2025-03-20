.PHONY: manifests deploy

dc = docker compose

ENVIRONMENT ?= local
HELM_ARGS = manifests/chart \
	-f manifests/values.yaml \
	-f manifests/env/${ENVIRONMENT}.yaml \
	--set image.tag=${VERSION}

REGISTRY ?= 127.0.0.1:5001
REPOSITORY ?= salmagundi/mm-api
VERSION ?= latest
CHART_VERSION ?= 1.9.1

CONTAINER = mm-api

all: build push deploy fixtures

build:
	$(dc) build

test:
	echo "No tests available"

migrate:
	docker exec -it ${CONTAINER} sh -c "php bin/console --no-interaction doctrine:migrations:migrate"

fixtures: migrate
	docker exec -it ${CONTAINER} sh -c "php bin/console doc:fix:load  --no-interaction --purge-with-truncate"

push:
	$(dc) push

manifests:
	@helm template mm-api $(HELM_ARGS) $(ARGS)

deploy: manifests
	helm upgrade --install mm-api $(HELM_ARGS) $(ARGS)

update-chart:
	rm -rf manifests/chart
	git clone --branch $(CHART_VERSION) --depth 1 git@github.com:Amsterdam/helm-application.git manifests/chart
	rm -rf manifests/chart/.git

clean:
	$(dc) down -v --remove-orphans

reset:
	kubectl delete deployment mm-api-mm-api && kubectl delete deployment mm-api-nginx-mm-api && kubectl delete ingress mm-api-nginx-internal-mm-api && helm uninstall mm-api

refresh: reset build push deploy

build-dev:
	$(dc) -f docker-compose-dev.yml build

dev:
	-docker network create markten
	$(dc) -f docker-compose-dev.yml up -d

dev-down:
	$(dc) -f docker-compose-dev.yml down
