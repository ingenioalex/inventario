#!/usr/bin/env bash
set -o errexit
pip install -r requirements.txt
python -c "import database as db; db.init_db(); print('BD lista:', db.motor_bd())"
