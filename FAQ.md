# Installation and updates

## How can I update the Sen2-Agri system?

If already have a version of the Sen2-Agri system installed, you can easily upgrade it to a newer version by downloading the new installation package and running the update script. To do so, you should navigate to the installation package directory and start the process:

```bash
cd ~/Downloads/Sen2AgriDistribution/install_script
sudo ./update.sh
```

See the [MACCS update section](#how-do-i-install-or-update-maccs) for instructions about updating MACCS.

## How do I install or update `MACCS`?

Starting from version 1.6, MACCS is no longer with the Sen2-Agri installation files. You will have to download it from the CNES website. Steps are detailed in the Software User Manual (p. 30). Basically, you should:

1. Go to <https://logiciels.cnes.fr/content/maccs?language=en>.
1. Click on the "Télécharger" or "Download" button in the tab with the same name
1. Read the license agreement carefully
1. Fill the form with your information using "Sentinel-2 for Agriculture" as the project name
1. Click on the "I accept the license" checkbox and then on the button that appears. The download should now start.

Once you have downloaded the MACCS installation package, you can follow the installation process detailed in its manual. You can find the MACCS manual in the installation package that you just downloaded. The process is summarized here:

```bash
# extract the package
unzip MACCS-site-CNES.zip
tar xf maccs-5.1.5-centos.7.2.1511.x86_64-release-gcc.TGZ.tar
tar xf maccs-cots-4.3.1-rhel.7.2.x86_64-release-gcc.tar

# install its dependencies (COTS)
cd maccs-cots-4.3.1-rhel.7.2.x86_64-release-gcc
sudo ./install-maccs-cots.sh

# install MACCS
cd ..
sudo ./install-maccs-5.1.5-centos.7.2.1511.x86_64-release-gcc.TGZ.sh

# if needed, update the MACCS location in the Sen2-Agri database
sudo -u postgres psql sen2agri -c \
    "update config set value = '/opt/maccs/core/5.1/bin/maccs' \
     where key = 'demmaccs.maccs-launcher';"
```

## How do I stop or restart the Sen2-Agri services?

In some specific scenarios (e.g. when updating MACCS) you might want to temporarily stop the Sen2-Agri services. You can do this with the following command:

```bash
sudo systemctl stop \
    sen2agri-monitor-agent sen2agri-scheduler sen2agri-executor \
    sen2agri-orchestrator sen2agri-http-listener \
    sen2agri-{sentinel,landsat}-downloader.{service,timer} \
    sen2agri-demmaccs.{service,timer}
```

To restart them you can use a similar command:

```bash
sudo systemctl start \
    sen2agri-monitor-agent sen2agri-scheduler sen2agri-executor \
    sen2agri-orchestrator sen2agri-http-listener \
    sen2agri-{sentinel,landsat}-downloader.{service,timer} \
    sen2agri-demmaccs.{service,timer}
```

## Should I add the server host name to /etc/hosts?

There is no need to.

## How can I uninstall the Sen2-Agri system?

1. [Stop the running services](#how-do-i-stop-or-restart-the-sen2-agri-services)
1. Optionally, remove the `PostgreSQL` database
    ```bash
    sudo -u postgres psql -c "drop database sen2agri;"
    ```
1. Stop `SLURM`, `MariaDB` and `PostgreSQL`
    ```bash
    sudo systemctl stop slurmd slurmctld slurmdbd munge mariadb postgresql-9.4
    sudo systemctl disable slurmd slurmctld slurmdbd munge mariadb postgresql-9.4
    ```
1. Uninstall the Sen2-Agri packages
    ```bash
    sudo yum remove \
        sen2agri-processors sen2agri-website sen2agri-downloaders-demmaccs \
        sen2agri-app
    ```
1. Uninstall `SLURM`, `MariaDB` and `PostgreSQL`
    ```bash
    sudo yum remove \
        slurm slurm-munge slurm-slurmdb-direct slurm-sql slurm-torque slurm-sjstat \
        slurm-plugins slurm-slurmdbd slurm-devel slurm-perlapi slurm-sjobexit \
        slurm-pam_slurm munge munge-devel munge-libs mariadb-server mariadb-devel \
        mariadb postgresql94-server postgresql94-contrib postgresql94
    ```
1. Stop and disable the web server
    ```bash
    sudo systemctl stop http
    sudo systemctl disable http
    ```
1. Uninstall the web server and `PHP`
    ```bash
    sudo yum remove httpd php
    ```
1. Uninstall `OTB`
    ```bash
    sudo yum remove otb
    ```
1. Uninstall the other dependencies
    ```bash
    sudo yum remove \
        boost tinyxml tinyxml-devel qt qt5-qtbase qt5-qtbase-postgresql qt-x11 fftw \
        gdal geos libgeotiff libsvm muParser opencv openjpeg2 openjpeg2-tools proj \
        proj-epsg swig gsl
    ```

    Note that other applications on the system might depend on these packages. Before continuing, please make sure to double-check the list of packages to be removed.
1. Uninstall MACCS
    ```bash
    sudo rm -rf /opt/maccs
    ```

    For more information, please consult the MACCS Software User Manual.
1. Optionally, remove the products
    ```bash
    sudo rm -rf /mnt/{archive,scratch,upload}
    ```

# System status

## How can I check the system status?

To check that the system is running, you can use the following commands:

```bash
# check the downloader status
systemctl status sen2agri-{sentinel,landsat}-downloader.{timer,service}

# check the L2A processor status
systemctl status sen2agri-demmaccs.{timer,service}

# check the other services
systemctl status \
    sen2agri-executor sen2agri-http-listener sen2agri-monitor-agent \
    sen2agri-scheduler sen2agri-orchestrator
```

For each of the components above you can use `journalctl` to see the last log messages, e.g.:

```bash
# check today's log messages for the Sentinel-2 downloader
journalctl -u sen2agri-sentinel-downloader --since today

# check the last log messages
journalctl -e
```

You can also run `systemctl` without parameters to quickly check whether any service has crashed.

See also the section on [how to check whether the web site is running](#how-can-i-check-that-the-web-site-is-working).

## How can I check that the web site is working?

You can use the following commands:

```bash
# check the web server status
# if running, it will show up as "active (running)"
systemctl status httpd

# check that the web server is listening on the HTTP port
sudo ss -pantl | grep 80

# open the site, either by navigating to it in a browser
# or from the command line, like below:
xdg-open http://localhost/
```

# Credentials

## What are the default web site credentials?

The default username and password are `sen2agri` and `sen2agri`. Currently, there is no way to change them from the web interface.

To add users or change a password, you can use the following commands:

```bash
# add an user
# role_id is 1 for administrators and 2 for other users
# site_id is a site id to limit access, or NULL
sudo -u postgres psql sen2agri -c \
    "select sp_adduser('login', 'email', 'password', role_id :: smallint, site_id);"

# change the password
# first find out the user id
sudo -u postgres psql sen2agri -c 'select id, login from "user";'
sudo -u postgres psql sen2agri -c \
    "select \"sp_changePassword\"(
                user_id :: smallint,
                'old password',
                'new password');"
```

## What are the PostgreSQL database credentials?

The database connection settings can be found in `/etc/sen2agri/sen2agri.conf`.

# Administration

## How can I pause, resume or delete a running job?

If you want to control a running job, you first need to find out its identifier. For that, you can use the "Monitoring" tab of the web interface. In this context, a job is only created shortly before it starts running. See [the corresponding section](#how-can-i-cancel-a-scheduled-jobtask) for cancelling a scheduled task.

Once you have the `id`, you can use the `job_operations.py` script to cancel it:

```bash
# display the usage information
python job_operations.py -h

python job_operations.py -j JOB_ID -o cancel
python job_operations.py -j JOB_ID -o delete
```

The difference between the two operations is that `cancel` is supposed to stop the job execution, while `delete` will remove its tracking information from the system.

You might also want to pause a running job. In this case, use the `pause` operation. To start the job again, use the `resume` operation.

```bash
python job_operations.py -j JOB_ID -o pause
python job_operations.py -j JOB_ID -o resume
```

Note that the job cancellation, pause and resume functionality is experimental and might not work properly.

## How can I cancel a scheduled job/task?

This operation is not available in the web interface. You will need to remove the scheduled task directly from the database, like below:

```bash
# first find out the scheduled task id
sudo -u postgres psql sen2agri -c \
    "select scheduled_task.id, site.name, scheduled_task.name \
     from scheduled_task \
     inner join site \
         on site.id = site_id;"

# then delete it, don't forget to replace [ID] below
sudo -u postgres psql sen2agri -c \
    "delete \
     from scheduled_task \
     where id = [ID]"
```

## Is there a way to import existing products into the database?

If you have L2A or higher-level products from a different Sen2-Agri installation, you can import them into the database using the `insert_l2a_product_to_db.py` script. You will be able to associate them to an existing site. Note that the script name is misleading, as it should also work for products of a different level.

You can either import a single product or multiple ones. To import every product in a directory, you should add `-m True` in the command line. Otherwise, the path must point to a single product, as the default is `-m False`.

There is no filtering functionality available. For that you will need to either move them to another location, or run the script in the single product mode on each of them. An example is provided below.

```bash
# display the usage information
insert_l2a_product_to_db.py -h

# import one L2A product to the Sudan site
# use the short name from the web interface
insert_l2a_product_to_db.py -s sudan -p l2a -t l2a -d product_path

# import multiple L2As to the France site
insert_l2a_product_to_db.py -m True -s france -p l2a -t l2a \
    -d /mnt/old_products/france/l2a

# import only the Landsat 8 products; their names start with LC8_
for p in /path/to/l2a/LC8_*; do
    insert_l2a_product_to_db.py -s mali -p l2a -t l2a -d ${p}
done
```

## How can I delete a site?

The web interface doesn't allow the user to delete a site. If you want to remove a site, you can use the `delete_site.py` script.

```bash
# display the usage information
delete_site.py -h

# delete the "Belgium" site, but keep its products
# use the short name from the web interface
delete_site.py -s belgium

# delete the "Test Site" site, together with the corresponding products
delete_site.py -s test_site -d false -e false -a false -l false -m false -t false
```

You can find the short name of the site in the web interface or by looking at the path of its products. Generally, the short name is generated by converting the site name to lower case and replacing spaces with underscores.

## Is there a way to exclude some tiles from the download?

The Sen2-Agri system supports defining a list of Sentinel-2 and Landsat 8 tiles for each site. This is useful because the uploaded shapefile might overlap the edges of tiles outside the area of interest.

This functionality is not exposed in the web site. To use it, you must first add the site normally, but leave it disabled on creation. You still need to set the season dates and use a suitable shapefile.

After the site is create, you can use the `filter_site_download_tiles.py` script to select the desired tiles.

```bash
# display the usage information
filter_site_download_tiles.py -h

# disable Landsat 8 for a specific site
filter_site_download_tiles.py -t 2 -s test_czech -e false

# choose specific Sentinel-2 tiles
filter_site_download_tiles.py -t 1 -s test_czech -e true -l "33UVR, 33UWR"
```

After running the script you can enable the site in the web interface.

## Can I move or store the files on a network drive?

The Sen2-Agri system doesn't use any advanced filesystem functionality, so the anything can be used for its temporary and output locations.

The paths can be changed in the database, but if you want to store the files somewhere else, like on a larger drive or network volume, it's probably simpler to either mount the drive to `/mnt/archive`, or replace those with symlinks:

```bash
sudo mv /mnt/archive /mnt/archive_old
sudo ln -s /mnt/large_disk/sen2agri /mnt/archive
```

If you forgot to do this when installing the system, you should [stop the services](#how-do-i-stop-or-restart-the-sen2-agri-services), replace or re-mount the directories and start the system back up after copying the data.

## Can I disable the Landsat 8 download?

If, for some reason you want to disable the Landsat 8 download for all sites, you can run the following command:

```bash
sudo systemctl stop sen2agri-landsat-downloader.{service,timer}
sudo systemctl disable sen2agri-landsat-downloader.timer
```

To re-enable it back again, run:

```bash
sudo systemctl start sen2agri-landsat-downloader.{service,timer}
sudo systemctl enable sen2agri-landsat-downloader.timer
```

[You can also disable the Landsat 8 download for specific sites.](#is-there-a-way-to-exclude-some-tiles-from-the-download)

## How can I use products from a local `L1C` store like the IPT Cloud platform?

Currently, this can be done by modifying the downloader command line and only works for Sentinel-2 products. You can use the following commands to see and edit the `systemd` downloader unit:

```bash
systemctl cat sen2agri-sentinel-downloader
sudo systemctl edit sen2agri-sentinel-downloader
```

In the override file, add a `Service` section with an additional argument in the `ExecStart` clause, e.g.:

```ini
[Service]
ExecStart=/usr/share/sen2agri/sen2agri-downloaders/downloader.py \
    -r s2 -s /usr/share/sen2agri/sen2agri-downloaders/apihub.txt \
    -l local -i /eodata/Sentinel-2/MSI/L1C
```

making sure to replace the source path (after `-i`). The path needs to be quoted if it contains spaces or other special characters.

After saving the file you need restart the service:

```bash
sudo systemctl restart sen2agri-sentinel-downloader
```

To undo this, simply remove the override file, reload and restart:

```bash
sudo rm -rf /etc/systemd/system/sen2agri-sentinel-downloader.service.d
sudo systemctl daemon-reload
sudo systemctl restart sen2agri-sentinel-downloader
```

# Other questions

## Why does the system download products outside the growing season?

You might notice that the system starts downloading products three months before the defined season start date. This behaviour is intended and the extra data is used to initialize the L2A processor. MACCS uses a multi-temporal atmospheric correction method and the output quality (of both the surface reflectance valus and the cloud, cloud shadow, snow and water masks) improves with longer time series. A period of three months is estimated to have at least two or three cloud-free dates for each pixel over a given site.

These L2A products are removed by the system once they are no longer needed, to conserve disk space. Besides the MACCS initialization, they are not used for the higher level products.

The system will also try to download products for two months after the end of the season. This is needed for products acquired during the growing season, but processed or published late by ESA or USGS. Products acquired after the season end date will not be downloaded.

## What is `SLURM` used for?

`SLURM` is a utility that manages resource allocation and job scheduling on a computer cluster. When running an automated or custom (created from the Sen2-Agri web site) job, the system uses it to invoke the processors.  While the Sen2-Agri system can run on a single machine, `SLURM` allows the processing to span multiple computers.

The Sen2-Agri installer configures `SLURM` for a single-machine cluster, but a system administrator can modify these settings as she finds appropriate. The settings include `QoS` (Quality of Service) options that can be used to prioritize certain job types.

Describing the SLURM configuration is out of the scope of this document, but more information is available on the [SLURM web site](https://slurm.schedmd.com/).

Note that execution via SLURM is only performed for scheduled and custom jobs.

## What are the databases used for?

The system stores its configuration and state in a PostgreSQL database. `SLURM` does not support PostgreSQL at this time, so a MariaDB installation is also necessary for its job tracking functionality.

## Is there an easy way to access the database?

You can use the `pgAdmin` application to connect to the database. `pgAdmin` is a free and open-source graphical user interface administration tool to manage PostgreSQL databases. To install it, run

```bash
sudo yum install pgadmin3
```

You can then start it from the applications menu or from a terminal, like below:

```bash
pgadmin &
disown
```

Initially, you will need to create a new connection. The host is `localhost` if `pgadmin` is running on the server, or the server name or address otherwise. You should use the default port (`5432`). Use anything for the connection name, `admin` as the user name and the `sen2agri` password. The database is called `sen2agri`.

## How can I edit files

The base CentOS 7 install has `vi` available. You can install `joe` or `nano`, or `vim` as a friendlier version of `vi`.

# Troubleshooting

## `MACCS` installation fails

If you get an `Error: the MACCS COTS are not installed` error message, the MACCS depedencies were not installed. You should try installing them again.

A common issue is that of pressing `y` at the following prompt:

```text
Ready  (Y/n) ?
```

A lower case `y` will cancel the installation. You should type the upper case letter `Y`.

## Shapefile uploads don't work

You should check the permissions on the `/mnt/upload` directory. The web server user (commonly `apache`) and the Sen2-Agri system user (`sen2agri-service`) need respectively write, and read access.

The easiest way to provide this is to make it world-writable:

```bash
# check the permissions
ls -ld /mnt/upload

# mark the directory world-writable
sudo chmod 777 /mnt/upload
```

## Sentinel-2 downloads fail

The SciHub server (used for searching products) has a request size limit. If the site definition is too complex, its `WKT` representation might be too long. You should try using a simpler polygon.

## The `L4A` and `L4B` processors crash on large sites

The `L4` processors are designed to read the full time series at once, for every Sentinel-2 tile that is processed. Since there can be a relatively large number of products and each of them has multiple files, this means that the processors have to open a lot of files. Unfortunately, Linux systems have very strict (small) limits for the number of open files.

These are the settings that influence this behaviour:

1. System-wide open file limit &mdash; as configured by the `fs.file-max` `sysctl`. This defaults to `13 060 355` in CentOS 7, which should be sufficiently large.

1. Per-user file descriptor limits &mdash; these come in "soft" and "hard" varieties and can be displayed by running `ulimit -n; ulimit -Hn`. The soft limit is applied by default, but processes can raise it up to the value specified by the hard limit. They can be configured in `/etc/security/limits.conf` or by placing a file in `/etc/security/limits.d`. On CentOS 7 the defaults are `1 024` for the soft limit  and `4 096` for the hard one.

1. Per-service file descriptor limit &mdash; as applied by a service manager like `systemd` on CentOS 7. For example, `samba` has a default limit of `16 384` file descriptors. This can be changed by running `systemctl edit smb` and setting the `LimitNOFILE` value in the `Service` section.

1. `SLURM` node daemon file descriptor limit &mdash; similar to the one above, but defaults to `51 200` in `SLURM`.

The first point above should not be a problem for the Sen2-Agri system. The installer mitigates the second point by placing the following file:

```text
*         hard    nofile      500000
*         soft    nofile      500000
```

in `/etc/security/limits.d/50-open-files.conf`.

The third point may apply under specific situations, e.g. when the input products are located on a Samba share. In that case, you should manually configure the server to increase these limits. A sample override file is the following:

```ini
[Service]
LimitNOFILE=500000
```

The `SLURM` limit is configured by a `systemd` override file saved in `/etc/systemd/system/slurmd.service.d/override.conf`, with the following contents:

```ini
[Service]
LimitNOFILE=512000
```

Note that you might have to reboot the system after changing these values.

## Does the Sen2-Agri installer change any system settings?

Yes, the installer:

* switches SELinux to the permissive mode. There is no technical reason for that, but it has proven somewhat problematic in the past. A determined administrator should be able to re-enable it and make sure that everything is working.
* disables the firewall. The main reason for it is that `SLURM` uses dynamic port allocation. The port range can be restricted, but the Sen2-Agri system does not ship with configuration for this.
* increases the open file limits. For more information about this, see [this section](#the-l4a-and-l4b-processors-crash-on-large-sites).
* switches Transparent HugePages to `madvise` mode, effectively disabling them for applications that do not specifically request it. The reason for this is that we noticed `THP` causing a lot of performance problems. This is done by placing the following file under `/usr/lib/tmpfiles.d`:

```text
w   /sys/kernel/mm/transparent_hugepage/enabled -   -   -   -   madvise
```

These settings are reverted after [uninstalling the system](#how-can-i-uninstall-the-sen2-agri-system).

## What other system settings might affect performance?

* on HP hardware with the `HP Dynamic Smart Array` software `RAID` controller we've seen poor disk throughput until we changed the system to the `throughput-performance` mode by running `tuned-adm profile throughput-performance`.

* on a dual-socket system we've seen poor performance caused by the automatic `NUMA` balancing feature. It can be disabled by running `echo 0 | sudo tee /proc/sys/kernel/numa_balancing`.

There are, of course, other resources that are better suited to discus Linux performance tuning.

## How can I reprocess some `L1` products?

The status of `L1` products is stored in the `downloader_history` table. You can check it with the following command:

```bash
sudo -u postgres psql sen2agri -c \
    "select product_name, status_id, status_description \
     from downloader_history \
     inner join downloader_status \
        on downloader_status.id = status_id;"
```

If you want to e.g. reprocess some of the failed product, you can change their status:

```bash
sudo -u postgres psql sen2agri -c \
    "update downloader_history \
     set status_id = 2 -- downloaded
     where status_id = 6 -- processing_failed
       and product_name like 'S2B%';"
```

## I have another issue

To request assistance, you need an account on the [Sentinel-2 for Agriculture portal](http://www.esa-sen2agri.org/). The [forum](http://www.esa-sen2agri.org/forum/?view=forum&id=2) is accessible to logged-in users.