
ABOUT THIS MODULE
-----------------

Stale IP addresses clog up your database with useless data, not to 
mention, may be subject to subpoena by legal authorities in some 
jurisdictions.

The IP Anonymize module helps ensure users' privacy by implementing a
retention policy for IP addresses recorded in the database.  IP 
addresses are scrubbed on each cron run according to a configurable
retention period.  For example, you may wish to preserve IP addresses 
for a short while for purposes of identifying spam.

IP Anonymize cannot guarantee anonymity, as IP addresses are recorded at
least temporarily, and may also be logged elsewhere in the system such 
as webserver error logs.  For more robust anonymization, Cryptolog
module can be used to replace client IP addresses with ephemeral
identifiers.

HOW TO INSTALL
--------------

Place the ip_anon directory in your modules directory, enable the module 
at admin/modules and go to admin/config/people/ip_anon to configure it.
