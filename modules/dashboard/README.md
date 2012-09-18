Module: Dashboard
=================

The dashboard hosts widgets that display information about what is happening in the CMS.
A widget might display the last records modified by the user, another might display an
overview of the different records available, another the last comments posted by users.

Widgets can be rearranged, added, removed, and configured.




Event callback: ICanBoogie\HTTP\Dispatcher::dispatch:before
-----------------------------------------------------------

Authenticated users requesting the "/admin" URL are redirected to the dashboard
("/admin/dashbord").




User meta: dashboard.order
--------------------------

JSON encoded order of the dashboard blocks.