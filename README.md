# mapFeeder Connect
A simple user-driven web mapping application for the quick reporting of public generated feedback to an .sql database.

# Instructions for adding new communities/instances:

1. Add entry in the form.html #issue_city select options list that matches up with the URL param for the city= or map=<instance_or_city_name>
2. Add entry in the config.json
3. Copy database: `CREATE DATABASE mfc_new_city_name WITH TEMPLATE mfc_ak_kodiak;`
4. Update the /etc/postgresql/9.3/main/pg_hba.conf for host and localhost to access the new db. And reload the conf with SELECT pg_reload_conf();

5. Optional: for instances with several or changed URL param instance names, you can re-route users by adding entries in the .htaccess file
