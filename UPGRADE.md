# UPGRADE

To 1.0.0

Env needed to deploy

* `APP_ENV`
* `APP_SECRET`
* `DATABASE_URL`
* `DVF_YEAR_AVAILABLE` : `["2016","2017","2018","2019","2020"]`
* `ELASTICSEARCH_CONTACT_INDEX_NAME` : `fastmdb-contact`
* `ELASTICSEARCH_DVF_INDEX_NAME` : `fastmdb-dvf`
* `ELASTICSEARCH_HOST`
* `MAILER_DSN`

For front

* `GOOGLE_STREET_VIEW_API`
* `IGN_API_KEY`
* `NODE_ENV`

Command to run after deploy

* `heroku run php bin/console fast-mdb:init:elasticsearch-index`
* `heroku run php bin/console fast-mdb:import:dvf 2020 https://www.data.gouv.fr/fr/datasets/r/90a98de0-f562-4328-aa16-fe0dd1dca60f 62`
* `heroku run php bin/console fast-mdb:import:dvf 2019 https://www.data.gouv.fr/fr/datasets/r/3004168d-bec4-44d9-a781-ef16f41856a2 62`
* `heroku run php bin/console fast-mdb:import:dvf 2018 https://www.data.gouv.fr/fr/datasets/r/1be77ca5-dc1b-4e50-af2b-0240147e0346 62`
* `heroku run php bin/console fast-mdb:import:dvf 2017 https://www.data.gouv.fr/fr/datasets/r/7161c9f2-3d91-4caf-afa2-cfe535807f04 62`
* `heroku run php bin/console fast-mdb:import:dvf 2016 https://www.data.gouv.fr/fr/datasets/r/0ab442c5-57d1-4139-92c2-19672336401c 62`
