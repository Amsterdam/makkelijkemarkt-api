WORKDIR=$(pwd)
TEST_DIR=$WORKDIR/tests
SRC_DIR=$WORKDIR/src

docker run -v $TEST_DIR:/app/tests -v $SRC_DIR:/app/src --name mmtest mmtest
