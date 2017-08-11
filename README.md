# Bandwidth-Meter 

Mikrotik Accountig CGI and API user is required.

Mikrotik setup:
  * Enable Accounting and start CGI service:
    * ip accounting set enabled=yes account-local-traffic=no
    * ip accounting web-access set accessible-via-web=yes
    
  * Enable API access and add read user:
    *  ip service set api disabled=no
    *  user add name=api_read group=read password=12345


Optional:
* If you don't want local network traffic displayed on graphs:
  * ip accounting set account-local-traffic=no
