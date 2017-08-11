# Bandwidth-Meter 

Mikrotik Accountig CGI is required.

Mikrotik setup:
  * ip accounting set enabled=yes account-local-traffic=no
  * ip accounting web-access set accessible-via-web=yes 

Optional:
  * If you don't want traffic inside local network on graphs:
    * ip accounting set account-local-traffic=no
   
