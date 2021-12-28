## Running the test suite

1. Get the local api running: `docker-compose build && docker-compose up`
2. Build the test runner image (only once): `docker build -f testrunner/Dockerfile -t mmtest .`
3. run the tests `docker run mmtest`
