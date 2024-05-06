# mapFeeder Connect
A simple user-driven web mapping application for the quick reporting of public generated feedback to an .sql database.

# Instructions for adding new communities/instances:

1. Add entry in the form.html #issue_city select options list that matches up with the URL param for the city= or map=<instance_or_city_name>
2. Add entry in the config.json
3. Copy database: `CREATE DATABASE mfc_new_city_name WITH TEMPLATE mfc_ak_kodiak;`
4. Update the /etc/postgresql/9.3/main/pg_hba.conf for host and localhost to access the new db. And reload the conf with SELECT pg_reload_conf();

5. Optional: for instances with several or changed URL param instance names, you can re-route users by adding entries in the .htaccess file


# Instructions for exporting comments

1. Open a connection to the database and populate the geom column with this command 
`UPDATE workorder.mfc_data SET the_geom = ST_SetSRID(ST_MakePoint(lng, lat), 4326);`
*This could be automated via trigger or explicitly in the serverside code*



## Via QGIS into geojson format
1. open workorder.mfc_data table in QGIS
2. Right click on the layer in the layer menu > click export > select format as .shp *make sure the projection is set to the desired one*


## Via QGIS into .csv / .excel format (includes lat lng columns)
2. Right click on the layer in the layer menu > click export > select format as .csv 
3. Under the geometry dropdown select no geom.
4. Open the .csv in Excel
5. Save As > .xlsx format


# Database Backup
1. Run this command on the server
`/usr/bin/pg_dump <name of database> -f <filename you want for the backup file>.backup -Fc -U postgres --no-owner`




