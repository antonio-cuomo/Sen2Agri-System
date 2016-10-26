#!/bin/sh

systemctl stop sen2agri-executor sen2agri-orchestrator sen2agri-http-listener sen2agri-sentinel-downloader sen2agri-landsat-downloader sen2agri-demmaccs sen2agri-sentinel-downloader.timer sen2agri-landsat-downloader.timer sen2agri-demmaccs.timer sen2agri-monitor-agent

yum -y install python-beautifulsoup4

rpm -Uvh --force ../rpm_binaries/*.rpm

for file in migrations/*.sql
do
    cat "$file" | su -l postgres -c "psql sen2agri"
done

systemctl daemon-reload

systemctl start sen2agri-executor sen2agri-orchestrator sen2agri-http-listener sen2agri-sentinel-downloader sen2agri-landsat-downloader sen2agri-demmaccs sen2agri-sentinel-downloader.timer sen2agri-landsat-downloader.timer sen2agri-demmaccs.timer sen2agri-monitor-agent