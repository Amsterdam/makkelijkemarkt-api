docker exec makkelijkemarkt-api_web_1 /bin/bash testrunner/clear_db.sh
docker exec makkelijkemarkt-api_database_1 /bin/bash -c "psql -U makkelijkemarkt -c 'CREATE EXTENSION IF NOT EXISTS \"uuid-ossp\"'"
docker exec makkelijkemarkt-api_web_1 /bin/bash testrunner/load_db.sh
docker run mmtest
